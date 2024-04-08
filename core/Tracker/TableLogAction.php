<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker;

use Piwik\Common;
use Piwik\Segment\SegmentExpression;

/**
 * This class is used to query Action IDs from the log_action table.
 *
 * A pageview, outlink, download or site search are made of several "Action IDs"
 * For example pageview is idaction_url and idaction_name.
 *
 */
class TableLogAction
{
    /**
     * This function will find the idaction from the lookup table log_action,
     * given an Action name, type, and an optional URL Prefix.
     *
     * This is used to record Page URLs, Page Titles, Ecommerce items SKUs, item names, item categories
     *
     * If the action name does not exist in the lookup table, it will INSERT it
     * @param array $actionsNameAndType Array of one or many (name,type)
     * @return array Returns the an array (Field name => idaction)
     */
    public static function loadIdsAction($actionsNameAndType)
    {
        // Add url prefix if not set
        foreach ($actionsNameAndType as &$action) {
            if (2 == count($action)) {
                $action[] = null;
            }
        }

        $actionIds = self::queryIdsAction($actionsNameAndType);

        [$queriedIds, $fieldNamesToInsert] = self::processIdsToInsert($actionsNameAndType, $actionIds);

        $insertedIds = self::insertNewIdsAction($actionsNameAndType, $fieldNamesToInsert);
        $queriedIds  = $queriedIds + $insertedIds;

        return $queriedIds;
    }

    /**
     * @param $matchType
     * @param $actionType
     * @return string
     * @throws \Exception
     */
    private static function getSelectQueryWhereNameContains($matchType, $actionType)
    {
        // now, we handle the cases =@ (contains) and !@ (does not contain)
        // build the expression based on the match type
        $sql = 'SELECT idaction FROM ' . Common::prefixTable('log_action') . ' WHERE %s AND type = ' . $actionType . ' )';

        switch ($matchType) {
            case SegmentExpression::MATCH_CONTAINS:
                // use concat to make sure, no %s occurs because some plugins use %s in their sql
                $where = '( name LIKE CONCAT(\'%\', ?, \'%\') ';
                break;
            case SegmentExpression::MATCH_DOES_NOT_CONTAIN:
                $where = '( name NOT LIKE CONCAT(\'%\', ?, \'%\') ';
                break;
            case SegmentExpression::MATCH_STARTS_WITH:
                // use concat to make sure, no %s occurs because some plugins use %s in their sql
                $where = '( name LIKE CONCAT(?, \'%\') ';
                break;
            case SegmentExpression::MATCH_ENDS_WITH:
                // use concat to make sure, no %s occurs because some plugins use %s in their sql
                $where = '( name LIKE CONCAT(\'%\', ?) ';
                break;
            default:
                throw new \Exception("This match type $matchType is not available for action-segments.");
                break;
        }

        $sql = sprintf($sql, $where);

        return $sql;
    }

    private static function insertNewIdsAction($actionsNameAndType, $fieldNamesToInsert)
    {
        // Then, we insert all new actions in the lookup table
        $inserted = array();

        foreach ($fieldNamesToInsert as $fieldName) {
            [$name, $type, $urlPrefix] = $actionsNameAndType[$fieldName];

            $actionId = self::getModel()->createNewIdAction($name, $type, $urlPrefix);

            Common::printDebug("Recorded a new action (" . Action::getTypeAsString($type) . ") in the lookup table: " . $name . " (idaction = " . $actionId . ")");

            $inserted[$fieldName] = $actionId;
        }

        return $inserted;
    }

    private static function getModel()
    {
        return new Model();
    }

    private static function queryIdsAction($actionsNameAndType)
    {
        $toQuery = array();
        foreach ($actionsNameAndType as &$actionNameType) {
            [$name, $type, $urlPrefix] = $actionNameType;
            $toQuery[] = array('name' => $name, 'type' => $type);
        }

        $actionIds = self::getModel()->getIdsAction($toQuery);

        return $actionIds;
    }

    private static function processIdsToInsert($actionsNameAndType, $actionIds)
    {
        // For the Actions found in the lookup table, add the idaction in the array,
        // If not found in lookup table, queue for INSERT
        $fieldNamesToInsert = $fieldNameToActionId = array();

        foreach ($actionsNameAndType as $fieldName => &$actionNameType) {
            @list($name, $type, $urlPrefix) = $actionNameType;
            if (empty($name)) {
                $fieldNameToActionId[$fieldName] = false;
                continue;
            }

            $found = false;
            foreach ($actionIds as $row) {
                if (
                    $name == $row['name']
                    && $type == $row['type']
                ) {
                    $found = true;

                    $fieldNameToActionId[$fieldName] = $row['idaction'];
                    continue;
                }
            }
            if (!$found) {
                $fieldNamesToInsert[] = $fieldName;
            }
        }

        return array($fieldNameToActionId, $fieldNamesToInsert);
    }

    /**
     * Convert segment expression to an action ID or an SQL expression.
     *
     * This method is used as a sqlFilter-callback for the segments of this plugin.
     * Usually, these callbacks only return a value that should be compared to the
     * column in the database. In this case, that doesn't work since multiple IDs
     * can match an expression (e.g. "pageUrl=@foo").
     * @param string $valueToMatch
     * @param string $sqlField
     * @param string $matchType
     * @param string $segmentName
     * @throws \Exception
     * @return array|int|string
     */
    public static function getIdActionFromSegment($valueToMatch, $sqlField, $matchType, $segmentName)
    {
        if ($segmentName === 'actionType') {
            $actionType   = (int) $valueToMatch;
            $valueToMatch = array();
            $sql = 'SELECT idaction FROM ' . Common::prefixTable('log_action') . ' WHERE type = ' . $actionType . ' )';
        } else {
            $actionType = self::guessActionTypeFromSegment($segmentName);
            $valueToMatch = self::removeProtocolIfSegmentStoredWithoutIt($valueToMatch, $actionType, $segmentName);

            $unsanitizedValue = $valueToMatch;
            $valueToMatch = self::normaliseActionString($actionType, $valueToMatch);
            if (
                $matchType == SegmentExpression::MATCH_EQUAL
                || $matchType == SegmentExpression::MATCH_NOT_EQUAL
            ) {
                $idAction = self::getModel()->getIdActionMatchingNameAndType($valueToMatch, $actionType);
                // If action can't be found normalized try search for it with original value
                // This can eg happen for outlinks that contain a &amp; see https://github.com/matomo-org/matomo/issues/11806
                if (empty($idAction)) {
                    $idAction = self::getModel()->getIdActionMatchingNameAndType($unsanitizedValue, $actionType);
                    // Action is not found (eg. &segment=pageTitle==Větrnásssssss)
                    if (empty($idAction)) {
                        $idAction = null;
                    }
                }
                return $idAction;
            }

            // "name contains $string" match can match several idaction so we cannot return yet an idaction
            // special case
            $sql = self::getSelectQueryWhereNameContains($matchType, $actionType);
        }

        return array(
            // mark that the returned value is an sql-expression instead of a literal value
            'SQL' => $sql,
            'bind' => $valueToMatch,
        );
    }

    /**
     * @param $segmentName
     * @return int
     * @throws \Exception
     */
    private static function guessActionTypeFromSegment($segmentName)
    {
        $exactMatch = array(
            'outlinkUrl'         => Action::TYPE_OUTLINK,
            'downloadUrl'        => Action::TYPE_DOWNLOAD,
            'eventUrl'           => Action::TYPE_EVENT,
            'eventAction'        => Action::TYPE_EVENT_ACTION,
            'eventCategory'      => Action::TYPE_EVENT_CATEGORY,
            'eventName'          => Action::TYPE_EVENT_NAME,
            'contentPiece'       => Action::TYPE_CONTENT_PIECE,
            'contentTarget'      => Action::TYPE_CONTENT_TARGET,
            'contentName'        => Action::TYPE_CONTENT_NAME,
            'contentInteraction' => Action::TYPE_CONTENT_INTERACTION,
            'productName'        => Action::TYPE_ECOMMERCE_ITEM_NAME,
            'productSku'         => Action::TYPE_ECOMMERCE_ITEM_SKU,
            'productViewName'    => Action::TYPE_ECOMMERCE_ITEM_NAME,
            'productViewSku'     => Action::TYPE_ECOMMERCE_ITEM_SKU
        );

        if (!empty($exactMatch[$segmentName])) {
            return $exactMatch[$segmentName];
        }

        if (stripos($segmentName, 'pageurl') !== false) {
            return Action::TYPE_PAGE_URL;
        } elseif (stripos($segmentName, 'pagetitle') !== false) {
            return Action::TYPE_PAGE_TITLE;
        } elseif (stripos($segmentName, 'sitesearch') !== false) {
            return Action::TYPE_SITE_SEARCH;
        } elseif (
            stripos($segmentName, 'productcategory') !== false
            || stripos($segmentName, 'productviewcategory') !== false) {
            return Action::TYPE_ECOMMERCE_ITEM_CATEGORY;
        } else {
            throw new \Exception("We cannot guess the action type from the segment $segmentName.");
        }
    }

    /**
     * This function will sanitize or not if it's needed for the specified action type
     *
     * URLs (Download URL, Outlink URL) are stored raw (unsanitized)
     * while other action types are stored Sanitized
     *
     * @param $actionType
     * @param $actionString
     * @return string
     */
    private static function normaliseActionString($actionType, $actionString)
    {
        $actionString = Common::unsanitizeInputValue($actionString);

        if (self::isActionTypeStoredUnsanitized($actionType)) {
            return $actionString;
        }

        return Common::sanitizeInputValue($actionString);
    }

    /**
     * @param $actionType
     * @return bool
     */
    private static function isActionTypeStoredUnsanitized($actionType)
    {
        $actionsTypesStoredUnsanitized = array(
            Action::TYPE_DOWNLOAD,
            Action::TYPE_OUTLINK,
            Action::TYPE_PAGE_URL,
            Action::TYPE_CONTENT,
        );

        return in_array($actionType, $actionsTypesStoredUnsanitized);
    }

    public static function removeProtocolIfSegmentStoredWithoutIt($url, $actionType, $segmentName)
    {
        if ($actionType == Action::TYPE_PAGE_URL || $segmentName == 'eventUrl') {
            // for urls trim protocol and www because it is not recorded in the db
            $url = preg_replace('@^http[s]?://(www\.)?@i', '', $url);
        }
        return $url;
    }

    /**
     * Returns an idaction value to match an idaction column by searching log_action, if $matchType is
     * SegmentExpression::MATCH_EQUAL or SegmentExpression::MATCH_NOT_EQUAL. This method is used
     * to optimize segment conditions involving idaction queries, avoiding a join by querying the log_action
     * table beforehand.
     *
     * Should be used as the $sqlFilter property for idaction dimensions that use `ActionNameJoin`.
     *
     * @param string $value the value in the segment condition
     * @param string $sqlField the table column of the segment condition
     * @param string $matchType the SegmentExpression match type, eg, `SegmentExpression::MATCH_NOT_EQUAL`
     * @param string $segmentName the name of the segment, ie, `pageUrl`
     * @return array|null|string
     */
    public static function getOptimizedIdActionSqlMatch($value, $sqlField, $matchType, $segmentName)
    {
        if ($matchType == SegmentExpression::MATCH_EQUAL || $matchType == SegmentExpression::MATCH_NOT_EQUAL) {
            $result = self::getIdActionFromSegment($value, $sqlField, $matchType, $segmentName);

            if (is_numeric($result)) {
                return ['value' => $result, 'joinTable' => false];
            }

            return $result;
        }

        $actionType = self::guessActionTypeFromSegment($segmentName);
        $value = self::removeProtocolIfSegmentStoredWithoutIt($value, $actionType, $segmentName);

        return $value;
    }
}
