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
use Piwik\Plugins\AIProviders\WebSearchUsage;

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

    // Anthropic pins its server tools to a dated type. The basic version is used
    // deliberately: later versions default `allowed_callers` to code execution,
    // which turns one completion into a code-execution run.
    private const WEB_SEARCH_TOOL_TYPE = 'web_search_20250305';
    private const WEB_SEARCH_TOOL_NAME = 'web_search';

    /**
     * Searches allowed per grounded request. This is the cost cap: every search
     * is billed as a server tool request on top of the retrieved page content,
     * which lands in the prompt as input tokens.
     */
    private const WEB_SEARCH_MAX_USES = 5;

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
     *
     * With web search on, `stop_reason` can come back as `pause_turn`: Anthropic
     * ended the turn mid-search and expects the message to be sent back to
     * resume. This method is deliberately single-round-trip, so the reason is
     * passed through unchanged and the partial answer is returned.
     *
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

        if ($this->wantsWebSearch($request)) {
            // No tool_choice: it defaults to "auto", so Claude decides whether the
            // prompt needs fresh sources and an unnecessary search is not paid for.
            $payload['tools'] = [[
                'type' => self::WEB_SEARCH_TOOL_TYPE,
                'name' => self::WEB_SEARCH_TOOL_NAME,
                'max_uses' => self::WEB_SEARCH_MAX_USES,
            ]];
        }

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
            $payload,
            $this->completionTimeoutSeconds($request)
        );

        $text = $this->concatenateTextBlocks($response['content'] ?? []);
        $stopReason = is_string($response['stop_reason'] ?? null) ? $response['stop_reason'] : null;

        return $this->buildResponse(
            $request,
            $model,
            $text,
            isset($response['usage']['input_tokens']) ? (int) $response['usage']['input_tokens'] : null,
            isset($response['usage']['output_tokens']) ? (int) $response['usage']['output_tokens'] : null,
            $stopReason,
            $this->parseWebSearchUsage($request, $response)
        );
    }

    public function supportsWebSearch(): bool
    {
        return true;
    }

    /**
     * Concatenates every `text` block in order. The answer is not a single
     * block: extended thinking puts a `thinking` block first, and web search
     * splits the prose around `server_tool_use`/`web_search_tool_result` blocks.
     *
     * Joined with nothing between them, because a grounded answer is split
     * mid-sentence at a citation boundary and each fragment carries its own
     * spacing. Inserting a separator would break the prose, and in JSON mode a
     * newline landing inside a string value makes the response undecodable.
     *
     * @param mixed $content
     */
    private function concatenateTextBlocks($content): string
    {
        if (!is_array($content)) {
            return '';
        }

        $texts = [];
        foreach ($content as $block) {
            if (!is_array($block)) {
                continue;
            }

            if (($block['type'] ?? null) === 'text' && is_string($block['text'] ?? null)) {
                $texts[] = $block['text'];
            }
        }

        return implode('', $texts);
    }

    /**
     * Reads the grounding trail out of the message: queries from
     * `server_tool_use` blocks, sources from `web_search_tool_result` rows and
     * from the `web_search_result_location` citations on text blocks (cited ones
     * first), and the search count from `usage.server_tool_use.web_search_requests`.
     *
     * @param array<string, mixed> $response
     */
    private function parseWebSearchUsage(AIRequest $request, array $response): WebSearchUsage
    {
        if (!$this->wantsWebSearch($request)) {
            return WebSearchUsage::none();
        }

        $content = is_array($response['content'] ?? null) ? $response['content'] : [];

        $queries = [];
        $cited = [];
        $returned = [];

        foreach ($content as $block) {
            if (!is_array($block)) {
                continue;
            }

            $type = $block['type'] ?? null;

            if ($type === 'server_tool_use' && ($block['name'] ?? null) === self::WEB_SEARCH_TOOL_NAME) {
                $query = $block['input']['query'] ?? null;
                if (is_string($query)) {
                    $queries[] = $query;
                }
                continue;
            }

            if ($type === 'web_search_tool_result') {
                // A failed search (e.g. max_uses_exceeded) still returns HTTP 200 with
                // `content` set to a single web_search_tool_result_error object rather
                // than a list of rows; its scalar members fail the is_array() test below.
                foreach ((is_array($block['content'] ?? null) ? $block['content'] : []) as $row) {
                    if (is_array($row) && ($row['type'] ?? null) === 'web_search_result') {
                        $returned[] = ['url' => $row['url'] ?? null, 'title' => $row['title'] ?? null];
                    }
                }
                continue;
            }

            if ($type === 'text' && is_array($block['citations'] ?? null)) {
                foreach ($block['citations'] as $citation) {
                    if (!is_array($citation) || ($citation['type'] ?? null) !== 'web_search_result_location') {
                        continue;
                    }
                    $cited[] = ['url' => $citation['url'] ?? null, 'title' => $citation['title'] ?? null];
                }
            }
        }

        $requestCount = $response['usage']['server_tool_use']['web_search_requests'] ?? null;

        return WebSearchUsage::fromProviderData(
            array_merge($cited, $returned),
            // Counted on the deduplicated queries so the fallback cannot exceed
            // what getWebSearchQueries() reports.
            is_numeric($requestCount) ? (int) $requestCount : count(array_unique($queries)),
            $queries
        );
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
