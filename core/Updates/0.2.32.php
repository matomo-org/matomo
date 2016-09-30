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
class Updates_0_2_32 extends Updates
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
            // 0.2.32 [941]
            $this->migration->db->changeColumnType('access', 'login', 'VARCHAR( 100 ) NOT NULL'),
            $this->migration->db->changeColumnType('user', 'login', 'VARCHAR( 100 ) NOT NULL'),
            $this->migration->db->changeColumnType('user_dashboard', 'login', 'VARCHAR( 100 ) NOT NULL'),
            $this->migration->db->changeColumnType('user_language', 'login', 'VARCHAR( 100 ) NOT NULL'),
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
