<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Db;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 4.12.0-b3
 */
class Updates_4_12_0_b3 extends PiwikUpdates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    /**
     * @param Updater $updater
     *
     * @return Migration[]
     */
    public function getMigrations(Updater $updater)
    {
        $column = Db::fetchRow('SHOW COLUMNS FROM ' . Common::prefixTable('user') . ' LIKE \'idchange_last_viewed\'');

        if (
            empty($column)
            || strpos(strtolower($column['Type']), 'int') !== false
            || strpos(strtolower($column['Type']), 'unsigned') !== false
        ) {
            return [];
        }

        $removeValues = $this->migration->db->sql('UPDATE ' . Common::prefixTable('user') . ' SET idchange_last_viewed = NULL');
        $columnUpdate = $this->migration->db->changeColumnType('user', 'idchange_last_viewed', 'INTEGER UNSIGNED');

        return [$removeValues, $columnUpdate];
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
