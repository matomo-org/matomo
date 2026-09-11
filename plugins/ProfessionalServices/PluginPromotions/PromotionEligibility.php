<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions;

use Piwik\Config;
use Piwik\Plugin\Manager;
use Piwik\ProfessionalServices\Advertising;

/**
 * The conditions that have to hold before a plugin may be promoted at all, whatever
 * triggered the promotion and wherever it is displayed.
 *
 * These are the same conditions the promo widgets check in
 * {@see \Piwik\Plugins\ProfessionalServices\PromoWidgetApplicable}, minus the widget
 * specific dismissal: promotions must be enabled in the configuration, the instance must
 * be able to reach the Marketplace, and there is nothing to promote once the plugin is
 * already installed.
 */
class PromotionEligibility
{
    private Manager $manager;

    private Config $config;

    public function __construct(Manager $manager, Config $config)
    {
        $this->manager = $manager;
        $this->config = $config;
    }

    public function isAllowedForPlugin(string $pluginName): bool
    {
        if (Advertising::isAdsEnabledInConfig($this->config->General) === false) {
            return false;
        }

        if ($this->manager->isPluginActivated('Marketplace') === false) {
            return false;
        }

        if ((bool) $this->config->General['enable_internet_features'] === false) {
            return false;
        }

        return $this->manager->isPluginActivated($pluginName) === false;
    }
}
