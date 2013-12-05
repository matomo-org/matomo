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
    const LABEL_PREFIX_ADWORDS_KEYWORD = '(adwords) ';
    const LABEL_ADWORDS_NAME = 'AdWords';

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

        $referrerDetected = $this->detectReferrerCampaign();

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
            $this->nameReferrerAnalyzed = Common::mb_strtolower($this->referrerHost);
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
         *                                        This parameter is initialized to the results
         *                                        of Piwik's default search engine detection
         *                                        logic.
         * @param string referrerUrl The referrer URL from the tracking request.
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
        return !empty($this->keywordReferrerAnalyzed);
    }

    protected function detectReferrerCampaignFromLandingUrl()
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
            $this->detectCampaignFromString($this->currentUrlParse['fragment']);
        }
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

    protected function detectCampaignKeywordFromReferrerUrl()
    {
        if(!empty($this->nameReferrerAnalyzed)
            && !empty($this->keywordReferrerAnalyzed)) {
            // keyword is already set, we skip
            return true;
        }

        // Set the Campaign keyword to the keyword found in the Referrer URL if any
        if(!empty($this->nameReferrerAnalyzed)) {
            $referrerUrlInfo = UrlHelper::extractSearchEngineInformationFromUrl($this->referrerUrl);
            if (!empty($referrerUrlInfo['keywords'])) {
                $this->keywordReferrerAnalyzed = $referrerUrlInfo['keywords'];
            }
        }

        // Set the keyword, to the hostname found, in a Adsense Referrer URL '&url=' parameter
        if (empty($this->keywordReferrerAnalyzed)
            && !empty($this->referrerUrlParse['query'])
            && !empty($this->referrerHost)
            && (strpos($this->referrerHost, 'googleads') !== false || strpos($this->referrerHost, 'doubleclick') !== false)
        ) {
            // This parameter sometimes is found & contains the page with the adsense ad bringing visitor to our site
            $value = $this->getParameterValueFromReferrerUrl('url');
            if (!empty($value)) {
                $parsedAdsenseReferrerUrl = parse_url($value);
                if (!empty($parsedAdsenseReferrerUrl['host'])) {

                    if(empty($this->nameReferrerAnalyzed)) {
                        $type = $this->getParameterValueFromReferrerUrl('ad_type');
                        $type = $type ? " ($type)" : '';
                        $this->nameReferrerAnalyzed = self::LABEL_ADWORDS_NAME . $type;
                        $this->typeReferrerAnalyzed = Common::REFERRER_TYPE_CAMPAIGN;
                    }
                    $this->keywordReferrerAnalyzed = self::LABEL_PREFIX_ADWORDS_KEYWORD . $parsedAdsenseReferrerUrl['host'];
                }
            }
        }

    }

    /**
     * @return string
     */
    protected function getParameterValueFromReferrerUrl($adsenseReferrerParameter)
    {
        $value = trim(urldecode(UrlHelper::getParameterFromQueryString($this->referrerUrlParse['query'], $adsenseReferrerParameter)));
        return $value;
    }

    /**
     * @return bool
     */
    protected function detectReferrerCampaign()
    {
        $this->detectReferrerCampaignFromLandingUrl();
        $this->detectCampaignKeywordFromReferrerUrl();

        // if we detected a campaign but there is still no keyword set, we set the keyword to the Referrer host
        if ($this->typeReferrerAnalyzed != Common::REFERRER_TYPE_CAMPAIGN) {
            return false;
        }
        if(empty($this->keywordReferrerAnalyzed)) {
            $this->keywordReferrerAnalyzed = $this->referrerHost;
        }

        $this->keywordReferrerAnalyzed = Common::mb_strtolower($this->keywordReferrerAnalyzed);
        $this->nameReferrerAnalyzed = Common::mb_strtolower($this->nameReferrerAnalyzed);
        return true;
    }

}
