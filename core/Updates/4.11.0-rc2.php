<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Updater;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates as PiwikUpdates;

/**
 * Update for version 4.11.0-rc2
 */
class Updates_4_11_0_rc2 extends PiwikUpdates
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

        return [
          $this->migration->db->changeColumn('user', 'invite_status', 'invite_token', 'VARCHAR(191) DEFAULT null'),
          $this->migration->db->addColumns('user', ['invited_by' => 'VARCHAR(100) DEFAULT null']),
          $this->migration->db->addColumns('user', ['invite_expired_at' => 'TIMESTAMP null DEFAULT null']),
          $this->migration->db->addColumns('user', ['invite_accept_at' => 'TIMESTAMP null DEFAULT null']),

        ];
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
