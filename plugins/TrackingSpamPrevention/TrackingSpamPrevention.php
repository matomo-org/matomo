<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention;

use Matomo\Network\IP;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Log\LoggerInterface;
use Piwik\Settings\Storage\Factory as StorageFactory;
use Piwik\Tracker\Request;
use Piwik\Tracker\VisitExcluded;

class TrackingSpamPrevention extends \Piwik\Plugin
{
    private const PLUGIN_NAME = 'TrackingSpamPrevention';

    private $isInstalledInThisRequest = false;

    public function registerEvents()
    {
        return [
            'Tracker.isExcludedVisit' => 'isExcludedVisit',
            'Tracker.setTrackerCacheGeneral' => 'setTrackerCacheGeneral',
            'TrackingSpamPrevention.banIp' => 'onBanIp',
        ];
    }

    public function install()
    {
        $this->isInstalledInThisRequest = true;
        $config = new Configuration();
        $config->install();
        $this->storeDefaultCloudBlockingSettings();
    }

    /**
     * Cloud blocking is on for installs that are genuinely new, while the settings themselves default
     * to the pre-6.0.0-b2 behaviour so that an install which has not run the migration yet keeps the
     * blocking it had. Writing the on values here is what separates the two.
     *
     * Nothing in here may throw. install() is reached from Plugin\Manager::installLoadedPlugins() on
     * every front controller dispatch, which during the web installer happens before the tables exist,
     * and Plugin\Manager::executePluginInstall() turns any exception into a fatal that stops the
     * installation. Core makes the same concession immediately after activate(), where
     * Plugin\Manager::savePluginTime() only rethrows once SettingsPiwik::isMatomoInstalled().
     *
     * The values are written straight to the settings storage rather than through
     * SystemSettings::save(), which would see block_clouds change and synchronously fetch every cloud
     * provider's IP ranges over the network in the middle of an install.
     */
    private function storeDefaultCloudBlockingSettings(): void
    {
        try {
            $pluginConfig = Config::getInstance()->{self::PLUGIN_NAME};
            $pluginConfig = is_array($pluginConfig) ? $pluginConfig : [];

            $storage = StaticContainer::get(StorageFactory::class)->getPluginStorage(self::PLUGIN_NAME, '');

            // a config.ini.php entry shadows the stored value, so writing one would be dead weight
            if (!array_key_exists('block_clouds', $pluginConfig)) {
                $storage->setValue('block_clouds', true);
            }
            if (!array_key_exists('cloud_blocking_mode', $pluginConfig)) {
                $storage->setValue('cloud_blocking_mode', SystemSettings::CLOUD_BLOCKING_DEFAULT_LIST);
            }

            $storage->save();
        } catch (\Throwable $e) {
            // A new install that starts with cloud blocking off is a wrong default an admin can change;
            // an installer that dies here is a broken product. Report it and carry on.
            try {
                StaticContainer::get(LoggerInterface::class)->warning(
                    'TrackingSpamPrevention could not store its default cloud blocking settings on install: {message}',
                    ['message' => $e->getMessage()]
                );
            } catch (\Throwable $ignored) {
                // logging is not available this early either; there is nothing further to try
            }
        }
    }

    public function activate()
    {
        $this->isInstalledInThisRequest = true;
    }

    public function uninstall()
    {
        $config = new Configuration();
        $config->uninstall();
    }

    public function onBanIp($ipRange, $ip)
    {
        $settings = $this->getSystemSettings();
        $email = $settings->notification_email->getValue();
        $maxActions = $settings->max_actions->getValue();
        $locationData = $this->getBlockGeoIp()->detectLocation($ip, Common::getBrowserLanguage());
        $now = Date::now()->getDatetime();

        $banIpMail = new BanIpNotificationEmail();
        $banIpMail->send($ipRange, $ip, $email, $maxActions, $locationData, $now);
    }

    public function setTrackerCacheGeneral(&$cache)
    {
        $isTestMode = defined('PIWIK_TEST_MODE') && PIWIK_TEST_MODE;
        if ($this->isInstalledInThisRequest && !$isTestMode) {
            // dont do anything when plugin gets loaded as DI config would not be loaded yet and it would
            // cause an issue with activity log since it does a geolocation which uses the tracker cache
            return;
        }
        $ranges = $this->getBlockedIpRanges();
        $cache[BlockedIpRanges::OPTION_KEY] = $ranges->getBlockedRanges();
    }

    public function isExcludedVisit(&$excluded, Request $request)
    {
        if ($excluded) {
            return; // already excluded, not needed to check
        }

        $visitExcluded = new VisitExcluded($request);
        $ipString = $request->getIpString();

        $ip = IP::fromStringIP($ipString);
        if ($visitExcluded->isChromeDataSaverUsed($ip)) {
            Common::printDebug("Not excluding visit as chrome data saver is used");
            return;
        }

        if (StaticContainer::get(AllowListIpRange::class)->isAllowed($ipString)) {
            Common::printDebug("Not excluding visit as it matches an IP range that is always allowed");
            return;
        }

        if (StaticContainer::get(BlockListIpRange::class)->isBlocked($ipString)) {
            Common::printDebug("Excluding visit as it matches an IP range on the block list");
            $excluded = 'excluded: ip block list';
            return;
        }

        $settings = $this->getSystemSettings();
        $blockGeoIp = $this->getBlockGeoIp();
        $browserLang = $request->getBrowserLanguage();

        $browserDetection = new BrowserDetection();
        $clientHints = json_encode($request->getClientHints());
        if (
            $settings->blockHeadless->getValue() &&
            (
                $browserDetection->isHeadlessBrowser($request->getUserAgent()) ||
                $browserDetection->isHeadlessBrowser($clientHints)
            )
        ) {
            // note above user agent could have been overwritten with UA parameter but that's fine since it's easy to change useragent anyway
            Common::printDebug("Excluding visit as headless browser detected");
            $excluded = 'excluded: headless browser';
            return;
        }

        if ($blockGeoIp->isExcludedProvider($ipString, $browserLang)) {
            Common::printDebug("Excluding visit as geoip detects a cloud provider");
            $excluded = 'excluded: geoip cloud provider';
            return;
        }

        if ($this->getBlockedIpRanges()->isExcluded($ipString)) {
            // we also execute this when block clouds disabled because it might contain banned ips
            Common::printDebug("Excluding visit as IP originates from a cloud provider");
            $excluded = 'excluded: ip cloud provider';
            return;
        }

        if (
            $blockGeoIp->isExcludedCountry(
                $ipString,
                $browserLang,
                $settings->getExcludedCountryCodes(),
                $settings->getIncludedCountryCodes()
            )
        ) {
            Common::printDebug("Excluding visit as geoip detects an excluded (or not included) country");
            $excluded = 'excluded: country';
            return;
        }

        if (
            $settings->blockServerSideLibraries->getValue() &&
            $browserDetection->isLibrary($request->getUserAgent())
        ) {
            Common::printDebug("Excluding visit as Server Side Library detected");
            $excluded = 'excluded: ServerSideLibraries-';
            return;
        }
    }

    private function getSystemSettings()
    {
        return StaticContainer::get(SystemSettings::class);
    }

    private function getBlockedIpRanges()
    {
        return StaticContainer::get(BlockedIpRanges::class);
    }

    private function getBlockGeoIp()
    {
        return StaticContainer::get(BlockedGeoIp::class);
    }

    public function isTrackerPlugin()
    {
        return true;
    }
}
