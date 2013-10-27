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
namespace Piwik\Tracker;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\UrlHelper;

/**
 * @package Piwik
 * @subpackage Tracker
 */
class Referrer
{
    // @see detect*() referrer methods
    protected $typeReferrerAnalyzed;
    protected $nameReferrerAnalyzed;
    protected $keywordReferrerAnalyzed;
    protected $referrerHost;
    protected $referrerUrl;
    protected $referrerUrlParse;
    protected $currentUrlParse;
    protected $idsite;

    // Used to prefix when a adsense referrer is detected
    const LABEL_PREFIX_ADSENSE_KEYWORD = '(adsense) ';

    /**
     * Returns an array containing the following information:
     * - referer_type
     *        - direct            -- absence of referrer URL OR referrer URL has the same host
     *        - site                -- based on the referrer URL
     *        - search_engine        -- based on the referrer URL
     *        - campaign            -- based on campaign URL parameter
     *
     * - referer_name
     *         - ()
     *         - piwik.net            -- site host name
     *         - google.fr            -- search engine host name
     *         - adwords-search    -- campaign name
     *
     * - referer_keyword
     *         - ()
     *         - ()
     *         - my keyword
     *         - my paid keyword
     *         - ()
     *         - ()
     *
     * - referer_url : the same for all the referrer types
     *
     * @param string $referrerUrl must be URL Encoded
     * @param string $currentUrl
     * @param int $idSite
     * @return array
     */
    public function getReferrerInformation($referrerUrl, $currentUrl, $idSite)
    {
        $this->idsite = $idSite;

        // default values for the referer_* fields
        $referrerUrl = Common::unsanitizeInputValue($referrerUrl);
        if (!empty($referrerUrl)
            && !UrlHelper::isLookLikeUrl($referrerUrl)
        ) {
            $referrerUrl = '';
        }

        $currentUrl = PageUrl::cleanupUrl($currentUrl);

        $this->referrerUrl = $referrerUrl;
        $this->referrerUrlParse = @parse_url($this->referrerUrl);
        $this->currentUrlParse = @parse_url($currentUrl);
        $this->typeReferrerAnalyzed = Common::REFERRER_TYPE_DIRECT_ENTRY;
        $this->nameReferrerAnalyzed = '';
        $this->keywordReferrerAnalyzed = '';
        $this->referrerHost = '';

        if (isset($this->referrerUrlParse['host'])) {
            $this->referrerHost = $this->referrerUrlParse['host'];
        }

        $referrerDetected = false;

        if (!empty($this->currentUrlParse['host'])
            && $this->detectReferrerCampaign()
        ) {
            $referrerDetected = true;
        }

        if (!$referrerDetected) {
            if ($this->detectReferrerDirectEntry()
                || $this->detectReferrerSearchEngine()
            ) {
                $referrerDetected = true;
            }
        }

        if (!empty($this->referrerHost)
            && !$referrerDetected
        ) {
            $this->typeReferrerAnalyzed = Common::REFERRER_TYPE_WEBSITE;
            $this->nameReferrerAnalyzed = mb_strtolower($this->referrerHost, 'UTF-8');
        }

        $referrerInformation = array(
            'referer_type'    => $this->typeReferrerAnalyzed,
            'referer_name'    => $this->nameReferrerAnalyzed,
            'referer_keyword' => $this->keywordReferrerAnalyzed,
            'referer_url'     => $this->referrerUrl,
        );

        return $referrerInformation;
    }

    /**
     * Search engine detection
     * @return bool
     */
    protected function detectReferrerSearchEngine()
    {
        $searchEngineInformation = UrlHelper::extractSearchEngineInformationFromUrl($this->referrerUrl);

        /**
         * Triggered when detecting the search engine of a referrer URL.
         * 
         * Plugins can use this event to provide custom search engine detection
         * logic.
         * 
         * @param array &$searchEngineInformation An array with the following information:
         * 
         *                                        - **name**: The search engine name.
         *                                        - **keywords**: The search keywords used.
         *  
         *                                        This parameter will be defaulted to the results
         *                                        of Piwik's default search engine detection
         *                                        logic.
         * @param string referrerUrl The referrer URL.
         */
        Piwik::postEvent('Tracker.detectReferrerSearchEngine', array(&$searchEngineInformation, $this->referrerUrl));
        if ($searchEngineInformation === false) {
            return false;
        }
        $this->typeReferrerAnalyzed = Common::REFERRER_TYPE_SEARCH_ENGINE;
        $this->nameReferrerAnalyzed = $searchEngineInformation['name'];
        $this->keywordReferrerAnalyzed = $searchEngineInformation['keywords'];
        return true;
    }

    /**
     * @param string $string
     * @return bool
     */
    protected function detectCampaignFromString($string)
    {
        foreach ($this->campaignNames as $campaignNameParameter) {
            $campaignName = trim(urldecode(UrlHelper::getParameterFromQueryString($string, $campaignNameParameter)));
            if (!empty($campaignName)) {
                break;
            }
        }

        if (empty($campaignName)) {
            return false;
        }
        $this->typeReferrerAnalyzed = Common::REFERRER_TYPE_CAMPAIGN;
        $this->nameReferrerAnalyzed = $campaignName;

        foreach ($this->campaignKeywords as $campaignKeywordParameter) {
            $campaignKeyword = UrlHelper::getParameterFromQueryString($string, $campaignKeywordParameter);
            if (!empty($campaignKeyword)) {
                $this->keywordReferrerAnalyzed = trim(urldecode($campaignKeyword));
                break;
            }
        }

        // if the campaign keyword is empty, try to get a keyword from the referrer URL
        if (empty($this->keywordReferrerAnalyzed)) {
            // Set the Campaign keyword to the keyword found in the Referrer URL if any
            $referrerUrlInfo = UrlHelper::extractSearchEngineInformationFromUrl($this->referrerUrl);
            if (!empty($referrerUrlInfo['keywords'])) {
                $this->keywordReferrerAnalyzed = $referrerUrlInfo['keywords'];
            }

            // Set the keyword, to the hostname found, in a Adsense Referrer URL '&url=' parameter
            if (empty($this->keywordReferrerAnalyzed)
                && !empty($this->referrerUrlParse['query'])
                && !empty($this->referrerHost)
                && (strpos($this->referrerHost, 'google') !== false || strpos($this->referrerHost, 'doubleclick') !== false)
            ) {
                // This parameter sometimes is found & contains the page with the adsense ad bringing visitor to our site
                $adsenseReferrerParameter = 'url';
                $value = trim(urldecode(UrlHelper::getParameterFromQueryString($this->referrerUrlParse['query'], $adsenseReferrerParameter)));
                if (!empty($value)) {
                    $parsedAdsenseReferrerUrl = parse_url($value);
                    if (!empty($parsedAdsenseReferrerUrl['host'])) {
                        $this->keywordReferrerAnalyzed = self::LABEL_PREFIX_ADSENSE_KEYWORD . $parsedAdsenseReferrerUrl['host'];
                    }
                }
            }

            // or we default to the referrer hostname otherwise
            if (empty($this->keywordReferrerAnalyzed)) {
                $this->keywordReferrerAnalyzed = $this->referrerHost;
            }
        }

        return true;

    }

    /**
     * Campaign analysis
     * @return bool
     */
    protected function detectReferrerCampaign()
    {
        if (!isset($this->currentUrlParse['query'])
            && !isset($this->currentUrlParse['fragment'])
        ) {
            return false;
        }
        $campaignParameters = Common::getCampaignParameters();
        $this->campaignNames = $campaignParameters[0];
        $this->campaignKeywords = $campaignParameters[1];

        $found = false;

        // 1) Detect campaign from query string
        if (isset($this->currentUrlParse['query'])) {
            $found = $this->detectCampaignFromString($this->currentUrlParse['query']);
        }

        // 2) Detect from fragment #hash
        if (!$found
            && isset($this->currentUrlParse['fragment'])
        ) {
            $found = $this->detectCampaignFromString($this->currentUrlParse['fragment']);
        }
        return $found;
    }

    /**
     * We have previously tried to detect the campaign variables in the URL
     * so at this stage, if the referrer host is the current host,
     * or if the referrer host is any of the registered URL for this website,
     * it is considered a direct entry
     * @return bool
     */
    protected function detectReferrerDirectEntry()
    {
        if (!empty($this->referrerHost)) {
            // is the referrer host the current host?
            if (isset($this->currentUrlParse['host'])) {
                $currentHost = mb_strtolower($this->currentUrlParse['host'], 'UTF-8');
                if ($currentHost == mb_strtolower($this->referrerHost, 'UTF-8')) {
                    $this->typeReferrerAnalyzed = Common::REFERRER_TYPE_DIRECT_ENTRY;
                    return true;
                }
            }
            if (Visit::isHostKnownAliasHost($this->referrerHost, $this->idsite)) {
                $this->typeReferrerAnalyzed = Common::REFERRER_TYPE_DIRECT_ENTRY;
                return true;
            }
        }
        return false;
    }
}
