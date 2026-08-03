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
class Anthropic extends AIProvider
{
    private const DEFAULT_MODEL = 'claude-haiku-4-5';
    private const ANTHROPIC_VERSION = '2023-06-01';

    // Anthropic requires budget_tokens >= 1024 and strictly < max_tokens; this
    // headroom keeps room for the visible answer on top of the thinking budget.
    private const THINKING_MIN_BUDGET = 1024;
    private const THINKING_OUTPUT_HEADROOM = 1024;

    public function __construct()
    {
        parent::__construct(
            'anthropic',
            'Anthropic',
            'AIProviders_AnthropicDefaultModelDescription',
            false
        );
    }

    public function getDefaultEndpointUrl(): string
    {
        return 'https://api.anthropic.com/v1/messages';
    }

    public function getDefaultModel(): string
    {
        return self::DEFAULT_MODEL;
    }

    /**
     * Custom Anthropic chat completion method.
     * @see https://platform.claude.com/docs/en/api/messages/create
     * @param array<string, string> $configuration
     */
    public function complete(AIRequest $request, array $configuration): AIProviderResponse
    {
        $model = $this->resolveModel($request);

        $payload = [
            'model' => $model,
            'max_tokens' => $request->getMaxTokens(),
            'temperature' => $request->getTemperature(),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $request->getUserPrompt(),
                ],
            ],
        ];

        $this->applyThinking($payload, $request);

        $systemPrompt = $this->getSystemPrompt($request);
        if ($systemPrompt !== null && $systemPrompt !== '') {
            $payload['system'] = $systemPrompt;
        }

        $response = $this->sendJsonRequest(
            $this->getEndpointUrl($configuration),
            [
                'anthropic-version' => self::ANTHROPIC_VERSION,
                'x-api-key' => $this->getApiKey($configuration),
            ],
            $payload
        );

        $text = $this->getFirstTextBlock($response['content'] ?? []);
        $stopReason = is_string($response['stop_reason'] ?? null) ? $response['stop_reason'] : null;

        return $this->buildResponse(
            $request,
            $model,
            $text,
            isset($response['usage']['input_tokens']) ? (int) $response['usage']['input_tokens'] : null,
            isset($response['usage']['output_tokens']) ? (int) $response['usage']['output_tokens'] : null,
            $stopReason
        );
    }

    /**
     * With thinking enabled, anthropic returns the text block at a different index.
     *
     * @param mixed $content
     */
    private function getFirstTextBlock($content): string
    {
        if (!is_array($content)) {
            return '';
        }

        foreach ($content as $block) {
            if (!is_array($block)) {
                continue;
            }

            if (($block['type'] ?? null) === 'text' && is_string($block['text'] ?? null)) {
                return $block['text'];
            }
        }

        return '';
    }

    /**
     * Turns on Anthropic extended thinking when the request resolves to the
     * thinking capability. Enabling it has three wire requirements: the
     * `thinking` block with a budget of at least 1024 tokens, a `max_tokens`
     * strictly larger than that budget (bumped here when needed), and no custom
     * `temperature` (only the default is allowed with thinking), so it is
     * dropped. Instant requests leave the payload untouched.
     *
     * @param array<string, mixed> $payload
     */
    private function applyThinking(array &$payload, AIRequest $request): void
    {
        if (!$this->wantsThinking($request)) {
            return;
        }

        $budget = max(self::THINKING_MIN_BUDGET, $this->thinkingBudget($request));

        $payload['thinking'] = ['type' => 'enabled', 'budget_tokens' => $budget];
        $payload['max_tokens'] = max($request->getMaxTokens(), $budget + self::THINKING_OUTPUT_HEADROOM);
        unset($payload['temperature']);
    }

    /**
     * Validates credentials and reachability with a cheap models listing
     * (`GET /v1/models`) instead of spending generation tokens.
     *
     * @see https://platform.claude.com/docs/en/api/models-list
     * @param array<string, string> $configuration
     */
    public function verifyConnection(array $configuration): void
    {
        $modelsEndpoint = preg_replace(
            '#/messages/?$#',
            '/models',
            $this->getEndpointUrl($configuration)
        ) ?? $this->getEndpointUrl($configuration);

        $this->sendGetRequest(
            $modelsEndpoint,
            [
                'anthropic-version' => self::ANTHROPIC_VERSION,
                'x-api-key' => $this->getApiKey($configuration),
            ]
        );
    }

    public function supportsConversations(): bool
    {
        return true;
    }

    /**
     * Runs one conversational round-trip against the Anthropic Messages API.
     *
     * The canonical message shape maps almost one-to-one onto the Anthropic
     * wire format: roles, text/tool_use/tool_result blocks, and the
     * end_turn/tool_use/max_tokens/stop_sequence stop reasons all match.
     * Canonical 'tool' messages fold into 'user' messages carrying
     * tool_result blocks; consecutive same-role messages are valid input
     * (the API combines them), so no merging is needed.
     *
     * @see https://platform.claude.com/docs/en/api/messages/create
     * @param array<string, string> $configuration
     */
    public function converse(AIConversationRequest $request, array $configuration): AIConversationResponse
    {
        $model = $this->resolveConversationModel($request);

        $payload = [
            'model' => $model,
            'max_tokens' => $request->getMaxTokens(),
            'temperature' => $request->getTemperature(),
            'messages' => $this->canonicalMessagesToAnthropic($request->getMessages()),
        ];

        $systemPrompt = $request->getSystemPrompt();
        if ($systemPrompt !== null && $systemPrompt !== '') {
            $payload['system'] = $systemPrompt;
        }

        $tools = $this->toolCatalogToAnthropic($request->getTools());
        if ($tools !== null) {
            $payload['tools'] = $tools;
        }

        $response = $this->sendJsonRequest(
            $this->getEndpointUrl($configuration),
            [
                'anthropic-version' => self::ANTHROPIC_VERSION,
                'x-api-key' => $this->getApiKey($configuration),
            ],
            $payload,
            $request->getTimeoutSeconds()
        );

        $rawContent = is_array($response['content'] ?? null) ? $response['content'] : [];
        $stopReason = is_string($response['stop_reason'] ?? null) ? $response['stop_reason'] : '';

        return $this->buildConversationResponse(
            $model,
            $this->anthropicContentToCanonical($rawContent),
            $stopReason,
            isset($response['usage']['input_tokens']) ? (int) $response['usage']['input_tokens'] : null,
            isset($response['usage']['output_tokens']) ? (int) $response['usage']['output_tokens'] : null
        );
    }

    /**
     * @param list<CanonicalMessageArray> $messages canonical messages
     * @return list<array{role: string, content: list<array<string, mixed>>}>
     */
    private function canonicalMessagesToAnthropic(array $messages): array
    {
        $anthropicMessages = [];
        foreach ($messages as $message) {
            // Anthropic uses two roles; canonical 'tool' rows carry their
            // tool_result blocks under role=user so the tool_use/tool_result
            // pairing survives the round trip.
            $role = $message['role'] === 'assistant' ? 'assistant' : 'user';

            $blocks = [];
            foreach ($message['content'] as $block) {
                $translated = $this->canonicalBlockToAnthropic($block);
                if ($translated !== null) {
                    $blocks[] = $translated;
                }
            }

            $anthropicMessages[] = ['role' => $role, 'content' => $blocks];
        }

        return $anthropicMessages;
    }

    /**
     * @param array<string, mixed> $block
     * @return array<string, mixed>|null null when the block shape is unrecognised
     */
    private function canonicalBlockToAnthropic(array $block): ?array
    {
        $type = $block['type'] ?? null;

        if ($type === 'text' && is_string($block['text'] ?? null)) {
            return ['type' => 'text', 'text' => $block['text']];
        }

        if ($type === 'tool_use') {
            $id = $block['id'] ?? null;
            $name = $block['name'] ?? null;
            $input = $block['input'] ?? [];
            if (!is_string($id) || !is_string($name) || !is_array($input)) {
                return null;
            }

            // tool_use.input must be a JSON object even when empty;
            // json_decode collapses `{}` to `[]`, so coerce empty inputs back
            // to stdClass so json_encode produces `{}` again.
            return [
                'type' => 'tool_use',
                'id' => $id,
                'name' => $name,
                'input' => $input === [] ? new \stdClass() : $input,
            ];
        }

        if ($type === 'tool_result') {
            $toolUseId = $block['tool_use_id'] ?? null;
            if (!is_string($toolUseId)) {
                return null;
            }
            $structured = is_array($block['structuredContent'] ?? null) ? $block['structuredContent'] : null;
            $mcpContent = is_array($block['content'] ?? null) ? $block['content'] : [];

            return [
                'type' => 'tool_result',
                'tool_use_id' => $toolUseId,
                'content' => $this->toolResultContentToAnthropic($structured, $mcpContent),
                'is_error' => !empty($block['is_error']),
            ];
        }

        return null;
    }

    /**
     * Anthropic tool_result content is a list of text/image blocks. The
     * tool's structured output, when present, is serialised into a single
     * text block; otherwise each MCP content block is translated, with
     * non-text blocks JSON-stringified so their data still reaches the model.
     *
     * @param array<string, mixed>|null $structured
     * @param list<array<string, mixed>> $mcpContent
     * @return list<array<string, mixed>>
     */
    private function toolResultContentToAnthropic(?array $structured, array $mcpContent): array
    {
        if ($structured !== null) {
            $serialised = json_encode($structured);

            return [['type' => 'text', 'text' => $serialised === false ? '' : $serialised]];
        }

        $blocks = [];
        foreach ($mcpContent as $block) {
            if (!is_array($block)) {
                continue;
            }
            if (($block['type'] ?? null) === 'text' && is_string($block['text'] ?? null)) {
                $blocks[] = ['type' => 'text', 'text' => $block['text']];
                continue;
            }
            $serialised = json_encode($block);
            if ($serialised !== false) {
                $blocks[] = ['type' => 'text', 'text' => $serialised];
            }
        }

        if ($blocks === []) {
            $blocks[] = ['type' => 'text', 'text' => ''];
        }

        return $blocks;
    }

    /**
     * @param list<ToolCatalogEntryArray> $tools
     * @return list<array{name: string, description: string, input_schema: array<string, mixed>}>|null
     */
    private function toolCatalogToAnthropic(array $tools): ?array
    {
        if ($tools === []) {
            return null;
        }

        $anthropicTools = [];
        foreach ($tools as $tool) {
            $anthropicTools[] = [
                'name' => $tool['name'],
                'description' => $tool['description'],
                'input_schema' => $tool['inputSchema'],
            ];
        }

        return $anthropicTools;
    }

    /**
     * @param list<mixed> $content Anthropic assistant content blocks
     * @return list<CanonicalContentBlockArray> canonical assistant content blocks
     */
    private function anthropicContentToCanonical(array $content): array
    {
        $canonical = [];
        foreach ($content as $block) {
            if (!is_array($block)) {
                continue;
            }

            if (($block['type'] ?? null) === 'text' && is_string($block['text'] ?? null)) {
                $canonical[] = ['type' => 'text', 'text' => $block['text']];
                continue;
            }

            if (($block['type'] ?? null) === 'tool_use') {
                $id = $block['id'] ?? null;
                $name = $block['name'] ?? null;
                $input = $block['input'] ?? [];
                if (!is_string($id) || !is_string($name) || !is_array($input)) {
                    continue;
                }
                $normalizedInput = [];
                foreach ($input as $key => $value) {
                    if (is_string($key)) {
                        $normalizedInput[$key] = $value;
                    }
                }
                $canonical[] = [
                    'type' => 'tool_use',
                    'id' => $id,
                    'name' => $name,
                    'input' => $normalizedInput,
                ];
                continue;
            }

            // Other block shapes (thinking, server tool results, …) are
            // dropped until a canonical block type exists for them.
        }

        return $canonical;
    }
}
