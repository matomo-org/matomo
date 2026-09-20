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
use Piwik\Plugins\AIProviders\Exception\AIProviderClientException;

class CustomProvider extends AIProvider
{
    public function __construct()
    {
        parent::__construct(
            'custom-provider',
            'Custom Provider',
            'AIProviders_CustomProviderDescription',
            true
        );
    }

    /**
     * Custom servers are expected to be OpenAI-compatible. The configured base
     * URL must point at the API root (the part before `/chat/completions`),
     * typically the `/v1` path. The API key is optional, because such servers
     * commonly run without authentication. There is no built-in default model:
     * the model comes from the request or the saved configuration (the admin
     * picks one from the server's discovered models), so callers can target the
     * exact alias a given server exposes.
     *
     * @param array<string, string> $configuration
     */
    public function complete(AIRequest $request, array $configuration): AIProviderResponse
    {
        return $this->completeChatCompletion(
            $request->withModel($this->resolveConfiguredModel($request->getModel(), $configuration)),
            $this->getChatCompletionsEndpoint($this->getEndpointUrl($configuration)),
            $this->getBearerAuthorizationHeaders($configuration)
        );
    }

    /**
     * Validates the connection with the standard OpenAI-compatible
     * `GET {base}/models` probe instead of spending generation tokens. The
     * API key is optional; getBearerAuthorizationHeaders() omits the header
     * when no key was provided.
     *
     * @param array<string, string> $configuration
     */
    public function verifyConnection(array $configuration): void
    {
        $this->listModels($configuration);
    }

    /**
     * Discovers the models the configured server can serve via its standard
     * OpenAI-compatible `GET {base}/models` listing. Doubles as the
     * connection probe (it fails the same way an unreachable server would).
     *
     * @param array<string, string> $configuration
     * @return list<string>
     */
    public function listModels(array $configuration): array
    {
        $response = $this->sendGetRequest(
            $this->openAiCompatibleModelsEndpoint($this->getEndpointUrl($configuration)),
            $this->getBearerAuthorizationHeaders($configuration)
        );

        $models = [];
        foreach ($response['data'] ?? [] as $model) {
            if (is_array($model) && isset($model['id']) && is_string($model['id']) && $model['id'] !== '') {
                $models[] = $model['id'];
            }
        }

        sort($models);

        return $models;
    }

    public function supportsConversations(): bool
    {
        return true;
    }

    /**
     * Custom servers are expected to be OpenAI-compatible. The model comes from
     * the request or the saved configuration (see {@link complete()}).
     *
     * @param array<string, string> $configuration
     */
    public function converse(AIConversationRequest $request, array $configuration): AIConversationResponse
    {
        return $this->converseChatCompletion(
            $request->withModel($this->resolveConfiguredModel($request->getModel(), $configuration)),
            $this->getChatCompletionsEndpoint($this->getEndpointUrl($configuration)),
            $this->getBearerAuthorizationHeaders($configuration)
        );
    }

    /**
     * Resolves the model to send: the per-request model wins, otherwise the
     * model saved in the provider configuration. There is no hardcoded fallback
     * because model names are server-specific; a missing model is a clear
     * configuration error rather than a silent wrong-model call.
     *
     * @param array<string, string> $configuration
     */
    private function resolveConfiguredModel(?string $requestModel, array $configuration): string
    {
        $model = $requestModel !== null && $requestModel !== ''
            ? $requestModel
            : trim($configuration['model'] ?? '');

        if ($model === '') {
            throw new AIProviderClientException(
                'No model is configured for the custom provider. Select a model in the AI Providers settings.'
            );
        }

        return $model;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getExtraChatCompletionPayload(AIRequest $request): array
    {
        return ['think' => $this->wantsThinking($request)];
    }

    private function getChatCompletionsEndpoint(string $endpointUrl): string
    {
        if (preg_match('#/chat/completions/?$#', $endpointUrl)) {
            return $endpointUrl;
        }

        return rtrim($endpointUrl, '/') . '/chat/completions';
    }
}
