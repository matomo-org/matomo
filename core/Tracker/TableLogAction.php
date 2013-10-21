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

use Piwik\Common;
use Piwik\Tracker;


/**
 * This class is used to query Action IDs from the log_action table.
 *
 * A pageview, outlink, download or site search are made of several "Action IDs"
 * For example pageview is idaction_url and idaction_name.
 *
 * @package Piwik\Tracker
 */
class TableLogAction
{
    public static function getSqlSelectActionId()
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
     * @param array $actionsNameAndType Array of one or many (name,type)
     * @return array Returns the an array (Field name => idaction)
     */
    public static function loadIdsAction($actionsNameAndType)
    {
        $actionIds = self::queryIdsAction($actionsNameAndType);

        list($queriedIds, $fieldNamesToInsert) = self::processIdsToInsert($actionsNameAndType, $actionIds);

        $insertedIds = self::insertNewIdsAction($actionsNameAndType, $fieldNamesToInsert);

        $queriedIds = $queriedIds + $insertedIds;

        return $queriedIds;
    }

    protected static function insertNewIdsAction($actionsNameAndType, $fieldNamesToInsert)
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

    protected static function queryIdsAction($actionsNameAndType)
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

    protected static function processIdsToInsert($actionsNameAndType, $actionIds)
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
}

