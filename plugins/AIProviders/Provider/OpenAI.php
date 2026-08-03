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

class OpenAI extends AIProvider
{
    private const DEFAULT_MODEL = 'gpt-5.4-mini';

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
     * @param array<string, string> $configuration
     */
    public function complete(AIRequest $request, array $configuration): AIProviderResponse
    {
        return $this->completeChatCompletion(
            $request,
            $this->getEndpointUrl($configuration),
            ['Authorization' => 'Bearer ' . $this->getApiKey($configuration)]
        );
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
        return ['reasoning_effort' => $this->wantsThinking($request) ? 'medium' : 'none'];
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
