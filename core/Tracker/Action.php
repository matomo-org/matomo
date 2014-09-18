<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker;

use Exception;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugin\Manager;
use Piwik\Tracker;

/**
 * An action
 *
 */
abstract class Action
{
    const TYPE_PAGE_URL = 1;
    const TYPE_OUTLINK = 2;
    const TYPE_DOWNLOAD = 3;
    const TYPE_PAGE_TITLE = 4;
    const TYPE_ECOMMERCE_ITEM_SKU = 5;
    const TYPE_ECOMMERCE_ITEM_NAME = 6;
    const TYPE_ECOMMERCE_ITEM_CATEGORY = 7;
    const TYPE_SITE_SEARCH = 8;

    const TYPE_EVENT = 10; // Alias TYPE_EVENT_CATEGORY
    const TYPE_EVENT_CATEGORY = 10;
    const TYPE_EVENT_ACTION = 11;
    const TYPE_EVENT_NAME = 12;

    const TYPE_CONTENT = 13; // Alias TYPE_CONTENT_NAME
    const TYPE_CONTENT_NAME = 13;
    const TYPE_CONTENT_PIECE = 14;
    const TYPE_CONTENT_TARGET = 15;
    const TYPE_CONTENT_INTERACTION = 16;

    const DB_COLUMN_CUSTOM_FLOAT = 'custom_float';

    private static $factoryPriority = array(
        self::TYPE_PAGE_URL, self::TYPE_CONTENT, self::TYPE_SITE_SEARCH, self::TYPE_EVENT, self::TYPE_OUTLINK, self::TYPE_DOWNLOAD
    );

    /**
     * Makes the correct Action object based on the request.
     *
     * @param Request $request
     * @return Action
     */
    public static function factory(Request $request)
    {
        /** @var Action[] $actions */
        $actions = self::getAllActions($request);

        foreach ($actions as $actionType) {
            if (empty($action)) {
                $action = $actionType;
                continue;
            }

            $posPrevious = self::getPriority($action);
            $posCurrent  = self::getPriority($actionType);

            if ($posCurrent > $posPrevious) {
                $action = $actionType;
            }
        }

        if (!empty($action)) {
            return $action;
        }

        return new ActionPageview($request);
    }

    private static function getPriority(Action $actionType)
    {
        $key = array_search($actionType->getActionType(), self::$factoryPriority);

        if (false === $key) {
            return -1;
        }

        return $key;
    }

    public static function shouldHandle(Request $request)
    {
        return false;
    }

    private static function getAllActions(Request $request)
    {
        $actions   = Manager::getInstance()->findMultipleComponents('Actions', '\\Piwik\\Tracker\\Action');
        $instances = array();

        foreach ($actions as $action) {
            /** @var \Piwik\Tracker\Action $instance */
            if ($action::shouldHandle($request)) {
                $instances[] = new $action($request);
            }
        }

        return $instances;
    }

    /**
     * Public so that events listener can access it
     *
     * @var Request
     */
    public $request;

    private $idLinkVisitAction;
    private $actionIdsCached = array();
    private $actionName;
    private $actionType;
    private $actionUrl;

    public function __construct($type, Request $request)
    {
        $this->actionType = $type;
        $this->request    = $request;
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

    abstract protected function getActionsToLookup();

    protected function getUrlAndType()
    {
        $url = $this->getActionUrl();
        if (!empty($url)) {
            // normalize urls by stripping protocol and www
            $url = PageUrl::normalizeUrl($url);
            return array($url['url'], self::TYPE_PAGE_URL, $url['prefixId']);
        }
        return false;
    }

    public function getIdActionUrl()
    {
        $idUrl = $this->actionIdsCached['idaction_url'];
        // note; idaction_url = 0 is displayed as "Page URL Not Defined"
        return (int)$idUrl;
    }

    public function getIdActionUrlForEntryAndExitIds()
    {
        return $this->getIdActionUrl();
    }

    public function getIdActionNameForEntryAndExitIds()
    {
        return $this->getIdActionName();
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
        $type = self::getTypeAsString($this->getActionType());
        Common::printDebug("Action is a $type,
                Action name =  " . $this->getActionName() . ",
                Action URL = " . $this->getActionUrl());
        return true;
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

        $actions    = $this->getActionsToLookup();
        $dimensions = ActionDimension::getAllDimensions();

        foreach ($dimensions as $dimension) {
            $value = $dimension->onLookupAction($this->request, $this);

            if ($value !== false) {
                $field = $dimension->getColumnName();

                if (empty($field)) {
                    throw new Exception('Dimension ' . get_class($dimension) . ' does not define a field name');
                }

                $actions[$field] = array($value, $dimension->getActionId());
                Common::printDebug("$field = $value");
            }
        }

        $actions = array_filter($actions, 'count');

        if (empty($actions)) {
            return;
        }

        $loadedActionIds = TableLogAction::loadIdsAction($actions);

        $this->actionIdsCached = $loadedActionIds;
        return $this->actionIdsCached;
    }

    /**
     * Records in the DB the association between the visit and this action.
     *
     * @param int $idReferrerActionUrl is the ID of the last action done by the current visit.
     * @param $idReferrerActionName
     * @param Visitor $visitor
     */
    public function record(Visitor $visitor, $idReferrerActionUrl, $idReferrerActionName)
    {
        $this->loadIdsFromLogActionTable();

        $visitAction = array(
            'idvisit'           => $visitor->getVisitorColumn('idvisit'),
            'idsite'            => $this->request->getIdSite(),
            'idvisitor'         => $visitor->getVisitorColumn('idvisitor'),
            'idaction_url'      => $this->getIdActionUrl(),
            'idaction_url_ref'  => $idReferrerActionUrl,
            'idaction_name_ref' => $idReferrerActionName
        );

        $dimensions = ActionDimension::getAllDimensions();

        foreach ($dimensions as $dimension) {
            $value = $dimension->onNewAction($this->request, $visitor, $this);

            if ($value !== false) {
                $visitAction[$dimension->getColumnName()] = $value;
            }
        }

        // idaction_name is NULLable. we only set it when applicable
        if ($this->isActionHasActionName()) {
            $visitAction['idaction_name'] = (int)$this->getIdActionName();
        }

        foreach ($this->actionIdsCached as $field => $idAction) {
            $visitAction[$field] = ($idAction === false) ? 0 : $idAction;
        }

        $customValue = $this->getCustomFloatValue();
        if (!empty($customValue)) {
            $visitAction[self::DB_COLUMN_CUSTOM_FLOAT] = $customValue;
        }

        $customVariables = $this->getCustomVariables();
        if (!empty($customVariables)) {
            Common::printDebug("Page level Custom Variables: ");
            Common::printDebug($customVariables);
        }

        $visitAction = array_merge($visitAction, $customVariables);
        $fields      = implode(", ", array_keys($visitAction));
        $bind        = array_values($visitAction);
        $values      = Common::getSqlStringFieldsArray($visitAction);

        $sql = "INSERT INTO " . Common::prefixTable('log_link_visit_action') . " ($fields) VALUES ($values)";
        Tracker::getDatabase()->query($sql, $bind);

        $this->idLinkVisitAction = Tracker::getDatabase()->lastInsertId();
        $visitAction['idlink_va'] = $this->idLinkVisitAction;

        Common::printDebug("Inserted new action:");
        Common::printDebug($visitAction);

        /**
         * Triggered after successfully persisting a [visit action entity](/guides/persistence-and-the-mysql-backend#visit-actions).
         *
         * @param Action $tracker Action The Action tracker instance.
         * @param array $visitAction The visit action entity that was persisted. Read
         *                           [this](/guides/persistence-and-the-mysql-backend#visit-actions) to see what it contains.
         */
        Piwik::postEvent('Tracker.recordAction', array($trackerAction = $this, $visitAction));
    }

    /**
     * @return bool
     */
    protected function isActionHasActionName()
    {
        return in_array($this->getActionType(), array(self::TYPE_PAGE_TITLE,
                                                      self::TYPE_PAGE_URL,
                                                      self::TYPE_SITE_SEARCH));
    }
}
