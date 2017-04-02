<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live;

use Piwik\Cache;
use Piwik\CacheId;
use Piwik\DataTable\Filter\ColumnDelete;
use Piwik\Date;
use Piwik\Metrics\Formatter;
use Piwik\Plugin;
use Piwik\Piwik;
use Piwik\Tracker\GoalManager;

class Visitor implements VisitorInterface
{
    private $details = array();

    function __construct($visitorRawData)
    {
        $this->details = $visitorRawData;
    }

    function getAllVisitorDetails()
    {
        $visitor = array();

        $instances = self::getAllVisitorDetailsInstances();

        foreach ($instances as $instance) {
            $instance->setDetails($this->details);
            $instance->extendVisitorDetails($visitor);
        }

        /**
         * This event can be used to add any details to a visitor. The visitor's details are for instance used in
         * API requests like 'Live.getVisitorProfile' and 'Live.getLastVisitDetails'. This can be useful for instance
         * in case your plugin defines any visit dimensions and you want to add the value of your dimension to a user.
         * It can be also useful if you want to enrich a visitor with custom fields based on other fields or if you
         * want to change or remove any fields from the user.
         *
         * **Example**
         *
         *     Piwik::addAction('Live.getAllVisitorDetails', function (&visitor, $details) {
         *         $visitor['userPoints'] = $details['actions'] + $details['events'] + $details['searches'];
         *         unset($visitor['anyFieldYouWantToRemove']);
         *     });
         *
         * @param array &visitor You can add or remove fields to the visitor array and it will reflected in the API output
         * @param array $details The details array contains all visit dimensions (columns of log_visit table)
         *
         * @deprecated  will be removed in Piwik 4
         */
        Piwik::postEvent('Live.getAllVisitorDetails', array(&$visitor, $this->details));

        return $visitor;
    }

    /**
     * Returns all available activities
     *
     * @return VisitorDetailsAbstract[]
     * @throws \Exception
     */
    protected static function getAllVisitorDetailsInstances()
    {
        $cacheId = CacheId::pluginAware('VisitorDetails');
        $cache   = Cache::getTransientCache();

        if (!$cache->contains($cacheId)) {
            $instances = [
                new VisitorDetails() // needs to be first
            ];

            foreach (self::getAllVisitorDetailsClasses() as $className) {
                $instance = new $className();

                if ($instance instanceof VisitorDetails) {
                    continue;
                }

                $instances[] = $instance;
            }

            $cache->save($cacheId, $instances);
        }

        return $cache->fetch($cacheId);
    }

    /**
     * Returns class names of all VisitorDetails classes.
     *
     * @return string[]
     * @api
     */
    protected static function getAllVisitorDetailsClasses()
    {
        return Plugin\Manager::getInstance()->findComponents('VisitorDetails', 'Piwik\Plugins\Live\VisitorDetailsAbstract');
    }

    function getVisitorId()
    {
        if (isset($this->details['idvisitor'])) {
            return bin2hex($this->details['idvisitor']);
        }
        return false;
    }

    /**
     * Removes fields that are not meant to be displayed (md5 config hash)
     * Or that the user should only access if they are Super User or admin (cookie, IP)
     *
     * @param array $visitorDetails
     * @return array
     */
    public static function cleanVisitorDetails($visitorDetails)
    {
        $toUnset = array('config_id');
        if (Piwik::isUserIsAnonymous()) {
            $toUnset[] = 'idvisitor';
            $toUnset[] = 'user_id';
            $toUnset[] = 'location_ip';
        }
        foreach ($toUnset as $keyName) {
            if (isset($visitorDetails[$keyName])) {
                unset($visitorDetails[$keyName]);
            }
        }

        return $visitorDetails;
    }

    /**
     * The &flat=1 feature is used by API.getSuggestedValuesForSegment
     *
     * @param $visitorDetailsArray
     * @return array
     */
    public static function flattenVisitorDetailsArray($visitorDetailsArray)
    {
        // NOTE: if you flatten more fields from the "actionDetails" array
        //       ==> also update API/API.php getSuggestedValuesForSegment(), the $segmentsNeedActionsInfo array

        // flatten visit custom variables
        if (!empty($visitorDetailsArray['customVariables'])
            && is_array($visitorDetailsArray['customVariables'])) {
            foreach ($visitorDetailsArray['customVariables'] as $thisCustomVar) {
                $visitorDetailsArray = array_merge($visitorDetailsArray, $thisCustomVar);
            }
            unset($visitorDetailsArray['customVariables']);
        }

        // flatten page views custom variables
        $count = 1;
        foreach ($visitorDetailsArray['actionDetails'] as $action) {
            if (!empty($action['customVariables'])) {
                foreach ($action['customVariables'] as $thisCustomVar) {
                    foreach ($thisCustomVar as $cvKey => $cvValue) {
                        $flattenedKeyName = $cvKey . ColumnDelete::APPEND_TO_COLUMN_NAME_TO_KEEP . $count;
                        $visitorDetailsArray[$flattenedKeyName] = $cvValue;
                        $count++;
                    }
                }
            }
        }

        // Flatten Goals
        $count = 1;
        foreach ($visitorDetailsArray['actionDetails'] as $action) {
            if (!empty($action['goalId'])) {
                $flattenedKeyName = 'visitConvertedGoalId' . ColumnDelete::APPEND_TO_COLUMN_NAME_TO_KEEP . $count;
                $visitorDetailsArray[$flattenedKeyName] = $action['goalId'];
                $count++;
            }
        }

        // Flatten Page Titles/URLs
        $count = 1;
        foreach ($visitorDetailsArray['actionDetails'] as $action) {

            // API.getSuggestedValuesForSegment
            $flattenForActionType = array(
                'outlink' => 'outlinkUrl',
                'download' => 'downloadUrl',
                'action' => 'pageUrl'
            );
            foreach($flattenForActionType as $actionType => $flattenedKeyPrefix) {
                if (!empty($action['url'])
                    && $action['type'] == $actionType) {
                    $flattenedKeyName = $flattenedKeyPrefix . ColumnDelete::APPEND_TO_COLUMN_NAME_TO_KEEP . $count;
                    $visitorDetailsArray[$flattenedKeyName] = $action['url'];
                }
            }

            $flatten = array( 'pageTitle', 'siteSearchKeyword', 'eventCategory', 'eventAction', 'eventName', 'eventValue');
            foreach($flatten as $toFlatten) {
                if (!empty($action[$toFlatten])) {
                    $flattenedKeyName = $toFlatten . ColumnDelete::APPEND_TO_COLUMN_NAME_TO_KEEP . $count;
                    $visitorDetailsArray[$flattenedKeyName] = $action[$toFlatten];
                }
            }
            $count++;
        }

        // Entry/exit pages
        $firstAction = $lastAction = false;
        foreach ($visitorDetailsArray['actionDetails'] as $action) {
            if ($action['type'] == 'action') {
                if (empty($firstAction)) {
                    $firstAction = $action;
                }
                $lastAction = $action;
            }
        }

        if (!empty($firstAction['pageTitle'])) {
            $visitorDetailsArray['entryPageTitle'] = $firstAction['pageTitle'];
        }
        if (!empty($firstAction['url'])) {
            $visitorDetailsArray['entryPageUrl'] = $firstAction['url'];
        }
        if (!empty($lastAction['pageTitle'])) {
            $visitorDetailsArray['exitPageTitle'] = $lastAction['pageTitle'];
        }
        if (!empty($lastAction['url'])) {
            $visitorDetailsArray['exitPageUrl'] = $lastAction['url'];
        }

        return $visitorDetailsArray;
    }

    /**
     * @param $visitorDetailsArray
     * @param $actionsLimit
     * @param $timezone
     * @return array
     */
    public static function enrichVisitorArrayWithActions($visitorDetailsArray, $actionsLimit, $idSite, $timezone)
    {
        $idVisit = $visitorDetailsArray['idVisit'];

        $visitorDetailsManipulators = self::getAllVisitorDetailsInstances();

        $model = new Model();
        $actionDetails = $model->queryActionsForVisit($idVisit, $actionsLimit);

        foreach ($visitorDetailsManipulators as $instance) {
            $instance->filterActions($actionDetails);
        }

        $formatter = new Formatter();

        foreach ($actionDetails AS $idx => &$action) {
            // Enrich with time spent per action
            $nextAction = isset($actionDetails[$idx+1]) ? $actionDetails[$idx+1] : null;

            // Set the time spent for this action (which is the timeSpentRef of the next action)
            if ($nextAction) {
                $action['timeSpent'] = $nextAction['timeSpentRef'];
            } else {

                // Last action of a visit.
                // By default, Piwik does not know how long the user stayed on the page
                // If enableHeartBeatTimer() is used in piwik.js then we can find the accurate time on page for the last pageview
                $visitTotalTime = $visitorDetailsArray['visitDuration'];
                $timeOfLastAction = Date::factory($action['serverTimePretty'])->getTimestamp();

                $timeSpentOnAllActionsApartFromLastOne = ($timeOfLastAction - $visitorDetailsArray['firstActionTimestamp']);
                $timeSpentOnPage = $visitTotalTime - $timeSpentOnAllActionsApartFromLastOne;

                // Safe net, we assume the time is correct when it's more than 10 seconds
                if ($timeSpentOnPage > 10) {
                    $action['timeSpent'] = $timeSpentOnPage;
                }
            }

            if (isset($action['timeSpent'])) {
                $action['timeSpentPretty'] = $formatter->getPrettyTimeFromSeconds($action['timeSpent'], true);
            }

            unset($action['timeSpentRef']); // not needed after timeSpent is added
        }

        // If the visitor converted a goal, we shall select all Goals
        $goalDetails = $model->queryGoalConversionsForVisit($idVisit, $actionsLimit);

        $ecommerceDetails = $model->queryEcommerceConversionsForVisit($idVisit, $actionsLimit);
        foreach ($ecommerceDetails as &$ecommerceDetail) {
            if ($ecommerceDetail['type'] == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART) {
                unset($ecommerceDetail['orderId']);
                unset($ecommerceDetail['revenueSubTotal']);
                unset($ecommerceDetail['revenueTax']);
                unset($ecommerceDetail['revenueShipping']);
                unset($ecommerceDetail['revenueDiscount']);
            }

            // 25.00 => 25
            foreach ($ecommerceDetail as $column => $value) {
                if (strpos($column, 'revenue') !== false) {
                    if ($value == round($value)) {
                        $ecommerceDetail[$column] = round($value);
                    }
                }
            }
        }

        // Enrich ecommerce carts/orders with the list of products
        usort($ecommerceDetails, array('static', 'sortByServerTime'));
        foreach ($ecommerceDetails as &$ecommerceConversion) {
            $idOrder = isset($ecommerceConversion['orderId']) ? $ecommerceConversion['orderId'] : GoalManager::ITEM_IDORDER_ABANDONED_CART;

            $itemsDetails = $model->queryEcommerceItemsForOrder($idVisit, $idOrder, $actionsLimit);
            foreach ($itemsDetails as &$detail) {
                if ($detail['price'] == round($detail['price'])) {
                    $detail['price'] = round($detail['price']);
                }
            }
            $ecommerceConversion['itemDetails'] = $itemsDetails;
        }

        $actions = array_merge($actionDetails, $goalDetails, $ecommerceDetails);
        usort($actions, array('static', 'sortByServerTime'));

        $visitorDetailsArray['goalConversions'] = count($goalDetails);

        $actions = array_values($actions);

        foreach ($actions as $actionIdx => &$actionDetail) {
            $actionDetail =& $actions[$actionIdx];
            $nextAction = isset($actions[$actionIdx+1]) ? $actions[$actionIdx+1] : null;

            foreach ($visitorDetailsManipulators as $instance) {
                $instance->extendActionDetails($actionDetail, $nextAction, $visitorDetailsArray);
            }
        }

        $visitorDetailsArray['actionDetails'] = $actions;

        return $visitorDetailsArray;
    }

    private static function sortByServerTime($a, $b)
    {
        $ta = strtotime($a['serverTimePretty']);
        $tb = strtotime($b['serverTimePretty']);

        if ($ta < $tb) {
            return -1;
        }

        if ($ta == $tb) {
            if ($a['idlink_va'] > $b['idlink_va']) {
               return 1;
            }

            return -1;
        }

        return 1;
    }
}
