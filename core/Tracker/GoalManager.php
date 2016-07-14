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
use Piwik\Date;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\ConversionDimension;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Tracker;
use Piwik\Tracker\Visit\VisitProperties;

/**
 */
class GoalManager
{
    // log_visit.visit_goal_buyer
    const TYPE_BUYER_OPEN_CART = 2;
    const TYPE_BUYER_ORDERED_AND_OPEN_CART = 3;

    // log_conversion.idorder is NULLable, but not log_conversion_item which defaults to zero for carts
    const ITEM_IDORDER_ABANDONED_CART = 0;

    // log_conversion.idgoal special values
    const IDGOAL_CART = -1;
    const IDGOAL_ORDER = 0;

    const REVENUE_PRECISION = 2;

    /**
     * @var array
     */
    private $currentGoal = array();

    public function detectIsThereExistingCartInVisit($visitInformation)
    {
        if (empty($visitInformation['visit_goal_buyer'])) {
            return false;
        }

        $goalBuyer = $visitInformation['visit_goal_buyer'];
        $types     = array(GoalManager::TYPE_BUYER_OPEN_CART, GoalManager::TYPE_BUYER_ORDERED_AND_OPEN_CART);

        // Was there a Cart for this visit prior to the order?
        return in_array($goalBuyer, $types);
    }

    public static function getGoalDefinitions($idSite)
    {
        $websiteAttributes = Cache::getCacheWebsiteAttributes($idSite);

        if (isset($websiteAttributes['goals'])) {
            return $websiteAttributes['goals'];
        }

        return array();
    }

    public static function getGoalDefinition($idSite, $idGoal)
    {
        $goals = self::getGoalDefinitions($idSite);

        foreach ($goals as $goal) {
            if ($goal['idgoal'] == $idGoal) {
                return $goal;
            }
        }

        throw new Exception('Goal not found');
    }

    public static function getGoalIds($idSite)
    {
        $goals   = self::getGoalDefinitions($idSite);
        $goalIds = array();

        foreach ($goals as $goal) {
            $goalIds[] = $goal['idgoal'];
        }

        return $goalIds;
    }

    /**
     * Look at the URL or Page Title and sees if it matches any existing Goal definition
     *
     * @param int $idSite
     * @param Action $action
     * @throws Exception
     * @return array[] Goals matched
     */
    public function detectGoalsMatchingUrl($idSite, $action)
    {
        if (!Common::isGoalPluginEnabled()) {
            return array();
        }

        $goals = $this->getGoalDefinitions($idSite);

        $convertedGoals = array();
        foreach ($goals as $goal) {
            $convertedUrl = $this->detectGoalMatch($goal, $action);
            if (!is_null($convertedUrl)) {
                $convertedGoals[] = array('url' => $convertedUrl) + $goal;
            }
        }
        return $convertedGoals;
    }

    /**
     * Detects if an Action matches a given goal. If it does, the URL that triggered the goal
     * is returned. Otherwise null is returned.
     *
     * @param array $goal
     * @param Action $action
     * @return if a goal is matched, a string of the Action URL is returned, or if no goal was matched it returns null
     */
    public function detectGoalMatch($goal, Action $action)
    {
        $actionType = $action->getActionType();

        $attribute = $goal['match_attribute'];

        // if the attribute to match is not the type of the current action
        if ((($attribute == 'url' || $attribute == 'title') && $actionType != Action::TYPE_PAGE_URL)
          || ($attribute == 'file' && $actionType != Action::TYPE_DOWNLOAD)
          || ($attribute == 'external_website' && $actionType != Action::TYPE_OUTLINK)
          || ($attribute == 'manually')
          || in_array($attribute, array('event_action', 'event_name', 'event_category')) && $actionType != Action::TYPE_EVENT
        ) {
            return null;
        }


        switch ($attribute) {
            case 'title':
                // Matching on Page Title
                $actionToMatch = $action->getActionName();
                break;
            case 'event_action':
                $actionToMatch = $action->getEventAction();
                break;
            case 'event_name':
                $actionToMatch = $action->getEventName();
                break;
            case 'event_category':
                $actionToMatch = $action->getEventCategory();
                break;
            // url, external_website, file, manually...
            default:
                $actionToMatch = $action->getActionUrlRaw();
                break;
        }

        $pattern_type = $goal['pattern_type'];

        $match = $this->isUrlMatchingGoal($goal, $pattern_type, $actionToMatch);
        if (!$match) {
            return null;
        }

        return $action->getActionUrl();
    }

    public function detectGoalId($idSite, Request $request)
    {
        if (!Common::isGoalPluginEnabled()) {
            return null;
        }

        $idGoal = $request->getParam('idgoal');

        $goals = $this->getGoalDefinitions($idSite);

        if (!isset($goals[$idGoal])) {
            return null;
        }

        $goal = $goals[$idGoal];

        $url         = $request->getParam('url');
        $goal['url'] = PageUrl::excludeQueryParametersFromUrl($url, $idSite);
        return $goal;
    }

    /**
     * Records one or several goals matched in this request.
     *
     * @param Visitor $visitor
     * @param array $visitorInformation
     * @param array $visitCustomVariables
     * @param Action $action
     */
    public function recordGoals(VisitProperties $visitProperties, Request $request)
    {
        $visitorInformation = $visitProperties->getProperties();
        $visitCustomVariables = $request->getMetadata('CustomVariables', 'visitCustomVariables') ?: array();

        /** @var Action $action */
        $action = $request->getMetadata('Actions', 'action');

        $goal = $this->getGoalFromVisitor($visitProperties, $request, $action);

        // Copy Custom Variables from Visit row to the Goal conversion
        // Otherwise, set the Custom Variables found in the cookie sent with this request
        $goal += $visitCustomVariables;
        $maxCustomVariables = CustomVariables::getNumUsableCustomVariables();

        for ($i = 1; $i <= $maxCustomVariables; $i++) {
            if (isset($visitorInformation['custom_var_k' . $i])
                && strlen($visitorInformation['custom_var_k' . $i])
            ) {
                $goal['custom_var_k' . $i] = $visitorInformation['custom_var_k' . $i];
            }
            if (isset($visitorInformation['custom_var_v' . $i])
                && strlen($visitorInformation['custom_var_v' . $i])
            ) {
                $goal['custom_var_v' . $i] = $visitorInformation['custom_var_v' . $i];
            }
        }

        // some goals are converted, so must be ecommerce Order or Cart Update
        $isRequestEcommerce = $request->getMetadata('Ecommerce', 'isRequestEcommerce');
        if ($isRequestEcommerce) {
            $this->recordEcommerceGoal($visitProperties, $request, $goal, $action);
        } else {
            $this->recordStandardGoals($visitProperties, $request, $goal, $action);
        }
    }


    /**
     * Records an Ecommerce conversion in the DB. Deals with Items found in the request.
     * Will deal with 2 types of conversions: Ecommerce Order and Ecommerce Cart update (Add to cart, Update Cart etc).
     *
     * @param array $conversion
     * @param Visitor $visitor
     * @param Action $action
     * @param array $visitInformation
     */
    protected function recordEcommerceGoal(VisitProperties $visitProperties, Request $request, $conversion, $action)
    {
        $isThereExistingCartInVisit = $request->getMetadata('Goals', 'isThereExistingCartInVisit');
        if ($isThereExistingCartInVisit) {
            Common::printDebug("There is an existing cart for this visit");
        }

        $visitor = Visitor::makeFromVisitProperties($visitProperties, $request);

        $isGoalAnOrder = $request->getMetadata('Ecommerce', 'isGoalAnOrder');
        if ($isGoalAnOrder) {
            $debugMessage = 'The conversion is an Ecommerce order';

            $orderId = $request->getParam('ec_id');

            $conversion['idorder'] = $orderId;
            $conversion['idgoal']  = self::IDGOAL_ORDER;
            $conversion['buster']  = Common::hashStringToInt($orderId);

            $conversionDimensions = ConversionDimension::getAllDimensions();
            $conversion = $this->triggerHookOnDimensions($request, $conversionDimensions, 'onEcommerceOrderConversion', $visitor, $action, $conversion);
        } // If Cart update, select current items in the previous Cart
        else {
            $debugMessage = 'The conversion is an Ecommerce Cart Update';

            $conversion['buster'] = 0;
            $conversion['idgoal'] = self::IDGOAL_CART;

            $conversionDimensions = ConversionDimension::getAllDimensions();
            $conversion = $this->triggerHookOnDimensions($request, $conversionDimensions, 'onEcommerceCartUpdateConversion', $visitor, $action, $conversion);
        }

        Common::printDebug($debugMessage . ':' . var_export($conversion, true));

        $ecommerce = new EcommerceItems($request);

        // Do not track ecommerce if there is no item
        if($ecommerce->isItemsInRequestInvalid()) {
            Common::printDebug("Ecommerce items in the request could not be read -> we do not record the ecommerce order.");
            return;
        }

        $conversion['items'] = $ecommerce->getItemsCount();


        if ($isThereExistingCartInVisit) {
            $recorded = $this->getModel()->updateConversion(
                $visitProperties->getProperty('idvisit'), self::IDGOAL_CART, $conversion);
        } else {
            $recorded = $this->insertNewConversion($conversion, $visitProperties->getProperties(), $request);
        }

        if ($recorded) {
            $ecommerce->recordEcommerceItems($conversion);
        } else {
            Common::printDebug("Ecommerce order status did not need to be updated in the DB, so we skip storing ecommerce items.");
        }

        /**
         * Triggered after successfully persisting an ecommerce conversion.
         *
         * _Note: Subscribers should be wary of doing any expensive computation here as it may slow
         * the tracker down._
         *
         * This event is deprecated, use [Dimensions](http://developer.piwik.org/guides/dimensions) instead.
         *
         * @param array $conversion The conversion entity that was just persisted. See what information
         *                          it contains [here](/guides/persistence-and-the-mysql-backend#conversions).
         * @param array $visitInformation The visit entity that we are tracking a conversion for. See what
         *                                information it contains [here](/guides/persistence-and-the-mysql-backend#visits).
         * @deprecated
         */
        Piwik::postEvent('Tracker.recordEcommerceGoal', array($conversion, $visitProperties->getProperties()));
    }

    private function getModel()
    {
        return new Model();
    }

    public function getGoalColumn($column)
    {
        if (array_key_exists($column, $this->currentGoal)) {
            return $this->currentGoal[$column];
        }

        return false;
    }

    /**
     * Records a standard non-Ecommerce goal in the DB (URL/Title matching),
     * linking the conversion to the action that triggered it
     * @param $goal
     * @param Visitor $visitor
     * @param Action $action
     * @param $visitorInformation
     */
    protected function recordStandardGoals(VisitProperties $visitProperties, Request $request, $goal, $action)
    {
        $visitor = Visitor::makeFromVisitProperties($visitProperties, $request);

        $convertedGoals = $request->getMetadata('Goals', 'goalsConverted') ?: array();
        foreach ($convertedGoals as $convertedGoal) {
            $this->currentGoal = $convertedGoal;
            Common::printDebug("- Goal " . $convertedGoal['idgoal'] . " matched. Recording...");
            $conversion = $goal;
            $conversion['idgoal'] = $convertedGoal['idgoal'];
            $conversion['url']    = $convertedGoal['url'];

            if (!is_null($action)) {
                $conversion['idaction_url'] = $action->getIdActionUrl();
                $conversion['idlink_va'] = $action->getIdLinkVisitAction();
            }

            // If multiple Goal conversions per visit, set a cache buster
            $conversion['buster'] = $convertedGoal['allow_multiple'] == 0
                ? '0'
                : $visitProperties->getProperty('visit_last_action_time');

            $conversionDimensions = ConversionDimension::getAllDimensions();
            $conversion = $this->triggerHookOnDimensions($request, $conversionDimensions, 'onGoalConversion', $visitor, $action, $conversion);

            $this->insertNewConversion($conversion, $visitProperties->getProperties(), $request);

            /**
             * Triggered after successfully recording a non-ecommerce conversion.
             *
             * _Note: Subscribers should be wary of doing any expensive computation here as it may slow
             * the tracker down._
             *
             * This event is deprecated, use [Dimensions](http://developer.piwik.org/guides/dimensions) instead.
             *
             * @param array $conversion The conversion entity that was just persisted. See what information
             *                          it contains [here](/guides/persistence-and-the-mysql-backend#conversions).
             * @deprecated
             */
            Piwik::postEvent('Tracker.recordStandardGoals', array($conversion));
        }
    }

    /**
     * Helper function used by other record* methods which will INSERT or UPDATE the conversion in the DB
     *
     * @param array $conversion
     * @param array $visitInformation
     * @return bool
     */
    protected function insertNewConversion($conversion, $visitInformation, Request $request)
    {
        /**
         * Triggered before persisting a new [conversion entity](/guides/persistence-and-the-mysql-backend#conversions).
         *
         * This event can be used to modify conversion information or to add new information to be persisted.
         *
         * This event is deprecated, use [Dimensions](http://developer.piwik.org/guides/dimensions) instead.
         *
         * @param array $conversion The conversion entity. Read [this](/guides/persistence-and-the-mysql-backend#conversions)
         *                          to see what it contains.
         * @param array $visitInformation The visit entity that we are tracking a conversion for. See what
         *                                information it contains [here](/guides/persistence-and-the-mysql-backend#visits).
         * @param \Piwik\Tracker\Request $request An object describing the tracking request being processed.
         * @deprecated
         */
        Piwik::postEvent('Tracker.newConversionInformation', array(&$conversion, $visitInformation, $request));

        $newGoalDebug = $conversion;
        $newGoalDebug['idvisitor'] = bin2hex($newGoalDebug['idvisitor']);
        Common::printDebug($newGoalDebug);

        $wasInserted = $this->getModel()->createConversion($conversion);

        return $wasInserted;
    }


    /**
     * @param $goal
     * @param $pattern_type
     * @param $url
     * @return bool
     * @throws \Exception
     */
    protected function isUrlMatchingGoal($goal, $pattern_type, $url)
    {
        $url = Common::unsanitizeInputValue($url);
        $goal['pattern'] = Common::unsanitizeInputValue($goal['pattern']);

        $match = $this->isGoalPatternMatchingUrl($goal, $pattern_type, $url);

        if (!$match) {
            // Users may set Goal matching URL as URL encoded
            $goal['pattern'] = urldecode($goal['pattern']);

            $match = $this->isGoalPatternMatchingUrl($goal, $pattern_type, $url);
        }
        return $match;
    }

    /**
     * @param ConversionDimension[] $dimensions
     * @param string $hook
     * @param Visitor $visitor
     * @param Action|null $action
     * @param array|null $valuesToUpdate If null, $this->visitorInfo will be updated
     *
     * @return array|null The updated $valuesToUpdate or null if no $valuesToUpdate given
     */
    private function triggerHookOnDimensions(Request $request, $dimensions, $hook, $visitor, $action, $valuesToUpdate)
    {
        foreach ($dimensions as $dimension) {
            $value = $dimension->$hook($request, $visitor, $action, $this);

            if (false !== $value) {
                if (is_float($value)) {
                    $value = Common::forceDotAsSeparatorForDecimalPoint($value);
                }

                $fieldName = $dimension->getColumnName();
                $visitor->setVisitorColumn($fieldName, $value);

                $valuesToUpdate[$fieldName] = $value;
            }
        }

        return $valuesToUpdate;
    }

    private function getGoalFromVisitor(VisitProperties $visitProperties, Request $request, $action)
    {
        $goal = array(
            'idvisit'     => $visitProperties->getProperty('idvisit'),
            'idvisitor'   => $visitProperties->getProperty('idvisitor'),
            'server_time' => Date::getDatetimeFromTimestamp($visitProperties->getProperty('visit_last_action_time')),
        );

        $visitDimensions = VisitDimension::getAllDimensions();

        $visit = Visitor::makeFromVisitProperties($visitProperties, $request);
        foreach ($visitDimensions as $dimension) {
            $value = $dimension->onAnyGoalConversion($request, $visit, $action);
            if (false !== $value) {
                $goal[$dimension->getColumnName()] = $value;
            }
        }

        return $goal;
    }

    /**
     * @param $goal
     * @param $pattern_type
     * @param $url
     * @return bool
     * @throws Exception
     */
    protected function isGoalPatternMatchingUrl($goal, $pattern_type, $url)
    {
        switch ($pattern_type) {
            case 'regex':
                $pattern = $goal['pattern'];
                if (strpos($pattern, '/') !== false
                    && strpos($pattern, '\\/') === false
                ) {
                    $pattern = str_replace('/', '\\/', $pattern);
                }
                $pattern = '/' . $pattern . '/';
                if (!$goal['case_sensitive']) {
                    $pattern .= 'i';
                }
                $match = (@preg_match($pattern, $url) == 1);
                break;
            case 'contains':
                if ($goal['case_sensitive']) {
                    $matched = strpos($url, $goal['pattern']);
                } else {
                    $matched = stripos($url, $goal['pattern']);
                }
                $match = ($matched !== false);
                break;
            case 'exact':
                if ($goal['case_sensitive']) {
                    $matched = strcmp($goal['pattern'], $url);
                } else {
                    $matched = strcasecmp($goal['pattern'], $url);
                }
                $match = ($matched == 0);
                break;
            default:
                throw new Exception(Piwik::translate('General_ExceptionInvalidGoalPattern', array($pattern_type)));
                break;
        }
        return $match;
    }
}
