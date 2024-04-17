<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

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
            'token' => ' VARCHAR(100) NULL',
            'email' => 'VARCHAR(100) NOT NULL',
            'ts_subscribed' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'ts_unsubscribed' => 'TIMESTAMP NULL',
        );
        return array(
            $this->migration->db->createTable('report_subscriptions', $columns, ['idreport', 'email']),
            $this->migration->db->addUniqueKey('report_subscriptions', 'token', 'unique_token')
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
