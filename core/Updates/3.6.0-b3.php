<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updater;
use Piwik\Updates;

/**
 * Update for version 3.6.0-b3.
 */
class Updates_3_6_0_b3 extends Updates
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
        $columns = array(
            'idreport' => 'INT(11) NOT NULL',
            'token' => ' VARCHAR(100) NOT NULL',
            'email' => 'VARCHAR(100) NOT NULL'
        );
        return array(
            $this->migration->db->createTable('report_subscriptions', $columns, $primary = 'token'),
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
