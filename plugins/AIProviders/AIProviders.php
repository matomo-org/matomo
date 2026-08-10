<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders;

use Piwik\Piwik;
use Piwik\Plugin;

class AIProviders extends Plugin
{
    public function registerEvents(): array
    {
        return [
            'AIProviders.addAIProviders'             => 'addAIProviders',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        ];
    }

    /**
     * Registers the built-in AI providers.
     *
     * Provider IDs are unique and cannot be overwritten: the first registration
     * for an ID wins (see {@link AIProvidersList::addProvider()}). This protects
     * the built-in providers, and a provider that is centrally forced in a
     * managed environment, from being shadowed by another plugin.
     */
    public function addAIProviders(AIProvidersList $providers): void
    {
        $providers->addProvider(new Provider\Anthropic());
        $providers->addProvider(new Provider\Google());
        $providers->addProvider(new Provider\OpenAI());
        $providers->addProvider(new Provider\Bedrock());
        $providers->addProvider(new Provider\CustomProvider());
    }

    /**
     * Returns all AI providers registered by active plugins.
     */
    public static function getAvailableProviders(): AIProvidersList
    {
        $providers = new AIProvidersList();

        /**
         * Triggered to let plugins register AI providers.
         *
         * Plugins can add providers by calling `$providers->addProvider()` with
         * a `Piwik\Plugins\AIProviders\Provider\AIProvider` instance.
         *
         * **Example**
         *
         *     public function registerEvents()
         *     {
         *         return ['AIProviders.addAIProviders' => 'addAIProviders'];
         *     }
         *
         *     public function addAIProviders(AIProvidersList $providers): void
         *     {
         *         $providers->addProvider(new MyProvider());
         *     }
         *
         * @param AIProvidersList $providers Provider registry to mutate.
         */
        Piwik::postEvent('AIProviders.addAIProviders', [$providers]);

        /**
         * Triggered after providers have been registered, so plugins can remove
         * or adjust providers before they are shown or used.
         *
         * A managed environment that wants providers hidden from users but
         * still available to allowlisted plugins should demote them with
         * `$providers->setSelectable($id, false)` instead of removing them
         * (see {@link AIProvidersList}). To replace a built-in provider's
         * implementation under the same ID (registration is first-wins, so
         * shadowing it in `addAIProviders` is not possible), remove it here
         * and register the replacement.
         *
         * @param AIProvidersList $providers Provider registry to mutate.
         */
        Piwik::postEvent('AIProviders.filterAIProviders', [$providers]);

        return $providers;
    }

    public function getClientSideTranslationKeys(array &$translations): void
    {
        $translations[] = 'AIProviders_AnthropicDefaultModelDescription';
        $translations[] = 'AIProviders_ApiKey';
        $translations[] = 'AIProviders_ApiKeyAlreadyConfiguredPlaceholder';
        $translations[] = 'AIProviders_ApiKeyPlaceholder';
        $translations[] = 'AIProviders_BedrockDescription';
        $translations[] = 'AIProviders_BedrockEndpointPlaceholder';
        $translations[] = 'AIProviders_BedrockEndpointTitle';
        $translations[] = 'AIProviders_BedrockUseFipsEndpoint';
        $translations[] = 'AIProviders_ClickTestConnectionToShowAvailableModels';
        $translations[] = 'AIProviders_ConfigurationIntro';
        $translations[] = 'AIProviders_CustomProviderDescription';
        $translations[] = 'AIProviders_DefaultBadge';
        $translations[] = 'AIProviders_DefaultCapabilityLevel';
        $translations[] = 'AIProviders_DefaultCapabilityLevelHelp';
        $translations[] = 'AIProviders_DefaultProvider';
        $translations[] = 'AIProviders_DefaultProviderHelp';
        $translations[] = 'AIProviders_DefaultsTitle';
        $translations[] = 'AIProviders_Disconnect';
        $translations[] = 'AIProviders_DisconnectSuccess';
        $translations[] = 'AIProviders_Disconnecting';
        $translations[] = 'AIProviders_EndpointUrl';
        $translations[] = 'AIProviders_EndpointUrlPlaceholder';
        $translations[] = 'AIProviders_GoogleDefaultModelDescription';
        $translations[] = 'AIProviders_InstantCapability';
        $translations[] = 'AIProviders_InstantCapabilityDescription';
        $translations[] = 'AIProviders_ManagedConfigurationHelp';
        $translations[] = 'AIProviders_MenuTitle';
        $translations[] = 'AIProviders_Model';
        $translations[] = 'AIProviders_NoDefaultProviderWarning';
        $translations[] = 'AIProviders_OpenAIDefaultModelDescription';
        $translations[] = 'AIProviders_RefreshModels';
        $translations[] = 'AIProviders_RequestFailed';
        $translations[] = 'AIProviders_SettingsSaveSuccess';
        $translations[] = 'AIProviders_StatusConnected';
        $translations[] = 'AIProviders_StatusNotConnected';
        $translations[] = 'AIProviders_TestConnection';
        $translations[] = 'AIProviders_TestConnectionSuccess';
        $translations[] = 'AIProviders_TestingConnection';
        $translations[] = 'AIProviders_ThinkingCapability';
        $translations[] = 'AIProviders_ThinkingCapabilityDescription';
        $translations[] = 'AIProviders_UnexpectedError';
        $translations[] = 'AIProviders_UnsavedChanges';
        $translations[] = 'General_Cancel';
        $translations[] = 'General_LoadingData';
    }
}
