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
 * Contains helper methods that can be used to get common Piwik settings.
 * 
 * @package Piwik
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
     * Returns the list of stored segments to pre-process for all sites when executing cron archiving.
     *
     * @return array The list of stored segments that apply to all sites.
     * @api
     */
    public static function getKnownSegmentsToArchive()
    {
        if (self::$cachedKnownSegmentsToArchive === null) {
            $segments = Config::getInstance()->Segments;
            $segmentsToProcess = isset($segments['Segments']) ? $segments['Segments'] : array();

            /**
             * Triggered during the cron archiving process to collect segments that
             * should be pre-processed for all websites. The archiving process will be launched
             * for each of these segments when archiving data for each site.
             * 
             * This event can be used to add segments to be pre-processed. This can be provide
             * enhanced performance if your plugin depends on data from a specific segment.
             * 
             * Note: If you just want to add a segment that is managed by the user, you should use the
             * SegmentEditor API.
             * 
             * @param array &$segmentsToProcess List of segment definitions, eg,
             *                                  ```
             *                                  array(
             *                                      'browserCode=ff;resolution=800x600',
             *                                      'country=JP;city=Tokyo'
             *                                  )
             *                                  ```
             *                                  Add segments to process to this array in your event
             *                                  handler.
             */
            Piwik::postEvent('Segments.getKnownSegmentsToArchiveAllSites', array(&$segmentsToProcess));

            self::$cachedKnownSegmentsToArchive = array_unique($segmentsToProcess);
        }

        return self::$cachedKnownSegmentsToArchive;
    }

    /**
     * Returns the list of stored segments to pre-process for an individual site when executing
     * cron archiving.
     * 
     * @param int $idSite The ID of the site to get stored segments for.
     * @return string The list of stored segments that apply to the requested site.
     */
    public static function getKnownSegmentsToArchiveForSite($idSite)
    {
        $segments = array();

        /**
         * Triggered during the cron archiving process to collect segments that
         * should be pre-processed for one specific site. The archiving process will be launched
         * for each of these segments when archiving data for that one site.
         * 
         * This event can be used to add segments to be pre-processed. This can be provide
         * enhanced performance if your plugin depends on data from a specific segment.
         * 
         * Note: If you just want to add a segment that is managed by the user, you should use the
         * SegmentEditor API.
         * 
         * @param array &$segmentsToProcess List of segment definitions, eg,
         *                                  ```
         *                                  array(
         *                                      'browserCode=ff;resolution=800x600',
         *                                      'country=JP;city=Tokyo'
         *                                  )
         *                                  ```
         *                                  Add segments to process to this array in your event
         *                                  handler.
         * @param int $idSite The ID of the site to get segments for.
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
     * Returns the URL to this Piwik instance, eg. http://demo.piwik.org/ or http://example.org/piwik/.
     *
     * @return string
     * @api
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
     * Returns true if segmentation is allowed for this user, false if otherwise.
     *
     * @return bool
     * @api
     */
    public static function isSegmentationEnabled()
    {
        return !Piwik::isUserIsAnonymous()
        || Config::getInstance()->General['anonymous_user_enable_use_segments_API'];
    }

    /**
     * Returns true if unique visitors should be processed for the given period type.
     * 
     * Unique visitor processing is controlled by the **[General] enable_processing_unique_visitors_...**
     * INI config options. By default, day/week/month periods always process unique visitors and
     * year/range are not.
     *
     * @param string $periodLabel `"day"`, `"week"`, `"month"`, `"year"` or `"range"`
     * @return bool
     * @api
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