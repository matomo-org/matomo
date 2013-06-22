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
 * Interface of the Action object.
 * New Action classes can be defined in plugins and used instead of the default one.
 *
 * @package Piwik
 * @subpackage Piwik_Tracker
 */
interface Piwik_Tracker_Action_Interface
{
    const TYPE_ACTION_URL = 1;
    const TYPE_OUTLINK = 2;
    const TYPE_DOWNLOAD = 3;
    const TYPE_ACTION_NAME = 4;
    const TYPE_ECOMMERCE_ITEM_SKU = 5;
    const TYPE_ECOMMERCE_ITEM_NAME = 6;
    const TYPE_ECOMMERCE_ITEM_CATEGORY = 7;
    const TYPE_SITE_SEARCH = 8;

    public function setRequest($requestArray);

    public function setIdSite($idSite);

    public function init();

    public function getActionUrl();

    public function getActionName();

    public function getActionType();

    public function record($idVisit, $visitorIdCookie, $idRefererActionUrl, $idRefererActionName, $timeSpentRefererAction);

    public function getIdActionUrl();

    public function getIdActionName();

    public function getIdLinkVisitAction();
}

/**
 * Handles an action (page view, download or outlink) by the visitor.
 * Parses the action name and URL from the request array, then records the action in the log table.
 *
 * @package Piwik
 * @subpackage Piwik_Tracker
 */
class Piwik_Tracker_Action implements Piwik_Tracker_Action_Interface
{
    private $request;
    private $idSite;
    private $timestamp;
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
     *
     * @var string
     */
    private $pageEncoding = false;

    static private $queryParametersToExclude = array('phpsessid', 'jsessionid', 'sessionid', 'aspsessionid', 'fb_xd_fragment', 'fb_comment_id', 'doing_wp_cron', 'gclid');

    /* Custom Variable names & slots used for Site Search metadata (category, results count) */
    const CVAR_KEY_SEARCH_CATEGORY = '_pk_scat';
    const CVAR_KEY_SEARCH_COUNT = '_pk_scount';
    const CVAR_INDEX_SEARCH_CATEGORY = '4';
    const CVAR_INDEX_SEARCH_COUNT = '5';

    /* Tracking API Parameters used to force a site search request */
    const PARAMETER_NAME_SEARCH_COUNT = 'search_count';
    const PARAMETER_NAME_SEARCH_CATEGORY = 'search_cat';
    const PARAMETER_NAME_SEARCH_KEYWORD = 'search';

    /* Custom Variables names & slots plus Tracking API Parameters for performance analytics */
    const DB_COLUMN_TIME_GENERATION = 'custom_float';
    const PARAMETER_NAME_TIME_GENERATION = 'gt_ms';

    /**
     * Map URL prefixes to integers.
     * @see self::normalizeUrl(), self::reconstructNormalizedUrl()
     */
    static public $urlPrefixMap = array(
        'http://www.'  => 1,
        'http://'      => 0,
        'https://www.' => 3,
        'https://'     => 2
    );

    /**
     * Extract the prefix from a URL.
     * Return the prefix ID and the rest.
     *
     * @param string $url
     * @return array
     */
    static public function normalizeUrl($url)
    {
        foreach (self::$urlPrefixMap as $prefix => $id) {
            if (strtolower(substr($url, 0, strlen($prefix))) == $prefix) {
                return array(
                    'url'      => substr($url, strlen($prefix)),
                    'prefixId' => $id
                );
            }
        }
        return array('url' => $url, 'prefixId' => null);
    }

    /**
     * Build the full URL from the prefix ID and the rest.
     *
     * @param string $url
     * @param integer $prefixId
     * @return string
     */
    static public function reconstructNormalizedUrl($url, $prefixId)
    {
        $map = array_flip(self::$urlPrefixMap);
        if ($prefixId !== null && isset($map[$prefixId])) {
            $fullUrl = $map[$prefixId] . $url;
        } else {
            $fullUrl = $url;
        }

        // Clean up host & hash tags, for URLs
        $parsedUrl = @parse_url($fullUrl);
        $parsedUrl = self::cleanupHostAndHashTag($parsedUrl);
        $url = Piwik_Common::getParseUrlReverse($parsedUrl);
        if (!empty($url)) {
            return $url;
        }

        return $fullUrl;
    }


    /**
     * Set request parameters
     *
     * @param array $requestArray
     */
    public function setRequest($requestArray)
    {
        $this->request = $requestArray;
    }

    /**
     * Returns the current set request parameters
     *
     * @return array
     */
    public function getRequest()
    {
        return $this->request;
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
            case Piwik_Tracker_Action_Interface::TYPE_ACTION_URL:
                $actionNameType = Piwik_Tracker_Action_Interface::TYPE_ACTION_NAME;
                break;

            case Piwik_Tracker_Action_Interface::TYPE_SITE_SEARCH:
                $actionNameType = Piwik_Tracker_Action_Interface::TYPE_SITE_SEARCH;
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
        $name = self::cleanupString($name);
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

    /**
     * Converts Matrix URL format
     * from http://example.org/thing;paramA=1;paramB=6542
     * to   http://example.org/thing?paramA=1&paramB=6542
     *
     * @param string $originalUrl
     * @return string
     */
    static public function convertMatrixUrl($originalUrl)
    {
        $posFirstSemiColon = strpos($originalUrl, ";");
        if ($posFirstSemiColon === false) {
            return $originalUrl;
        }
        $posQuestionMark = strpos($originalUrl, "?");
        $replace = ($posQuestionMark === false);
        if ($posQuestionMark > $posFirstSemiColon) {
            $originalUrl = substr_replace($originalUrl, ";", $posQuestionMark, 1);
            $replace = true;
        }
        if ($replace) {
            $originalUrl = substr_replace($originalUrl, "?", strpos($originalUrl, ";"), 1);
            $originalUrl = str_replace(";", "&", $originalUrl);
        }
        return $originalUrl;
    }

    static public function cleanupUrl($url)
    {
        $url = Piwik_Common::unsanitizeInputValue($url);
        $url = self::cleanupString($url);
        $url = self::convertMatrixUrl($url);
        return $url;
    }

    /**
     * Will cleanup the hostname (some browser do not strolower the hostname),
     * and deal ith the hash tag on incoming URLs based on website setting.
     *
     * @param $parsedUrl
     * @param $idSite int|bool  The site ID of the current visit. This parameter is
     *                          only used by the tracker to see if we should remove
     *                          the URL fragment for this site.
     * @return array
     */
    static public function cleanupHostAndHashTag($parsedUrl, $idSite = false)
    {
        if (empty($parsedUrl)) {
            return $parsedUrl;
        }
        if (!empty($parsedUrl['host'])) {
            $parsedUrl['host'] = mb_strtolower($parsedUrl['host'], 'UTF-8');
        }

        if (!empty($parsedUrl['fragment'])) {
            $parsedUrl['fragment'] = self::processUrlFragment($parsedUrl['fragment'], $idSite);
        }

        return $parsedUrl;
    }

    /**
     * Cleans and/or removes the URL fragment of a URL.
     *
     * @param $urlFragment      string The URL fragment to process.
     * @param $idSite           int|bool  If not false, this function will check if URL fragments
     *                          should be removed for the site w/ this ID and if so,
     *                          the returned processed fragment will be empty.
     *
     * @return string The processed URL fragment.
     */
    public static function processUrlFragment($urlFragment, $idSite = false)
    {
        // if we should discard the url fragment for this site, return an empty string as
        // the processed url fragment
        if ($idSite !== false
            && self::shouldRemoveURLFragmentFor($idSite)
        ) {
            return '';
        } else {
            // Remove trailing Hash tag in ?query#hash#
            if (substr($urlFragment, -1) == '#') {
                $urlFragment = substr($urlFragment, 0, strlen($urlFragment) - 1);
            }
            return $urlFragment;
        }
    }

    /**
     * Returns true if URL fragments should be removed for a specific site,
     * false if otherwise.
     *
     * This function uses the Tracker cache and not the MySQL database.
     *
     * @param $idSite int The ID of the site to check for.
     * @return bool
     */
    public static function shouldRemoveURLFragmentFor($idSite)
    {
        $websiteAttributes = Piwik_Tracker_Cache::getCacheWebsiteAttributes($idSite);
        return !$websiteAttributes['keep_url_fragment'];
    }

    /**
     * Given the Input URL, will exclude all query parameters set for this site
     * Note: Site Search parameters are excluded in detectSiteSearch()
     * @static
     * @param $originalUrl
     * @param $idSite
     * @return bool|string
     */
    static public function excludeQueryParametersFromUrl($originalUrl, $idSite)
    {
        $originalUrl = self::cleanupUrl($originalUrl);

        $parsedUrl = @parse_url($originalUrl);
        $parsedUrl = self::cleanupHostAndHashTag($parsedUrl, $idSite);
        $parametersToExclude = self::getQueryParametersToExclude($idSite);

        if (empty($parsedUrl['query'])) {
            if (empty($parsedUrl['fragment'])) {
                return Piwik_Common::getParseUrlReverse($parsedUrl);
            }
            // Exclude from the hash tag as well
            $queryParameters = Piwik_Common::getArrayFromQueryString($parsedUrl['fragment']);
            $parsedUrl['fragment'] = self::getQueryStringWithExcludedParameters($queryParameters, $parametersToExclude);
            $url = Piwik_Common::getParseUrlReverse($parsedUrl);
            return $url;
        }
        $queryParameters = Piwik_Common::getArrayFromQueryString($parsedUrl['query']);
        $parsedUrl['query'] = self::getQueryStringWithExcludedParameters($queryParameters, $parametersToExclude);
        $url = Piwik_Common::getParseUrlReverse($parsedUrl);
        return $url;
    }

    /**
     * Returns the array of parameters names that must be excluded from the Query String in all tracked URLs
     * @static
     * @param $idSite
     * @return array
     */
    public static function getQueryParametersToExclude($idSite)
    {
        $campaignTrackingParameters = Piwik_Common::getCampaignParameters();

        $campaignTrackingParameters = array_merge(
            $campaignTrackingParameters[0], // campaign name parameters
            $campaignTrackingParameters[1] // campaign keyword parameters
        );

        $website = Piwik_Tracker_Cache::getCacheWebsiteAttributes($idSite);
        $excludedParameters = isset($website['excluded_parameters'])
            ? $website['excluded_parameters']
            : array();

        if (!empty($excludedParameters)) {
            printDebug('Excluding parameters "' . implode(',', $excludedParameters) . '" from URL');
        }

        $parametersToExclude = array_merge($excludedParameters,
            self::$queryParametersToExclude,
            $campaignTrackingParameters);

        $parametersToExclude = array_map('strtolower', $parametersToExclude);
        return $parametersToExclude;
    }

    /**
     * Returns a Query string,
     * Given an array of input parameters, and an array of parameter names to exclude
     *
     * @static
     * @param $queryParameters
     * @param $parametersToExclude
     * @return string
     */
    public static function getQueryStringWithExcludedParameters($queryParameters, $parametersToExclude)
    {
        $validQuery = '';
        $separator = '&';
        foreach ($queryParameters as $name => $value) {
            // decode encoded square brackets
            $name = str_replace(array('%5B', '%5D'), array('[', ']'), $name);

            if (!in_array(strtolower($name), $parametersToExclude)) {
                if (is_array($value)) {
                    foreach ($value as $param) {
                        if ($param === false) {
                            $validQuery .= $name . '[]' . $separator;
                        } else {
                            $validQuery .= $name . '[]=' . $param . $separator;
                        }
                    }
                } else if ($value === false) {
                    $validQuery .= $name . $separator;
                } else {
                    $validQuery .= $name . '=' . $value . $separator;
                }
            }
        }
        $validQuery = substr($validQuery, 0, -strlen($separator));
        return $validQuery;
    }

    public function init()
    {
        $this->pageEncoding = Piwik_Common::getRequestVar('cs', false, null, $this->request);

        $info = $this->extractUrlAndActionNameFromRequest();

        $originalUrl = $info['url'];
        $info['url'] = self::excludeQueryParametersFromUrl($originalUrl, $this->idSite);

        if ($originalUrl != $info['url']) {
            printDebug(' Before was "' . $originalUrl . '"');
            printDebug(' After is "' . $info['url'] . '"');
        }

        // Set Final attributes for this Action (Pageview, Search, etc.)
        $this->setActionName($info['name']);
        $this->setActionType($info['type']);
        $this->setActionUrl($info['url']);
    }

    static public function getSqlSelectActionId()
    {
        $sql = "SELECT idaction, type, name
							FROM " . Piwik_Common::prefixTable('log_action')
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
            if ($type == Piwik_Tracker_Action::TYPE_ACTION_URL) {
                // normalize urls by stripping protocol and www
                $normalizedUrls[$index] = self::normalizeUrl($name);
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
        $actionIds = Piwik_Tracker::getDatabase()->fetchAll($sql, $bind);

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

        $sql = "INSERT INTO " . Piwik_Common::prefixTable('log_action') .
            "( name, hash, type, url_prefix ) VALUES (?,CRC32(?),?,?)";
        // Then, we insert all new actions in the lookup table
        foreach ($actionsToInsert as $actionToInsert) {
            list($name, $type) = $actionNamesAndTypes[$actionToInsert];

            $urlPrefix = null;
            if (isset($normalizedUrls[$actionToInsert])) {
                $name = $normalizedUrls[$actionToInsert]['url'];
                $urlPrefix = $normalizedUrls[$actionToInsert]['prefixId'];
            }

            Piwik_Tracker::getDatabase()->query($sql, array($name, $name, $type, $urlPrefix));
            $actionId = Piwik_Tracker::getDatabase()->lastInsertId();
            printDebug("Recorded a new action (" . self::getActionTypeName($type) . ") in the lookup table: " . $name . " (idaction = " . $actionId . ")");

            $actionNamesAndTypes[$actionToInsert][] = $actionId;
        }
        return $actionNamesAndTypes;
    }

    static public function getActionTypeName($type)
    {
        switch ($type) {
            case self::TYPE_ACTION_URL:
                return 'Page URL';
                break;
            case self::TYPE_OUTLINK:
                return 'Outlink URL';
                break;
            case self::TYPE_DOWNLOAD:
                return 'Download URL';
                break;
            case self::TYPE_ACTION_NAME:
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
        if ($nameType == Piwik_Tracker_Action::TYPE_SITE_SEARCH) {
            $urlType = Piwik_Tracker_Action::TYPE_ACTION_URL;

            // By default, Site Search does not record the URL for the Search Result page, to slightly improve performance
            if (empty(Piwik_Config::getInstance()->Tracker['action_sitesearch_record_url'])) {
                $url = false;
            }
        }
        if (!is_null($urlType) && !empty($url)) {
            $actions[] = array($url, $urlType);
        }

        $loadedActionIds = self::loadActionId($actions);

        foreach ($loadedActionIds as $loadedActionId) {
            list($name, $type, $actionId) = $loadedActionId;
            if ($type == Piwik_Tracker_Action::TYPE_ACTION_NAME
                || $type == Piwik_Tracker_Action::TYPE_SITE_SEARCH
            ) {
                $this->idActionName = $actionId;
            } else {
                $this->idActionUrl = $actionId;
            }
        }
    }

    /**
     * @param int $idSite
     */
    function setIdSite($idSite)
    {
        $this->idSite = $idSite;
    }

    function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }


    /**
     * Records in the DB the association between the visit and this action.
     *
     * @param int $idVisit is the ID of the current visit in the DB table log_visit
     * @param $visitorIdCookie
     * @param int $idRefererActionUrl is the ID of the last action done by the current visit.
     * @param $idRefererActionName
     * @param int $timeSpentRefererAction is the number of seconds since the last action was done.
     *                 It is directly related to idRefererActionUrl.
     */
    public function record($idVisit, $visitorIdCookie, $idRefererActionUrl, $idRefererActionName, $timeSpentRefererAction)
    {
        $this->loadIdActionNameAndUrl();

        $idActionName = in_array($this->getActionType(), array(Piwik_Tracker_Action::TYPE_ACTION_NAME,
                                                               Piwik_Tracker_Action::TYPE_ACTION_URL,
                                                               Piwik_Tracker_Action::TYPE_SITE_SEARCH))
            ? (int)$this->getIdActionName()
            : null;


        $insert = array(
            'idvisit'               => $idVisit,
            'idsite'                => $this->idSite,
            'idvisitor'             => $visitorIdCookie,
            'server_time'           => Piwik_Tracker::getDatetimeFromTimestamp($this->timestamp),
            'idaction_url'          => $this->getIdActionUrl(),
            'idaction_name'         => $idActionName,
            'idaction_url_ref'      => $idRefererActionUrl,
            'idaction_name_ref'     => $idRefererActionName,
            'time_spent_ref_action' => $timeSpentRefererAction
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
        $values = Piwik_Common::getSqlStringFieldsArray($insertWithoutNulls);

        $sql = "INSERT INTO " . Piwik_Common::prefixTable('log_link_visit_action') . " ($fields) VALUES ($values)";
        Piwik_Tracker::getDatabase()->query($sql, $bind);

        $this->idLinkVisitAction = Piwik_Tracker::getDatabase()->lastInsertId();

        $info = array(
            'idSite'                 => $this->idSite,
            'idLinkVisitAction'      => $this->idLinkVisitAction,
            'idVisit'                => $idVisit,
            'idRefererActionUrl'     => $idRefererActionUrl,
            'idRefererActionName'    => $idRefererActionName,
            'timeSpentRefererAction' => $timeSpentRefererAction,
        );
        printDebug($insertWithoutNulls);

        /*
        * send the Action object ($this)  and the list of ids ($info) as arguments to the event
        */
        Piwik_PostEvent('Tracker.Action.record', $this, $info);
    }

    public function getCustomVariables()
    {
        $customVariables = Piwik_Tracker_Visit::getCustomVariables($scope = 'page', $this->request);

        // Enrich Site Search actions with Custom Variables, overwriting existing values
        if (!empty($this->searchCategory)) {
            if (!empty($customVariables['custom_var_k' . self::CVAR_INDEX_SEARCH_CATEGORY])) {
                printDebug("WARNING: Overwriting existing Custom Variable  in slot " . self::CVAR_INDEX_SEARCH_CATEGORY . " for this page view");
            }
            $customVariables['custom_var_k' . self::CVAR_INDEX_SEARCH_CATEGORY] = self::CVAR_KEY_SEARCH_CATEGORY;
            $customVariables['custom_var_v' . self::CVAR_INDEX_SEARCH_CATEGORY] = Piwik_Tracker_Visit::truncateCustomVariable($this->searchCategory);
        }
        if ($this->searchCount !== false) {
            if (!empty($customVariables['custom_var_k' . self::CVAR_INDEX_SEARCH_COUNT])) {
                printDebug("WARNING: Overwriting existing Custom Variable  in slot " . self::CVAR_INDEX_SEARCH_COUNT . " for this page view");
            }
            $customVariables['custom_var_k' . self::CVAR_INDEX_SEARCH_COUNT] = self::CVAR_KEY_SEARCH_COUNT;
            $customVariables['custom_var_v' . self::CVAR_INDEX_SEARCH_COUNT] = (int)$this->searchCount;
        }

        if (!empty($customVariables)) {
            printDebug("Page level Custom Variables: ");
            printDebug($customVariables);
        }
        return $customVariables;
    }

    /**
     * Returns the ID of the newly created record in the log_link_visit_action table
     *
     * @return int | false
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
        $actionName = null;

        // download?
        $downloadUrl = Piwik_Common::getRequestVar('download', '', 'string', $this->request);
        if (!empty($downloadUrl)) {
            $actionType = self::TYPE_DOWNLOAD;
            $url = $downloadUrl;
        }

        // outlink?
        if (empty($actionType)) {
            $outlinkUrl = Piwik_Common::getRequestVar('link', '', 'string', $this->request);
            if (!empty($outlinkUrl)) {
                $actionType = self::TYPE_OUTLINK;
                $url = $outlinkUrl;
            }
        }

        // handle encoding
        $actionName = Piwik_Common::getRequestVar('action_name', '', 'string', $this->request);

        // defaults to page view
        if (empty($actionType)) {
            $actionType = self::TYPE_ACTION_URL;
            $url = Piwik_Common::getRequestVar('url', '', 'string', $this->request);

            // get the delimiter, by default '/'; BC, we read the old action_category_delimiter first (see #1067)
            $actionCategoryDelimiter = isset(Piwik_Config::getInstance()->General['action_category_delimiter'])
                ? Piwik_Config::getInstance()->General['action_category_delimiter']
                : Piwik_Config::getInstance()->General['action_url_category_delimiter'];

            // create an array of the categories delimited by the delimiter
            $split = explode($actionCategoryDelimiter, $actionName);

            // trim every category
            $split = array_map('trim', $split);

            // remove empty categories
            $split = array_filter($split, 'strlen');

            // rebuild the name from the array of cleaned categories
            $actionName = implode($actionCategoryDelimiter, $split);
        }
        $url = self::cleanupString($url);

        if (!Piwik_Common::isLookLikeUrl($url)) {
            printDebug("WARNING: URL looks invalid and is discarded");
            $url = '';
        }

        // Site search?
        if ($actionType == self::TYPE_ACTION_URL) {
            // Look in tracked URL for the Site Search parameters
            $siteSearch = $this->detectSiteSearch($url);
            if (!empty($siteSearch)) {
                $actionType = self::TYPE_SITE_SEARCH;
                list($actionName, $url) = $siteSearch;
            }
            // Look for performance analytics parameters
            $this->detectPerformanceAnalyticsParameters();
        }
        $actionName = self::cleanupString($actionName);

        return array(
            'name' => empty($actionName) ? '' : $actionName,
            'type' => $actionType,
            'url'  => $url,
        );
    }

    protected function detectSiteSearch($originalUrl)
    {
        $website = Piwik_Tracker_Cache::getCacheWebsiteAttributes($this->idSite);
        if (empty($website['sitesearch'])) {
            printDebug("Internal 'Site Search' tracking is not enabled for this site. ");
            return false;
        }
        $actionName = $url = $categoryName = $count = false;
        $doTrackUrlForSiteSearch = !empty(Piwik_Config::getInstance()->Tracker['action_sitesearch_record_url']);

        $originalUrl = self::cleanupUrl($originalUrl);


        // Detect Site search from Tracking API parameters rather than URL
        $searchKwd = Piwik_Common::getRequestVar(self::PARAMETER_NAME_SEARCH_KEYWORD, '', 'string', $this->request);
        if (!empty($searchKwd)) {
            $actionName = $searchKwd;
            if ($doTrackUrlForSiteSearch) {
                $url = $originalUrl;
            }
            $isCategoryName = Piwik_Common::getRequestVar(self::PARAMETER_NAME_SEARCH_CATEGORY, false, 'string', $this->request);
            if (!empty($isCategoryName)) {
                $categoryName = $isCategoryName;
            }
            $isCount = Piwik_Common::getRequestVar(self::PARAMETER_NAME_SEARCH_COUNT, -1, 'int', $this->request);
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
            printDebug("(this is not a Site Search request)");
            return false;
        }

        printDebug("Detected Site Search keyword '$actionName'. ");
        if (!empty($categoryName)) {
            printDebug("- Detected Site Search Category '$categoryName'. ");
        }
        if ($count !== false) {
            printDebug("- Search Results Count was '$count'. ");
        }
        if ($url != $originalUrl) {
            printDebug("NOTE: The Page URL was changed / removed, during the Site Search detection, was '$originalUrl', now is '$url'");
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
        $parametersRaw = Piwik_Common::getArrayFromQueryString($queryString);

        // strtolower the parameter names for smooth site search detection
        $parameters = array();
        foreach ($parametersRaw as $k => $v) {
            $parameters[Piwik_Common::mb_strtolower($k)] = $v;
        }
        // decode values if they were sent from a client using another charset
        self::reencodeParameters($parameters, $this->pageEncoding);

        // Detect Site Search keyword
        foreach ($keywordParameters as $keywordParameterRaw) {
            $keywordParameter = Piwik_Common::mb_strtolower($keywordParameterRaw);
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
            $categoryParameter = Piwik_Common::mb_strtolower($categoryParameterRaw);
            if (!empty($parameters[$categoryParameter])) {
                $categoryName = $parameters[$categoryParameter];
                break;
            }
        }

        if (isset($parameters[self::PARAMETER_NAME_SEARCH_COUNT])
            && $this->isValidSearchCount($parameters[self::PARAMETER_NAME_SEARCH_COUNT])
        ) {
            $count = $parameters[self::PARAMETER_NAME_SEARCH_COUNT];
        }
        // Remove search kwd from URL
        if ($doRemoveSearchParametersFromUrl) {
            // @see excludeQueryParametersFromUrl()
            // Excluded the detected parameters from the URL
            $parametersToExclude = array($categoryParameterRaw, $keywordParameterRaw);
            $parsedUrl['query'] = self::getQueryStringWithExcludedParameters(Piwik_Common::getArrayFromQueryString($parsedUrl['query']), $parametersToExclude);
            $parsedUrl['fragment'] = self::getQueryStringWithExcludedParameters(Piwik_Common::getArrayFromQueryString($parsedUrl['fragment']), $parametersToExclude);
        }
        $url = Piwik_Common::getParseUrlReverse($parsedUrl);
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

    const GENERATION_TIME_MS_MAXIMUM = 3600000; // 1 hour
    protected function detectPerformanceAnalyticsParameters()
    {
        $generationTime = Piwik_Common::getRequestVar(self::PARAMETER_NAME_TIME_GENERATION, -1, 'int', $this->request);
        if ($generationTime > 0
            && $generationTime < self::GENERATION_TIME_MS_MAXIMUM) {
            $this->timeGeneration = (int)$generationTime;
        }
    }

    /**
     * Clean up string contents (filter, truncate, ...)
     *
     * @param string $string Dirty string
     * @return string
     */
    protected static function cleanupString($string)
    {
        $string = trim($string);
        $string = str_replace(array("\n", "\r", "\0"), '', $string);

        $limit = Piwik_Config::getInstance()->Tracker['page_maximum_length'];
        return substr($string, 0, $limit);
    }

    /**
     * Checks if query parameters are of a non-UTF-8 encoding and converts the values
     * from the specified encoding to UTF-8.
     * This method is used to workaround browser/webapp bugs (see #3450). When
     * browsers fail to encode query parameters in UTF-8, the tracker will send the
     * charset of the page viewed and we can sometimes work around invalid data
     * being stored.
     *
     * @param array        $queryParameters Name/value mapping of query parameters.
     * @param bool|string  $encoding        of the HTML page the URL is for. Used to workaround
     *                                      browser bugs & mis-coded webapps. See #3450.
     *
     * @return array
     */
    private static function reencodeParameters(&$queryParameters, $encoding = false)
    {
        // if query params are encoded w/ non-utf8 characters (due to browser bug or whatever),
        // encode to UTF-8.
        if ($encoding !== false
            && strtolower($encoding) != 'utf-8'
            && function_exists('mb_check_encoding')
        ) {
            $queryParameters = self::reencodeParametersArray($queryParameters, $encoding);
        }
        return $queryParameters;
    }

    private static function reencodeParametersArray($queryParameters, $encoding)
    {
        foreach ($queryParameters as &$value) {
            if (is_array($value)) {
                $value = self::reencodeParametersArray($value, $encoding);
            } else {
                $value = self::reencodeParameterValue($value, $encoding);
            }
        }
        return $queryParameters;
    }

    private static function reencodeParameterValue($value, $encoding)
    {
        if (is_string($value)) {
            $decoded = urldecode($value);
            if (@mb_check_encoding($decoded, $encoding)) {
                $value = urlencode(mb_convert_encoding($decoded, 'UTF-8', $encoding));
            }
        }
        return $value;
    }
}
