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
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Tracker\Request;
use Piwik\Tracker\VisitExcluded;

class TrackingSpamPrevention extends \Piwik\Plugin
{
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

        if (
            $settings->block_clouds->getValue()
            && $blockGeoIp->isExcludedProvider($ipString, $browserLang)
        ) {
            // only needs to be done when cloud providers are blocked specifically
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
