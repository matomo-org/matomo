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

class TableActionIds
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
     * @param array $actionNamesAndTypes Array of one or many (name,type)
     * @return array Returns the input array, with the idaction appended ie. Array of one or many (name,type,idaction)
     */
    public static function loadActionId($actionNamesAndTypes)
    {
        // First, we try and select the actions that are already recorded
        $sql = TableActionIds::getSqlSelectActionId();
        $bind = array();
        $i = 0;
        foreach ($actionNamesAndTypes as &$actionNameType) {
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
            return $actionNamesAndTypes;
        }
        $actionIds = Tracker::getDatabase()->fetchAll($sql, $bind);

        // For the Actions found in the lookup table, add the idaction in the array,
        // If not found in lookup table, queue for INSERT
        $actionsToInsert = array();
        foreach ($actionNamesAndTypes as $index => &$actionNameType) {
            list($name, $type, $urlPrefix) = $actionNameType;
            if (empty($name)) {
                continue;
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

        $sql = "INSERT INTO " . Common::prefixTable('log_action') .
            "( name, hash, type, url_prefix ) VALUES (?,CRC32(?),?,?)";
        // Then, we insert all new actions in the lookup table
        foreach ($actionsToInsert as $actionToInsert) {
            list($name, $type, $urlPrefix) = $actionNamesAndTypes[$actionToInsert];

            Tracker::getDatabase()->query($sql, array($name, $name, $type, $urlPrefix));
            $actionId = Tracker::getDatabase()->lastInsertId();
            Common::printDebug("Recorded a new action (" . Action::getTypeAsString($type) . ") in the lookup table: " . $name . " (idaction = " . $actionId . ")");

            $keyIdAction = 3;
            $actionNamesAndTypes[$actionToInsert][$keyIdAction] = $actionId;
        }
        return $actionNamesAndTypes;
    }
}