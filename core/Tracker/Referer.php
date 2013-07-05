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

/**
 * @package Piwik
 * @subpackage Piwik_Tracker
 */
class Piwik_Tracker_Referer
{
    // @see detect*() referer methods
    protected $typeRefererAnalyzed;
    protected $nameRefererAnalyzed;
    protected $keywordRefererAnalyzed;
    protected $refererHost;
    protected $refererUrl;
    protected $refererUrlParse;
    protected $currentUrlParse;
    protected $idsite;

    // Used to prefix when a adsense referer is detected
    const LABEL_PREFIX_ADSENSE_KEYWORD = '(adsense) ';


    /**
     * Returns an array containing the following information:
     * - referer_type
     *        - direct            -- absence of referer URL OR referer URL has the same host
     *        - site                -- based on the referer URL
     *        - search_engine        -- based on the referer URL
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
     * - referer_url : the same for all the referer types
     *
     * @param $refererUrl must be URL Encoded
     * @param $currentUrl
     * @param $idSite
     * @return array
     */
    public function getRefererInformation($refererUrl, $currentUrl, $idSite)
    {
        $this->idsite = $idSite;

        // default values for the referer_* fields
        $refererUrl = Piwik_Common::unsanitizeInputValue($refererUrl);
        if (!empty($refererUrl)
            && !Piwik_Common::isLookLikeUrl($refererUrl)
        ) {
            $refererUrl = '';
        }

        $currentUrl = Piwik_Tracker_Action::cleanupUrl($currentUrl);

        $this->refererUrl = $refererUrl;
        $this->refererUrlParse = @parse_url($this->refererUrl);
        $this->currentUrlParse = @parse_url($currentUrl);
        $this->typeRefererAnalyzed = Piwik_Common::REFERER_TYPE_DIRECT_ENTRY;
        $this->nameRefererAnalyzed = '';
        $this->keywordRefererAnalyzed = '';
        $this->refererHost = '';

        if (isset($this->refererUrlParse['host'])) {
            $this->refererHost = $this->refererUrlParse['host'];
        }

        $refererDetected = false;

        if (!empty($this->currentUrlParse['host'])
            && $this->detectRefererCampaign()
        ) {
            $refererDetected = true;
        }

        if (!$refererDetected) {
            if ($this->detectRefererDirectEntry()
                || $this->detectRefererSearchEngine()
            ) {
                $refererDetected = true;
            }
        }

        if (!empty($this->refererHost)
            && !$refererDetected
        ) {
            $this->typeRefererAnalyzed = Piwik_Common::REFERER_TYPE_WEBSITE;
            $this->nameRefererAnalyzed = mb_strtolower($this->refererHost, 'UTF-8');
        }

        $refererInformation = array(
            'referer_type'    => $this->typeRefererAnalyzed,
            'referer_name'    => $this->nameRefererAnalyzed,
            'referer_keyword' => $this->keywordRefererAnalyzed,
            'referer_url'     => $this->refererUrl,
        );

        return $refererInformation;
    }

    /**
     * Search engine detection
     * @return bool
     */
    protected function detectRefererSearchEngine()
    {
        $searchEngineInformation = Piwik_Common::extractSearchEngineInformationFromUrl($this->refererUrl);
        Piwik_PostEvent('Tracker.detectRefererSearchEngine', array(&$searchEngineInformation, $this->refererUrl));
        if ($searchEngineInformation === false) {
            return false;
        }
        $this->typeRefererAnalyzed = Piwik_Common::REFERER_TYPE_SEARCH_ENGINE;
        $this->nameRefererAnalyzed = $searchEngineInformation['name'];
        $this->keywordRefererAnalyzed = $searchEngineInformation['keywords'];
        return true;
    }

    /**
     * @param string $string
     * @return bool
     */
    protected function detectCampaignFromString($string)
    {
        foreach ($this->campaignNames as $campaignNameParameter) {
            $campaignName = trim(urldecode(Piwik_Common::getParameterFromQueryString($string, $campaignNameParameter)));
            if (!empty($campaignName)) {
                break;
            }
        }

        if (!empty($campaignName)) {
            $this->typeRefererAnalyzed = Piwik_Common::REFERER_TYPE_CAMPAIGN;
            $this->nameRefererAnalyzed = $campaignName;

            foreach ($this->campaignKeywords as $campaignKeywordParameter) {
                $campaignKeyword = Piwik_Common::getParameterFromQueryString($string, $campaignKeywordParameter);
                if (!empty($campaignKeyword)) {
                    $this->keywordRefererAnalyzed = trim(urldecode($campaignKeyword));
                    break;
                }
            }

            // if the campaign keyword is empty, try to get a keyword from the referrer URL
            if (empty($this->keywordRefererAnalyzed)) {
                // Set the Campaign keyword to the keyword found in the Referer URL if any
                $referrerUrlInfo = Piwik_Common::extractSearchEngineInformationFromUrl($this->refererUrl);
                if (!empty($referrerUrlInfo['keywords'])) {
                    $this->keywordRefererAnalyzed = $referrerUrlInfo['keywords'];
                }

                // Set the keyword, to the hostname found, in a Adsense Referer URL '&url=' parameter
                if (empty($this->keywordRefererAnalyzed)
                    && !empty($this->refererUrlParse['query'])
                    && !empty($this->refererHost)
                    && (strpos($this->refererHost, 'google') !== false || strpos($this->refererHost, 'doubleclick') !== false)
                ) {
                    // This parameter sometimes is found & contains the page with the adsense ad bringing visitor to our site
                    $adsenseReferrerParameter = 'url';
                    $value = trim(urldecode(Piwik_Common::getParameterFromQueryString($this->refererUrlParse['query'], $adsenseReferrerParameter)));
                    if (!empty($value)) {
                        $parsedAdsenseReferrerUrl = parse_url($value);
                        if (!empty($parsedAdsenseReferrerUrl['host'])) {
                            $this->keywordRefererAnalyzed = self::LABEL_PREFIX_ADSENSE_KEYWORD . $parsedAdsenseReferrerUrl['host'];
                        }
                    }
                }

                // or we default to the referrer hostname otherwise
                if (empty($this->keywordRefererAnalyzed)) {
                    $this->keywordRefererAnalyzed = $this->refererHost;
                }
            }

            return true;
        }
        return false;
    }

    /**
     * Campaign analysis
     * @return bool
     */
    protected function detectRefererCampaign()
    {
        if (!isset($this->currentUrlParse['query'])
            && !isset($this->currentUrlParse['fragment'])
        ) {
            return false;
        }
        $campaignParameters = Piwik_Common::getCampaignParameters();
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
     * so at this stage, if the referer host is the current host,
     * or if the referer host is any of the registered URL for this website,
     * it is considered a direct entry
     * @return bool
     */
    protected function detectRefererDirectEntry()
    {
        if (!empty($this->refererHost)) {
            // is the referer host the current host?
            if (isset($this->currentUrlParse['host'])) {
                $currentHost = mb_strtolower($this->currentUrlParse['host'], 'UTF-8');
                if ($currentHost == mb_strtolower($this->refererHost, 'UTF-8')) {
                    $this->typeRefererAnalyzed = Piwik_Common::REFERER_TYPE_DIRECT_ENTRY;
                    return true;
                }
            }
            if (Piwik_Tracker_Visit::isHostKnownAliasHost($this->refererHost, $this->idsite)) {
                $this->typeRefererAnalyzed = Piwik_Common::REFERER_TYPE_DIRECT_ENTRY;
                return true;
            }
        }
        return false;
    }
}
