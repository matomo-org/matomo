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
use Piwik\Tracker;
use Piwik\Log\LoggerInterface;

class Model
{
    const CACHE_KEY_INDEX_IDSITE_IDVISITOR_TIME = 'log_visit_has_index_idsite_idvisitor_time';

    /**
     * Write an visit action record to the database
     *
     * @param array $visitAction
     *
     * @return int
     * @throws Db\DbException
     */
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

    /**
     * Write a goal conversion to the database
     *
     * @param array $conversion
     *
     * @return bool
     * @throws Db\DbException
     */
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

    /**
     * Update an existing goal conversion in the database
     *
     * @param int $idVisit
     * @param int $idGoal
     * @param array $newConversion
     *
     * @return bool
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function updateConversion($idVisit, $idGoal, $newConversion)
    {
        $updateWhere = [
            'idvisit' => $idVisit,
            'idgoal'  => $idGoal,
            'buster'  => 0,
        ];

        $updateParts = $sqlBind = $updateWhereParts = [];

        foreach ($newConversion as $name => $value) {
            $updateParts[] = $name . " = ?";
            $sqlBind[]     = $value;
        }

        foreach ($updateWhere as $name => $value) {
            $updateWhereParts[] = $name . " = ?";
            $sqlBind[]          = $value;
        }

        $parts = implode(', ', $updateParts);
        $table = Common::prefixTable('log_conversion');

        $sql   = "UPDATE $table SET $parts WHERE " . implode(' AND ', $updateWhereParts);

        try {
            $this->getDb()->query($sql, $sqlBind);
        } catch (Exception $e) {
            StaticContainer::get(LoggerInterface::class)->error("There was an error while updating the Conversion: {exception}", [
                'exception' => $e,
            ]);

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
        $sql = "SELECT idaction_sku, idaction_name, idaction_category, idaction_category2, idaction_category3, idaction_category4, idaction_category5, price, quantity, deleted, idorder AS idorder_original_value
				FROM " . Common::prefixTable('log_conversion_item') . "
				WHERE idvisit = ? AND (idorder = ? OR idorder = ?)";

        $bind = [
            $goal['idvisit'],
            isset($goal['idorder']) ? $goal['idorder'] : $defaultIdOrder,
            $defaultIdOrder
        ];

        $itemsInDb = $this->getDb()->fetchAll($sql, $bind);

        Common::printDebug("Items found in current cart, for conversion_item (visit,idorder)=" . var_export($bind, true));
        Common::printDebug($itemsInDb);

        return $itemsInDb;
    }

    /**
     * Write ecommerce item to the conversion item table
     *
     * @param array $ecommerceItems
     *
     * @throws Db\DbException
     */
    public function createEcommerceItems($ecommerceItems)
    {
        $sql = "INSERT IGNORE INTO " . Common::prefixTable('log_conversion_item');
        $i    = 0;
        $bind = [];

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

    /**
     * Insert a new action into the DB
     *
     * @param string $name
     * @param int $type
     * @param string $urlPrefix
     *
     * @return int
     * @throws Db\DbException
     */
    private function insertNewAction($name, $type, $urlPrefix)
    {
        $table = Common::prefixTable('log_action');
        $sql   = "INSERT INTO $table (name, hash, type, url_prefix) VALUES (?,CRC32(?),?,?)";

        $db = $this->getDb();
        $db->query($sql, [$name, $name, $type, $urlPrefix]);

        $actionId = $db->lastInsertId();

        return $actionId;
    }

    /**
     * Get an idaction key from the DB
     *
     * @return string
     */
    private function getSqlSelectActionId()
    {
        // it is possible for multiple actions to exist in the DB (due to rare concurrency issues), so the ORDER BY and
        // LIMIT are important
        $sql = "SELECT idaction, type, name FROM " . Common::prefixTable('log_action')
            . "  WHERE " . $this->getSqlConditionToMatchSingleAction() . " "
            . "ORDER BY idaction ASC LIMIT 1";

        return $sql;
    }

    /**
     * Get an idaction key from the DB by name and type
     *
     * @param string $name
     * @param int $type
     *
     * @return bool|mixed|string
     * @throws Exception
     */
    public function getIdActionMatchingNameAndType($name, $type)
    {
        $sql  = $this->getSqlSelectActionId();
        $bind = [$name, $name, $type];

        $idAction = $this->getDb()->fetchOne($sql, $bind);

        return $idAction;
    }

    /**
     * Returns the IDs for multiple actions based on name + type values.
     *
     * @param array $actionsNameAndType Array like `[ ['name' => '...', 'type' => 1], ... ]`
     * @return array|false Array of DB rows w/ columns: **idaction**, **type**, **name**.
     */
    public function getIdsAction($actionsNameAndType)
    {
        $sql = "SELECT `idaction`, `type`, `name` FROM " . Common::prefixTable('log_action') . " WHERE";
        $bind = [];

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

        // Case URL & Title are empty
        if (empty($bind)) {
            return false;
        }

        $rows = $this->getDb()->fetchAll($sql, $bind);

        $actionsPerType = [];

        foreach ($rows as $row) {
            $name = $row['name'];
            $type = $row['type'];

            if (!isset($actionsPerType[$type])) {
                $actionsPerType[$type] = [];
            }

            if (!isset($actionsPerType[$type][$name])) {
                $actionsPerType[$type][$name] = $row;
            } elseif ($row['idaction'] < $actionsPerType[$type][$name]['idaction']) {
                // keep the lowest idaction for this type, name
                $actionsPerType[$type][$name] = $row;
            }
        }

        $actionsToReturn = [];
        foreach ($actionsPerType as $type => $actionsPerName) {
            foreach ($actionsPerName as $actionPerName) {
                $actionsToReturn[] = $actionPerName;
            }
        }

        return $actionsToReturn;
    }

    /**
     * Update an existing ecommerce item in the conversion items table
     *
     * @param string $originalIdOrder
     * @param array $newItem
     *
     * @throws Db\DbException
     */
    public function updateEcommerceItem($originalIdOrder, $newItem)
    {
        $updateParts = $sqlBind = [];
        foreach ($newItem as $name => $value) {
            $updateParts[] = $name . " = ?";
            $sqlBind[]     = $value;
        }

        $parts = implode(', ', $updateParts);
        $table = Common::prefixTable('log_conversion_item');

        $sql = "UPDATE $table SET $parts WHERE idvisit = ? AND idorder = ? AND idaction_sku = ?";

        $sqlBind[] = $newItem['idvisit'];
        $sqlBind[] = $originalIdOrder;
        $sqlBind[] = $newItem['idaction_sku'];

        $this->getDb()->query($sql, $sqlBind);
    }

    /**
     * Create new visit in the DB
     *
     * @param array $visit
     *
     * @return int
     * @throws Db\DbException
     */
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

    /**
     * Update an existing visit in the DB
     *
     * @param int $idSite
     * @param int $idVisit
     * @param $valuesToUpdate
     *
     * @return bool
     * @throws Db\DbException
     */
    public function updateVisit($idSite, $idVisit, $valuesToUpdate)
    {
        [$updateParts, $sqlBind] = $this->fieldsToQuery($valuesToUpdate);

        $parts = implode(', ', $updateParts);
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

    /**
     * Update an existing action in the database
     *
     * @param $idLinkVa
     * @param $valuesToUpdate
     *
     * @return bool|void
     * @throws Db\DbException
     */
    public function updateAction($idLinkVa, $valuesToUpdate)
    {
        if (empty($idLinkVa)) {
            return;
        }

        [$updateParts, $sqlBind] = $this->fieldsToQuery($valuesToUpdate);

        $parts = implode(', ', $updateParts);
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

    /**
     * Attempt to find an existing visit record in the database
     *
     * @param int    $idSite
     * @param string $configId
     * @param string $idVisitor
     * @param string $userId
     * @param array  $fieldsToRead
     * @param bool   $shouldMatchOneFieldOnly
     * @param bool   $isVisitorIdToLookup
     * @param string $timeLookBack
     * @param string $timeLookAhead
     *
     * @return array|bool|mixed
     */
    public function findVisitor($idSite, $configId, $idVisitor, $userId, $fieldsToRead, $shouldMatchOneFieldOnly,
                                $isVisitorIdToLookup, $timeLookBack, $timeLookAhead)
    {
        $selectFields = implode(', ', $fieldsToRead);

        $select = "SELECT $selectFields ";
        $from   = "FROM " . Common::prefixTable('log_visit');

        // Two use cases:
        // 1) there is no visitor ID so we try to match only on config_id (heuristics)
        //         Possible causes of no visitor ID: no browser cookie support, direct Tracking API request without visitor ID passed,
        //        importing server access logs with import_logs.py, etc.
        //         In this case we use config_id heuristics to try find the visitor in tahhhe past. There is a risk to assign
        //         this page view to the wrong visitor, but this is better than creating artificial visits.
        // 2) there is a visitor ID and we trust it (config setting trust_visitors_cookies, OR it was set using &cid= in tracking API),
        //      and in these cases, we force to look up this visitor id
        $configIdWhere = "visit_last_action_time >= ? AND visit_last_action_time <= ? AND idsite = ?";
        $configIdbindSql = [
            $timeLookBack,
            $timeLookAhead,
            $idSite
        ];

        $visitorIdWhere = 'idsite = ? AND visit_last_action_time <= ?';
        $visitorIdbindSql = [$idSite, $timeLookAhead];

        if ($shouldMatchOneFieldOnly && $isVisitorIdToLookup) {
            $visitRow = $this->findVisitorByVisitorId($idVisitor, $select, $from, $visitorIdWhere, $visitorIdbindSql);
        } elseif ($shouldMatchOneFieldOnly) {
            $visitRow = $this->findVisitorByConfigId($configId, $select, $from, $configIdWhere, $configIdbindSql);
        } else {
            if (!empty($idVisitor)) {
                $visitRow = $this->findVisitorByVisitorId($idVisitor, $select, $from, $visitorIdWhere, $visitorIdbindSql);
            } else {
                $visitRow = false;
            }

            if (empty($visitRow)) {
                if (!empty($userId)) {
                    $configIdWhere .= ' AND ( user_id IS NULL OR user_id = ? )';
                    $configIdbindSql[] = $userId;
                }
                $visitRow = $this->findVisitorByConfigId($configId, $select, $from, $configIdWhere, $configIdbindSql);
            }
        }

        return $visitRow;
    }

    /**
     * Return true if a visit record exists for the idvisit key and site
     *
     * @param int $idSite
     * @param int $idVisit
     *
     * @return bool
     * @throws Exception
     */
    public function hasVisit($idSite, $idVisit)
    {
        // will use INDEX index_idsite_idvisitor_time (idsite, idvisitor, visit_last_action_time)
        $sql = 'SELECT idsite FROM ' . Common::prefixTable('log_visit') . ' WHERE idvisit = ? LIMIT 1';
        $bindSql = [$idVisit];

        $val = $this->getDb()->fetchOne($sql, $bindSql);
        return $val == $idSite;
    }

    /**
     * Attempt to find an existing visit record in the database by visitor id and passed query fragments
     *
     * @param string $idVisitor
     * @param string $select
     * @param string $from
     * @param string $where
     * @param array $bindSql
     *
     * @return array|bool|mixed
     */
    private function findVisitorByVisitorId($idVisitor, $select, $from, $where, $bindSql)
    {
        $cache = Cache::getCacheGeneral();

        // use INDEX index_idsite_idvisitor_time (idsite, idvisitor, visit_last_action_time) if available
        if (array_key_exists(self::CACHE_KEY_INDEX_IDSITE_IDVISITOR_TIME,
                             $cache) && true === $cache[self::CACHE_KEY_INDEX_IDSITE_IDVISITOR_TIME]) {
            $from .= ' FORCE INDEX (index_idsite_idvisitor_time) ';
        }

        $where .= ' AND idvisitor = ?';
        $bindSql[] = $idVisitor;

        return $this->fetchVisitor($select, $from, $where, $bindSql);
    }

    /**
     * Attempt to find an existing visit record in the database by config id and passed query fragments
     *
     * @param string $configId
     * @param string $select
     * @param string $from
     * @param string $where
     * @param array $bindSql
     *
     * @return array|bool|mixed
     */
    private function findVisitorByConfigId($configId, $select, $from, $where, $bindSql)
    {
        // will use INDEX index_idsite_config_datetime (idsite, config_id, visit_last_action_time)
        $where .= ' AND config_id = ?';
        $bindSql[] = $configId;

        return $this->fetchVisitor($select, $from, $where, $bindSql);
    }

    /**
     * Retrieve a visit row from the database using the passed query fragments
     *
     * @param string $select
     * @param string $from
     * @param string $where
     * @param array $bindSql
     *
     * @return array|bool|mixed
     * @throws Db\DbException
     */
    private function fetchVisitor($select, $from, $where, $bindSql)
    {
        $sql = "$select $from WHERE " . $where . "
                ORDER BY visit_last_action_time DESC
                LIMIT 1";

        $visitRow = $this->getDb()->fetch($sql, $bindSql);

        return $visitRow;
    }

    /**
     * Returns true if the site doesn't have raw data.
     *
     * @param int $siteId
     * @return bool
     */
    public function isSiteEmpty($siteId)
    {
        $sql = sprintf('SELECT idsite FROM %s WHERE idsite = ? limit 1', Common::prefixTable('log_visit'));

        $result = \Piwik\Db::fetchOne($sql, [$siteId]);

        return $result == null;
    }

    /**
     * Build an array of fields and bind values
     *
     * @param array $valuesToUpdate
     *
     * @return array[]
     */
    private function fieldsToQuery($valuesToUpdate)
    {
        $updateParts = [];
        $sqlBind     = [];

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

        return [$updateParts, $sqlBind];
    }

    /**
     * Delete an action record by key
     *
     * @param int $newActionId
     *
     * @throws Db\DbException
     */
    private function deleteDuplicateAction($newActionId)
    {
        $sql = "DELETE FROM " . Common::prefixTable('log_action') . " WHERE idaction = ?";

        $db = $this->getDb();
        $db->query($sql, [$newActionId]);
    }

    /**
     * Get the tracker DB object
     *
     * @return \Piwik\Db|Db\Mysqli|Db\Pdo\Mysql|null
     * @throws Db\DbException
     */
    private function getDb()
    {
        return Tracker::getDatabase();
    }

    /**
     * Get sql query where clauses used to match a single action
     *
     * @return string
     */
    private function getSqlConditionToMatchSingleAction()
    {
        return "( hash = CRC32(?) AND name = ? AND type = ? )";
    }
}
