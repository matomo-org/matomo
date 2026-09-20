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
use Piwik\Plugins\AIProviders\Exception\AIProviderClientException;
use Piwik\Plugins\AIProviders\Model\Configuration;

/**
 * AWS Bedrock provider using the Converse HTTP API.
 *
 * Authenticates with an Amazon Bedrock API key sent as a bearer token, so no
 * AWS SDK or SigV4 signing is needed. The key must be a long-term API key
 * (short-term keys expire within 12 hours and cannot serve a stored
 * configuration).
 *
 * The AWS region is configured directly; runtime and control-plane endpoints
 * are derived internally so Matomo never sends the key to arbitrary hosts.
 *
 * @see https://docs.aws.amazon.com/bedrock/latest/userguide/api-keys.html
 * @phpstan-import-type CanonicalMessageArray from CanonicalMessage
 * @phpstan-import-type CanonicalContentBlockArray from CanonicalMessage
 * @phpstan-import-type ToolCatalogEntryArray from AIConversationRequest
 */
class Bedrock extends AIProvider
{
    public const ID = 'bedrock';

    private const DEFAULT_REGION = 'us-east-1';
    private const DEFAULT_MODEL = 'openai.gpt-oss-120b-1:0';

    /** Timeout for single-shot completions; conversations use the request's own budget. */
    private const COMPLETE_TIMEOUT_SECONDS = 30;

    /** Exact Bedrock model ID fragments that support the gpt-oss reasoning_effort field. */
    private const GPT_OSS_MODEL_PATTERN = '/(?:^|[.\/])openai\.gpt-oss-(?:20b|120b)-1:0$/';

    /**
     * Amazon Nova 2 text models that support the structured reasoningConfig
     * extended-thinking control. Only Nova 2 Lite is generally available with a
     * confirmed model ID; the Pro and Omni tiers are still in preview (and Omni
     * is a multimodal generation model, not a text reasoning model), so they
     * are intentionally excluded until they reach GA with a verified ID.
     *
     * @see https://docs.aws.amazon.com/nova/latest/userguide/extended-thinking.html
     */
    private const NOVA_2_MODEL_PATTERN = '/(?:^|[.\/])amazon\.nova-2-lite-v1:0$/';

    /**
     * Nova 2 does not expose its reasoning text yet: it returns the literal
     * placeholder "[REDACTED]" (still billed as reasoning tokens). That
     * placeholder carries no information and must not be surfaced as reasoning.
     * If AWS starts returning real text it will no longer match and flow through.
     *
     * @see https://docs.aws.amazon.com/nova/latest/nova2-userguide/extended-thinking.html
     */
    private const REDACTED_REASONING_PATTERN = '/^\[REDACTED\]\.?$/';

    /**
     * First-generation Amazon Nova text models (Micro/Lite/Pro/Premier). Unlike
     * gpt-oss and Nova 2, these have no structured reasoning output and no
     * reasoning API; they surface chain-of-thought as inline <thinking> tags in
     * the answer text, so that reasoning has to be split out here instead.
     *
     * @see https://docs.aws.amazon.com/nova/latest/userguide/prompting-chain-of-thought.html
     */
    private const NOVA_GEN1_MODEL_PATTERN = '/(?:^|[.\/])amazon\.nova-(?:micro|lite|pro|premier)-v1:0$/';

    /** Balanced leading inline reasoning blocks emitted by Nova gen-1 responses. */
    private const LEADING_REASONING_PATTERN = '/^\s*<(reasoning|think|thinking)>/i';

    /**
     * Mistral Large models. Unlike other Converse families, Large only returns
     * a structured toolUse block when a tool call is forced with
     * `toolChoice: {any}`; under the default (auto) its tool calls leak back as
     * plain text and are lost. Observed behaviour: with `any` set the model
     * still answers directly when no tool is needed (contrary to the documented
     * "must request at least one tool"), so forcing it here does not break
     * conceptual or clarifying turns. This is undocumented Bedrock behaviour and
     * is scoped to Large only; every other family works under auto.
     */
    private const MISTRAL_LARGE_MODEL_PATTERN = '/(?:^|[.\/])mistral\.mistral-large-/i';

    public function __construct()
    {
        parent::__construct(
            self::ID,
            'AWS Bedrock',
            'AIProviders_BedrockDescription',
            true
        );
    }

    public function getDefaultEndpointUrl(): string
    {
        return self::DEFAULT_REGION;
    }

    public function getEndpointFieldTitle(): string
    {
        return 'AIProviders_BedrockEndpointTitle';
    }

    public function getEndpointFieldPlaceholder(): string
    {
        return 'AIProviders_BedrockEndpointPlaceholder';
    }

    public function getEndpointFieldErrorMessage(): string
    {
        return 'AIProviders_ErrorInvalidAwsRegion';
    }

    public function supportsFipsEndpoint(): bool
    {
        return true;
    }

    public function endpointFieldRequiresUrl(): bool
    {
        return false;
    }

    public function getDefaultModel(): string
    {
        return self::DEFAULT_MODEL;
    }

    /**
     * The endpoint has a working default (it only carries the region), so the
     * API key is the one required piece.
     *
     * @param array<string, string> $configuration
     */
    public function isConfigured(array $configuration): bool
    {
        return trim($configuration['apiKey'] ?? '') !== '';
    }

    /**
     * Matomo's HTTP layer blocks `*.amazonaws.com` as SSRF protection (see
     * `http.blocklist.hosts`). Only the genuine Bedrock service hostnames are
     * trusted; other AWS services and custom endpoints stay blocked.
     */
    protected function isTrustedRequestHost(string $host): bool
    {
        return preg_match('/^bedrock(-runtime)?(-fips)?\.[a-z]{2}(?:-[a-z0-9]+)+-[0-9]+\.amazonaws\.com$/i', $host) === 1;
    }

    /**
     * @param array<string, string> $configuration
     */
    public function complete(AIRequest $request, array $configuration): AIProviderResponse
    {
        $model = $this->resolveConfiguredModel($request->getModel(), $configuration);

        $payload = [
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        ['text' => $request->getUserPrompt()],
                    ],
                ],
            ],
            'inferenceConfig' => [
                'maxTokens' => $request->getMaxTokens(),
                'temperature' => $request->getTemperature(),
            ],
        ];

        // Completions carry a per-request thinking budget that overrides the
        // capability level, so resolve the effective intent via wantsThinking().
        $this->applyReasoningConfiguration($payload, $model, $this->wantsThinking($request));

        $systemPrompt = $this->getSystemPrompt($request);
        if ($systemPrompt !== null && $systemPrompt !== '') {
            $payload['system'] = [
                ['text' => $systemPrompt],
            ];
        }

        $response = $this->sendConverseRequest($model, $payload, self::COMPLETE_TIMEOUT_SECONDS, $configuration);

        $stopReason = is_string($response['stopReason'] ?? null) ? $response['stopReason'] : null;

        return $this->buildResponse(
            $request,
            $model,
            $this->extractText($response, $this->isNovaGen1Model($model)),
            isset($response['usage']['inputTokens']) ? (int) $response['usage']['inputTokens'] : null,
            isset($response['usage']['outputTokens']) ? (int) $response['usage']['outputTokens'] : null,
            $stopReason
        );
    }

    public function supportsConversations(): bool
    {
        return true;
    }

    /**
     * Runs one conversational round-trip, translating the canonical message
     * shape to and from the Converse wire format.
     *
     * @see https://docs.aws.amazon.com/bedrock/latest/APIReference/API_runtime_Converse.html
     * @param array<string, string> $configuration
     */
    public function converse(AIConversationRequest $request, array $configuration): AIConversationResponse
    {
        $model = $this->resolveConfiguredModel($request->getModel(), $configuration);

        $payload = [
            'messages' => $this->canonicalMessagesToBedrock($request->getMessages()),
            'inferenceConfig' => [
                'maxTokens' => $request->getMaxTokens(),
                'temperature' => $request->getTemperature(),
            ],
        ];

        // Conversation requests have no thinking budget, so the capability
        // level alone decides whether reasoning is on.
        $thinking = $request->getCapabilityLevel() === Configuration::CAPABILITY_THINKING;
        $this->applyReasoningConfiguration($payload, $model, $thinking);

        $systemPrompt = $request->getSystemPrompt();
        if ($systemPrompt !== null && $systemPrompt !== '') {
            $payload['system'] = [['text' => $systemPrompt]];
        }

        $toolConfig = $this->toolCatalogToBedrock($request->getTools());
        if ($toolConfig !== null) {
            // Mistral Large only emits a structured toolUse block when tool use
            // is forced (see MISTRAL_LARGE_MODEL_PATTERN). stdClass keeps the
            // json_encode output as `{}` rather than `[]`.
            if ($this->isMistralLargeModel($model)) {
                $toolConfig['toolChoice'] = ['any' => new \stdClass()];
            }
            $payload['toolConfig'] = $toolConfig;
        }

        $response = $this->sendConverseRequest($model, $payload, $request->getTimeoutSeconds(), $configuration);

        $rawContent = $response['output']['message']['content'] ?? null;
        $stopReason = is_string($response['stopReason'] ?? null) ? $response['stopReason'] : '';

        return $this->buildConversationResponse(
            $model,
            $this->bedrockContentToCanonical(
                is_array($rawContent) ? $rawContent : [],
                $this->isNovaGen1Model($model),
                $thinking
            ),
            $stopReason,
            isset($response['usage']['inputTokens']) ? (int) $response['usage']['inputTokens'] : null,
            isset($response['usage']['outputTokens']) ? (int) $response['usage']['outputTokens'] : null
        );
    }

    /**
     * Probes with the model listing instead of spending generation tokens.
     *
     * @param array<string, string> $configuration
     */
    public function verifyConnection(array $configuration): void
    {
        $this->listModels($configuration);
    }

    /**
     * Discovers invokable models via the control-plane listing (host
     * `bedrock.`, not the runtime `bedrock-runtime.`, same region).
     * `byInferenceType=ON_DEMAND` drops models that need an inference profile.
     *
     * @see https://docs.aws.amazon.com/bedrock/latest/APIReference/API_ListFoundationModels.html
     * @param array<string, string> $configuration
     * @return list<string>
     */
    public function listModels(array $configuration): array
    {
        $response = $this->sendGetRequest(
            $this->getModelListingEndpoint($this->getRegion($configuration), $configuration),
            $this->getAuthorizationHeader($configuration)
        );

        $models = [];
        foreach ($response['modelSummaries'] ?? [] as $summary) {
            if (is_array($summary) && isset($summary['modelId']) && is_string($summary['modelId']) && $summary['modelId'] !== '') {
                $models[] = $summary['modelId'];
            }
        }

        sort($models);

        return $models;
    }

    /**
     * Normalizes the AWS region. Bedrock endpoints are derived internally so
     * user/config input never decides the request host directly.
     */
    public function normalizeEndpointUrl(string $endpointUrl): string
    {
        return $this->normalizeRegion($endpointUrl);
    }

    public function normalizeRegion(string $region): string
    {
        $region = strtolower(trim($region));

        if ($region === '') {
            return self::DEFAULT_REGION;
        }

        if (preg_match('/^[a-z]{2}(?:-[a-z0-9]+)+-[0-9]+$/', $region) !== 1) {
            throw new AIProviderClientException(sprintf('The AWS region for %s is invalid.', $this->getName()));
        }

        return $region;
    }

    /**
     * Applies region normalization on use as well: config-file credentials
     * bypass the admin-side normalization.
     *
     * @param array<string, mixed> $configuration
     */
    protected function getEndpointUrl(array $configuration): string
    {
        return $this->getRuntimeEndpoint($this->getRegion($configuration), $configuration);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function getRegion(array $configuration): string
    {
        return $this->normalizeRegion((string) ($configuration['endpointUrl'] ?? ''));
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function getRuntimeEndpoint(string $region, array $configuration): string
    {
        $service = $this->useFipsEndpoint($configuration) ? 'bedrock-runtime-fips' : 'bedrock-runtime';

        return sprintf('https://%s.%s.amazonaws.com', $service, $region);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function getControlPlaneEndpoint(string $region, array $configuration): string
    {
        $service = $this->useFipsEndpoint($configuration) ? 'bedrock-fips' : 'bedrock';

        return sprintf('https://%s.%s.amazonaws.com', $service, $region);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function useFipsEndpoint(array $configuration): bool
    {
        return !empty($configuration['useFipsEndpoint']);
    }

    /**
     * Resolves the model: per-request, then saved configuration, then default.
     *
     * @param array<string, string> $configuration
     */
    protected function resolveConfiguredModel(?string $requestModel, array $configuration): string
    {
        if ($requestModel !== null && $requestModel !== '') {
            return $requestModel;
        }

        $configuredModel = trim($configuration['model'] ?? '');

        return $configuredModel !== '' ? $configuredModel : $this->getDefaultModel();
    }

    /**
     * POSTs the Converse payload to the regional runtime endpoint with
     * bearer-key auth.
     *
     * @param array<string, mixed> $payload Converse request body, without the model
     * @param array<string, string> $configuration
     * @return array<string, mixed>
     */
    protected function sendConverseRequest(string $model, array $payload, int $timeoutSeconds, array $configuration): array
    {
        return $this->sendJsonRequest(
            $this->getConverseEndpoint($this->getEndpointUrl($configuration), $model),
            $this->getAuthorizationHeader($configuration),
            $payload,
            $timeoutSeconds
        );
    }

    /**
     * The model travels in the URL path, not the payload. Inference-profile
     * IDs and ARNs contain `:` and `/`, so the model must be encoded as a
     * single path segment.
     */
    private function getConverseEndpoint(string $endpointUrl, string $model): string
    {
        return rtrim($endpointUrl, '/') . '/model/' . rawurlencode($model) . '/converse';
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function getModelListingEndpoint(string $region, array $configuration): string
    {
        return $this->getControlPlaneEndpoint($region, $configuration) . '/foundation-models?byInferenceType=ON_DEMAND';
    }

    /**
     * @param array<string, string> $configuration
     * @return array<string, string>
     */
    private function getAuthorizationHeader(array $configuration): array
    {
        return ['Authorization' => 'Bearer ' . $this->getApiKey($configuration)];
    }

    /**
     * Translates canonical messages into Bedrock Converse messages.
     *
     * Converse requires turns to alternate user/assistant, and a canonical
     * 'tool' row folds into a 'user' turn. A tool-result turn followed by the
     * user's next message would emit two consecutive 'user' turns, which
     * Bedrock rejects, so runs of same-role messages are merged into one turn
     * (concatenating their content blocks is valid Converse input).
     *
     * @param list<CanonicalMessageArray> $messages canonical messages
     * @return list<array{role: string, content: list<array<string, mixed>>}>
     */
    private function canonicalMessagesToBedrock(array $messages): array
    {
        $bedrockMessages = [];
        foreach ($messages as $message) {
            // Converse uses two roles ('user', 'assistant') and folds tool
            // results into a 'user' message body so the toolUse/toolResult
            // pairing survives a round trip.
            $role = $message['role'] === 'assistant' ? 'assistant' : 'user';

            $blocks = [];
            foreach ($message['content'] as $block) {
                $translated = $this->canonicalBlockToBedrock($block);
                if ($translated !== null) {
                    $blocks[] = $translated;
                }
            }

            $lastIndex = count($bedrockMessages) - 1;
            if ($lastIndex >= 0 && $bedrockMessages[$lastIndex]['role'] === $role) {
                $bedrockMessages[$lastIndex]['content'] = array_merge(
                    $bedrockMessages[$lastIndex]['content'],
                    $blocks
                );
                continue;
            }

            $bedrockMessages[] = ['role' => $role, 'content' => $blocks];
        }

        return $bedrockMessages;
    }

    /**
     * @param array<string, mixed> $block
     * @return array<string, mixed>|null null when the block shape is unrecognised
     */
    private function canonicalBlockToBedrock(array $block): ?array
    {
        $type = $block['type'] ?? null;

        if ($type === 'text' && is_string($block['text'] ?? null)) {
            return ['text' => $block['text']];
        }

        if ($type === 'reasoning') {
            // Canonical reasoning is display-only and must never be replayed.
            return null;
        }

        if ($type === 'tool_use') {
            $id = $block['id'] ?? null;
            $name = $block['name'] ?? null;
            $input = $block['input'] ?? [];
            if (!is_string($id) || !is_string($name) || !is_array($input)) {
                return null;
            }

            // Bedrock requires toolUse.input to be a JSON object, even when
            // empty. `json_decode($body, true)` collapses `{}` to `[]`; if
            // that round-trips back to Bedrock it would emit `"[]"` and the
            // next turn rejects it with a 400. Coerce empty inputs back to
            // stdClass so json_encode produces `{}`.
            $inputForWire = $input === [] ? new \stdClass() : $input;

            return ['toolUse' => ['toolUseId' => $id, 'name' => $name, 'input' => $inputForWire]];
        }

        if ($type === 'tool_result') {
            $toolUseId = $block['tool_use_id'] ?? null;
            if (!is_string($toolUseId)) {
                return null;
            }
            $structured = is_array($block['structuredContent'] ?? null) ? $block['structuredContent'] : null;
            $mcpContent = is_array($block['content'] ?? null) ? $block['content'] : [];

            return [
                'toolResult' => [
                    'toolUseId' => $toolUseId,
                    'content' => $this->toolResultContentToBedrock($structured, $mcpContent),
                    'status' => !empty($block['is_error']) ? 'error' : 'success',
                ],
            ];
        }

        return null;
    }

    /**
     * Chooses the optimal Bedrock `toolResult.content` shape:
     *
     * - structuredContent → `[{json: <object>}]`. The model receives the
     *   tool's structured output natively without re-parsing an escaped JSON
     *   string from a text block.
     * - Otherwise each MCP content block is translated to its Bedrock
     *   counterpart, with non-text blocks JSON-stringified so the model
     *   still sees the data.
     *
     * @param array<string, mixed>|null $structured
     * @param list<array<string, mixed>> $mcpContent
     * @return list<array<string, mixed>>
     */
    private function toolResultContentToBedrock(?array $structured, array $mcpContent): array
    {
        if ($structured !== null) {
            return [['json' => $structured]];
        }

        $bedrockBlocks = [];
        foreach ($mcpContent as $block) {
            if (($block['type'] ?? null) === 'text' && is_string($block['text'] ?? null)) {
                $bedrockBlocks[] = ['text' => $block['text']];
                continue;
            }
            $serialised = json_encode($block);
            if ($serialised !== false) {
                $bedrockBlocks[] = ['text' => $serialised];
            }
        }

        if ($bedrockBlocks === []) {
            $bedrockBlocks[] = ['text' => ''];
        }

        return $bedrockBlocks;
    }

    /**
     * Translates the tool catalogue into the Converse `toolConfig` shape, or
     * null for an empty catalogue so `toolConfig` is omitted entirely.
     *
     * @param list<ToolCatalogEntryArray> $tools
     * @return array{tools: list<array<string, mixed>>}|null
     */
    private function toolCatalogToBedrock(array $tools): ?array
    {
        if ($tools === []) {
            return null;
        }

        $bedrockTools = [];
        foreach ($tools as $tool) {
            $bedrockTools[] = [
                'toolSpec' => [
                    'name' => $tool['name'],
                    'description' => $tool['description'],
                    'inputSchema' => ['json' => $tool['inputSchema']],
                ],
            ];
        }

        return ['tools' => $bedrockTools];
    }

    /**
     * @param list<mixed> $content Bedrock assistant content blocks
     * @return list<CanonicalContentBlockArray> canonical assistant content blocks
     */
    private function bedrockContentToCanonical(
        array $content,
        bool $splitInlineReasoning,
        bool $includeReasoning
    ): array {
        $canonical = [];
        foreach ($content as $block) {
            if (!is_array($block)) {
                continue;
            }

            if (is_array($block['reasoningContent'] ?? null)) {
                $reasoningText = $this->bedrockReasoningText($block['reasoningContent']);
                if (
                    $includeReasoning
                    && is_string($reasoningText)
                    && trim($reasoningText) !== ''
                    && !$this->isRedactedReasoning($reasoningText)
                ) {
                    $canonical[] = ['type' => 'reasoning', 'text' => $reasoningText];
                }
                continue;
            }

            if (is_string($block['text'] ?? null)) {
                if ($splitInlineReasoning) {
                    foreach ($this->splitLeadingReasoning($block['text']) as $splitBlock) {
                        if ($includeReasoning || $splitBlock['type'] !== 'reasoning') {
                            $canonical[] = $splitBlock;
                        }
                    }
                } else {
                    $canonical[] = ['type' => 'text', 'text' => $block['text']];
                }
                continue;
            }

            if (is_array($block['toolUse'] ?? null)) {
                $toolUse = $block['toolUse'];
                $id = $toolUse['toolUseId'] ?? null;
                $name = $toolUse['name'] ?? null;
                $input = $toolUse['input'] ?? [];
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

            // Unknown Bedrock block shapes are dropped until a canonical
            // block type exists for them.
        }

        return $canonical;
    }

    /**
     * @param array<string, mixed> $reasoningContent
     */
    private function bedrockReasoningText(array $reasoningContent): ?string
    {
        $reasoningText = $reasoningContent['reasoningText'] ?? null;

        return is_array($reasoningText) && is_string($reasoningText['text'] ?? null)
            ? $reasoningText['text']
            : null;
    }

    private function isRedactedReasoning(string $text): bool
    {
        return preg_match(self::REDACTED_REASONING_PATTERN, trim($text)) === 1;
    }

    /**
     * Adds the model-family-specific reasoning control to the request payload,
     * leaving models without a reasoning API untouched.
     *
     * gpt-oss exposes a flat `reasoning_effort`, while Nova 2 uses a
     * structured `reasoningConfig`. Nova gen-1 has no reasoning API and is
     * handled on the response side instead (see NOVA_GEN1_MODEL_PATTERN).
     *
     * @param array<string, mixed> $payload
     */
    private function applyReasoningConfiguration(array &$payload, string $model, bool $thinking): void
    {
        if ($this->isGptOssModel($model)) {
            $payload['additionalModelRequestFields']['reasoning_effort'] = $thinking ? 'medium' : 'low';
            return;
        }

        if ($this->isNovaReasoningModel($model)) {
            $payload['additionalModelRequestFields']['reasoningConfig'] = $thinking
                ? ['type' => 'enabled', 'maxReasoningEffort' => 'medium']
                : ['type' => 'disabled'];
        }
    }

    private function isGptOssModel(string $model): bool
    {
        return preg_match(self::GPT_OSS_MODEL_PATTERN, $model) === 1;
    }

    private function isNovaReasoningModel(string $model): bool
    {
        return preg_match(self::NOVA_2_MODEL_PATTERN, $model) === 1;
    }

    private function isNovaGen1Model(string $model): bool
    {
        return preg_match(self::NOVA_GEN1_MODEL_PATTERN, $model) === 1;
    }

    private function isMistralLargeModel(string $model): bool
    {
        return preg_match(self::MISTRAL_LARGE_MODEL_PATTERN, $model) === 1;
    }

    /**
     * Splits balanced leading reasoning tags from the remaining answer text.
     *
     * Nova gen-1 models emit their chain-of-thought as inline <thinking> tags
     * at the start of the answer instead of as structured reasoningContent, so
     * the reasoning has to be separated from the visible answer here.
     *
     * @return list<array{type: string, text: string}>
     */
    private function splitLeadingReasoning(string $raw): array
    {
        $blocks = [];
        $rest = $raw;

        while (preg_match(self::LEADING_REASONING_PATTERN, $rest, $open, PREG_OFFSET_CAPTURE) === 1) {
            $reasoningStart = strlen($open[0][0]);
            $closePattern = '/<\/' . preg_quote($open[1][0], '/') . '>/i';

            if (preg_match($closePattern, $rest, $close, PREG_OFFSET_CAPTURE, $reasoningStart) !== 1) {
                break;
            }

            $reasoning = trim(substr($rest, $reasoningStart, $close[0][1] - $reasoningStart));
            if ($reasoning !== '') {
                $blocks[] = ['type' => 'reasoning', 'text' => $reasoning];
            }

            $rest = substr($rest, $close[0][1] + strlen($close[0][0]));
        }

        if (trim($rest) !== '') {
            $blocks[] = ['type' => 'text', 'text' => $rest];
        }

        return $blocks;
    }

    /**
     * @param array<string, mixed> $response
     */
    private function extractText(array $response, bool $stripInlineReasoning): string
    {
        $content = $response['output']['message']['content'] ?? [];

        if (is_array($content)) {
            foreach ($content as $block) {
                if (isset($block['text']) && is_string($block['text'])) {
                    if (!$stripInlineReasoning) {
                        return $block['text'];
                    }

                    foreach ($this->splitLeadingReasoning($block['text']) as $splitBlock) {
                        if ($splitBlock['type'] === 'text') {
                            return $splitBlock['text'];
                        }
                    }
                }
            }
        }

        return '';
    }
}
