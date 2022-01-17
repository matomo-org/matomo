<?php
/**
 * Copyright (C) InnoCraft Ltd - All rights reserved.
 *
 * NOTICE:  All information contained herein is, and remains the property of InnoCraft Ltd.
 * The intellectual and technical concepts contained herein are protected by trade secret or copyright law.
 * Redistribution of this information or reproduction of this material is strictly forbidden
 * unless prior written permission is obtained from InnoCraft Ltd.
 *
 * You shall use this code only in accordance with the license agreement obtained from InnoCraft Ltd.
 *
 * @link https://www.innocraft.com/
 * @license For license details see https://www.innocraft.com/license
 */

namespace Piwik\Updates;

use Piwik\Db;
use Piwik\Common;
use Piwik\Updater;
use Piwik\Updater\Migration\Db as DbAlias;
use Piwik\Updater\Migration\Db\DropIndex;
use Piwik\Updater\Migration\Db\Sql;
use Piwik\Updates;

/**
 * Update for version 5.0.0-b1
 */
class Updates_5_0_0_b1 extends Updates
{
    private $tableName;
    private $indexName;

    public function __construct()
    {
        $this->tableName = Common::prefixTable('log_visit');
        $this->indexName = 'index_idsite_idvisitor';
    }

    public function doUpdate(Updater $updater)
    {
        if ($this->requiresUpdatedLogVisitTableIndex()) {
            $this->updateLogVisitTableIndex();
        }
    }

    private function updateLogVisitTableIndex()
    {
        try {
            $dropIndex = new DropIndex($this->tableName, $this->indexName);
            $dropIndex->exec();

            // Using the base `Sql` class instead of the AddIndex class as it doesn't support DESC collation
            $sql = "ALTER TABLE `{$this->tableName}` ADD INDEX `{$this->indexName}` (`idsite`, `idvisitor`, `visit_last_action_time` DESC)";
            $addIndex = new Sql($sql, [DbAlias::ERROR_CODE_DUPLICATE_KEY, DbAlias::ERROR_CODE_KEY_COLUMN_NOT_EXISTS]);
            $addIndex->exec();
        } catch (\Exception $e) {
            if (!$dropIndex->shouldIgnoreError($e)) {
                throw $e;
            }
        }
    }
    
    private function requiresUpdatedLogVisitTableIndex()
    {
        $sql = "SHOW INDEX FROM {$this->tableName} WHERE Key_name = '{$this->indexName}'";

        $result = Db::fetchAll($sql);

        return empty($result);
    }
}