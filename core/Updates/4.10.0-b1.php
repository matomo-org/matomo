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
 * Update for version 4.10.0-b1
 */
class Updates_4_10_0_b1 extends PiwikUpdates
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
        $table        = Common::prefixTable('report');
        $invalidCount = Db::fetchOne(
            "SELECT COUNT(*) FROM $table WHERE reports = ? OR parameters = ?",
            ['Array', 'Array']
        );

        if (0 === (int) $invalidCount) {
            return [];
        }

        return [
            $this->migration->db->sql("DELETE FROM " . $table . " WHERE reports = 'Array' OR parameters = 'Array'"),
        ];
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
