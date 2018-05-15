<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_2_0_a7 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater)
    {
        return array(
            $this->migration->db->addColumn('logger_message', 'tag', 'VARCHAR(50) NULL', 'idlogger_message'),
            $this->migration->db->addColumn('logger_message', 'level', 'TINYINT', 'timestamp'),
        );
    }

    public function doUpdate(Updater $updater)
    {
        // add tag & level columns to logger_message table
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
