<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Tracker;
use Piwik\DeviceDetector\DeviceDetectorFactory;
use Piwik\SettingsPiwik;

class Settings // TODO: merge w/ visitor recognizer or make it it's own service. the class name is required for BC.
{
    const OS_BOT = 'BOT';

    /**
     * If `true`, the config ID for a visitor will be the same no matter what site is being tracked.
     * If `false, the config ID will be different.
     *
     * @var bool
     */
    private $isSameFingerprintsAcrossWebsites;

    public function __construct($isSameFingerprintsAcrossWebsites)
    {
        $this->isSameFingerprintsAcrossWebsites = $isSameFingerprintsAcrossWebsites;
    }

    public function getConfigId(Request $request, $ipAddress)
    {
        list($plugin_Flash, $plugin_Java, $plugin_Quicktime, $plugin_RealPlayer, $plugin_PDF,
            $plugin_WindowsMedia, $plugin_Silverlight, $plugin_Cookie) = $request->getPlugins();

        $userAgent = $request->getUserAgent();

        $deviceDetector = StaticContainer::get(DeviceDetectorFactory::class)->makeInstance($userAgent, $request->getClientHints());
        $aBrowserInfo   = $deviceDetector->getClient();

        if (empty($aBrowserInfo['type']) || 'browser' !== $aBrowserInfo['type']) {
            // for now only track browsers
            unset($aBrowserInfo);
        }

        $browserName    = !empty($aBrowserInfo['short_name']) ? $aBrowserInfo['short_name'] : 'UNK';
        $browserVersion = !empty($aBrowserInfo['version']) ? $aBrowserInfo['version'] : '';

        if ($deviceDetector->isBot()) {
            $os = self::OS_BOT;
        } else {
            $os = $deviceDetector->getOS();
            $os = empty($os['short_name']) ? 'UNK' : $os['short_name'];
        }

        $client = $deviceDetector->getClient();
        if (!empty($client['name']) && $client['name'] === 'Internet Explorer') {
            // we assume cookies are disabled... when in tracker cookies are disabled, this ensures when upgrading to this version
            // that no fingerprint changes in the 30min window during the upgrade...
            // We don't include it anymore as it otherwise may cause new visits to be created when switching between
            // cookies disabled and enabled in IE11 or older. Before Matomo 3.13.7 when cookies were disabled, then
            // this value was set to 0. For people with cookies enabled the fingerprint is not as relevant as the visitorId
            // is used to identify a visitor
            $plugin_Cookie = '0';
        }

        $browserLang = substr($request->getBrowserLanguage(), 0, 20); // limit the length of this string to match db
        $trackerConfig = Config::getInstance()->Tracker;

        $fingerprintSalt = '';

        // fingerprint salt won't work when across multiple sites since all sites could have different timezones
        // also cant add fingerprint salt for a specific day when we dont create new visit after midnight
        if (!$this->isSameFingerprintsAcrossWebsites && !empty($trackerConfig['create_new_visit_after_midnight'])) {
            $cache = Cache::getCacheWebsiteAttributes($request->getIdSite());
            $date = Date::factory((int) $request->getCurrentTimestamp());
            $fingerprintSaltKey = new FingerprintSalt();
            $dateString = $fingerprintSaltKey->getDateString($date, $cache['timezone']);

            if (!empty($cache[FingerprintSalt::OPTION_PREFIX . $dateString])) {
                $fingerprintSalt = $cache[FingerprintSalt::OPTION_PREFIX . $dateString];
            } else {
                // we query the DB directly for requests older than 2-3 days...
                $fingerprintSalt = $fingerprintSaltKey->getSalt($dateString, $request->getIdSite());
            }

            $fingerprintSalt .= $dateString;

            if (defined('PIWIK_TEST_MODE') && PIWIK_TEST_MODE) {
                $fingerprintSalt = ''; // use fixed value so they don't change randomly in tests
            }
        }

        return $this->getConfigHash(
            $request,
            $os,
            $browserName,
            $browserVersion,
            $plugin_Flash,
            $plugin_Java,
            $plugin_Quicktime,
            $plugin_RealPlayer,
            $plugin_PDF,
            $plugin_WindowsMedia,
            $plugin_Silverlight,
            $plugin_Cookie,
            $ipAddress,
            $browserLang,
            $fingerprintSalt);
    }

    /**
     * Returns a 64-bit hash that attempts to identify a user.
     * Maintaining some privacy by default, eg. prevents the merging of several Piwik serve together for matching across instances..
     *
     * @param $os
     * @param $browserName
     * @param $browserVersion
     * @param $plugin_Flash
     * @param $plugin_Java
     * @param $plugin_Quicktime
     * @param $plugin_RealPlayer
     * @param $plugin_PDF
     * @param $plugin_WindowsMedia
     * @param $plugin_Silverlight
     * @param $plugin_Cookie
     * @param $ip
     * @param $browserLang
     * @param $fingerprintHash
     * @return string
     */
    protected function getConfigHash(Request $request, $os, $browserName, $browserVersion, $plugin_Flash, $plugin_Java,
                                     $plugin_Quicktime, $plugin_RealPlayer, $plugin_PDF,
                                     $plugin_WindowsMedia, $plugin_Silverlight, $plugin_Cookie, $ip,
                                     $browserLang, $fingerprintHash)
    {
        // prevent the config hash from being the same, across different Piwik instances
        // (limits ability of different Piwik instances to cross-match users)
        $salt = SettingsPiwik::getSalt();

        $configString =
              $os
            . $browserName . $browserVersion
            . $plugin_Flash . $plugin_Java . '0' . $plugin_Quicktime . $plugin_RealPlayer . $plugin_PDF
            . $plugin_WindowsMedia . '0' . $plugin_Silverlight . $plugin_Cookie
            . $ip
            . $browserLang
            . $salt
            . $fingerprintHash;

        if (!$this->isSameFingerprintsAcrossWebsites) {
            $configString .= $request->getIdSite();
        }

        $hash = md5($configString, $raw_output = true);

        return substr($hash, 0, Tracker::LENGTH_BINARY_ID);
    }
}
