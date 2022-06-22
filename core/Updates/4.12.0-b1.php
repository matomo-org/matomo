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
 * Update for version 4.12.0-b1
 */
class Updates_4_12_0_b1 extends PiwikUpdates
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
          $this->migration->db->dropColumn('user', 'invite_status'),
          $this->migration->db->addColumns('user', 'invite_token', 'VARCHAR( 191 ) DEFAULT null'),
          $this->migration->db->addColumns('user', 'invited_by', 'VARCHAR( 100 ) DEFAULT null'),
          $this->migration->db->addColumns('user', 'invite_expired_at', 'TIMESTAMP DEFAULT null'),
          $this->migration->db->addColumns('user', 'invite_accept_at', 'TIMESTAMP DEFAULT null'),

        ];
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
