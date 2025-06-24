<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Option;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Updater;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates as PiwikUpdates;

/**
 * Update for version 5.4.0-b4
 */
class Updates_5_4_0_b4 extends PiwikUpdates
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
          $this->migration->db->addColumns('user', ['ts_last_seen' => 'TIMESTAMP null DEFAULT null']),
        ];
    }

    private function migrateLastLoginFromOptionsTable()
    {
        $userModel = new Model();
        $optionPrefix = 'UsersManager.lastSeen.';
        foreach (Option::getLike($optionPrefix . '%') as $name => $value) {
            $username = str_replace($optionPrefix, '', $name);
            $userModel->setLastSeenTimestamp($username, $value);
            Option::delete($name);
        }
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));

        $this->migrateLastLoginFromOptionsTable();
    }
}
