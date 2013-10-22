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
use Piwik\Piwik;
use Piwik\Tracker;

/**
 *
 *
 * @package Piwik
 * @subpackage Tracker
 */
abstract class Action
{

    //FIXMEA lookup uses and check Events compat
    const TYPE_PAGE_URL = 1;
    const TYPE_OUTLINK = 2;
    const TYPE_DOWNLOAD = 3;
    const TYPE_PAGE_TITLE = 4;
    const TYPE_ECOMMERCE_ITEM_SKU = 5;
    const TYPE_ECOMMERCE_ITEM_NAME = 6;
    const TYPE_ECOMMERCE_ITEM_CATEGORY = 7;
    const TYPE_SITE_SEARCH = 8;

    //FIXMEA lookup uses and check Events compat
    const TYPE_EVENT = 11; // Same as TYPE_EVENT_ACTION
    const TYPE_EVENT_CATEGORY = 10;
    const TYPE_EVENT_ACTION = 11;
    const TYPE_EVENT_NAME = 12;


    const DB_COLUMN_CUSTOM_FLOAT = 'custom_float';

    /**
     * Makes the correct Action object based on the request.
     *
     * @param Request $request
     * @return ActionClickUrl|ActionPageview|ActionSiteSearch
     */
    static public function factory(Request $request)
    {
        $downloadUrl = $request->getParam('download');
        if (!empty($downloadUrl)) {
            return new ActionClickUrl(self::TYPE_DOWNLOAD, $downloadUrl, $request);
        }

        $outlinkUrl = $request->getParam('link');
        if (!empty($outlinkUrl)) {
            return new ActionClickUrl(self::TYPE_OUTLINK, $outlinkUrl, $request);
        }

        $url = $request->getParam('url');

        $eventCategory = $request->getParam('e_c');
        $eventAction = $request->getParam('e_a');
        if(strlen($eventCategory) > 0 && strlen($eventAction) > 0 ) {
            return new ActionEvent($eventCategory, $eventAction, $url, $request);
        }

        $action = new ActionSiteSearch($url, $request);
        if ($action->isSearchDetected()) {
            return $action;
        }
        return new ActionPageview($url, $request);
    }

    /**
     * @var Request
     */
    protected $request;

    private $idLinkVisitAction;

    protected $actionIdsCached = array();

    private $actionName;
    private $actionType;
    private $actionUrl;

    public function __construct($type, Request $request)
    {
        $this->actionType = $type;
        $this->request = $request;
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

    public function getCustomVariables()
    {
        $customVariables = $this->request->getCustomVariables($scope = 'page');
        return $customVariables;
    }

    // custom_float column
    public function getCustomFloatValue()
    {
        return false;
    }


    protected function setActionName($name)
    {
        $name = PageUrl::cleanupString((string)$name);
        $this->actionName = $name;
    }

    protected function setActionUrl($url)
    {
        $urlBefore = $url;
        $url = PageUrl::excludeQueryParametersFromUrl($url, $this->request->getIdSite());

        if ($url != $urlBefore) {
            Common::printDebug(' Before was "' . $urlBefore . '"');
            Common::printDebug(' After is "' . $url . '"');
        }

        $url = PageUrl::getUrlIfLookValid($url);
        $this->actionUrl = $url;
    }

    protected function getActionsToLookup()
    {
        return array(
            'idaction_name' => $this->getNameAndType(),
            'idaction_url' => $this->getUrlAndType()
        );
    }

    protected function getNameAndType()
    {
        return array($this->getActionName(), Action::TYPE_PAGE_TITLE);
    }

    protected function getUrlAndType()
    {
        $url = $this->getActionUrl();
        if (!empty($url)) {
            // normalize urls by stripping protocol and www
            $url = PageUrl::normalizeUrl($url);
            return array($url['url'], Tracker\Action::TYPE_PAGE_URL, $url['prefixId']);
        }
        return false;
    }

    public function getIdActionUrl()
    {
        $idUrl = $this->actionIdsCached['idaction_url'];
        // note; idaction_url = 0 is displayed as "Page URL Not Defined"
        return (int)$idUrl;
    }

    public function getIdActionName()
    {
        if(!isset($this->actionIdsCached['idaction_name'])) {
            return false;
        }
        return $this->actionIdsCached['idaction_name'];
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

    public function writeDebugInfo()
    {
        if (isset($GLOBALS['PIWIK_TRACKER_DEBUG']) && $GLOBALS['PIWIK_TRACKER_DEBUG']) {
            $type = self::getTypeAsString($this->getActionType());
            Common::printDebug("Action is a $type,
                    Action name =  " . $this->getActionName() . ",
                    Action URL = " . $this->getActionUrl());
        }
    }

    public static function getTypeAsString($type)
    {
        $class = new \ReflectionClass("\\Piwik\\Tracker\\Action");
        $constants = $class->getConstants();

        $typeId = array_search($type, $constants);
        if($typeId === false) {
            throw new Exception("Unexpected action type " . $type);
        }
        return str_replace('TYPE_', '', $typeId);
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
    public function loadIdsFromLogActionTable()
    {
        if(!empty($this->actionIdsCached)) {
            return;
        }
        $actions = $this->getActionsToLookup();
        $actions = array_filter($actions, 'count');

        if(empty($actions)) {
            return;
        }

        $loadedActionIds = TableLogAction::loadIdsAction($actions);

        $this->actionIdsCached = $loadedActionIds;
        return $this->actionIdsCached;
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
        $this->loadIdsFromLogActionTable();

        $idActionName = in_array($this->getActionType(), array(Tracker\Action::TYPE_PAGE_TITLE,
                                                               Tracker\Action::TYPE_PAGE_URL,
                                                               Tracker\Action::TYPE_SITE_SEARCH
                                                         ))
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

        $customValue = $this->getCustomFloatValue();
        if (!empty($customValue)) {
            $insert[self::DB_COLUMN_CUSTOM_FLOAT] = $customValue;
        }

        $customVariables = $this->getCustomVariables();

        if (!empty($customVariables)) {
            Common::printDebug("Page level Custom Variables: ");
            Common::printDebug($customVariables);
        }

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
        Common::printDebug("Inserted new action:");
        Common::printDebug($insertWithoutNulls);

        /**
         * This hook is called after saving (and updating) visitor information. You can use it for instance to sync the
         * recorded action with third party systems.
         */
        Piwik::postEvent('Tracker.recordAction', array($trackerAction = $this, $info));
    }

}
