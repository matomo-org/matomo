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
 * be able to reach the Marketplace, and there is nothing to promote once the customer has
 * the plugin - installed on disk even if deactivated, or covered by their license and not
 * downloaded yet.
 */
class PromotionEligibility
{
    private Manager $manager;

    private Config $config;

    private PremiumEntitlements $entitlements;

    public function __construct(Manager $manager, Config $config, PremiumEntitlements $entitlements)
    {
        $this->manager = $manager;
        $this->config = $config;
        $this->entitlements = $entitlements;
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

        // Present on disk, whether or not it is switched on. A plugin sitting deactivated
        // has already been obtained, and offering it again reads as not knowing what the
        // customer has. `isPluginActivated()` alone let that case through.
        if ($this->manager->isPluginInFilesystem($pluginName)) {
            return false;
        }

        // Or paid for and not installed yet. Only a definite yes rules the promotion out:
        // the Marketplace being unreachable means there is no license information to go on,
        // and that must not silence every promotion on an instance that cannot reach it.
        return true !== $this->entitlements->isLicensed($pluginName);
    }
}
