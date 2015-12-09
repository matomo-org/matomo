<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Columns;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\Referrers\SearchEngine AS SearchEngineDetection;
use Piwik\Plugins\SitesManager\SiteUrls;
use Piwik\Tracker\Cache;
use Piwik\Tracker\PageUrl;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;
use Piwik\UrlHelper;

abstract class Base extends VisitDimension
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

    private static $cachedReferrerSearchEngine = array();

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
    protected function getReferrerInformation($referrerUrl, $currentUrl, $idSite, Request $request, Visitor $visitor)
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

        $referrerDetected = $this->detectReferrerCampaign($request, $visitor);

        if (!$referrerDetected) {
            if ($this->detectReferrerDirectEntry()
                || $this->detectReferrerSearchEngine()
            ) {
                $referrerDetected = true;
            }
        }

        if (!$referrerDetected && !empty($this->referrerHost)) {
            $this->typeReferrerAnalyzed = Common::REFERRER_TYPE_WEBSITE;
            $this->nameReferrerAnalyzed = Common::mb_strtolower($this->referrerHost);

            $urlsByHost = $this->getCachedUrlsByHostAndIdSite();

            $directEntry = new SiteUrls();
            $path = $directEntry->getPathMatchingUrl($this->referrerUrlParse, $urlsByHost);
            if (!empty($path) && $path !== '/') {
                $this->nameReferrerAnalyzed .= rtrim($path, '/');
            }
        }

        $referrerInformation = array(
            'referer_type'    => $this->typeReferrerAnalyzed,
            'referer_name'    => $this->nameReferrerAnalyzed,
            'referer_keyword' => $this->keywordReferrerAnalyzed,
            'referer_url'     => $this->referrerUrl,
        );

        return $referrerInformation;
    }

    protected function getReferrerInformationFromRequest(Request $request, Visitor $visitor)
    {
        $referrerUrl = $request->getParam('urlref');
        $currentUrl  = $request->getParam('url');

        return $this->getReferrerInformation($referrerUrl, $currentUrl, $request->getIdSite(), $request, $visitor);
    }

    /**
     * Search engine detection
     * @return bool
     */
    protected function detectReferrerSearchEngine()
    {
        if (isset(self::$cachedReferrerSearchEngine[$this->referrerUrl])) {
            $searchEngineInformation = self::$cachedReferrerSearchEngine[$this->referrerUrl];
        } else {
            $searchEngineInformation = SearchEngineDetection::getInstance()->extractInformationFromUrl($this->referrerUrl);

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

            self::$cachedReferrerSearchEngine[$this->referrerUrl] = $searchEngineInformation;
        }

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


    protected function detectReferrerCampaignFromTrackerParams(Request $request)
    {
        $campaignName = $this->getReferrerCampaignQueryParam($request, '_rcn');
        if (empty($campaignName)) {
            return false;
        }

        $this->typeReferrerAnalyzed = Common::REFERRER_TYPE_CAMPAIGN;
        $this->nameReferrerAnalyzed = $campaignName;

        $keyword = $this->getReferrerCampaignQueryParam($request, '_rck');
        if (!empty($keyword)) {
            $this->keywordReferrerAnalyzed = $keyword;
        }

        return true;
    }

    private function getCachedUrlsByHostAndIdSite()
    {
        $cache = Cache::getCacheGeneral();

        if (!empty($cache['allUrlsByHostAndIdSite'])) {
            return $cache['allUrlsByHostAndIdSite'];
        }

        return array();
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
        if (empty($this->referrerHost)) {
            return false;
        }

        $urlsByHost = $this->getCachedUrlsByHostAndIdSite();

        $directEntry   = new SiteUrls();
        $matchingSites = $directEntry->getIdSitesMatchingUrl($this->referrerUrlParse, $urlsByHost);

        if (isset($matchingSites) && is_array($matchingSites) && in_array($this->idsite, $matchingSites)) {
            $this->typeReferrerAnalyzed = Common::REFERRER_TYPE_DIRECT_ENTRY;
            return true;
        } elseif (isset($matchingSites)) {
            return false;
        }

        // fallback logic if the referrer domain is not known to any site to not break BC
        if (isset($this->currentUrlParse['host'])) {
            // this might be actually buggy if first thing tracked is eg an outlink and referrer is from that site
            $currentHost = Common::mb_strtolower($this->currentUrlParse['host']);
            if ($currentHost == Common::mb_strtolower($this->referrerHost)) {
                $this->typeReferrerAnalyzed = Common::REFERRER_TYPE_DIRECT_ENTRY;
                return true;
            }
        }

        return false;
    }

    protected function detectCampaignKeywordFromReferrerUrl()
    {
        if (!empty($this->nameReferrerAnalyzed)
            && !empty($this->keywordReferrerAnalyzed)) {
            // keyword is already set, we skip
            return true;
        }

        // Set the Campaign keyword to the keyword found in the Referrer URL if any
        if (!empty($this->nameReferrerAnalyzed)) {
            $referrerUrlInfo = SearchEngineDetection::getInstance()->extractInformationFromUrl($this->referrerUrl);
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

                    if (empty($this->nameReferrerAnalyzed)) {
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
    protected function detectReferrerCampaign(Request $request, Visitor $visitor)
    {
        $isCampaign = $this->detectReferrerCampaignFromTrackerParams($request);
        if (!$isCampaign) {
            $this->detectReferrerCampaignFromLandingUrl();
        }

        $this->detectCampaignKeywordFromReferrerUrl();

        $isCurrentVisitACampaignWithSameName = $visitor->getVisitorColumn('referer_name') == $this->nameReferrerAnalyzed;
        $isCurrentVisitACampaignWithSameName = $isCurrentVisitACampaignWithSameName && $visitor->getVisitorColumn('referer_type') == Common::REFERRER_TYPE_CAMPAIGN;

        // if we detected a campaign but there is still no keyword set, we set the keyword to the Referrer host
        if (empty($this->keywordReferrerAnalyzed)) {
            if ($isCurrentVisitACampaignWithSameName) {
                $this->keywordReferrerAnalyzed = $visitor->getVisitorColumn('referer_keyword');
                // it is an existing visit and no referrer keyword was used initially (or a different host),
                // we do not use the default referrer host in this case as it would create a new visit. It would create
                // a new visit because initially the referrer keyword was not set (or from a different host) and now
                // we would set it suddenly. The changed keyword would be recognized as a campaign change and a new
                // visit would be forced. Why would it suddenly set a keyword but not do it initially?
                // This happens when on the first visit when the URL was opened directly (no referrer or different host)
                // and then the user navigates to another page where the referrer host becomes the own host
                // (referrer = own website) see https://github.com/piwik/piwik/issues/9299
            } else {
                $this->keywordReferrerAnalyzed = $this->referrerHost;
            }
        }

        if ($this->typeReferrerAnalyzed != Common::REFERRER_TYPE_CAMPAIGN) {
            $this->keywordReferrerAnalyzed = null;
            $this->nameReferrerAnalyzed = null;
            return false;
        }

        $this->keywordReferrerAnalyzed = Common::mb_strtolower($this->keywordReferrerAnalyzed);
        $this->nameReferrerAnalyzed = Common::mb_strtolower($this->nameReferrerAnalyzed);
        return true;
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function getValueForRecordGoal(Request $request, Visitor $visitor)
    {
        $referrerUrl             = $request->getParam('_ref');
        $referrerCampaignName    = $this->getReferrerCampaignQueryParam($request, '_rcn');
        $referrerCampaignKeyword = $this->getReferrerCampaignQueryParam($request, '_rck');

        // Attributing the correct Referrer to this conversion.
        // Priority order is as follows:
        // 0) In some cases, the campaign is not passed from the JS so we look it up from the current visit
        // 1) Campaign name/kwd parsed in the JS
        // 2) Referrer URL stored in the _ref cookie
        // 3) If no info from the cookie, attribute to the current visit referrer


        Common::printDebug("Attributing a referrer to this Goal...");

        // 3) Default values: current referrer
        $type    = $visitor->getVisitorColumn('referer_type');
        $name    = $visitor->getVisitorColumn('referer_name');
        $keyword = $visitor->getVisitorColumn('referer_keyword');

        // 0) In some (unknown!?) cases the campaign is not found in the attribution cookie, but the URL ref was found.
        //    In this case we look up if the current visit is credited to a campaign and will credit this campaign rather than the URL ref (since campaigns have higher priority)
        if (empty($referrerCampaignName)
            && $type == Common::REFERRER_TYPE_CAMPAIGN
            && !empty($name)
        ) {
            // Use default values per above
            Common::printDebug("Invalid Referrer information found: current visitor seems to have used a campaign, but campaign name was not found in the request.");
        } // 1) Campaigns from 1st party cookie
        elseif (!empty($referrerCampaignName)) {
            $type    = Common::REFERRER_TYPE_CAMPAIGN;
            $name    = $referrerCampaignName;
            $keyword = $referrerCampaignKeyword;
            Common::printDebug("Campaign information from 1st party cookie is used.");
        } // 2) Referrer URL parsing
        elseif (!empty($referrerUrl)) {

            $idSite   = $request->getIdSite();
            $referrer = $this->getReferrerInformation($referrerUrl, $currentUrl = '', $idSite, $request, $visitor);

            // if the parsed referrer is interesting enough, ie. website or search engine
            if (in_array($referrer['referer_type'], array(Common::REFERRER_TYPE_SEARCH_ENGINE, Common::REFERRER_TYPE_WEBSITE))) {
                $type    = $referrer['referer_type'];
                $name    = $referrer['referer_name'];
                $keyword = $referrer['referer_keyword'];

                Common::printDebug("Referrer URL (search engine or website) is used.");
            } else {
                Common::printDebug("No referrer attribution found for this user. Current user's visit referrer is used.");
            }
        } else {
            Common::printDebug("No referrer attribution found for this user. Current user's visit referrer is used.");
        }

        $this->setCampaignValuesToLowercase($type, $name, $keyword);

        $fields = array(
            'referer_type'              => $type,
            'referer_name'              => $name,
            'referer_keyword'           => $keyword,
        );

        if (array_key_exists($this->columnName, $fields)) {
            return $fields[$this->columnName];
        }

        return false;
    }

    /**
     * @param $type
     * @param $name
     * @param $keyword
     */
    protected function setCampaignValuesToLowercase($type, &$name, &$keyword)
    {
        if ($type === Common::REFERRER_TYPE_CAMPAIGN) {
            if (!empty($name)) {
                $name = Common::mb_strtolower($name);
            }
            if (!empty($keyword)) {
                $keyword = Common::mb_strtolower($keyword);
            }
        }
    }

    protected function isReferrerInformationNew(Visitor $visitor, $information)
    {
        foreach (array('referer_keyword', 'referer_name', 'referer_type') as $infoName) {
            if ($this->hasReferrerColumnChanged($visitor, $information, $infoName)) {
                return true;
            }
        }
        return false;
    }

    protected function hasReferrerColumnChanged(Visitor $visitor, $information, $infoName)
    {
        return Common::mb_strtolower($visitor->getVisitorColumn($infoName)) != Common::mb_strtolower($information[$infoName]);
    }

    protected function doesLastActionHaveSameReferrer(Visitor $visitor, $referrerType)
    {
        return $visitor->getVisitorColumn('referer_type') == $referrerType;
    }

    protected function getReferrerCampaignQueryParam(Request $request, $paramName)
    {
        return trim(urldecode($request->getParam($paramName)));
    }
}
