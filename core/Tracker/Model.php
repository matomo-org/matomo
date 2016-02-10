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
use Piwik\Tracker;

class Model
{

    public function createAction($visitAction)
    {
        $fields = implode(", ", array_keys($visitAction));
        $values = Common::getSqlStringFieldsArray($visitAction);
        $table  = Common::prefixTable('log_link_visit_action');

        $sql  = "INSERT INTO $table ($fields) VALUES ($values)";
        $bind = array_values($visitAction);

        $db = $this->getDb();
        $db->query($sql, $bind);

        $id = $db->lastInsertId();

        return $id;
    }

    public function createConversion($conversion)
    {
        $fields     = implode(", ", array_keys($conversion));
        $bindFields = Common::getSqlStringFieldsArray($conversion);
        $table      = Common::prefixTable('log_conversion');

        $sql    = "INSERT IGNORE INTO $table ($fields) VALUES ($bindFields) ";
        $bind   = array_values($conversion);

        $db     = $this->getDb();
        $result = $db->query($sql, $bind);

        // If a record was inserted, we return true
        return $db->rowCount($result) > 0;
    }

    public function updateConversion($idVisit, $idGoal, $newConversion)
    {
        $updateWhere = array(
            'idvisit' => $idVisit,
            'idgoal'  => $idGoal,
            'buster'  => 0,
        );

        $updateParts = $sqlBind = $updateWhereParts = array();

        foreach ($newConversion as $name => $value) {
            $updateParts[] = $name . " = ?";
            $sqlBind[]     = $value;
        }

        foreach ($updateWhere as $name => $value) {
            $updateWhereParts[] = $name . " = ?";
            $sqlBind[]          = $value;
        }

        $parts = implode($updateParts, ', ');
        $table = Common::prefixTable('log_conversion');

        $sql   = "UPDATE $table SET $parts WHERE " . implode($updateWhereParts, ' AND ');

        try {
            $this->getDb()->query($sql, $sqlBind);
        } catch (Exception $e) {
            Common::printDebug("There was an error while updating the Conversion: " . $e->getMessage());

            return false;
        }

        return true;
    }


    /**
     * Loads the Ecommerce items from the request and records them in the DB
     *
     * @param array $goal
     * @param int   $defaultIdOrder
     * @throws Exception
     * @return array
     */
    public function getAllItemsCurrentlyInTheCart($goal, $defaultIdOrder)
    {
        $sql = "SELECT idaction_sku, idaction_name, idaction_category, idaction_category2, idaction_category3, idaction_category4, idaction_category5, price, quantity, deleted, idorder as idorder_original_value
				FROM " . Common::prefixTable('log_conversion_item') . "
				WHERE idvisit = ? AND (idorder = ? OR idorder = ?)";

        $bind = array(
            $goal['idvisit'],
            isset($goal['idorder']) ? $goal['idorder'] : $defaultIdOrder,
            $defaultIdOrder
        );

        $itemsInDb = $this->getDb()->fetchAll($sql, $bind);

        Common::printDebug("Items found in current cart, for conversion_item (visit,idorder)=" . var_export($bind, true));
        Common::printDebug($itemsInDb);

        return $itemsInDb;
    }

    public function createEcommerceItems($ecommerceItems)
    {
        $sql = "INSERT INTO " . Common::prefixTable('log_conversion_item');
        $i    = 0;
        $bind = array();

        foreach ($ecommerceItems as $item) {
            if ($i === 0) {
                $fields = implode(', ', array_keys($item));
                $sql   .= ' (' . $fields . ') VALUES ';
            } elseif ($i > 0) {
                $sql   .= ',';
            }

            $newRow = array_values($item);
            $sql   .= " ( " . Common::getSqlStringFieldsArray($newRow) . " ) ";
            $bind   = array_merge($bind, $newRow);
            $i++;
        }

        Common::printDebug($sql);
        Common::printDebug($bind);

        try {
            $this->getDb()->query($sql, $bind);
        } catch (Exception $e) {
            if ($e->getCode() == 23000 ||
                false !== strpos($e->getMessage(), 'Duplicate entry') ||
                false !== strpos($e->getMessage(), 'Integrity constraint violation')) {
                Common::printDebug('Did not create ecommerce item as item was already created');
            } else {
                throw $e;
            }
        }
    }

    /**
     * Inserts a new action into the log_action table. If there is an existing action that was inserted
     * due to another request pre-empting this one, the newly inserted action is deleted.
     *
     * @param string $name
     * @param int $type
     * @param int $urlPrefix
     * @return int The ID of the action (can be for an existing action or new action).
     */
    public function createNewIdAction($name, $type, $urlPrefix)
    {
        $newActionId = $this->insertNewAction($name, $type, $urlPrefix);

        $realFirstActionId = $this->getIdActionMatchingNameAndType($name, $type);

        // if the inserted action ID is not the same as the queried action ID, then that means we inserted
        // a duplicate, so remove it now
        if ($realFirstActionId != $newActionId) {
            $this->deleteDuplicateAction($newActionId);
        }

        return $realFirstActionId;
    }

    private function insertNewAction($name, $type, $urlPrefix)
    {
        $table = Common::prefixTable('log_action');
        $sql   = "INSERT INTO $table (name, hash, type, url_prefix) VALUES (?,CRC32(?),?,?)";

        $db = $this->getDb();
        $db->query($sql, array($name, $name, $type, $urlPrefix));

        $actionId = $db->lastInsertId();

        return $actionId;
    }

    private function getSqlSelectActionId()
    {
        // it is possible for multiple actions to exist in the DB (due to rare concurrency issues), so the ORDER BY and
        // LIMIT are important
        $sql = "SELECT idaction, type, name FROM " . Common::prefixTable('log_action')
            . "  WHERE " . $this->getSqlConditionToMatchSingleAction() . " "
            . "ORDER BY idaction ASC LIMIT 1";

        return $sql;
    }

    public function getIdActionMatchingNameAndType($name, $type)
    {
        $sql  = $this->getSqlSelectActionId();
        $bind = array($name, $name, $type);

        $idAction = $this->getDb()->fetchOne($sql, $bind);

        return $idAction;
    }

    /**
     * Returns the IDs for multiple actions based on name + type values.
     *
     * @param array $actionsNameAndType Array like `array( array('name' => '...', 'type' => 1), ... )`
     * @return array|false Array of DB rows w/ columns: **idaction**, **type**, **name**.
     */
    public function getIdsAction($actionsNameAndType)
    {
        $sql = "SELECT MIN(idaction) as idaction, type, name FROM " . Common::prefixTable('log_action')
             . " WHERE";
        $bind = array();

        $i = 0;
        foreach ($actionsNameAndType as $actionNameType) {
            $name = $actionNameType['name'];

            if (empty($name)) {
                continue;
            }

            if ($i > 0) {
                $sql .= " OR";
            }

            $sql .= " " . $this->getSqlConditionToMatchSingleAction() . " ";

            $bind[] = $name;
            $bind[] = $name;
            $bind[] = $actionNameType['type'];
            $i++;
        }

        $sql .= " GROUP BY type, hash, name";

        // Case URL & Title are empty
        if (empty($bind)) {
            return false;
        }

        $actionIds = $this->getDb()->fetchAll($sql, $bind);

        return $actionIds;
    }

    public function updateEcommerceItem($originalIdOrder, $newItem)
    {
        $updateParts = $sqlBind = array();
        foreach ($newItem as $name => $value) {
            $updateParts[] = $name . " = ?";
            $sqlBind[]     = $value;
        }

        $parts = implode($updateParts, ', ');
        $table = Common::prefixTable('log_conversion_item');

        $sql = "UPDATE $table SET $parts WHERE idvisit = ? AND idorder = ? AND idaction_sku = ?";

        $sqlBind[] = $newItem['idvisit'];
        $sqlBind[] = $originalIdOrder;
        $sqlBind[] = $newItem['idaction_sku'];

        $this->getDb()->query($sql, $sqlBind);
    }

    public function createVisit($visit)
    {
        $fields = array_keys($visit);
        $fields = implode(", ", $fields);
        $values = Common::getSqlStringFieldsArray($visit);
        $table  = Common::prefixTable('log_visit');

        $sql  = "INSERT INTO $table ($fields) VALUES ($values)";
        $bind = array_values($visit);

        $db = $this->getDb();
        $db->query($sql, $bind);

        return $db->lastInsertId();
    }

    public function updateVisit($idSite, $idVisit, $valuesToUpdate)
    {
        list($updateParts, $sqlBind) = $this->fieldsToQuery($valuesToUpdate);

        $parts = implode($updateParts, ', ');
        $table = Common::prefixTable('log_visit');

        $sqlQuery = "UPDATE $table SET $parts WHERE idsite = ? AND idvisit = ?";

        $sqlBind[] = $idSite;
        $sqlBind[] = $idVisit;

        $db          = $this->getDb();
        $result      = $db->query($sqlQuery, $sqlBind);
        $wasInserted = $db->rowCount($result) != 0;

        if (!$wasInserted) {
            Common::printDebug("Visitor with this idvisit wasn't found in the DB.");
            Common::printDebug("$sqlQuery --- ");
            Common::printDebug($sqlBind);
        }

        return $wasInserted;
    }

    public function updateAction($idLinkVa, $valuesToUpdate)
    {
        if (empty($idLinkVa)) {
            return;
        }

        list($updateParts, $sqlBind) = $this->fieldsToQuery($valuesToUpdate);

        $parts = implode($updateParts, ', ');
        $table = Common::prefixTable('log_link_visit_action');

        $sqlQuery = "UPDATE $table SET $parts WHERE idlink_va = ?";

        $sqlBind[] = $idLinkVa;

        $db          = $this->getDb();
        $result      = $db->query($sqlQuery, $sqlBind);
        $wasInserted = $db->rowCount($result) != 0;

        if (!$wasInserted) {
            Common::printDebug("Action with this idLinkVa wasn't found in the DB.");
            Common::printDebug("$sqlQuery --- ");
            Common::printDebug($sqlBind);
        }

        return $wasInserted;
    }

    public function findVisitor($idSite, $configId, $idVisitor, $fieldsToRead, $shouldMatchOneFieldOnly, $isVisitorIdToLookup, $timeLookBack, $timeLookAhead)
    {
        $selectCustomVariables = '';

        $selectFields = implode(', ', $fieldsToRead);

        $select = "SELECT $selectFields $selectCustomVariables ";
        $from   = "FROM " . Common::prefixTable('log_visit');

        // Two use cases:
        // 1) there is no visitor ID so we try to match only on config_id (heuristics)
        // 		Possible causes of no visitor ID: no browser cookie support, direct Tracking API request without visitor ID passed,
        //        importing server access logs with import_logs.py, etc.
        // 		In this case we use config_id heuristics to try find the visitor in tahhhe past. There is a risk to assign
        // 		this page view to the wrong visitor, but this is better than creating artificial visits.
        // 2) there is a visitor ID and we trust it (config setting trust_visitors_cookies, OR it was set using &cid= in tracking API),
        //      and in these cases, we force to look up this visitor id
        $whereCommon = "visit_last_action_time >= ? AND visit_last_action_time <= ? AND idsite = ?";
        $bindSql = array(
            $timeLookBack,
            $timeLookAhead,
            $idSite
        );

        if ($shouldMatchOneFieldOnly && $isVisitorIdToLookup) {
            $visitRow = $this->findVisitorByVisitorId($idVisitor, $select, $from, $whereCommon, $bindSql);
        } elseif ($shouldMatchOneFieldOnly) {
            $visitRow = $this->findVisitorByConfigId($configId, $select, $from, $whereCommon, $bindSql);
        } else {
            $visitRow = $this->findVisitorByVisitorId($idVisitor, $select, $from, $whereCommon, $bindSql);

            if (empty($visitRow)) {
                $whereCommon .= ' AND user_id IS NULL ';
                $visitRow = $this->findVisitorByConfigId($configId, $select, $from, $whereCommon, $bindSql);
            }
        }

        return $visitRow;
    }

    private function findVisitorByVisitorId($idVisitor, $select, $from, $where, $bindSql)
    {
        // will use INDEX index_idsite_idvisitor (idsite, idvisitor)
        $where .= ' AND idvisitor = ?';
        $bindSql[] = $idVisitor;

        return $this->fetchVisitor($select, $from, $where, $bindSql);
    }

    private function findVisitorByConfigId($configId, $select, $from, $where, $bindSql)
    {
        // will use INDEX index_idsite_config_datetime (idsite, config_id, visit_last_action_time)
        $where .= ' AND config_id = ?';
        $bindSql[] = $configId;

        return $this->fetchVisitor($select, $from, $where, $bindSql);
    }

    private function fetchVisitor($select, $from, $where, $bindSql)
    {
        $sql = "$select $from WHERE " . $where . "
                ORDER BY visit_last_action_time DESC
                LIMIT 1";

        $visitRow = $this->getDb()->fetch($sql, $bindSql);

        return $visitRow;
    }

    /**
     * Returns true if the site doesn't have log data.
     *
     * @param int $siteId
     * @return bool
     */
    public function isSiteEmpty($siteId)
    {
        $sql = sprintf('SELECT idsite FROM %s WHERE idsite = ? limit 1', Common::prefixTable('log_visit'));

        $result = \Piwik\Db::fetchOne($sql, array($siteId));

        return $result == null;
    }

    private function fieldsToQuery($valuesToUpdate)
    {
        $updateParts = array();
        $sqlBind     = array();

        foreach ($valuesToUpdate as $name => $value) {
            // Case where bind parameters don't work
            if ($value === $name . ' + 1') {
                //$name = 'visit_total_events'
                //$value = 'visit_total_events + 1';
                $updateParts[] = " $name = $value ";
            } else {
                $updateParts[] = $name . " = ?";
                $sqlBind[]     = $value;
            }
        }

        return array($updateParts, $sqlBind);
    }

    private function deleteDuplicateAction($newActionId)
    {
        $sql = "DELETE FROM " . Common::prefixTable('log_action') . " WHERE idaction = ?";

        $db = $this->getDb();
        $db->query($sql, array($newActionId));
    }

    private function getDb()
    {
        return Tracker::getDatabase();
    }

    private function getSqlConditionToMatchSingleAction()
    {
        return "( hash = CRC32(?) AND name = ? AND type = ? )";
    }
}
