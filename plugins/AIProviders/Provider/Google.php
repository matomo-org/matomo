<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders\Provider;

use Piwik\Plugins\AIProviders\AIConversationRequest;
use Piwik\Plugins\AIProviders\AIConversationResponse;
use Piwik\Plugins\AIProviders\AIProviderResponse;
use Piwik\Plugins\AIProviders\AIRequest;
use Piwik\Plugins\AIProviders\CanonicalMessage;

/**
 * @phpstan-import-type CanonicalMessageArray from CanonicalMessage
 * @phpstan-import-type CanonicalContentBlockArray from CanonicalMessage
 * @phpstan-import-type ToolCatalogEntryArray from AIConversationRequest
 */
class Google extends AIProvider
{
    private const DEFAULT_MODEL = 'gemini-3.1-flash-lite';

    public function __construct()
    {
        parent::__construct(
            'google',
            'Google',
            'AIProviders_GoogleDefaultModelDescription',
            false
        );
    }

    public function getDefaultEndpointUrl(): string
    {
        return $this->getEndpointUrlForModel($this->getDefaultModel());
    }

    public function getDefaultModel(): string
    {
        return self::DEFAULT_MODEL;
    }

    /**
     * Custom Google chat completion method.
     * @see https://ai.google.dev/gemini-api/docs/text-generation
     * @param array<string, string> $configuration
     */
    public function complete(AIRequest $request, array $configuration): AIProviderResponse
    {
        $model = $this->resolveModel($request);

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $request->getUserPrompt(),
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'maxOutputTokens' => $request->getMaxTokens(),
                'temperature' => $request->getTemperature(),
                'thinkingConfig' => [
                    'thinkingBudget' => $this->wantsThinking($request) ? $this->thinkingBudget($request) : 0,
                ],
            ],
        ];

        if ($request->isJsonResponse()) {
            $payload['generationConfig']['responseMimeType'] = 'application/json';
        }

        $systemPrompt = $this->getSystemPrompt($request);
        if ($systemPrompt !== null && $systemPrompt !== '') {
            $payload['systemInstruction'] = [
                'parts' => [
                    [
                        'text' => $systemPrompt,
                    ],
                ],
            ];
        }

        $response = $this->sendJsonRequest(
            $this->getEndpointUrlForModel($model),
            [
                'x-goog-api-key' => $this->getApiKey($configuration),
            ],
            $payload
        );

        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';

        return $this->buildResponse(
            $request,
            $model,
            is_string($text) ? $text : '',
            isset($response['usageMetadata']['promptTokenCount']) ? (int) $response['usageMetadata']['promptTokenCount'] : null,
            isset($response['usageMetadata']['candidatesTokenCount']) ? (int) $response['usageMetadata']['candidatesTokenCount'] : null
        );
    }

    /**
     * Validates credentials and reachability with a cheap models listing
     * instead of spending generation tokens.
     *
     * @see https://ai.google.dev/api/models#method:-models.list
     * @param array<string, string> $configuration
     */
    public function verifyConnection(array $configuration): void
    {
        $this->sendGetRequest(
            'https://generativelanguage.googleapis.com/v1beta/models',
            ['x-goog-api-key' => $this->getApiKey($configuration)]
        );
    }

    public function supportsConversations(): bool
    {
        return true;
    }

    /**
     * Runs one conversational round-trip against the Google generateContent API.
     *
     * Google diverges from the canonical shape in two ways that this method
     * reconciles. First, its roles are 'user' and 'model' (not 'assistant'),
     * and canonical 'tool' result messages fold into 'user' messages carrying
     * functionResponse parts. Second, and trickier, Google has no tool-call
     * IDs: a functionResponse correlates with its functionCall purely by
     * function NAME (and order). The canonical tool_result only carries the
     * originating tool_use_id, so this method first walks the history building
     * an id => name map from every assistant tool_use block, then resolves
     * each tool_result's name from that map. On the way out, responses get a
     * synthesized deterministic id per functionCall so the next turn's
     * tool_result can be matched back through the same map.
     *
     * @see https://ai.google.dev/gemini-api/docs/function-calling
     * @param array<string, string> $configuration
     */
    public function converse(AIConversationRequest $request, array $configuration): AIConversationResponse
    {
        $model = $this->resolveConversationModel($request);

        $payload = [
            'contents' => $this->canonicalMessagesToGoogle($request->getMessages()),
            'generationConfig' => [
                'maxOutputTokens' => $request->getMaxTokens(),
                'temperature' => $request->getTemperature(),
            ],
        ];

        $systemPrompt = $request->getSystemPrompt();
        if ($systemPrompt !== null && $systemPrompt !== '') {
            $payload['systemInstruction'] = [
                'parts' => [
                    [
                        'text' => $systemPrompt,
                    ],
                ],
            ];
        }

        $tools = $this->toolCatalogToGoogle($request->getTools());
        if ($tools !== null) {
            $payload['tools'] = $tools;
        }

        $response = $this->sendJsonRequest(
            $this->getEndpointUrlForModel($model),
            [
                'x-goog-api-key' => $this->getApiKey($configuration),
            ],
            $payload,
            $request->getTimeoutSeconds()
        );

        $parts = is_array($response['candidates'][0]['content']['parts'] ?? null)
            ? $response['candidates'][0]['content']['parts']
            : [];
        $finishReason = is_string($response['candidates'][0]['finishReason'] ?? null)
            ? $response['candidates'][0]['finishReason']
            : '';

        $content = $this->googlePartsToCanonical($parts);
        $stopReason = $this->resolveStopReason($content, $finishReason);

        return $this->buildConversationResponse(
            $model,
            $content,
            $stopReason,
            isset($response['usageMetadata']['promptTokenCount']) ? (int) $response['usageMetadata']['promptTokenCount'] : null,
            isset($response['usageMetadata']['candidatesTokenCount']) ? (int) $response['usageMetadata']['candidatesTokenCount'] : null
        );
    }

    /**
     * @param list<CanonicalMessageArray> $messages canonical messages
     * @return list<array{role: string, parts: list<array<string, mixed>>}>
     */
    private function canonicalMessagesToGoogle(array $messages): array
    {
        // Google correlates tool results to tool calls by function name, not
        // id, so build an id => name map from all tool_use blocks first.
        $toolUseNamesById = $this->buildToolUseNameLookup($messages);

        $contents = [];
        foreach ($messages as $message) {
            $role = $message['role'];

            if ($role === 'assistant') {
                $parts = $this->assistantBlocksToGoogleParts($message['content']);
                $contents[] = ['role' => 'model', 'parts' => $parts];
                continue;
            }

            if ($role === 'tool') {
                $parts = $this->toolResultBlocksToGoogleParts($message['content'], $toolUseNamesById);
                $contents[] = ['role' => 'user', 'parts' => $parts];
                continue;
            }

            // 'user' and any unknown role fold into a user message of text parts.
            $parts = [];
            foreach ($message['content'] as $block) {
                if (($block['type'] ?? null) === 'text' && is_string($block['text'] ?? null)) {
                    $parts[] = ['text' => $block['text']];
                }
            }
            $contents[] = ['role' => 'user', 'parts' => $parts];
        }

        return $contents;
    }

    /**
     * Builds the tool_use id => name lookup used to resolve functionResponse
     * names, since the canonical tool_result only carries the tool_use_id.
     *
     * @param list<CanonicalMessageArray> $messages
     * @return array<string, string>
     */
    private function buildToolUseNameLookup(array $messages): array
    {
        $lookup = [];
        foreach ($messages as $message) {
            foreach (CanonicalMessage::toolUseBlocks($message['content']) as $block) {
                $lookup[$block['id']] = $block['name'];
            }
        }

        return $lookup;
    }

    /**
     * @param list<CanonicalContentBlockArray> $blocks canonical assistant content blocks
     * @return list<array<string, mixed>>
     */
    private function assistantBlocksToGoogleParts(array $blocks): array
    {
        $parts = [];
        foreach ($blocks as $block) {
            $type = $block['type'] ?? null;

            if ($type === 'text' && is_string($block['text'] ?? null)) {
                $parts[] = ['text' => $block['text']];
                continue;
            }

            if ($type === 'tool_use') {
                $name = $block['name'] ?? null;
                $input = $block['input'] ?? [];
                if (!is_string($name) || !is_array($input)) {
                    continue;
                }
                // functionCall.args must be a JSON object even when empty;
                // json_decode collapses `{}` to `[]`, so coerce empty inputs
                // back to stdClass so json_encode produces `{}` again.
                $parts[] = [
                    'functionCall' => [
                        'name' => $name,
                        'args' => $input === [] ? new \stdClass() : $input,
                    ],
                ];
            }
        }

        return $parts;
    }

    /**
     * Translates canonical tool_result blocks into Google functionResponse
     * parts, resolving each result's function name from the id => name lookup.
     *
     * @param list<CanonicalContentBlockArray> $blocks canonical tool_result blocks
     * @param array<string, string> $toolUseNamesById
     * @return list<array<string, mixed>>
     */
    private function toolResultBlocksToGoogleParts(array $blocks, array $toolUseNamesById): array
    {
        $parts = [];
        foreach ($blocks as $block) {
            if (($block['type'] ?? null) !== 'tool_result') {
                continue;
            }

            $toolUseId = $block['tool_use_id'] ?? null;
            // Google correlates by name; recover it from the prior tool_use
            // block, falling back to a stable placeholder when unknown.
            $name = is_string($toolUseId) && isset($toolUseNamesById[$toolUseId])
                ? $toolUseNamesById[$toolUseId]
                : 'unknown';

            $structured = is_array($block['structuredContent'] ?? null) ? $block['structuredContent'] : null;
            $mcpContent = is_array($block['content'] ?? null) ? $block['content'] : [];

            $parts[] = [
                'functionResponse' => [
                    'name' => $name,
                    'response' => $this->toolResultResponseObject($structured, $mcpContent, !empty($block['is_error'])),
                ],
            ];
        }

        return $parts;
    }

    /**
     * Google expects functionResponse.response to be a JSON object. Structured
     * output is used verbatim when present; otherwise the MCP content blocks
     * are folded into a single {content: ...} object, with non-text blocks
     * JSON-stringified so their data still reaches the model. An error flag is
     * surfaced as {error: true}.
     *
     * @param array<string, mixed>|null $structured
     * @param list<array<string, mixed>> $mcpContent
     * @return array<string, mixed>
     */
    private function toolResultResponseObject(?array $structured, array $mcpContent, bool $isError): array
    {
        if ($structured !== null) {
            $response = $structured;
        } else {
            $texts = [];
            foreach ($mcpContent as $block) {
                if (!is_array($block)) {
                    continue;
                }
                if (($block['type'] ?? null) === 'text' && is_string($block['text'] ?? null)) {
                    $texts[] = $block['text'];
                    continue;
                }
                $serialised = json_encode($block);
                if ($serialised !== false) {
                    $texts[] = $serialised;
                }
            }
            $response = ['content' => implode("\n", $texts)];
        }

        if ($isError) {
            $response['error'] = true;
        }

        return $response;
    }

    /**
     * @param list<ToolCatalogEntryArray> $tools
     * @return list<array{functionDeclarations: list<array{name: string, description: string, parameters: array<string, mixed>}>}>|null
     */
    private function toolCatalogToGoogle(array $tools): ?array
    {
        if ($tools === []) {
            return null;
        }

        $declarations = [];
        foreach ($tools as $tool) {
            $declarations[] = [
                'name' => $tool['name'],
                'description' => $tool['description'],
                'parameters' => $this->googleParameterSchema($tool['inputSchema']),
            ];
        }

        return [['functionDeclarations' => $declarations]];
    }

    /**
     * Google's function-declaration parameters accept only a restricted subset
     * of the OpenAPI 3.0 schema and reject standard JSON Schema keywords such as
     * `additionalProperties` or `$schema` — and not just at the top level: it
     * rejects them at every nesting depth (e.g. inside `properties[...]`). The
     * shared {@see toToolParametersObjectSchema} only normalises the top level,
     * which is all OpenAI needs, so Google layers a recursive strip on top.
     *
     * The removed keywords are validation hints only; the tool server
     * re-validates arguments when the tool actually runs, so dropping them keeps
     * the tool callable without loosening real enforcement.
     *
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    private function googleParameterSchema(array $schema): array
    {
        return $this->stripGoogleUnsupportedKeywords(
            $this->toToolParametersObjectSchema($schema)
        );
    }

    /**
     * Recursively removes JSON Schema keywords Google rejects anywhere in the
     * tree, walking into `properties`, `items`, and `anyOf`/`oneOf`/`allOf`
     * branches so nested object/array schemas are cleaned too.
     *
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    private function stripGoogleUnsupportedKeywords(array $schema): array
    {
        unset(
            $schema['additionalProperties'],
            $schema['$schema'],
            $schema['$id'],
            $schema['$ref'],
            $schema['$defs'],
            $schema['definitions'],
            $schema['const'],
            $schema['patternProperties'],
            $schema['unevaluatedProperties']
        );

        foreach ($schema as $key => $value) {
            if (is_array($value)) {
                $schema[$key] = $this->stripGoogleUnsupportedKeywords($value);
            }
        }

        return $schema;
    }

    /**
     * @param list<mixed> $parts Google candidate content parts
     * @return list<CanonicalContentBlockArray> canonical assistant content blocks
     */
    private function googlePartsToCanonical(array $parts): array
    {
        $canonical = [];
        foreach ($parts as $index => $part) {
            if (!is_array($part)) {
                continue;
            }

            if (is_string($part['text'] ?? null) && $part['text'] !== '') {
                $canonical[] = ['type' => 'text', 'text' => $part['text']];
                continue;
            }

            if (is_array($part['functionCall'] ?? null)) {
                $call = $part['functionCall'];
                $name = is_string($call['name'] ?? null) ? $call['name'] : '';
                $args = is_array($call['args'] ?? null) ? $call['args'] : [];
                $normalizedInput = array_filter($args, function ($key) {
                    return is_string($key);
                }, ARRAY_FILTER_USE_KEY);
                // Google supplies no id; synthesize a deterministic one so the
                // caller can echo it back and the id => name resolver can
                // recover the function name on the next turn.
                $canonical[] = [
                    'type' => 'tool_use',
                    'id' => sprintf('google-%d-%s', $index, $name),
                    'name' => $name,
                    'input' => $normalizedInput,
                ];
            }
        }

        return $canonical;
    }

    /**
     * Any functionCall in the turn means the model wants a tool run; otherwise
     * the Google finishReason maps onto the canonical stop reasons, passing
     * unrecognised values through.
     *
     * @param list<CanonicalContentBlockArray> $content canonical assistant content blocks
     */
    private function resolveStopReason(array $content, string $finishReason): string
    {
        foreach ($content as $block) {
            if (($block['type'] ?? null) === 'tool_use') {
                return AIConversationResponse::STOP_TOOL_USE;
            }
        }

        switch ($finishReason) {
            case 'STOP':
                return AIConversationResponse::STOP_END_TURN;
            case 'MAX_TOKENS':
                return AIConversationResponse::STOP_MAX_TOKENS;
            case 'SAFETY':
            case 'RECITATION':
                return AIConversationResponse::STOP_GUARDRAIL_INTERVENED;
            default:
                return $finishReason;
        }
    }

    private function getEndpointUrlForModel(string $model): string
    {
        return sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
            $model
        );
    }
}
