<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker;

use Piwik\Common;
use Piwik\SegmentExpression;
use Piwik\Tracker;

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
     * This function will find the idaction from the lookup table piwik_log_action,
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
        foreach($actionsNameAndType as &$action) {
            if(count($action) == 2) {
                $action[] = null;
            }
        }
        $actionIds = self::queryIdsAction($actionsNameAndType);

        list($queriedIds, $fieldNamesToInsert) = self::processIdsToInsert($actionsNameAndType, $actionIds);

        $insertedIds = self::insertNewIdsAction($actionsNameAndType, $fieldNamesToInsert);

        $queriedIds = $queriedIds + $insertedIds;

        return $queriedIds;
    }

    /**
     * @param $name
     * @param $type
     * @return string
     */
    private static function getIdActionMatchingNameAndType($name, $type)
    {
        $sql = TableLogAction::getSqlSelectActionId();
        $bind = array($name, $name, $type);
        $idAction = \Piwik\Db::fetchOne($sql, $bind);
        return $idAction;
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
            case '=@':
                // use concat to make sure, no %s occurs because some plugins use %s in their sql
                $where = '( name LIKE CONCAT(\'%\', ?, \'%\') ';
                break;
            case '!@':
                $where = '( name NOT LIKE CONCAT(\'%\', ?, \'%\') ';
                break;
            default:
                throw new \Exception("This match type $matchType is not available for action-segments.");
                break;
        }
        $sql = sprintf($sql, $where);
        return $sql;
    }

    private static function getSqlSelectActionId()
    {
        $sql = "SELECT idaction, type, name
                        FROM " . Common::prefixTable('log_action')
            . "  WHERE "
            . "		( hash = CRC32(?) AND name = ? AND type = ? ) ";
        return $sql;
    }

    private static function insertNewIdsAction($actionsNameAndType, $fieldNamesToInsert)
    {
        $sql = "INSERT INTO " . Common::prefixTable('log_action') .
            "( name, hash, type, url_prefix ) VALUES (?,CRC32(?),?,?)";
        // Then, we insert all new actions in the lookup table
        $inserted = array();
        foreach ($fieldNamesToInsert as $fieldName) {
            list($name, $type, $urlPrefix) = $actionsNameAndType[$fieldName];

            Tracker::getDatabase()->query($sql, array($name, $name, $type, $urlPrefix));
            $actionId = Tracker::getDatabase()->lastInsertId();

            $inserted[$fieldName] = $actionId;

            Common::printDebug("Recorded a new action (" . Action::getTypeAsString($type) . ") in the lookup table: " . $name . " (idaction = " . $actionId . ")");
        }
        return $inserted;
    }

    private static function queryIdsAction($actionsNameAndType)
    {
        $sql = TableLogAction::getSqlSelectActionId();
        $bind = array();
        $i = 0;
        foreach ($actionsNameAndType as &$actionNameType) {
            list($name, $type, $urlPrefix) = $actionNameType;
            if (empty($name)) {
                continue;
            }
            if ($i > 0) {
                $sql .= " OR ( hash = CRC32(?) AND name = ? AND type = ? ) ";
            }
            $bind[] = $name;
            $bind[] = $name;
            $bind[] = $type;
            $i++;
        }
        // Case URL & Title are empty
        if (empty($bind)) {
            return false;
        }
        $actionIds = Tracker::getDatabase()->fetchAll($sql, $bind);
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
                if ($name == $row['name']
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
        $actionType = self::guessActionTypeFromSegment($segmentName);

        if ($actionType == Action::TYPE_PAGE_URL) {
            // for urls trim protocol and www because it is not recorded in the db
            $valueToMatch = preg_replace('@^http[s]?://(www\.)?@i', '', $valueToMatch);
        }
        $valueToMatch = Common::sanitizeInputValue(Common::unsanitizeInputValue($valueToMatch));

        if ($matchType == SegmentExpression::MATCH_EQUAL
            || $matchType == SegmentExpression::MATCH_NOT_EQUAL
        ) {
            $idAction = self::getIdActionMatchingNameAndType($valueToMatch, $actionType);
            // if the action is not found, we hack -100 to ensure it tries to match against an integer
            // otherwise binding idaction_name to "false" returns some rows for some reasons (in case &segment=pageTitle==Větrnásssssss)
            if (empty($idAction)) {
                $idAction = -100;
            }
            return $idAction;
        }

        // "name contains $string" match can match several idaction so we cannot return yet an idaction
        // special case
        $sql = TableLogAction::getSelectQueryWhereNameContains($matchType, $actionType);
        return array(
            // mark that the returned value is an sql-expression instead of a literal value
            'SQL'  => $sql,
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
            'eventAction' => Action::TYPE_EVENT_ACTION,
            'eventCategory' => Action::TYPE_EVENT_CATEGORY,
            'eventName' => Action::TYPE_EVENT_NAME,
            'contentPiece' => Action::TYPE_CONTENT_PIECE,
            'contentTarget' => Action::TYPE_CONTENT_TARGET,
            'contentName' => Action::TYPE_CONTENT_NAME,
            'contentInteraction' => Action::TYPE_CONTENT_INTERACTION,
        );
        if(!empty($exactMatch[$segmentName])) {
            return $exactMatch[$segmentName];
        }

        if (stripos($segmentName, 'pageurl') !== false) {
            $actionType = Action::TYPE_PAGE_URL;
            return $actionType;
        } elseif (stripos($segmentName, 'pagetitle') !== false) {
            $actionType = Action::TYPE_PAGE_TITLE;
            return $actionType;
        } elseif (stripos($segmentName, 'sitesearch') !== false) {
            $actionType = Action::TYPE_SITE_SEARCH;
            return $actionType;
        } else {
            throw new \Exception("We cannot guess the action type from the segment $segmentName.");
        }
    }

}

