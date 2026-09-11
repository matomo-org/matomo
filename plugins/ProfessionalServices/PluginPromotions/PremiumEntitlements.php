<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions;

use Piwik\Plugin\Manager;
use Piwik\Plugins\Marketplace\Consumer;

/**
 * What premium products this instance holds, for the promotions that are pitched on the
 * breadth of that use rather than on a website's reports.
 *
 * The license key is the authority here: the bundle promotions are specified against the
 * "number of plugins on license key", and it is also the only place a bundle can show up,
 * since holding one is a property of the license and not of the plugins directory.
 *
 * Every method returns null when the Marketplace could not be reached, which is a
 * different answer from "none". A caller that cannot tell what the consumer holds must
 * not promote a bundle: the one thing these promotions have to avoid is pitching a bundle
 * at someone who already bought it.
 */
class PremiumEntitlements
{
    private Consumer $consumer;

    private Manager $pluginManager;

    public function __construct(Consumer $consumer, Manager $pluginManager)
    {
        $this->consumer = $consumer;
        $this->pluginManager = $pluginManager;
    }

    /**
     * How many distinct premium products this instance uses, counting anything the license
     * covers as well as anything premium that is activated locally. Bundles are excluded:
     * a bundle is the thing being promoted, not one of the products that justifies it.
     *
     * @return int|null null when the Marketplace could not be reached
     */
    public function countPremiumProducts(): ?int
    {
        $licensed = $this->getLicensedProductNames();

        if (null === $licensed) {
            return null;
        }

        $names = array_merge($licensed, $this->getActivatedPremiumPluginNames());
        $names = array_diff(array_unique($names), PremiumBundle::getAllNames());

        return count($names);
    }

    /**
     * The tier of the largest bundle on the license, or 0 when it holds none.
     *
     * @return int|null null when the Marketplace could not be reached
     */
    public function getHighestBundleTierHeld(): ?int
    {
        $licensed = $this->getLicensedProductNames();

        if (null === $licensed) {
            return null;
        }

        $highest = 0;
        foreach ($licensed as $name) {
            $highest = max($highest, PremiumBundle::getTier($name));
        }

        return $highest;
    }

    /**
     * Whether the Marketplace offers the given bundle to this consumer at all.
     *
     * Not every bundle is sold to every account: the Team bundle is a later addition and a
     * legacy account is not offered it, so promoting it there would lead somewhere the
     * customer cannot buy. What the Marketplace lists for a consumer answers that, because
     * every catalogue request carries the license key and comes back scoped to it.
     *
     * Read from the already cached catalogue, which an hourly task keeps warm
     * ({@see \Piwik\Plugins\Marketplace\Tasks::warmCacheEntries()}), so this costs no
     * request of its own. A cold cache looks the same as a bundle that is not offered, and
     * both answer false: silence until the catalogue is warm is the safe way round, where
     * pitching a bundle the customer cannot buy is not.
     */
    public function isBundleOffered(string $bundleName): bool
    {
        return null !== $this->consumer->getApiClient()->findInCachedOverviewLists($bundleName);
    }

    /**
     * The products the license covers, by name.
     *
     * A license row is counted when the Marketplace says it is valid, which is the field
     * that survives the wording of `status` changing; an expired or cancelled row is not
     * evidence of current use.
     *
     * @return string[]|null null when the Marketplace could not be reached
     */
    private function getLicensedProductNames(): ?array
    {
        $licenses = $this->consumer->getConsumerPluginLicenses();

        if (null === $licenses) {
            return null;
        }

        $names = [];
        foreach ($licenses as $pluginName => $license) {
            if (!empty($license['isValid'])) {
                $names[] = (string) $pluginName;
            }
        }

        return $names;
    }

    /**
     * Premium plugins activated on this instance, which is the same test core itself uses
     * in `Manager::hasPremiumFeatures()`: a plugin is premium when its own `plugin.json`
     * gives it a price. Read from the filesystem, so it needs no Marketplace call.
     *
     * @return string[]
     */
    private function getActivatedPremiumPluginNames(): array
    {
        $names = [];
        foreach ($this->pluginManager->getPluginsLoadedAndActivated() as $plugin) {
            if ($plugin->isPremiumFeature()) {
                $names[] = $plugin->getPluginName();
            }
        }

        return $names;
    }
}
