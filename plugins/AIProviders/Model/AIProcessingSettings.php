<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders\Model;

use InvalidArgumentException;
use Piwik\Option;
use Piwik\Piwik;

/**
 * Stores which categories of data AI features may send to an AI provider.
 *
 * Both categories are disabled by default and apply to the whole instance.
 * Only a superuser may allow AI processing, never the hosting environment.
 *
 * Consuming plugins check {@link isEnabled()} for the data they send before
 * making AI requests, including API and background requests. AIProviders does
 * not enforce or police the categories when forwarding requests to a provider.
 */
class AIProcessingSettings
{
    /** Configuration and structured website information, such as site names, goals and segments. */
    public const CATEGORY_NON_ANALYTICS = 'nonAnalytics';

    /** Summarised metrics, dimensions and report results. Never raw visitor-level data. */
    public const CATEGORY_AGGREGATED_ANALYTICS = 'aggregatedAnalytics';

    public const CATEGORIES = [self::CATEGORY_NON_ANALYTICS, self::CATEGORY_AGGREGATED_ANALYTICS];

    private const OPTION_NAME = 'AIProviders.aiProcessingCategories';

    public function isEnabled(string $category): bool
    {
        return in_array($category, $this->getEnabledCategories(), true);
    }

    /**
     * @return list<string>
     */
    public function getEnabledCategories(): array
    {
        $stored = json_decode((string) Option::get(self::OPTION_NAME), true);

        return is_array($stored) ? array_values(array_intersect(self::CATEGORIES, $stored)) : [];
    }

    /**
     * Enables the given categories and disables all others.
     *
     * @param array<string> $categories
     */
    public function setEnabledCategories(array $categories): void
    {
        $unknown = array_diff($categories, self::CATEGORIES);

        if ($unknown !== []) {
            throw new InvalidArgumentException(
                Piwik::translate('AIProviders_ErrorUnknownAIProcessingCategory', (string) reset($unknown))
            );
        }

        $previous = $this->getEnabledCategories();
        $enabled = array_values(array_intersect(self::CATEGORIES, $categories));

        if ($enabled === $previous) {
            return;
        }

        Option::set(self::OPTION_NAME, json_encode($enabled));

        /**
         * Triggered after a super user changed which data categories AI features may process.
         *
         * @param list<string> $enabled Categories that are now enabled.
         * @param list<string> $previous Categories that were enabled before.
         */
        Piwik::postEvent('AIProviders.aiProcessingSettingsChanged', [$enabled, $previous]);
    }

    /**
     * Returns the AI features plugins registered per category, shown as "Used by" on the settings page.
     *
     * @return array<string, list<array{name: string, disclosureUrl: string}>>
     */
    public function getFeaturesByCategory(): array
    {
        $features = array_fill_keys(self::CATEGORIES, []);

        /**
         * Triggered to let plugins list their AI features under the data category they need.
         *
         * **Example**
         *
         *     public function addAIProcessingFeatures(array &$features): void
         *     {
         *         $features[AIProcessingSettings::CATEGORY_AGGREGATED_ANALYTICS][] = [
         *             'name' => Piwik::translate('MyPlugin_FeatureName'),
         *             'disclosureUrl' => 'https://matomo.org/faq/...',
         *         ];
         *     }
         *
         * @param array<string, list<array{name: string, disclosureUrl: string}>> &$features Keyed by category.
         */
        Piwik::postEvent('AIProviders.addAIProcessingFeatures', [&$features]);

        return array_intersect_key($features, array_flip(self::CATEGORIES));
    }
}
