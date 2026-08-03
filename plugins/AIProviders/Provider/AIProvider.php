<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders\Provider;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Development;
use Piwik\Http;
use Piwik\Log\LoggerInterface;
use Piwik\Plugins\AIProviders\AIConversationRequest;
use Piwik\Plugins\AIProviders\AIConversationResponse;
use Piwik\Plugins\AIProviders\AIProviderResponse;
use Piwik\Plugins\AIProviders\AIRequest;
use Piwik\Plugins\AIProviders\CanonicalMessage;
use Piwik\Plugins\AIProviders\Exception\AIProviderClientException;
use Piwik\Plugins\AIProviders\Exception\AIProviderException;
use Piwik\Plugins\AIProviders\Exception\AIProviderServerException;
use Piwik\Plugins\AIProviders\Model\Configuration;

/**
 * @phpstan-import-type CanonicalMessageArray from CanonicalMessage
 * @phpstan-import-type CanonicalContentBlockArray from CanonicalMessage
 * @phpstan-import-type ToolCatalogEntryArray from AIConversationRequest
 */
abstract class AIProvider
{
    private const TRANSIENT_ERROR_RETRY_DELAYS = [1, 2];

    /**
     * HTTP status codes that indicate a transient provider failure worth
     * retrying. Keep 408/502 for AWS Bedrock and 529 for Anthropic overloads.
     * Other 4xx responses (e.g. 400/401/403/404/413) are permanent and are
     * not retried.
     */
    private const TRANSIENT_ERROR_STATUS_CODES = [408, 429, 500, 502, 503, 504, 529];

    private const JSON_RESPONSE_INSTRUCTION = 'Respond with a single valid JSON object and nothing else. Do not wrap it in Markdown code fences.';

    /**
     * Token budget given to provider-specific "thinking" when the thinking
     * capability is requested without an explicit per-request budget. Kept well
     * above Anthropic's 1024-token minimum so it is valid for every provider.
     */
    protected const DEFAULT_THINKING_BUDGET = 2048;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var bool
     */
    private $supportsCustomEndpoint;

    /**
     * @var int|null
     */
    private $lastRequestExecutionTimeMs = null;

    public function __construct(
        string $id,
        string $name,
        string $description,
        bool $supportsCustomEndpoint = false
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->supportsCustomEndpoint = $supportsCustomEndpoint;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function supportsCustomEndpoint(): bool
    {
        return $this->supportsCustomEndpoint;
    }

    public function supportsFipsEndpoint(): bool
    {
        return false;
    }

    /**
     * Returns whether the endpoint field is the request URL itself, and can
     * therefore name any host. Return false only when the field is expanded into
     * a host the provider controls, the way a region is (see Bedrock).
     *
     * This decides whether a centrally managed API key may be paired with an
     * endpoint configured on the instance, so a provider whose field does reach
     * the request URL must not return false.
     */
    public function endpointFieldRequiresUrl(): bool
    {
        return true;
    }

    public function getDefaultEndpointUrl(): string
    {
        return '';
    }

    /**
     * Expands shorthand endpoint input (e.g. a bare AWS region) into the URL
     * to validate and store. Unrecognized values pass through unchanged so
     * URL validation still rejects them.
     */
    public function normalizeEndpointUrl(string $endpointUrl): string
    {
        return $endpointUrl;
    }

    /** Translation key for the endpoint field's label in the admin UI. */
    public function getEndpointFieldTitle(): string
    {
        return 'AIProviders_EndpointUrl';
    }

    /** Translation key for the endpoint field's placeholder in the admin UI. */
    public function getEndpointFieldPlaceholder(): string
    {
        return 'AIProviders_EndpointUrlPlaceholder';
    }

    /**
     * Translation key for the error shown when the settings form rejects the
     * endpoint field's value. Takes the provider name as its only argument.
     * Providers whose field is not a URL override this to name what they do
     * expect, the way they already override the field's label.
     */
    public function getEndpointFieldErrorMessage(): string
    {
        return 'AIProviders_ErrorInvalidEndpointUrl';
    }

    public function getDefaultModel(): string
    {
        return '';
    }

    protected function isTrustedRequestHost(string $host): bool
    {
        return false;
    }

    /**
     * Completes the given request using the provider.
     *
     * Implementations should honor the request's system prompt, model,
     * max tokens, and temperature, and populate token usage on the response
     * when the provider reports it.
     *
     * @param array{apiKey?: string, endpointUrl?: string, model?: string, useFipsEndpoint?: bool} $configuration
     */
    abstract public function complete(AIRequest $request, array $configuration): AIProviderResponse;

    /**
     * Returns whether the provider implements {@link converse()}: multi-turn
     * conversations with tool calling. Callers should check
     * {@link \Piwik\Plugins\AIProviders\AIProviderService::canConverse()}
     * before offering conversational features.
     */
    public function supportsConversations(): bool
    {
        return false;
    }

    /**
     * Runs one conversational round-trip and returns the assistant's turn in
     * the canonical shape (see {@link \Piwik\Plugins\AIProviders\CanonicalMessage}).
     *
     * Implementations must translate the canonical messages and the tool
     * catalogue to their wire format, honour the request's system prompt,
     * model, max tokens, temperature, and timeout, and map their stop-reason
     * vocabulary to the AIConversationResponse::STOP_* constants where a
     * mapping exists. Providers that override this method must also override
     * {@link supportsConversations()} to return true.
     *
     * @param array{apiKey?: string, endpointUrl?: string, model?: string, useFipsEndpoint?: bool} $configuration
     */
    public function converse(AIConversationRequest $request, array $configuration): AIConversationResponse
    {
        throw new AIProviderClientException(
            sprintf('%s does not support multi-turn conversations.', $this->getName())
        );
    }

    /**
     * Validates the given connection settings, throwing on failure.
     *
     * Used by the admin "test connection" flow before a configuration is
     * stored. The default implementation proves the full round-trip with a
     * tiny completion; providers that expose a cheaper auth/health endpoint
     * (e.g. a models listing) should override this to avoid spending
     * generation tokens. Returns normally when the connection works.
     *
     * @param array{apiKey?: string, endpointUrl?: string, model?: string, useFipsEndpoint?: bool} $configuration
     */
    public function verifyConnection(array $configuration): void
    {
        $this->verifyConnectionWithCompletion($configuration);
    }

    /**
     * Probes the connection with completion. Providers with a cheaper
     * probe override verifyConnection() but can reuse this.
     *
     * @param array{apiKey?: string, endpointUrl?: string, model?: string, useFipsEndpoint?: bool} $configuration
     */
    protected function verifyConnectionWithCompletion(array $configuration): void
    {
        $request = (new AIRequest('Reply with the single word: OK', 'AIProviders'))
            ->withFeatureKey('test-connection')
            ->withMaxTokens(16);

        $response = $this->complete($request, $configuration);

        if (trim($response->getText()) === '') {
            throw new AIProviderServerException(sprintf('%s returned an empty response.', $this->getName()));
        }
    }

    /**
     * Returns the list of model identifiers the provider can serve with the
     * given configuration, used to populate the model picker in the admin UI.
     * The default implementation returns no models; providers that can discover
     * them (e.g. an OpenAI-compatible `/models` listing) should override this.
     *
     * @param array{apiKey?: string, endpointUrl?: string, model?: string, useFipsEndpoint?: bool} $configuration
     * @return list<string>
     */
    public function listModels(array $configuration): array
    {
        return [];
    }

    /**
     * Returns whether the provider has everything it needs to run completions.
     *
     * Providers that talk to a fixed hosted API require an API key. Providers
     * that support a custom endpoint instead require the endpoint URL; their
     * API key is optional, because local LLM servers (Ollama, LM Studio,
     * llama.cpp, vLLM, …) commonly run with authentication disabled. Providers
     * whose credentials are supplied by the environment (rather than stored
     * configuration) should override this method.
     *
     * @param array{apiKey?: string, endpointUrl?: string, model?: string, useFipsEndpoint?: bool} $configuration
     */
    public function isConfigured(array $configuration): bool
    {
        if ($this->supportsCustomEndpoint()) {
            return trim($configuration['endpointUrl'] ?? '') !== '';
        }

        return trim($configuration['apiKey'] ?? '') !== '';
    }

    /**
     * Returns the model to use for the request, falling back to the provider default.
     */
    protected function resolveModel(AIRequest $request): string
    {
        $model = $request->getModel();

        // The capability level (instant/thinking) is not a different model: each
        // provider's default model handles both, toggled by provider-specific
        // thinking parameters (see wantsThinking()). An explicit per-request
        // model still wins.
        return $model !== null && $model !== '' ? $model : $this->getDefaultModel();
    }

    /**
     * Returns the model to use for the conversation request, falling back to
     * the provider default.
     */
    protected function resolveConversationModel(AIConversationRequest $request): string
    {
        $model = $request->getModel();

        return $model !== null && $model !== '' ? $model : $this->getDefaultModel();
    }

    /**
     * Returns the system prompt for the request, augmented with a JSON-output
     * instruction when JSON mode is requested. Providers should use this rather
     * than reading the request's system prompt directly, so JSON mode works even
     * for providers without a native JSON option (and so the word "JSON" is
     * present, which some providers require to enable their JSON mode).
     */
    protected function getSystemPrompt(AIRequest $request): ?string
    {
        $systemPrompt = $request->getSystemPrompt();

        if (!$request->isJsonResponse()) {
            return $systemPrompt;
        }

        if ($systemPrompt === null || trim($systemPrompt) === '') {
            return self::JSON_RESPONSE_INSTRUCTION;
        }

        return rtrim($systemPrompt) . "\n\n" . self::JSON_RESPONSE_INSTRUCTION;
    }

    /**
     * @param int|null $inputTokens  Prompt tokens reported by the provider, if any.
     * @param int|null $outputTokens Completion tokens reported by the provider, if any.
     */
    protected function buildResponse(
        AIRequest $request,
        string $model,
        string $text,
        ?int $inputTokens = null,
        ?int $outputTokens = null,
        ?string $stopReason = null
    ): AIProviderResponse {
        return new AIProviderResponse(
            $this->getId(),
            $this->getName(),
            $model,
            trim($text),
            $inputTokens,
            $outputTokens,
            $this->getReasoningLevelUsed($request),
            $this->isWebSearchUsed($request),
            $this->lastRequestExecutionTimeMs,
            $stopReason
        );
    }

    /**
     * @param list<CanonicalContentBlockArray> $content canonical assistant content blocks
     * @param int|null $inputTokens  Prompt tokens reported by the provider, if any.
     * @param int|null $outputTokens Completion tokens reported by the provider, if any.
     */
    protected function buildConversationResponse(
        string $model,
        array $content,
        string $stopReason,
        ?int $inputTokens = null,
        ?int $outputTokens = null
    ): AIConversationResponse {
        return new AIConversationResponse(
            $this->getId(),
            $this->getName(),
            $model,
            $content,
            $stopReason,
            $inputTokens,
            $outputTokens,
            $this->lastRequestExecutionTimeMs
        );
    }

    protected function getReasoningLevelUsed(AIRequest $request): string
    {
        // Reported back on the response so callers can see whether the request
        // actually ran with thinking. Providers that enable thinking per the
        // resolved capability level all map onto this single flag.
        return $this->wantsThinking($request) ? Configuration::CAPABILITY_THINKING : AIRequest::REASONING_NONE;
    }

    protected function isWebSearchUsed(AIRequest $request): bool
    {
        // TODO: Implement provider-specific web search/tool configuration for
        // OpenAI, Google, Anthropic, and managed providers separately.
        return false;
    }

    /**
     * Completes a request against an OpenAI-compatible Chat Completions endpoint.
     *
     * Shared by providers that speak the OpenAI `/chat/completions` wire format
     * (system + user messages, `max_tokens`/`temperature`, and a `usage` object
     * with `prompt_tokens`/`completion_tokens`).
     *
     * @param array<string, string> $headers Additional request headers, such as authentication.
     */
    protected function completeChatCompletion(AIRequest $request, string $endpointUrl, array $headers): AIProviderResponse
    {
        $messages = [];

        $systemPrompt = $this->getSystemPrompt($request);
        if ($systemPrompt !== null && $systemPrompt !== '') {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        $messages[] = ['role' => 'user', 'content' => $request->getUserPrompt()];

        $model = $this->resolveModel($request);

        $payload = [
            'model' => $model,
            'messages' => $messages,
        ];
        $payload[$this->chatCompletionTokenLimitField()] = $request->getMaxTokens();
        if ($this->chatCompletionSupportsTemperature()) {
            $payload['temperature'] = $request->getTemperature();
        }

        $payload = array_merge($payload, $this->getExtraChatCompletionPayload($request));

        if ($request->isJsonResponse()) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $response = $this->sendJsonRequest($endpointUrl, $headers, $payload);

        $text = $response['choices'][0]['message']['content'] ?? '';
        $finishReason = is_string($response['choices'][0]['finish_reason'] ?? null)
            ? $response['choices'][0]['finish_reason']
            : null;

        return $this->buildResponse(
            $request,
            $model,
            is_string($text) ? $text : '',
            isset($response['usage']['prompt_tokens']) ? (int) $response['usage']['prompt_tokens'] : null,
            isset($response['usage']['completion_tokens']) ? (int) $response['usage']['completion_tokens'] : null,
            $finishReason
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function getExtraChatCompletionPayload(AIRequest $request): array
    {
        return [];
    }

    /**
     * The Chat Completions field that bounds output length. Defaults to the
     * classic `max_tokens`; OpenAI's reasoning models require
     * `max_completion_tokens` instead.
     */
    protected function chatCompletionTokenLimitField(): string
    {
        return 'max_tokens';
    }

    /**
     * Whether the provider accepts a custom `temperature`. Reasoning models
     * (e.g. OpenAI's gpt-5 family) only allow the default temperature, so they
     * override this to false and the field is omitted.
     */
    protected function chatCompletionSupportsTemperature(): bool
    {
        return true;
    }

    /**
     * Whether this request should run with the provider's "thinking" mode on.
     *
     * The capability level set on the request — or the configured default,
     * applied by {@link \Piwik\Plugins\AIProviders\AIProviderService::complete()}
     * — decides this, but an explicit per-request thinking budget always wins:
     * a budget of 0 forces instant, a positive budget forces thinking. This is
     * how a caller overrides the configured default for a single request (see
     * {@link AIRequest::withThinkingBudget()}).
     */
    protected function wantsThinking(AIRequest $request): bool
    {
        $budget = $request->getThinkingBudget();
        if ($budget !== null) {
            return $budget > 0;
        }

        return $request->getCapabilityLevel() === Configuration::CAPABILITY_THINKING;
    }

    /**
     * The thinking token budget to send when thinking is on: the caller's
     * explicit positive budget, otherwise {@link self::DEFAULT_THINKING_BUDGET}.
     */
    protected function thinkingBudget(AIRequest $request): int
    {
        $budget = $request->getThinkingBudget();

        return $budget !== null && $budget > 0 ? $budget : self::DEFAULT_THINKING_BUDGET;
    }

    /**
     * Runs one conversational round-trip against an OpenAI-compatible Chat
     * Completions endpoint.
     *
     * Shared by providers that speak the OpenAI `/chat/completions` wire format
     * for multi-turn, tool-calling conversations. The canonical message shape
     * is translated to OpenAI `messages` (system/user/assistant/tool roles,
     * assistant `tool_calls` with JSON-string arguments, and one `tool` message
     * per tool result), the tool catalogue to `tools`, and the response's
     * `choices[0]` back to canonical content blocks with stop reasons mapped
     * onto the AIConversationResponse::STOP_* constants.
     *
     * @see https://platform.openai.com/docs/api-reference/chat/create
     * @param array<string, string> $headers Additional request headers, such as authentication.
     */
    protected function converseChatCompletion(AIConversationRequest $request, string $endpointUrl, array $headers): AIConversationResponse
    {
        $model = $this->resolveConversationModel($request);

        $payload = [
            'model' => $model,
            'messages' => $this->canonicalMessagesToOpenAI($request->getMessages(), $request->getSystemPrompt()),
        ];
        $payload[$this->chatCompletionTokenLimitField()] = $request->getMaxTokens();
        if ($this->chatCompletionSupportsTemperature()) {
            $payload['temperature'] = $request->getTemperature();
        }

        $tools = $this->toolCatalogToOpenAI($request->getTools());
        if ($tools !== null) {
            $payload['tools'] = $tools;
        }

        $response = $this->sendJsonRequest($endpointUrl, $headers, $payload, $request->getTimeoutSeconds());

        $message = is_array($response['choices'][0]['message'] ?? null) ? $response['choices'][0]['message'] : [];
        $finishReason = is_string($response['choices'][0]['finish_reason'] ?? null)
            ? $response['choices'][0]['finish_reason']
            : '';

        return $this->buildConversationResponse(
            $model,
            $this->openAIMessageToCanonical($message),
            $this->mapOpenAIFinishReason($finishReason),
            isset($response['usage']['prompt_tokens']) ? (int) $response['usage']['prompt_tokens'] : null,
            isset($response['usage']['completion_tokens']) ? (int) $response['usage']['completion_tokens'] : null
        );
    }

    /**
     * @param list<CanonicalMessageArray> $messages canonical messages
     * @return list<array<string, mixed>>
     */
    private function canonicalMessagesToOpenAI(array $messages, ?string $systemPrompt): array
    {
        $openAIMessages = [];

        if ($systemPrompt !== null && $systemPrompt !== '') {
            $openAIMessages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        foreach ($messages as $message) {
            if ($message['role'] === 'assistant') {
                $openAIMessages[] = $this->canonicalAssistantToOpenAI($message['content']);
                continue;
            }

            if ($message['role'] === 'tool') {
                // OpenAI expects one message per tool result, so a single
                // canonical 'tool' message fans out into N 'tool' messages.
                foreach ($this->canonicalToolResultsToOpenAI($message['content']) as $toolMessage) {
                    $openAIMessages[] = $toolMessage;
                }
                continue;
            }

            // Canonical 'user' messages carry only text blocks.
            $openAIMessages[] = ['role' => 'user', 'content' => $this->textBlocksToString($message['content'])];
        }

        return $openAIMessages;
    }

    /**
     * @param list<CanonicalContentBlockArray> $content canonical assistant content blocks
     * @return array<string, mixed>
     */
    private function canonicalAssistantToOpenAI(array $content): array
    {
        $toolCalls = [];
        foreach (CanonicalMessage::toolUseBlocks($content) as $block) {
            // tool_call.arguments must be a JSON string holding an object even
            // when empty; json_encode collapses `[]` to `[]`, so coerce empty
            // inputs to stdClass so the arguments string is `{}`.
            $arguments = json_encode($block['input'] === [] ? new \stdClass() : $block['input']);
            $toolCalls[] = [
                'id' => $block['id'],
                'type' => 'function',
                'function' => [
                    'name' => $block['name'],
                    'arguments' => $arguments === false ? '{}' : $arguments,
                ],
            ];
        }

        // OpenAI requires the content key to be present even when tool_calls
        // carry the turn; an empty string keeps it valid.
        $message = ['role' => 'assistant', 'content' => $this->textBlocksToString($content)];

        if ($toolCalls !== []) {
            $message['tool_calls'] = $toolCalls;
        }

        return $message;
    }

    /**
     * @param list<CanonicalContentBlockArray> $content canonical tool_result blocks
     * @return list<array<string, mixed>>
     */
    private function canonicalToolResultsToOpenAI(array $content): array
    {
        $messages = [];
        foreach ($content as $block) {
            if (($block['type'] ?? null) !== 'tool_result') {
                continue;
            }
            $toolUseId = $block['tool_use_id'] ?? null;
            if (!is_string($toolUseId)) {
                continue;
            }
            $structured = is_array($block['structuredContent'] ?? null) ? $block['structuredContent'] : null;
            $mcpContent = is_array($block['content'] ?? null) ? $block['content'] : [];

            $messages[] = [
                'role' => 'tool',
                'tool_call_id' => $toolUseId,
                'content' => $this->toolResultContentToOpenAI($structured, $mcpContent),
            ];
        }

        return $messages;
    }

    /**
     * OpenAI tool messages carry a single string content. The tool's
     * structured output, when present, is serialised; otherwise MCP text
     * blocks are concatenated, with non-text blocks JSON-stringified so their
     * data still reaches the model.
     *
     * @param array<string, mixed>|null $structured
     * @param list<array<string, mixed>> $mcpContent
     */
    private function toolResultContentToOpenAI(?array $structured, array $mcpContent): string
    {
        if ($structured !== null) {
            $serialised = json_encode($structured);

            return $serialised === false ? '' : $serialised;
        }

        $parts = [];
        foreach ($mcpContent as $block) {
            if (($block['type'] ?? null) === 'text' && is_string($block['text'] ?? null)) {
                $parts[] = $block['text'];
                continue;
            }
            $serialised = json_encode($block);
            if ($serialised !== false) {
                $parts[] = $serialised;
            }
        }

        return implode("\n", $parts);
    }

    /**
     * Concatenates the text blocks of a canonical content list, ignoring any
     * non-text blocks. Returns '' when there are none.
     *
     * @param list<CanonicalContentBlockArray> $content
     */
    private function textBlocksToString(array $content): string
    {
        $parts = [];
        foreach ($content as $block) {
            if (($block['type'] ?? null) === 'text' && is_string($block['text'] ?? null)) {
                $parts[] = $block['text'];
            }
        }

        return implode("\n", $parts);
    }

    /**
     * @param list<ToolCatalogEntryArray> $tools
     * @return list<array{type: string, function: array<string, mixed>}>|null
     */
    private function toolCatalogToOpenAI(array $tools): ?array
    {
        if ($tools === []) {
            return null;
        }

        $openAITools = [];
        foreach ($tools as $tool) {
            $openAITools[] = [
                'type' => 'function',
                'function' => [
                    'name' => $tool['name'],
                    'description' => $tool['description'],
                    'parameters' => $this->toToolParametersObjectSchema($tool['inputSchema']),
                ],
            ];
        }

        return $openAITools;
    }

    /**
     * Makes a tool's parameter schema acceptable to OpenAI and Google.
     *
     * Both reject certain keywords at the top level of the schema
     * (`oneOf`/`anyOf`/`allOf`/`not`/`enum`/`const`), so this method strips them and
     * forces a plain top-level `type: object`.
     *
     * These are only validation hints, and the tool server re-validates arguments
     * on the actual call, so nothing is really loosened. Nested property schemas
     * are left untouched, and `additionalProperties` are kept on purpose
     * (OpenAI's strict mode requires it).
     *
     * Anthropic accepts the original schema and skips this entirely.
     * Google is stricter and adds a deeper recursive strip on top, in
     * {@see Google::googleParameterSchema()}.
     *
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    protected function toToolParametersObjectSchema(array $schema): array
    {
        unset(
            $schema['oneOf'],
            $schema['anyOf'],
            $schema['allOf'],
            $schema['not'],
            $schema['enum'],
            $schema['const']
        );

        if (($schema['type'] ?? null) !== 'object') {
            $schema['type'] = 'object';
        }

        return $schema;
    }

    /**
     * @param array<string, mixed> $message OpenAI assistant message
     * @return list<CanonicalContentBlockArray> canonical assistant content blocks
     */
    private function openAIMessageToCanonical(array $message): array
    {
        $canonical = [];

        $text = $message['content'] ?? null;
        if (is_string($text) && $text !== '') {
            $canonical[] = ['type' => 'text', 'text' => $text];
        }

        $toolCalls = is_array($message['tool_calls'] ?? null) ? $message['tool_calls'] : [];
        foreach ($toolCalls as $toolCall) {
            if (!is_array($toolCall)) {
                continue;
            }
            $id = $toolCall['id'] ?? null;
            $name = $toolCall['function']['name'] ?? null;
            if (!is_string($id) || !is_string($name)) {
                continue;
            }

            $arguments = $toolCall['function']['arguments'] ?? null;
            $decoded = is_string($arguments) && $arguments !== '' ? json_decode($arguments, true) : [];
            $input = [];
            if (is_array($decoded)) {
                foreach ($decoded as $key => $value) {
                    if (is_string($key)) {
                        $input[$key] = $value;
                    }
                }
            }

            $canonical[] = [
                'type' => 'tool_use',
                'id' => $id,
                'name' => $name,
                'input' => $input,
            ];
        }

        return $canonical;
    }

    /**
     * Maps an OpenAI `finish_reason` onto the canonical stop reasons, passing
     * unknown values through unchanged.
     */
    private function mapOpenAIFinishReason(string $finishReason): string
    {
        switch ($finishReason) {
            case 'tool_calls':
                return AIConversationResponse::STOP_TOOL_USE;
            case 'stop':
                return AIConversationResponse::STOP_END_TURN;
            case 'length':
                return AIConversationResponse::STOP_MAX_TOKENS;
            case 'content_filter':
                return AIConversationResponse::STOP_GUARDRAIL_INTERVENED;
            default:
                return $finishReason;
        }
    }

    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     description: string,
     *     supportsCustomEndpoint: bool,
     *     supportsFipsEndpoint: bool,
     *     defaultEndpointUrl: string,
     *     endpointFieldTitle: string,
     *     endpointFieldPlaceholder: string,
     *     defaultModel: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'supportsCustomEndpoint' => $this->supportsCustomEndpoint(),
            'supportsFipsEndpoint' => $this->supportsFipsEndpoint(),
            'defaultEndpointUrl' => $this->getDefaultEndpointUrl(),
            'endpointFieldTitle' => $this->getEndpointFieldTitle(),
            'endpointFieldPlaceholder' => $this->getEndpointFieldPlaceholder(),
            'defaultModel' => $this->getDefaultModel(),
        ];
    }

    /**
     * @param array<string, string> $configuration
     */
    protected function getApiKey(array $configuration): string
    {
        $apiKey = trim($configuration['apiKey'] ?? '');

        if ($apiKey === '') {
            throw new AIProviderClientException(sprintf('No API key is configured for %s.', $this->getName()));
        }

        return $apiKey;
    }

    /**
     * Builds the bearer Authorization header for OpenAI-compatible providers,
     * omitting it entirely when no API key is configured. This lets custom
     * endpoints point at local LLM servers that run without authentication
     * while still sending the key when one is provided.
     *
     * @param array<string, string> $configuration
     * @return array<string, string>
     */
    protected function getBearerAuthorizationHeaders(array $configuration): array
    {
        $apiKey = trim($configuration['apiKey'] ?? '');

        return $apiKey === '' ? [] : ['Authorization' => 'Bearer ' . $apiKey];
    }

    /**
     * @param array<string, string> $configuration
     */
    protected function getEndpointUrl(array $configuration): string
    {
        $endpointUrl = $this->supportsCustomEndpoint()
            ? trim($configuration['endpointUrl'] ?? '')
            : '';

        if ($endpointUrl === '') {
            $endpointUrl = $this->getDefaultEndpointUrl();
        }

        if ($endpointUrl === '') {
            throw new AIProviderClientException(sprintf('No endpoint URL is configured for %s.', $this->getName()));
        }

        $parsedUrl = parse_url($endpointUrl);
        $scheme = is_array($parsedUrl) ? ($parsedUrl['scheme'] ?? '') : '';

        if (
            !filter_var($endpointUrl, FILTER_VALIDATE_URL)
            || !in_array($scheme, ['http', 'https'], true)
        ) {
            throw new AIProviderClientException(sprintf('The endpoint URL for %s is invalid.', $this->getName()));
        }

        return $endpointUrl;
    }

    /**
     * Derives the OpenAI-compatible models-listing endpoint from a chat
     * completions endpoint, e.g. `.../v1/chat/completions` or a bare `.../v1`
     * base both become `.../v1/models` — the standard `GET {base}/models`
     * probe used by the "test connection" flow.
     */
    protected function openAiCompatibleModelsEndpoint(string $chatEndpointUrl): string
    {
        $base = preg_replace('#/chat/completions/?$#', '', $chatEndpointUrl) ?? $chatEndpointUrl;

        return rtrim($base, '/') . '/models';
    }

    /**
     * Sends a JSON request to the provider and returns the decoded JSON object.
     *
     * Transient errors (HTTP 429/500/503/504/529) are retried with backoff. Failures are
     * classified: {@link AIProviderClientException} for authentication and
     * 4xx responses, {@link AIProviderServerException} for 5xx responses
     * after retries, {@link AIProviderException} for transport and protocol
     * failures.
     *
     * @param array<string, string> $headers
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    protected function sendJsonRequest(string $url, array $headers, array $payload, int $timeoutSeconds = 30): array
    {
        $requestBody = json_encode($payload);

        if (!is_string($requestBody)) {
            throw new AIProviderException(sprintf('Could not encode request body for %s.', $this->getName()));
        }

        return $this->sendRequest('POST', $url, $headers, $requestBody, $timeoutSeconds, $payload);
    }

    /**
     * Sends a GET request to the provider and returns the decoded JSON object,
     * used by the "test connection" probe. Failures are classified exactly as
     * in {@link sendJsonRequest()}.
     *
     * @param array<string, string> $headers
     * @return array<string, mixed>
     */
    protected function sendGetRequest(string $url, array $headers, int $timeoutSeconds = 10): array
    {
        return $this->sendRequest('GET', $url, $headers, null, $timeoutSeconds);
    }

    /**
     * Performs the actual HTTP exchange, retrying transient errors and
     * classifying failures. Shared by {@link sendJsonRequest()} and
     * {@link sendGetRequest()}.
     *
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $debugPayload
     * @return array<string, mixed>
     */
    private function sendRequest(
        string $method,
        string $url,
        array $headers,
        ?string $requestBody,
        int $timeoutSeconds,
        ?array $debugPayload = null
    ): array {
        $requestHeaders = [];
        if ($requestBody !== null) {
            $requestHeaders[] = 'Content-Type: application/json';
        }
        foreach ($headers as $name => $value) {
            $requestHeaders[] = $name . ': ' . $value;
        }

        $retryDelays = self::TRANSIENT_ERROR_RETRY_DELAYS;
        $startedAt = microtime(true);

        $host = parse_url($url, PHP_URL_HOST);
        $checkHostIsAllowed = !is_string($host) || !$this->isTrustedRequestHost($host);

        for ($attempt = 0; $attempt <= count($retryDelays); $attempt++) {
            $attemptStartedAt = microtime(true);
            $this->logProviderRequestDebugMetadata(
                $method,
                $url,
                $timeoutSeconds,
                strlen($requestBody ?? ''),
                $attempt + 1,
                $debugPayload
            );

            try {
                $response = Http::sendHttpRequestBy(
                    Http::getTransportMethod(),
                    $url,
                    max(1, $timeoutSeconds),
                    null,
                    null,
                    null,
                    0,
                    false,
                    false,
                    false,
                    true,
                    $method,
                    null,
                    null,
                    $requestBody,
                    $requestHeaders,
                    null,
                    $checkHostIsAllowed
                );
            } catch (Exception $e) {
                $this->logProviderTransportDebugMetadata($method, $url, $timeoutSeconds, $attempt + 1, $e);

                throw new AIProviderException(sprintf(
                    'Could not connect to %s.',
                    $this->getName()
                ), 0, $e);
            }

            $status = (int) ($response['status'] ?? 0);
            $body = is_string($response['data'] ?? null) ? $response['data'] : '';
            $decoded = $body !== '' ? json_decode($body, true) : null;

            $this->logProviderResponseDebugMetadata(
                $method,
                $url,
                $timeoutSeconds,
                $status,
                strlen($body),
                (int) round((microtime(true) - $attemptStartedAt) * 1000),
                $attempt + 1,
                $debugPayload
            );

            if ($status >= 200 && $status < 300) {
                if (!is_array($decoded)) {
                    throw new AIProviderException(sprintf('%s returned invalid JSON.', $this->getName()));
                }

                $this->lastRequestExecutionTimeMs = (int) round((microtime(true) - $startedAt) * 1000);

                return $decoded;
            }

            $providerError = $this->getProviderErrorMessage(is_array($decoded) ? $decoded : []);

            if ($this->isAuthenticationError($providerError)) {
                throw new AIProviderClientException(sprintf(
                    '%s rejected the API key. Check the key and try again.',
                    $this->getName()
                ));
            }

            if ($this->shouldRetryTransientError($status, $attempt, $retryDelays)) {
                sleep($retryDelays[$attempt]);
                continue;
            }

            $errorSuffix = $providerError !== '' ? ': ' . mb_strimwidth($providerError, 0, 300, '…', 'UTF-8') : '';
            $message = sprintf('%s request failed%s.', $this->getName(), $errorSuffix);

            if ($status >= 500) {
                throw new AIProviderServerException($message);
            }

            if ($status >= 400) {
                throw new AIProviderClientException($message);
            }

            throw new AIProviderException($message);
        }

        throw new AIProviderServerException(sprintf('%s request failed.', $this->getName()));
    }

    /**
     * @param array<string, mixed> $response
     */
    private function getProviderErrorMessage(array $response): string
    {
        $message = '';

        if (isset($response['error']) && is_string($response['error'])) {
            $message = $response['error'];
        } elseif (isset($response['error']) && is_array($response['error'])) {
            $error = $response['error'];
            if (isset($error['message']) && is_string($error['message'])) {
                $message = $error['message'];
            } elseif (isset($error['type']) && is_string($error['type'])) {
                $message = $error['type'];
            }
        } elseif (isset($response['message']) && is_string($response['message'])) {
            // AWS Bedrock errors carry a top-level `message` without an
            // `error` wrapper.
            $message = $response['message'];
        }

        return $message;
    }

    /**
     * Debug-only metadata for local provider testing. Intentionally does not
     * log request/response content, headers, API keys, prompts, or model output.
     *
     * @param array<string, mixed>|null $payload
     */
    private function logProviderRequestDebugMetadata(
        string $method,
        string $url,
        int $timeoutSeconds,
        int $requestBytes,
        int $attempt,
        ?array $payload
    ): void {
        if (!Development::isEnabled()) {
            return;
        }

        StaticContainer::get(LoggerInterface::class)->debug(
            'AIProviders debug provider request metadata: provider={provider}, method={method}, '
            . 'host={host}, path={path}, model={model}, thinking={thinking}, timeoutSeconds={timeoutSeconds}, '
            . 'attempt={attempt}, requestBytes={requestBytes}',
            array_merge($this->getProviderRequestDebugMetadata($url, $payload), [
                'provider' => $this->getName(),
                'method' => $method,
                'timeoutSeconds' => $timeoutSeconds,
                'attempt' => $attempt,
                'requestBytes' => $requestBytes,
            ])
        );
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    private function logProviderResponseDebugMetadata(
        string $method,
        string $url,
        int $timeoutSeconds,
        int $status,
        int $responseBytes,
        int $durationMs,
        int $attempt,
        ?array $payload
    ): void {
        if (!Development::isEnabled()) {
            return;
        }

        StaticContainer::get(LoggerInterface::class)->debug(
            'AIProviders debug provider response metadata: provider={provider}, method={method}, '
            . 'host={host}, path={path}, model={model}, thinking={thinking}, timeoutSeconds={timeoutSeconds}, '
            . 'attempt={attempt}, status={status}, durationMs={durationMs}, responseBytes={responseBytes}',
            array_merge($this->getProviderRequestDebugMetadata($url, $payload), [
                'provider' => $this->getName(),
                'method' => $method,
                'timeoutSeconds' => $timeoutSeconds,
                'attempt' => $attempt,
                'status' => $status,
                'durationMs' => $durationMs,
                'responseBytes' => $responseBytes,
            ])
        );
    }

    private function logProviderTransportDebugMetadata(
        string $method,
        string $url,
        int $timeoutSeconds,
        int $attempt,
        Exception $exception
    ): void {
        if (!Development::isEnabled()) {
            return;
        }

        StaticContainer::get(LoggerInterface::class)->debug(
            'AIProviders debug provider transport metadata: provider={provider}, method={method}, '
            . 'host={host}, path={path}, timeoutSeconds={timeoutSeconds}, attempt={attempt}, '
            . 'exceptionClass={exceptionClass}',
            array_merge($this->getProviderRequestDebugMetadata($url, null), [
                'provider' => $this->getName(),
                'method' => $method,
                'timeoutSeconds' => $timeoutSeconds,
                'attempt' => $attempt,
                'exceptionClass' => get_class($exception),
            ])
        );
    }

    /**
     * @param array<string, mixed>|null $payload
     * @return array{host: string, path: string, model: string, thinking: string}
     */
    private function getProviderRequestDebugMetadata(string $url, ?array $payload): array
    {
        $path = (string) parse_url($url, PHP_URL_PATH);

        return [
            'host' => (string) parse_url($url, PHP_URL_HOST),
            'path' => $path,
            'model' => $this->getDebugModel($payload, $path),
            'thinking' => $this->getDebugThinking($payload),
        ];
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    private function getDebugModel(?array $payload, string $path): string
    {
        if (isset($payload['model']) && is_string($payload['model'])) {
            return $payload['model'];
        }

        // Google carries the model at /models/<model>:<action>, Bedrock at
        // /model/<url-encoded model>/converse.
        if (preg_match('~/models?/([^/:]+)~', $path, $matches)) {
            return rawurldecode($matches[1]);
        }

        return '';
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    private function getDebugThinking(?array $payload): string
    {
        if ($payload === null) {
            return 'unknown';
        }

        if (isset($payload['reasoning_effort']) && is_string($payload['reasoning_effort'])) {
            return $payload['reasoning_effort'] === 'none' ? 'no' : 'yes';
        }

        if (isset($payload['thinking']) && is_array($payload['thinking'])) {
            return 'yes';
        }

        $thinkingBudget = $payload['generationConfig']['thinkingConfig']['thinkingBudget'] ?? null;
        if (is_int($thinkingBudget) || is_float($thinkingBudget)) {
            return $thinkingBudget > 0 ? 'yes' : 'no';
        }

        return 'unknown';
    }

    private function isAuthenticationError(string $message): bool
    {
        $message = strtolower($message);

        return strpos($message, 'api key') !== false
            || strpos($message, 'x-api-key') !== false
            || strpos($message, 'authentication') !== false
            || strpos($message, 'unauthorized') !== false
            || strpos($message, 'invalid key') !== false;
    }

    /**
     * @param int[] $retryDelays
     */
    private function shouldRetryTransientError(int $status, int $attempt, array $retryDelays): bool
    {
        return in_array($status, self::TRANSIENT_ERROR_STATUS_CODES, true)
            && array_key_exists($attempt, $retryDelays);
    }
}
