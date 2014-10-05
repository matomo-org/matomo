<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Actions\Actions;

use Piwik\Common;
use Piwik\Tracker\Action;
use Piwik\Tracker\PageUrl;
use Piwik\Tracker\Request;
use Piwik\Tracker\Cache;
use Piwik\UrlHelper;

/**
 * This class represents a search on the site.
 * - Its name is the search keyword
 * - by default the URL is not recorded (since it's not used)
 * - tracks site search result count and site search category as custom variables
 *
 */
class ActionSiteSearch extends Action
{
    private $searchCategory = false;
    private $searchCount = false;

    const CVAR_KEY_SEARCH_CATEGORY = '_pk_scat';
    const CVAR_KEY_SEARCH_COUNT = '_pk_scount';
    const CVAR_INDEX_SEARCH_CATEGORY = '4';
    const CVAR_INDEX_SEARCH_COUNT = '5';

    public function __construct(Request $request, $detect = true)
    {
        parent::__construct(Action::TYPE_SITE_SEARCH, $request);
        $this->originalUrl = $request->getParam('url');

        if ($detect) {
            $this->isSearchDetected();
        }
    }

    public static function shouldHandle(Request $request)
    {
        $search = new self($request, false);

        return $search->detectSiteSearch($request->getParam('url'));
    }

    protected function getActionsToLookup()
    {
        return array(
            'idaction_name' => array($this->getActionName(), Action::TYPE_SITE_SEARCH),
        );
    }

    public function getIdActionUrl()
    {
        // Site Search, by default, will not track URL. We do not want URL to appear as "Page URL not defined"
        // so we specifically set it to NULL in the table (the archiving query does IS NOT NULL)
        return null;
    }

    public function getCustomFloatValue()
    {
        return $this->request->getPageGenerationTime();
    }

    protected function isSearchDetected()
    {
        $siteSearch = $this->detectSiteSearch($this->originalUrl);

        if (empty($siteSearch)) {
            return false;
        }

        list($actionName, $url, $category, $count) = $siteSearch;

        if (!empty($category)) {
            $this->searchCategory = trim($category);
        }
        if ($count !== false) {
            $this->searchCount = $count;
        }
        $this->setActionName($actionName);
        $this->setActionUrl($url);

        return true;
    }

    public function getCustomVariables()
    {
        $customVariables = parent::getCustomVariables();

        // Enrich Site Search actions with Custom Variables, overwriting existing values
        if (!empty($this->searchCategory)) {
            if (!empty($customVariables['custom_var_k' . self::CVAR_INDEX_SEARCH_CATEGORY])) {
                Common::printDebug("WARNING: Overwriting existing Custom Variable  in slot " . self::CVAR_INDEX_SEARCH_CATEGORY . " for this page view");
            }
            $customVariables['custom_var_k' . self::CVAR_INDEX_SEARCH_CATEGORY] = self::CVAR_KEY_SEARCH_CATEGORY;
            $customVariables['custom_var_v' . self::CVAR_INDEX_SEARCH_CATEGORY] = Request::truncateCustomVariable($this->searchCategory);
        }
        if ($this->searchCount !== false) {
            if (!empty($customVariables['custom_var_k' . self::CVAR_INDEX_SEARCH_COUNT])) {
                Common::printDebug("WARNING: Overwriting existing Custom Variable  in slot " . self::CVAR_INDEX_SEARCH_COUNT . " for this page view");
            }
            $customVariables['custom_var_k' . self::CVAR_INDEX_SEARCH_COUNT] = self::CVAR_KEY_SEARCH_COUNT;
            $customVariables['custom_var_v' . self::CVAR_INDEX_SEARCH_COUNT] = (int)$this->searchCount;
        }
        return $customVariables;
    }

    protected function detectSiteSearchFromUrl($website, $parsedUrl)
    {
        $doRemoveSearchParametersFromUrl = true;
        $separator = '&';
        $count = $actionName = $categoryName = false;

        $keywordParameters = isset($website['sitesearch_keyword_parameters'])
            ? $website['sitesearch_keyword_parameters']
            : array();
        $queryString = (!empty($parsedUrl['query']) ? $parsedUrl['query'] : '') . (!empty($parsedUrl['fragment']) ? $separator . $parsedUrl['fragment'] : '');
        $parametersRaw = UrlHelper::getArrayFromQueryString($queryString);

        // strtolower the parameter names for smooth site search detection
        $parameters = array();
        foreach ($parametersRaw as $k => $v) {
            $parameters[Common::mb_strtolower($k)] = $v;
        }
        // decode values if they were sent from a client using another charset
        $pageEncoding = $this->request->getParam('cs');
        PageUrl::reencodeParameters($parameters, $pageEncoding);

        // Detect Site Search keyword
        foreach ($keywordParameters as $keywordParameterRaw) {
            $keywordParameter = Common::mb_strtolower($keywordParameterRaw);
            if (!empty($parameters[$keywordParameter])) {
                $actionName = $parameters[$keywordParameter];
                break;
            }
        }

        if (empty($actionName)) {
            return false;
        }

        $categoryParameters = isset($website['sitesearch_category_parameters'])
            ? $website['sitesearch_category_parameters']
            : array();

        foreach ($categoryParameters as $categoryParameterRaw) {
            $categoryParameter = Common::mb_strtolower($categoryParameterRaw);
            if (!empty($parameters[$categoryParameter])) {
                $categoryName = $parameters[$categoryParameter];
                break;
            }
        }

        if (isset($parameters['search_count'])
            && $this->isValidSearchCount($parameters['search_count'])
        ) {
            $count = $parameters['search_count'];
        }
        // Remove search kwd from URL
        if ($doRemoveSearchParametersFromUrl) {
            // @see excludeQueryParametersFromUrl()
            // Excluded the detected parameters from the URL
            $parametersToExclude = array($categoryParameterRaw, $keywordParameterRaw);
            if (isset($parsedUrl['query'])) {
                $parsedUrl['query'] = UrlHelper::getQueryStringWithExcludedParameters(UrlHelper::getArrayFromQueryString($parsedUrl['query']), $parametersToExclude);
            }
            if (isset($parsedUrl['fragment'])) {
                $parsedUrl['fragment'] = UrlHelper::getQueryStringWithExcludedParameters(UrlHelper::getArrayFromQueryString($parsedUrl['fragment']), $parametersToExclude);
            }
        }
        $url = UrlHelper::getParseUrlReverse($parsedUrl);
        if (is_array($actionName)) {
            $actionName = reset($actionName);
        }
        $actionName = trim(urldecode($actionName));
        if (empty($actionName)) {
            return false;
        }
        if (is_array($categoryName)) {
            $categoryName = reset($categoryName);
        }
        $categoryName = trim(urldecode($categoryName));
        return array($url, $actionName, $categoryName, $count);
    }

    protected function isValidSearchCount($count)
    {
        return is_numeric($count) && $count >= 0;
    }

    public function detectSiteSearch($originalUrl)
    {
        $website = Cache::getCacheWebsiteAttributes($this->request->getIdSite());
        if (empty($website['sitesearch'])) {
            Common::printDebug("Internal 'Site Search' tracking is not enabled for this site. ");
            return false;
        }

        $actionName = $url = $categoryName = $count = false;

        $originalUrl = PageUrl::cleanupUrl($originalUrl);

        // Detect Site search from Tracking API parameters rather than URL
        $searchKwd = $this->request->getParam('search');
        if (!empty($searchKwd)) {
            $actionName = $searchKwd;
            $isCategoryName = $this->request->getParam('search_cat');
            if (!empty($isCategoryName)) {
                $categoryName = $isCategoryName;
            }
            $isCount = $this->request->getParam('search_count');
            if ($this->isValidSearchCount($isCount)) {
                $count = $isCount;
            }
        }

        if (empty($actionName)) {
            $parsedUrl = @parse_url($originalUrl);

            // Detect Site Search from URL query parameters
            if (!empty($parsedUrl['query']) || !empty($parsedUrl['fragment'])) {
                // array($url, $actionName, $categoryName, $count);
                $searchInfo = $this->detectSiteSearchFromUrl($website, $parsedUrl);
                if (!empty($searchInfo)) {
                    list ($url, $actionName, $categoryName, $count) = $searchInfo;
                }
            }
        }

        $actionName = trim($actionName);
        $categoryName = trim($categoryName);

        if (empty($actionName)) {
            Common::printDebug("(this is not a Site Search request)");
            return false;
        }

        Common::printDebug("Detected Site Search keyword '$actionName'. ");
        if (!empty($categoryName)) {
            Common::printDebug("- Detected Site Search Category '$categoryName'. ");
        }
        if ($count !== false) {
            Common::printDebug("- Search Results Count was '$count'. ");
        }
        if ($url != $originalUrl) {
            Common::printDebug("NOTE: The Page URL was changed / removed, during the Site Search detection, was '$originalUrl', now is '$url'");
        }

        return array(
            $actionName,
            $url,
            $categoryName,
            $count
        );
    }

}
