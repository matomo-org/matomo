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
use Piwik\Plugins\AIProviders\WebSearchUsage;

class OpenAI extends AIProvider
{
    private const DEFAULT_MODEL = 'gpt-5.4-mini';

    /** gpt-5 reasoning levels: off for instant answers, medium for thinking. */
    private const REASONING_EFFORT_NONE = 'none';
    private const REASONING_EFFORT_THINKING = 'medium';

    /**
     * How much retrieved page content a search may pull into the prompt
     * (`low`/`medium`/`high`). OpenAI's own default; it is also the cost knob,
     * since search content is billed as input tokens on top of the per-call fee.
     */
    private const WEB_SEARCH_CONTEXT_SIZE = 'medium';

    public function __construct()
    {
        parent::__construct('openai', 'OpenAI', 'AIProviders_OpenAIDefaultModelDescription');
    }

    public function getDefaultEndpointUrl(): string
    {
        return 'https://api.openai.com/v1/chat/completions';
    }

    public function getDefaultModel(): string
    {
        return self::DEFAULT_MODEL;
    }

    /**
     * Ungrounded completions use Chat Completions; grounded ones the Responses
     * API, because the hosted `web_search` tool only exists there (on chat
     * completions it requires the separate `gpt-5-search-api` model).
     *
     * @param array<string, string> $configuration
     */
    public function complete(AIRequest $request, array $configuration): AIProviderResponse
    {
        if ($this->wantsWebSearch($request)) {
            return $this->completeGroundedResponse($request, $configuration);
        }

        return $this->completeChatCompletion(
            $request,
            $this->getEndpointUrl($configuration),
            ['Authorization' => 'Bearer ' . $this->getApiKey($configuration)]
        );
    }

    public function supportsWebSearch(): bool
    {
        return true;
    }

    /**
     * Runs a grounded completion against `/v1/responses` with the `web_search`
     * tool offered. No `tool_choice`: it defaults to auto, so the model decides
     * whether the prompt needs fresh sources and an unnecessary search is not
     * paid for.
     *
     * JSON mode is degraded here: OpenAI rejects `text.format` together with web
     * search ("Web Search cannot be used with JSON mode"), so only the JSON
     * instruction {@link getSystemPrompt()} adds to the prompt remains.
     *
     * @see https://platform.openai.com/docs/api-reference/responses
     * @param array<string, string> $configuration
     */
    private function completeGroundedResponse(AIRequest $request, array $configuration): AIProviderResponse
    {
        $model = $this->resolveModel($request);

        $input = [];
        $systemPrompt = $this->getSystemPrompt($request);
        if ($systemPrompt !== null && $systemPrompt !== '') {
            // `developer` is the Responses-API role that replaces `system`.
            $input[] = ['role' => 'developer', 'content' => $systemPrompt];
        }
        $input[] = ['role' => 'user', 'content' => $request->getUserPrompt()];

        $payload = [
            'model' => $model,
            'input' => $input,
            'max_output_tokens' => $request->getMaxTokens(),
            'reasoning' => [
                'effort' => $this->wantsThinking($request) ? self::REASONING_EFFORT_THINKING : self::REASONING_EFFORT_NONE,
            ],
            'tools' => [['type' => 'web_search', 'search_context_size' => self::WEB_SEARCH_CONTEXT_SIZE]],
            // Unlike chat completions, the Responses API retains requests and
            // output server-side (~30 days) by default. Matomo prompts carry site
            // names, URLs and report data, so storage is switched off explicitly.
            'store' => false,
        ];

        $response = $this->sendJsonRequest(
            $this->openAiCompatibleEndpoint($this->getEndpointUrl($configuration), 'responses'),
            ['Authorization' => 'Bearer ' . $this->getApiKey($configuration)],
            $payload,
            $this->completionTimeoutSeconds($request)
        );

        $outputItems = [];
        foreach ((is_array($response['output'] ?? null) ? $response['output'] : []) as $item) {
            if (is_array($item)) {
                $outputItems[] = $item;
            }
        }

        // `incomplete_details.reason` is the specific outcome (e.g. max_output_tokens);
        // `status` the generic one.
        $stopReason = $response['incomplete_details']['reason'] ?? $response['status'] ?? null;

        return $this->buildResponse(
            $request,
            $model,
            $this->extractResponsesText($outputItems),
            isset($response['usage']['input_tokens']) ? (int) $response['usage']['input_tokens'] : null,
            isset($response['usage']['output_tokens']) ? (int) $response['usage']['output_tokens'] : null,
            is_string($stopReason) && $stopReason !== '' ? $stopReason : null,
            $this->parseWebSearchUsage($outputItems)
        );
    }

    /**
     * Concatenates the `output_text` parts of every `message` item. The output
     * is a typed item list (`reasoning`, `web_search_call`, `message`, …) and a
     * grounded answer can span several `message` items.
     *
     * Parts within one message are joined with nothing between them, because a
     * grounded answer splits mid-sentence at a citation boundary and each part
     * carries its own spacing; a separator there would break the prose, and in
     * JSON mode a newline inside a string value makes the response undecodable.
     * Separate messages are distinct blocks of prose and keep a newline.
     *
     * @param list<array<string, mixed>> $outputItems
     */
    private function extractResponsesText(array $outputItems): string
    {
        $messages = [];
        foreach ($outputItems as $item) {
            if (($item['type'] ?? null) !== 'message' || !is_array($item['content'] ?? null)) {
                continue;
            }

            $parts = [];
            foreach ($item['content'] as $part) {
                if (is_array($part) && ($part['type'] ?? null) === 'output_text' && is_string($part['text'] ?? null)) {
                    $parts[] = $part['text'];
                }
            }

            if ($parts !== []) {
                $messages[] = implode('', $parts);
            }
        }

        return implode("\n", $messages);
    }

    /**
     * Counts `web_search_call` items and collects the `url_citation`
     * annotations on the answer's `output_text` parts. OpenAI does not always
     * echo the query it searched for, so queries can be empty when searches ran.
     *
     * @param list<array<string, mixed>> $outputItems
     */
    private function parseWebSearchUsage(array $outputItems): WebSearchUsage
    {
        $searchCalls = 0;
        $queries = [];
        $citations = [];

        foreach ($outputItems as $item) {
            $type = $item['type'] ?? null;

            if ($type === 'web_search_call') {
                // Reasoning models emit open_page and find_in_page actions on this
                // same item type. Those are follow-ups within a search, not new
                // billed searches, so counting them would overstate the fee.
                $action = $item['action']['type'] ?? null;
                if ($action === null || $action === 'search') {
                    $searchCalls++;
                }
                if (is_string($item['action']['query'] ?? null)) {
                    $queries[] = $item['action']['query'];
                }
                continue;
            }

            if ($type !== 'message' || !is_array($item['content'] ?? null)) {
                continue;
            }

            foreach ($item['content'] as $part) {
                foreach ((is_array($part['annotations'] ?? null) ? $part['annotations'] : []) as $annotation) {
                    if (is_array($annotation) && ($annotation['type'] ?? null) === 'url_citation') {
                        $citations[] = ['url' => $annotation['url'] ?? null, 'title' => $annotation['title'] ?? null];
                    }
                }
            }
        }

        return WebSearchUsage::fromProviderData($citations, $searchCalls, $queries);
    }

    /**
     * Validates credentials and reachability with a cheap models listing
     * (`GET /v1/models`) instead of spending generation tokens.
     *
     * @param array<string, string> $configuration
     */
    public function verifyConnection(array $configuration): void
    {
        $this->sendGetRequest(
            $this->openAiCompatibleModelsEndpoint($this->getEndpointUrl($configuration)),
            ['Authorization' => 'Bearer ' . $this->getApiKey($configuration)]
        );
    }

    public function supportsConversations(): bool
    {
        return true;
    }

    /**
     * gpt-5 reasoning models expose thinking through `reasoning_effort` rather
     * than a thinking budget: "none" disables reasoning for fast, cheap instant
     * answers; "medium" turns it on for better reasoning. (Supported because
     * gpt-5.4-mini is post-gpt-5.1, where "none" became valid.)
     */
    protected function getExtraChatCompletionPayload(AIRequest $request): array
    {
        return ['reasoning_effort' => $this->wantsThinking($request) ? self::REASONING_EFFORT_THINKING : self::REASONING_EFFORT_NONE];
    }

    /**
     * gpt-5 reasoning models require `max_completion_tokens` and reject a custom
     * `temperature` (only the default is allowed), so omit it.
     */
    protected function chatCompletionTokenLimitField(): string
    {
        return 'max_completion_tokens';
    }

    protected function chatCompletionSupportsTemperature(): bool
    {
        return false;
    }

    /**
     * Runs one conversational round-trip against the OpenAI Chat Completions API.
     *
     * @see https://platform.openai.com/docs/api-reference/chat/create
     * @param array<string, string> $configuration
     */
    public function converse(AIConversationRequest $request, array $configuration): AIConversationResponse
    {
        return $this->converseChatCompletion(
            $request,
            $this->getEndpointUrl($configuration),
            ['Authorization' => 'Bearer ' . $this->getApiKey($configuration)]
        );
    }
}
