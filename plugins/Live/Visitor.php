<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live;

use Piwik\Common;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable\Filter\ColumnDelete;
use Piwik\Date;
use Piwik\Db;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;
use Piwik\Plugins\Actions\Actions\ActionSiteSearch;
use Piwik\Tracker;
use Piwik\Tracker\Action;
use Piwik\Tracker\GoalManager;

class Visitor implements VisitorInterface
{
    const EVENT_VALUE_PRECISION = 3;

    private $details = array();

    function __construct($visitorRawData)
    {
        $this->details = $visitorRawData;
    }

    function getAllVisitorDetails()
    {
        $visitor = array(
            'idSite'                      => $this->getIdSite(),
            'idVisit'                     => $this->getIdVisit(),
            'visitIp'                     => $this->getIp(),
            'visitorId'                   => $this->getVisitorId(),

            // => false are placeholders to be filled in API later
            'actionDetails'               => false,
            'goalConversions'             => false,
            'siteCurrency'                => false,
            'siteCurrencySymbol'          => false,

            // all time entries
            'serverDate'                  => $this->getServerDate(),
            'visitServerHour'             => $this->getVisitServerHour(),
            'lastActionTimestamp'         => $this->getTimestampLastAction(),
            'lastActionDateTime'          => $this->getDateTimeLastAction(),
        );

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
         */
        Piwik::postEvent('Live.getAllVisitorDetails', array(&$visitor, $this->details));

        return $visitor;
    }

    function getVisitorId()
    {
        if (isset($this->details['idvisitor'])) {
            return bin2hex($this->details['idvisitor']);
        }
        return false;
    }

    function getVisitServerHour()
    {
        return date('G', strtotime($this->details['visit_last_action_time']));
    }

    function getServerDate()
    {
        return date('Y-m-d', strtotime($this->details['visit_last_action_time']));
    }

    function getIp()
    {
        if (isset($this->details['location_ip'])) {
            return IP::N2P($this->details['location_ip']);
        }
        return null;
    }

    function getIdVisit()
    {
        return $this->details['idvisit'];
    }

    function getIdSite()
    {
        return $this->details['idsite'];
    }

    function getTimestampLastAction()
    {
        return strtotime($this->details['visit_last_action_time']);
    }

    function getDateTimeLastAction()
    {
        return date('Y-m-d H:i:s', strtotime($this->details['visit_last_action_time']));
    }

    /**
     * Removes fields that are not meant to be displayed (md5 config hash)
     * Or that the user should only access if he is Super User or admin (cookie, IP)
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
        if (is_array($visitorDetailsArray['customVariables'])) {
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
            if (!empty($action['url'])) {
                $flattenedKeyName = 'pageUrl' . ColumnDelete::APPEND_TO_COLUMN_NAME_TO_KEEP . $count;
                $visitorDetailsArray[$flattenedKeyName] = $action['url'];
            }

            // API.getSuggestedValuesForSegment
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
    public static function enrichVisitorArrayWithActions($visitorDetailsArray, $actionsLimit, $timezone)
    {
        $idVisit = $visitorDetailsArray['idVisit'];

        $maxCustomVariables = CustomVariables::getMaxCustomVariables();

        $sqlCustomVariables = '';
        for ($i = 1; $i <= $maxCustomVariables; $i++) {
            $sqlCustomVariables .= ', custom_var_k' . $i . ', custom_var_v' . $i;
        }
        // The second join is a LEFT join to allow returning records that don't have a matching page title
        // eg. Downloads, Outlinks. For these, idaction_name is set to 0
        $sql = "
				SELECT
					COALESCE(log_action_event_category.type, log_action.type, log_action_title.type) AS type,
					log_action.name AS url,
					log_action.url_prefix,
					log_action_title.name AS pageTitle,
					log_action.idaction AS pageIdAction,
					log_link_visit_action.server_time as serverTimePretty,
					log_link_visit_action.time_spent_ref_action as timeSpentRef,
					log_link_visit_action.idlink_va AS pageId,
					log_link_visit_action.custom_float
					". $sqlCustomVariables . ",
					log_action_event_category.name AS eventCategory,
					log_action_event_action.name as eventAction
				FROM " . Common::prefixTable('log_link_visit_action') . " AS log_link_visit_action
					LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action
					ON  log_link_visit_action.idaction_url = log_action.idaction
					LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action_title
					ON  log_link_visit_action.idaction_name = log_action_title.idaction
					LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action_event_category
					ON  log_link_visit_action.idaction_event_category = log_action_event_category.idaction
					LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action_event_action
					ON  log_link_visit_action.idaction_event_action = log_action_event_action.idaction
				WHERE log_link_visit_action.idvisit = ?
				ORDER BY server_time ASC
				LIMIT 0, $actionsLimit
				 ";
        $actionDetails = Db::fetchAll($sql, array($idVisit));

        foreach ($actionDetails as $actionIdx => &$actionDetail) {
            $actionDetail =& $actionDetails[$actionIdx];
            $customVariablesPage = array();

            for ($i = 1; $i <= $maxCustomVariables; $i++) {
                if (!empty($actionDetail['custom_var_k' . $i])) {
                    $cvarKey = $actionDetail['custom_var_k' . $i];
                    $cvarKey = static::getCustomVariablePrettyKey($cvarKey);
                    $customVariablesPage[$i] = array(
                        'customVariablePageName' . $i  => $cvarKey,
                        'customVariablePageValue' . $i => $actionDetail['custom_var_v' . $i],
                    );
                }
                unset($actionDetail['custom_var_k' . $i]);
                unset($actionDetail['custom_var_v' . $i]);
            }
            if (!empty($customVariablesPage)) {
                $actionDetail['customVariables'] = $customVariablesPage;
            }

            if ($actionDetail['type'] == Action::TYPE_CONTENT) {

                unset($actionDetails[$actionIdx]);
                continue;

            } elseif ($actionDetail['type'] == Action::TYPE_EVENT_CATEGORY) {
                // Handle Event
                if (strlen($actionDetail['pageTitle']) > 0) {
                    $actionDetail['eventName'] = $actionDetail['pageTitle'];
                }

                unset($actionDetail['pageTitle']);

            } else if ($actionDetail['type'] == Action::TYPE_SITE_SEARCH) {
                // Handle Site Search
                $actionDetail['siteSearchKeyword'] = $actionDetail['pageTitle'];
                unset($actionDetail['pageTitle']);
            }

            // Event value / Generation time
            if ($actionDetail['type'] == Action::TYPE_EVENT_CATEGORY) {
                if (strlen($actionDetail['custom_float']) > 0) {
                    $actionDetail['eventValue'] = round($actionDetail['custom_float'], self::EVENT_VALUE_PRECISION);
                }
            } elseif ($actionDetail['custom_float'] > 0) {
                $actionDetail['generationTime'] = \Piwik\MetricsFormatter::getPrettyTimeFromSeconds($actionDetail['custom_float'] / 1000);
            }
            unset($actionDetail['custom_float']);

            if ($actionDetail['type'] != Action::TYPE_EVENT_CATEGORY) {
                unset($actionDetail['eventCategory']);
                unset($actionDetail['eventAction']);
            }

            // Reconstruct url from prefix
            $actionDetail['url'] = Tracker\PageUrl::reconstructNormalizedUrl($actionDetail['url'], $actionDetail['url_prefix']);
            unset($actionDetail['url_prefix']);

            // Set the time spent for this action (which is the timeSpentRef of the next action)
            if (isset($actionDetails[$actionIdx + 1])) {
                $actionDetail['timeSpent'] = $actionDetails[$actionIdx + 1]['timeSpentRef'];
                $actionDetail['timeSpentPretty'] = \Piwik\MetricsFormatter::getPrettyTimeFromSeconds($actionDetail['timeSpent']);
            }
            unset($actionDetails[$actionIdx]['timeSpentRef']); // not needed after timeSpent is added

        }

        // If the visitor converted a goal, we shall select all Goals
        $sql = "
				SELECT
						'goal' as type,
						goal.name as goalName,
						goal.idgoal as goalId,
						goal.revenue as revenue,
						log_conversion.idlink_va as goalPageId,
						log_conversion.server_time as serverTimePretty,
						log_conversion.url as url
				FROM " . Common::prefixTable('log_conversion') . " AS log_conversion
				LEFT JOIN " . Common::prefixTable('goal') . " AS goal
					ON (goal.idsite = log_conversion.idsite
						AND
						goal.idgoal = log_conversion.idgoal)
					AND goal.deleted = 0
				WHERE log_conversion.idvisit = ?
					AND log_conversion.idgoal > 0
                ORDER BY server_time ASC
				LIMIT 0, $actionsLimit
			";
        $goalDetails = Db::fetchAll($sql, array($idVisit));

        $sql = "SELECT
						case idgoal when " . GoalManager::IDGOAL_CART . " then '" . Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART . "' else '" . Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER . "' end as type,
						idorder as orderId,
						" . LogAggregator::getSqlRevenue('revenue') . " as revenue,
						" . LogAggregator::getSqlRevenue('revenue_subtotal') . " as revenueSubTotal,
						" . LogAggregator::getSqlRevenue('revenue_tax') . " as revenueTax,
						" . LogAggregator::getSqlRevenue('revenue_shipping') . " as revenueShipping,
						" . LogAggregator::getSqlRevenue('revenue_discount') . " as revenueDiscount,
						items as items,

						log_conversion.server_time as serverTimePretty
					FROM " . Common::prefixTable('log_conversion') . " AS log_conversion
					WHERE idvisit = ?
						AND idgoal <= " . GoalManager::IDGOAL_ORDER . "
					ORDER BY server_time ASC
					LIMIT 0, $actionsLimit";
        $ecommerceDetails = Db::fetchAll($sql, array($idVisit));

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
            $sql = "SELECT
							log_action_sku.name as itemSKU,
							log_action_name.name as itemName,
							log_action_category.name as itemCategory,
							" . LogAggregator::getSqlRevenue('price') . " as price,
							quantity as quantity
						FROM " . Common::prefixTable('log_conversion_item') . "
							INNER JOIN " . Common::prefixTable('log_action') . " AS log_action_sku
							ON  idaction_sku = log_action_sku.idaction
							LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action_name
							ON  idaction_name = log_action_name.idaction
							LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action_category
							ON idaction_category = log_action_category.idaction
						WHERE idvisit = ?
							AND idorder = ?
							AND deleted = 0
						LIMIT 0, $actionsLimit
				";
            $bind = array($idVisit, isset($ecommerceConversion['orderId'])
                ? $ecommerceConversion['orderId']
                : GoalManager::ITEM_IDORDER_ABANDONED_CART
            );

            $itemsDetails = Db::fetchAll($sql, $bind);
            foreach ($itemsDetails as &$detail) {
                if ($detail['price'] == round($detail['price'])) {
                    $detail['price'] = round($detail['price']);
                }
            }
            $ecommerceConversion['itemDetails'] = $itemsDetails;
        }

        $actions = array_merge($actionDetails, $goalDetails, $ecommerceDetails);

        usort($actions, array('static', 'sortByServerTime'));

        $visitorDetailsArray['actionDetails'] = $actions;
        foreach ($visitorDetailsArray['actionDetails'] as &$details) {
            switch ($details['type']) {
                case 'goal':
                    $details['icon'] = 'plugins/Morpheus/images/goal.png';
                    break;
                case Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER:
                case Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART:
                    $details['icon'] = 'plugins/Morpheus/images/' . $details['type'] . '.gif';
                    break;
                case Action::TYPE_DOWNLOAD:
                    $details['type'] = 'download';
                    $details['icon'] = 'plugins/Morpheus/images/download.png';
                    break;
                case Action::TYPE_OUTLINK:
                    $details['type'] = 'outlink';
                    $details['icon'] = 'plugins/Morpheus/images/link.gif';
                    break;
                case Action::TYPE_SITE_SEARCH:
                    $details['type'] = 'search';
                    $details['icon'] = 'plugins/Morpheus/images/search_ico.png';
                    break;
                case Action::TYPE_EVENT_CATEGORY:
                    $details['type'] = 'event';
                    $details['icon'] = 'plugins/Morpheus/images/event.png';
                    break;
                default:
                    $details['type'] = 'action';
                    $details['icon'] = null;
                    break;
            }
            // Convert datetimes to the site timezone
            $dateTimeVisit = Date::factory($details['serverTimePretty'], $timezone);
            $details['serverTimePretty'] = $dateTimeVisit->getLocalized(Piwik::translate('CoreHome_ShortDateFormat') . ' %time%');
        }
        $visitorDetailsArray['goalConversions'] = count($goalDetails);
        return $visitorDetailsArray;
    }

    private static function getCustomVariablePrettyKey($key)
    {
        $rename = array(
            ActionSiteSearch::CVAR_KEY_SEARCH_CATEGORY => Piwik::translate('Actions_ColumnSearchCategory'),
            ActionSiteSearch::CVAR_KEY_SEARCH_COUNT    => Piwik::translate('Actions_ColumnSearchResultsCount'),
        );
        if (isset($rename[$key])) {
            return $rename[$key];
        }
        return $key;
    }

    private static function sortByServerTime($a, $b)
    {
        $ta = strtotime($a['serverTimePretty']);
        $tb = strtotime($b['serverTimePretty']);
        return $ta < $tb
            ? -1
            : ($ta == $tb
                ? 0
                : 1);
    }
}
