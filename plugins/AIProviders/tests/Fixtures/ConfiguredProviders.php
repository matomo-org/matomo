<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\AIProviders\tests\Fixtures;

use Piwik\Plugins\AIProviders\AIProviders;
use Piwik\Plugins\AIProviders\Model\AIProcessingSettings;
use Piwik\Plugins\AIProviders\Model\Configuration;
use Piwik\Tests\Fixtures\EmptySite;

/**
 * Two connected providers (OpenAI, Anthropic with Anthropic as default) and one enabled AI processing category,
 * so the UI can be tested without calling a provider.
 */
class ConfiguredProviders extends EmptySite
{
    public function setUp(): void
    {
        parent::setUp();

        (new Configuration())->saveSettings(
            AIProviders::getAvailableProviders(),
            'anthropic',
            '',
            json_encode([
                'openai' => ['apiKey' => 'sk-test-openai'],
                'anthropic' => ['apiKey' => 'sk-test-anthropic'],
            ])
        );

        (new AIProcessingSettings())->setEnabledCategories([AIProcessingSettings::CATEGORY_AGGREGATED_ANALYTICS]);
    }
}
