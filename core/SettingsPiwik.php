<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik;

use Exception;

/**
 * Class SettingsPiwik
 * @package Piwik
 *
 * @api
 */
class SettingsPiwik
{
    /**
     * Get salt from [superuser] section
     *
     * @return string
     */
    public static function getSalt()
    {
        static $salt = null;
        if (is_null($salt)) {
            $salt = @Config::getInstance()->superuser['salt'];
        }
        return $salt;
    }

    /**
     * Should Piwik check that the login & password have minimum length and valid characters?
     *
     * @return bool  True if checks enabled; false otherwise
     */
    public static function isUserCredentialsSanityCheckEnabled()
    {
        return Config::getInstance()->General['disable_checks_usernames_attributes'] == 0;
    }

    /**
     * @see getKnownSegmentsToArchive
     *
     * @var array
     */
    public static $cachedKnownSegmentsToArchive = null;

    /**
     * Segments to pre-process
     *
     * @return string
     */
    static public function getKnownSegmentsToArchive()
    {
        if (self::$cachedKnownSegmentsToArchive === null) {
            $segments = Config::getInstance()->Segments;
            $segmentsToProcess = isset($segments['Segments']) ? $segments['Segments'] : array();

            /**
             * This event is triggered when the automatic archiving runs.
             * You can use it to add Segments to the list of segments to pre-process during archiving.
             * Segments specified in this array will be pre-processed for all websites.
             */
            Piwik::postEvent('Segments.getKnownSegmentsToArchiveAllSites', array(&$segmentsToProcess));

            self::$cachedKnownSegmentsToArchive = array_unique($segmentsToProcess);
        }

        return self::$cachedKnownSegmentsToArchive;
    }


    public static function getKnownSegmentsToArchiveForSite($idSite)
    {
        $segments = array();

        /**
         * This event is triggered when the automatic archiving runs.
         * You can use it to add Segments to the list of segments to pre-process during archiving,
         * for this particular website ID $idSite.
         */
        Piwik::postEvent('Segments.getKnownSegmentsToArchiveForSite', array(&$segments, $idSite));
        return $segments;
    }

    /**
     * Number of websites to show in the Website selector
     *
     * @return int
     */
    public static function getWebsitesCountToDisplay()
    {
        $count = max(Config::getInstance()->General['site_selector_max_sites'],
            Config::getInstance()->General['autocomplete_min_sites']);
        return (int)$count;
    }

    /**
     * Cache for result of getPiwikUrl.
     * Can be overwritten for testing purposes only.
     *
     * @var string
     */
    static public $piwikUrlCache = null;

    /**
     * Returns the cached the Piwik URL, eg. http://demo.piwik.org/ or http://example.org/piwik/
     * If not found, then tries to cache it and returns the value.
     *
     * If the Piwik URL changes (eg. Piwik moved to new server), the value will automatically be refreshed in the cache.
     *
     * @return string
     */
    public static function getPiwikUrl()
    {
        // Only set in tests
        if (self::$piwikUrlCache !== null) {
            return self::$piwikUrlCache;
        }

        $key = 'piwikUrl';
        $url = Option::get($key);
        if (Common::isPhpCliMode()
            // in case archive.php is triggered with domain localhost
            || SettingsServer::isArchivePhpTriggered()
            || defined('PIWIK_MODE_ARCHIVE')
        ) {
            return $url;
        }

        $currentUrl = Common::sanitizeInputValue(Url::getCurrentUrlWithoutFileName());

        if (empty($url)
            // if URL changes, always update the cache
            || $currentUrl != $url
        ) {
            if (strlen($currentUrl) >= strlen('http://a/')) {
                Option::set($key, $currentUrl, $autoLoad = true);
            }
            $url = $currentUrl;
        }
        return $url;
    }

    /**
     * Returns true if Segmentation is allowed for this user
     *
     * @return bool
     */
    public static function isSegmentationEnabled()
    {
        return !Piwik::isUserIsAnonymous()
        || Config::getInstance()->General['anonymous_user_enable_use_segments_API'];
    }

    /**
     * Should we process and display Unique Visitors?
     * -> Always process for day/week/month periods
     * For Year and Range, only process if it was enabled in the config file,
     *
     * @param string $periodLabel Period label (e.g., 'day')
     * @return bool
     */
    public static function isUniqueVisitorsEnabled($periodLabel)
    {
        $generalSettings = Config::getInstance()->General;

        $settingName = "enable_processing_unique_visitors_$periodLabel";
        $result = !empty($generalSettings[$settingName]) && $generalSettings[$settingName] == 1;

        // check enable_processing_unique_visitors_year_and_range for backwards compatibility
        if (($periodLabel == 'year' || $periodLabel == 'range')
            && isset($generalSettings['enable_processing_unique_visitors_year_and_range'])
        ) {
            $result |= $generalSettings['enable_processing_unique_visitors_year_and_range'] == 1;
        }

        return $result;
    }

    /**
     * If Piwik uses per-domain config file, also make tmp/ folder per-domain
     * @param $path
     * @return string
     * @throws \Exception
     */
    public static function rewriteTmpPathWithHostname($path)
    {
        try {
            $configByHost = Config::getInstance()->getConfigHostnameIfSet();
        } catch (Exception $e) {
            // Config file not found
        }
        if (empty($configByHost)) {
            return $path;
        }

        $tmp = '/tmp/';
        if (($posTmp = strrpos($path, $tmp)) === false) {
            throw new Exception("The path $path was expected to contain the string /tmp/ ");
        }

        $tmpToReplace = $tmp . $configByHost . '/';

        // replace only the latest occurrence (in case path contains twice /tmp)
        $path = substr_replace($path, $tmpToReplace, $posTmp, strlen($tmp));

        return $path;
    }
}
