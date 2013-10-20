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

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\Piwik;
use Piwik\Tracker;
use Piwik\UrlHelper;

/**
 * Handles an action (page view, download or outlink) by the visitor.
 * Parses the action name and URL from the request array, then records the action in the log table.
 *
 * @package Piwik
 * @subpackage Tracker
 */
class Action implements ActionInterface
{
    /**
     * @var Request
     */
    private $request;

    private $idLinkVisitAction;
    private $idActionName = false;
    private $idActionUrl = false;

    private $actionName;
    private $actionType;
    private $actionUrl;

    private $searchCategory = false;
    private $searchCount = false;

    private $timeGeneration = false;

    /**
     * Encoding of HTML page being viewed. See reencodeParameters for more info.
     * @var string
     */
    private $pageEncoding = false;

    /* Custom Variable names & slots used for Site Search metadata (category, results count) */
    const CVAR_KEY_SEARCH_CATEGORY = '_pk_scat';
    const CVAR_KEY_SEARCH_COUNT = '_pk_scount';
    const CVAR_INDEX_SEARCH_CATEGORY = '4';
    const CVAR_INDEX_SEARCH_COUNT = '5';

    const DB_COLUMN_TIME_GENERATION = 'custom_float';


    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->init();
    }

    /**
     * Returns URL of the page currently being tracked, or the file being downloaded, or the outlink being clicked
     *
     * @return string
     */
    public function getActionUrl()
    {
        return $this->actionUrl;
    }

    public function getActionName()
    {
        return $this->actionName;
    }

    public function getActionType()
    {
        return $this->actionType;
    }

    public function getActionNameType()
    {
        $actionNameType = null;

        // we can add here action types for names of other actions than page views (like downloads, outlinks)
        switch ($this->getActionType()) {
            case ActionInterface::TYPE_PAGE_URL:
                $actionNameType = ActionInterface::TYPE_PAGE_TITLE;
                break;

            case ActionInterface::TYPE_SITE_SEARCH:
                $actionNameType = ActionInterface::TYPE_SITE_SEARCH;
                break;
        }

        return $actionNameType;
    }

    public function getIdActionUrl()
    {
        $idUrl = $this->idActionUrl;
        if (!empty($idUrl)) {
            return $idUrl;
        }
        // Site Search, by default, will not track URL. We do not want URL to appear as "Page URL not defined"
        // so we specifically set it to NULL in the table (the archiving query does IS NOT NULL)
        if ($this->getActionType() == self::TYPE_SITE_SEARCH) {
            return null;
        }

        // However, for other cases, we record idaction_url = 0 which will be displayed as "Page URL Not Defined"
        return 0;
    }

    public function getIdActionName()
    {
        return $this->idActionName;
    }

    protected function setActionName($name)
    {
        $name = PageUrl::cleanupString($name);
        $this->actionName = $name;
    }

    protected function setActionType($type)
    {
        $this->actionType = $type;
    }

    protected function setActionUrl($url)
    {
        $this->actionUrl = $url;
    }


    protected function init()
    {
        $this->pageEncoding = $this->request->getParam('cs');

        $info = $this->extractUrlAndActionNameFromRequest();

        $originalUrl = $info['url'];
        $info['url'] = PageUrl::excludeQueryParametersFromUrl($originalUrl, $this->request->getIdSite());

        if ($originalUrl != $info['url']) {
            Common::printDebug(' Before was "' . $originalUrl . '"');
            Common::printDebug(' After is "' . $info['url'] . '"');
        }

        // Set Final attributes for this Action (Pageview, Search, etc.)
        $this->setActionName($info['name']);
        $this->setActionType($info['type']);
        $this->setActionUrl($info['url']);
    }

    static public function getSqlSelectActionId()
    {
        $sql = "SELECT idaction, type, name
                        FROM " . Common::prefixTable('log_action')
            . "  WHERE "
            . "		( hash = CRC32(?) AND name = ? AND type = ? ) ";
        return $sql;
    }

    /**
     * This function will find the idaction from the lookup table piwik_log_action,
     * given an Action name and type.
     *
     * This is used to record Page URLs, Page Titles, Ecommerce items SKUs, item names, item categories
     *
     * If the action name does not exist in the lookup table, it will INSERT it
     * @param array $actionNamesAndTypes Array of one or many (name,type)
     * @return array Returns the input array, with the idaction appended ie. Array of one or many (name,type,idaction)
     */
    static public function loadActionId($actionNamesAndTypes)
    {
        // First, we try and select the actions that are already recorded
        $sql = self::getSqlSelectActionId();
        $bind = array();
        $normalizedUrls = array();
        $i = 0;
        foreach ($actionNamesAndTypes as $index => &$actionNameType) {
            list($name, $type) = $actionNameType;
            if (empty($name)) {
                $actionNameType[] = false;
                continue;
            }
            if ($i > 0) {
                $sql .= " OR ( hash = CRC32(?) AND name = ? AND type = ? ) ";
            }
            if ($type == Tracker\Action::TYPE_PAGE_URL) {
                // normalize urls by stripping protocol and www
                $normalizedUrls[$index] = PageUrl::normalizeUrl($name);
                $name = $normalizedUrls[$index]['url'];
            }
            $bind[] = $name;
            $bind[] = $name;
            $bind[] = $type;
            $i++;
        }
        // Case URL & Title are empty
        if (empty($bind)) {
            return $actionNamesAndTypes;
        }
        $actionIds = Tracker::getDatabase()->fetchAll($sql, $bind);

        // For the Actions found in the lookup table, add the idaction in the array,
        // If not found in lookup table, queue for INSERT
        $actionsToInsert = array();
        foreach ($actionNamesAndTypes as $index => &$actionNameType) {
            list($name, $type) = $actionNameType;
            if (empty($name)) {
                continue;
            }
            if (isset($normalizedUrls[$index])) {
                $name = $normalizedUrls[$index]['url'];
            }
            $found = false;
            foreach ($actionIds as $row) {
                if ($name == $row['name']
                    && $type == $row['type']
                ) {
                    $found = true;
                    $actionNameType[] = $row['idaction'];
                    continue;
                }
            }
            if (!$found) {
                $actionsToInsert[] = $index;
            }
        }

        $sql = "INSERT INTO " . Common::prefixTable('log_action') .
            "( name, hash, type, url_prefix ) VALUES (?,CRC32(?),?,?)";
        // Then, we insert all new actions in the lookup table
        foreach ($actionsToInsert as $actionToInsert) {
            list($name, $type) = $actionNamesAndTypes[$actionToInsert];

            $urlPrefix = null;
            if (isset($normalizedUrls[$actionToInsert])) {
                $name = $normalizedUrls[$actionToInsert]['url'];
                $urlPrefix = $normalizedUrls[$actionToInsert]['prefixId'];
            }

            Tracker::getDatabase()->query($sql, array($name, $name, $type, $urlPrefix));
            $actionId = Tracker::getDatabase()->lastInsertId();
            Common::printDebug("Recorded a new action (" . self::getActionTypeName($type) . ") in the lookup table: " . $name . " (idaction = " . $actionId . ")");

            $actionNamesAndTypes[$actionToInsert][] = $actionId;
        }
        return $actionNamesAndTypes;
    }

    static public function getActionTypeName($type)
    {
        switch ($type) {
            case self::TYPE_PAGE_URL:
                return 'Page URL';
                break;
            case self::TYPE_OUTLINK:
                return 'Outlink URL';
                break;
            case self::TYPE_DOWNLOAD:
                return 'Download URL';
                break;
            case self::TYPE_PAGE_TITLE:
                return 'Page Title';
                break;
            case self::TYPE_SITE_SEARCH:
                return 'Site Search';
                break;
            case self::TYPE_ECOMMERCE_ITEM_SKU:
                return 'Ecommerce Item SKU';
                break;
            case self::TYPE_ECOMMERCE_ITEM_NAME:
                return 'Ecommerce Item Name';
                break;
            case self::TYPE_ECOMMERCE_ITEM_CATEGORY:
                return 'Ecommerce Item Category';
                break;
            default:
                throw new Exception("Unexpected action type " . $type);
                break;
        }
    }

    /**
     * Loads the idaction of the current action name and the current action url.
     * These idactions are used in the visitor logging table to link the visit information
     * (entry action, exit action) to the actions.
     * These idactions are also used in the table that links the visits and their actions.
     *
     * The methods takes care of creating a new record(s) in the action table if the existing
     * action name and action url doesn't exist yet.
     */
    function loadIdActionNameAndUrl()
    {
        if ($this->idActionUrl !== false
            && $this->idActionName !== false
        ) {
            return;
        }
        $actions = array();
        $nameType = $this->getActionNameType();
        $action = array($this->getActionName(), $nameType);
        if (!is_null($action[1])) {
            $actions[] = $action;
        }

        $urlType = $this->getActionType();
        $url = $this->getActionUrl();
        // this code is a mess, but basically, getActionType() returns SITE_SEARCH,
        // but we do want to record the site search URL as an ACTION_URL
        if ($nameType == Tracker\Action::TYPE_SITE_SEARCH) {
            $urlType = Tracker\Action::TYPE_PAGE_URL;

            // By default, Site Search does not record the URL for the Search Result page, to slightly improve performance
            if (empty(Config::getInstance()->Tracker['action_sitesearch_record_url'])) {
                $url = false;
            }
        }
        if (!is_null($urlType) && !empty($url)) {
            $actions[] = array($url, $urlType);
        }

        $loadedActionIds = self::loadActionId($actions);

        foreach ($loadedActionIds as $loadedActionId) {
            list($name, $type, $actionId) = $loadedActionId;
            if ($type == Tracker\Action::TYPE_PAGE_TITLE
                || $type == Tracker\Action::TYPE_SITE_SEARCH
            ) {
                $this->idActionName = $actionId;
            } else {
                $this->idActionUrl = $actionId;
            }
        }
    }

    /**
     * Records in the DB the association between the visit and this action.
     *
     * @param int $idVisit is the ID of the current visit in the DB table log_visit
     * @param $visitorIdCookie
     * @param int $idReferrerActionUrl is the ID of the last action done by the current visit.
     * @param $idReferrerActionName
     * @param int $timeSpentReferrerAction is the number of seconds since the last action was done.
     *                 It is directly related to idReferrerActionUrl.
     */
    public function record($idVisit, $visitorIdCookie, $idReferrerActionUrl, $idReferrerActionName, $timeSpentReferrerAction)
    {
        $this->loadIdActionNameAndUrl();

        $idActionName = in_array($this->getActionType(), array(Tracker\Action::TYPE_PAGE_TITLE,
                                                               Tracker\Action::TYPE_PAGE_URL,
                                                               Tracker\Action::TYPE_SITE_SEARCH))
            ? (int)$this->getIdActionName()
            : null;

        $insert = array(
            'idvisit'               => $idVisit,
            'idsite'                => $this->request->getIdSite(),
            'idvisitor'             => $visitorIdCookie,
            'server_time'           => Tracker::getDatetimeFromTimestamp($this->request->getCurrentTimestamp()),
            'idaction_url'          => $this->getIdActionUrl(),
            'idaction_name'         => $idActionName,
            'idaction_url_ref'      => $idReferrerActionUrl,
            'idaction_name_ref'     => $idReferrerActionName,
            'time_spent_ref_action' => $timeSpentReferrerAction
        );

        if (!empty($this->timeGeneration)) {
            $insert[self::DB_COLUMN_TIME_GENERATION] = $this->timeGeneration;
        }

        $customVariables = $this->getCustomVariables();

        $insert = array_merge($insert, $customVariables);

        // Mysqli apparently does not like NULL inserts?
        $insertWithoutNulls = array();
        foreach ($insert as $column => $value) {
            if (!is_null($value) || $column == 'idaction_url_ref') {
                $insertWithoutNulls[$column] = $value;
            }
        }

        $fields = implode(", ", array_keys($insertWithoutNulls));
        $bind = array_values($insertWithoutNulls);
        $values = Common::getSqlStringFieldsArray($insertWithoutNulls);

        $sql = "INSERT INTO " . Common::prefixTable('log_link_visit_action') . " ($fields) VALUES ($values)";
        Tracker::getDatabase()->query($sql, $bind);

        $this->idLinkVisitAction = Tracker::getDatabase()->lastInsertId();

        $info = array(
            'idSite'                  => $this->request->getIdSite(),
            'idLinkVisitAction'       => $this->idLinkVisitAction,
            'idVisit'                 => $idVisit,
            'idReferrerActionUrl'     => $idReferrerActionUrl,
            'idReferrerActionName'    => $idReferrerActionName,
            'timeSpentReferrerAction' => $timeSpentReferrerAction,
        );
        Common::printDebug($insertWithoutNulls);

        /**
         * This hook is called after saving (and updating) visitor information. You can use it for instance to sync the
         * recorded action with third party systems.
         */
        Piwik::postEvent('Tracker.recordAction', array($trackerAction = $this, $info));
    }

    public function getCustomVariables()
    {
        $customVariables = $this->request->getCustomVariables($scope = 'page');

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

        if (!empty($customVariables)) {
            Common::printDebug("Page level Custom Variables: ");
            Common::printDebug($customVariables);
        }
        return $customVariables;
    }

    /**
     * Returns the ID of the newly created record in the log_link_visit_action table
     *
     * @return int
     */
    public function getIdLinkVisitAction()
    {
        return $this->idLinkVisitAction;
    }

    /**
     * Generates the name of the action from the URL or the specified name.
     * Sets the name as $this->actionName
     *
     * @return array
     */
    protected function extractUrlAndActionNameFromRequest()
    {
        $actionName = $this->request->getParam('action_name');
        $url = $this->request->getParam('url');

        $downloadUrl = $this->request->getParam('download');
        if (!empty($downloadUrl)) {
            $actionType = self::TYPE_DOWNLOAD;
            $url = $downloadUrl;
        }

        if (empty($actionType)) {
            $outlinkUrl = $this->request->getParam('link');
            if (!empty($outlinkUrl)) {
                $actionType = self::TYPE_OUTLINK;
                $url = $outlinkUrl;
            }
        }

        // defaults to page view
        if (empty($actionType)) {
            $actionType = self::TYPE_PAGE_URL;
            $actionName = $this->cleanupActionName($actionName);

            // Look in tracked URL for the Site Search parameters
            $siteSearch = $this->detectSiteSearch($url);
            if (!empty($siteSearch)) {
                $actionType = self::TYPE_SITE_SEARCH;
                list($actionName, $url) = $siteSearch;
            }

            // Look for performance analytics parameters
            $this->timeGeneration = $this->request->getPageGenerationTime();
        }

        $url = PageUrl::getUrlIfLookValid($url);
        $actionName = PageUrl::cleanupString((string)$actionName);
        return array(
            'name' => $actionName,
            'type' => $actionType,
            'url'  => $url,
        );
    }

    protected function detectSiteSearch($originalUrl)
    {
        $website = Cache::getCacheWebsiteAttributes($this->request->getIdSite());
        if (empty($website['sitesearch'])) {
            Common::printDebug("Internal 'Site Search' tracking is not enabled for this site. ");
            return false;
        }
        $actionName = $url = $categoryName = $count = false;
        $doTrackUrlForSiteSearch = !empty(Config::getInstance()->Tracker['action_sitesearch_record_url']);

        $originalUrl = PageUrl::cleanupUrl($originalUrl);

        // Detect Site search from Tracking API parameters rather than URL
        $searchKwd = $this->request->getParam('search');
        if (!empty($searchKwd)) {
            $actionName = $searchKwd;
            if ($doTrackUrlForSiteSearch) {
                $url = $originalUrl;
            }
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

        if (!empty($categoryName) || $count !== false) {
            $this->setActionSearchMetadata($categoryName, $count);
        }
        return array(
            $actionName,
            $url
        );
    }

    protected function isValidSearchCount($count)
    {
        return is_numeric($count) && $count >= 0;
    }

    protected function setActionSearchMetadata($category, $count)
    {
        if (!empty($category)) {
            $this->searchCategory = trim($category);
        }
        if ($count !== false) {
            $this->searchCount = $count;
        }
    }

    protected function detectSiteSearchFromUrl($website, $parsedUrl)
    {
        $doRemoveSearchParametersFromUrl = false;
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
        PageUrl::reencodeParameters($parameters, $this->pageEncoding);

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
            $parsedUrl['query'] = UrlHelper::getQueryStringWithExcludedParameters(UrlHelper::getArrayFromQueryString($parsedUrl['query']), $parametersToExclude);
            $parsedUrl['fragment'] = UrlHelper::getQueryStringWithExcludedParameters(UrlHelper::getArrayFromQueryString($parsedUrl['fragment']), $parametersToExclude);
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

    protected function cleanupActionName($actionName)
    {
        // get the delimiter, by default '/'; BC, we read the old action_category_delimiter first (see #1067)
        $actionCategoryDelimiter = isset(Config::getInstance()->General['action_category_delimiter'])
            ? Config::getInstance()->General['action_category_delimiter']
            : Config::getInstance()->General['action_url_category_delimiter'];

        // create an array of the categories delimited by the delimiter
        $split = explode($actionCategoryDelimiter, $actionName);

        // trim every category
        $split = array_map('trim', $split);

        // remove empty categories
        $split = array_filter($split, 'strlen');

        // rebuild the name from the array of cleaned categories
        $actionName = implode($actionCategoryDelimiter, $split);
        return $actionName;
    }


}


/**
 * Interface of the Action object.
 * New Action classes can be defined in plugins and used instead of the default one.
 *
 * @package Piwik
 * @subpackage Tracker
 */
interface ActionInterface
{
    const TYPE_PAGE_URL = 1;
    const TYPE_OUTLINK = 2;
    const TYPE_DOWNLOAD = 3;
    const TYPE_PAGE_TITLE = 4;
    const TYPE_ECOMMERCE_ITEM_SKU = 5;
    const TYPE_ECOMMERCE_ITEM_NAME = 6;
    const TYPE_ECOMMERCE_ITEM_CATEGORY = 7;
    const TYPE_SITE_SEARCH = 8;

    public function __construct(Request $request);

    public function getActionUrl();

    public function getActionName();

    public function getActionType();

    public function record($idVisit, $visitorIdCookie, $idReferrerActionUrl, $idReferrerActionName, $timeSpentReferrerAction);

    public function getIdActionUrl();

    public function getIdActionName();

    public function getIdLinkVisitAction();
}