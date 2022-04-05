<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker;

use Exception;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugin\Manager;
use Psr\Log\LoggerInterface;

/**
 * An action
 *
 */
abstract class Action
{
    const TYPE_PAGE_URL   = 1;
    const TYPE_OUTLINK    = 2;
    const TYPE_DOWNLOAD   = 3;
    const TYPE_PAGE_TITLE = 4;
    const TYPE_ECOMMERCE_ITEM_SKU  = 5;
    const TYPE_ECOMMERCE_ITEM_NAME = 6;
    const TYPE_ECOMMERCE_ITEM_CATEGORY = 7;
    const TYPE_SITE_SEARCH = 8;

    const TYPE_EVENT          = 10; // Alias TYPE_EVENT_CATEGORY
    const TYPE_EVENT_CATEGORY = 10;
    const TYPE_EVENT_ACTION   = 11;
    const TYPE_EVENT_NAME     = 12;

    const TYPE_CONTENT             = 13; // Alias TYPE_CONTENT_NAME
    const TYPE_CONTENT_NAME        = 13;
    const TYPE_CONTENT_PIECE       = 14;
    const TYPE_CONTENT_TARGET      = 15;
    const TYPE_CONTENT_INTERACTION = 16;

    const DB_COLUMN_CUSTOM_FLOAT = 'custom_float';

    private static $factoryPriority = array(
        self::TYPE_PAGE_URL,
        self::TYPE_SITE_SEARCH,
        self::TYPE_CONTENT,
        self::TYPE_EVENT,
        self::TYPE_OUTLINK,
        self::TYPE_DOWNLOAD
    );

    /**
     * Public so that events listener can access it
     *
     * @var Request
     */
    public $request;

    private $idLinkVisitAction;
    private $actionIdsCached = array();
    private $customFields = array();
    private $actionName;
    private $actionType;

    /**
     * URL with excluded Query parameters
     */
    private $actionUrl;

    /**
     *  Raw URL (will contain excluded URL query parameters)
     */
    private $rawActionUrl;

    /**
     * @var mixed|LoggerInterface
     */
    private $logger;

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

        if (self::isCustomActionRequest($request)) {
            throw new Exception('Request was meant for a plugin which is no longer activated. Request needs to be ignored.');
        }

        return new ActionPageview($request);
    }

    /**
     * Returns true if the tracking request was meant for some action that isn't the page view. See
     * https://github.com/matomo-org/matomo/pull/16570 for more details. Basically, plugins that implement a tracker
     * action should send a `ca=1` tracking parameter along the request so it doesn't get executed should the plugin
     * be disabled but the JS tracker is still cached and keeps on sending these requests.
     *
     * @param Request $request
     * @return bool
     * @throws Exception
     */
    public static function isCustomActionRequest(Request  $request)
    {
        return $request->hasParam('ca') && $request->getParam('ca');
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
        static $actions;

        if (is_null($actions)) {
            $actions = Manager::getInstance()->findMultipleComponents('Actions', '\\Piwik\\Tracker\\Action');
        }

        $instances = array();

        foreach ($actions as $action) {
            /** @var \Piwik\Tracker\Action $action */
            if ($action::shouldHandle($request)) {
                $instances[] = new $action($request);
            }
        }

        return $instances;
    }

    public function __construct($type, Request $request)
    {
        $this->actionType = $type;
        $this->request    = $request;
        $this->logger = StaticContainer::get(LoggerInterface::class);
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

    /**
     * Returns URL of page being tracked, including all original Query parameters
     */
    public function getActionUrlRaw()
    {
        return $this->rawActionUrl;
    }

    public function getActionName()
    {
        return $this->actionName;
    }

    public function getActionType()
    {
        return $this->actionType;
    }

    // custom_float column
    public function getCustomFloatValue()
    {
        return false;
    }

    protected function setActionName($name)
    {
        $this->actionName = PageUrl::cleanupString((string)$name);
    }

    protected function setActionUrl($url)
    {
        $this->rawActionUrl = PageUrl::getUrlIfLookValid($url);
        $url2 = PageUrl::excludeQueryParametersFromUrl($url, $this->request->getIdSite());

        $this->actionUrl = PageUrl::getUrlIfLookValid($url2);

        if ($url != $this->rawActionUrl) {
            $this->logger->debug(' Before was "{rawActionUrl}"', [
                'rawActionUrl' => $this->rawActionUrl,
            ]);
            $this->logger->debug(' After is "{url2}"', [
                'url2' => $url2,
            ]);
        }
    }

    protected function setActionUrlWithoutExcludingParameters($url)
    {
        $url = PageUrl::getUrlIfLookValid($url);
        $this->rawActionUrl = $url;
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

    public function setCustomField($field, $value)
    {
        $this->customFields[$field] = $value;
    }

    public function getCustomField($field)
    {
        if (isset($this->customFields[$field])) {
            return $this->customFields[$field];
        }
    }

    public function getCustomFields()
    {
        return $this->customFields;
    }

    public function getIdActionUrl()
    {
        $idUrl = isset($this->actionIdsCached['idaction_url']) ? $this->actionIdsCached['idaction_url'] : 0;
        // note; idaction_url = 0 is displayed as "Page URL Not Defined"
        return (int)$idUrl;
    }

    public function getIdActionUrlForEntryAndExitIds()
    {
        return false;
    }

    public function getIdActionNameForEntryAndExitIds()
    {
        return false;
    }

    public function getIdActionName()
    {
        if (!isset($this->actionIdsCached['idaction_name'])) {
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

    public static function getTypeAsString($type)
    {
        $class     = new \ReflectionClass("\\Piwik\\Tracker\\Action");
        $constants = $class->getConstants();

        $typeId = array_search($type, $constants);

        if (false === $typeId) {
            return $type;
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
        if (!empty($this->actionIdsCached)) {
            return;
        }

        /** @var ActionDimension[] $dimensions */
        $dimensions = ActionDimension::getAllDimensions();
        $actions    = $this->getActionsToLookup();

        foreach ($dimensions as $dimension) {
            $value = $dimension->onLookupAction($this->request, $this);

            if (false !== $value) {
                if (is_float($value)) {
                    $value = Common::forceDotAsSeparatorForDecimalPoint($value);
                }

                $field = $dimension->getColumnName();

                if (empty($field)) {
                    $dimensionClass = get_class($dimension);
                    throw new Exception('Dimension ' . $dimensionClass . ' does not define a field name');
                }

                $actionId        = $dimension->getActionId();
                $actions[$field] = array($value, $actionId);
                $this->logger->debug("$field = $value");
            }
        }

        $actions = array_filter($actions);

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

        /** @var ActionDimension[] $dimensions */
        $dimensions = ActionDimension::getAllDimensions();

        foreach ($dimensions as $dimension) {
            $value = $dimension->onNewAction($this->request, $visitor, $this);

            if ($value !== false) {
                if (is_float($value)) {
                    $value = Common::forceDotAsSeparatorForDecimalPoint($value);
                }

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
        if ($customValue !== false && $customValue !== null && $customValue !== '') {
            $visitAction[self::DB_COLUMN_CUSTOM_FLOAT] = Common::forceDotAsSeparatorForDecimalPoint($customValue);
        }

        $visitAction = array_merge($visitAction, $this->customFields);

        $this->idLinkVisitAction = $this->getModel()->createAction($visitAction);

        $visitAction['idlink_va'] = $this->idLinkVisitAction;

        $visitActionDebug = $visitAction;
        $visitActionDebug['idvisitor'] = bin2hex($visitActionDebug['idvisitor']);
        $this->logger->debug("Inserted new action: {action}", [
            'action' => var_export($visitActionDebug, true),
        ]);
    }

    public function writeDebugInfo()
    {
        $type = self::getTypeAsString($this->getActionType());
        $name = $this->getActionName();
        $url  = $this->getActionUrl();

        $this->logger->debug('Action is a {type}, Action name = {name}, Action URL = {url}', [
            'type' => $type,
            'name' => $name,
            'url' => $url,
        ]);

        return true;
    }

    private function getModel()
    {
        return new Model();
    }

    /**
     * @return bool
     */
    private function isActionHasActionName()
    {
        $types = array(self::TYPE_PAGE_TITLE, self::TYPE_PAGE_URL, self::TYPE_SITE_SEARCH);

        return in_array($this->getActionType(), $types);
    }
}
