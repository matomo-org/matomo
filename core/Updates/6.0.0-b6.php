<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Date;
use Piwik\Option;
use Piwik\Updater;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates;

class Updates_6_0_0_b6 extends Updates
{
    private MigrationFactory $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    /**
     * @return Migration[]
     */
    public function getMigrations(Updater $updater)
    {
        return array_merge(
            [
                $this->migration->db->createTable('plugin_trial_request', [
                    'idrequest' => 'INTEGER UNSIGNED NOT NULL AUTO_INCREMENT',
                    'plugin_name' => 'VARCHAR(60) NOT NULL',
                    'login' => 'VARCHAR(100) NULL',
                    'ts_requested' => 'DATETIME NOT NULL',
                    'ts_fulfilled' => 'DATETIME NULL',
                ], ['idrequest']),
                $this->migration->db->addIndex('plugin_trial_request', ['plugin_name', 'ts_requested'], 'index_plugin_name_ts_requested'),
                $this->migration->db->addIndex('plugin_trial_request', ['login', 'plugin_name'], 'index_login_plugin_name'),
            ],
            $this->getPendingRequestMigrations()
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }

    /**
     * Copies the trial requests still pending in the option table, the only ones Matomo kept until now.
     *
     * @return Migration[]
     */
    private function getPendingRequestMigrations(): array
    {
        $optionPrefix = 'Marketplace.PluginTrialRequest.';
        $table = Common::prefixTable('plugin_trial_request');

        // A requester deleted since is stored as NULL, as if they had been anonymised on deletion.
        // NOT EXISTS keeps a re-run from copying a request twice.
        $sql = "INSERT INTO `$table` (plugin_name, login, ts_requested)
                SELECT ?, (SELECT login FROM `" . Common::prefixTable('user') . "` WHERE login = ?), ?
                FROM DUAL
                WHERE NOT EXISTS (SELECT 1 FROM `$table` WHERE plugin_name = ? AND ts_requested = ?)";

        $migrations = [];

        foreach (Option::getLike($optionPrefix . '%') as $optionName => $value) {
            $request = json_decode($value, true);

            $requestTime = (int) ($request['requestTime'] ?? 0);

            // Date::factory() throws for anything this old, which would abort the whole update
            if ($requestTime < Date::FIRST_WEBSITE_TIMESTAMP) {
                continue;
            }

            $pluginName = substr($optionName, strlen($optionPrefix));
            $requestedAt = Date::factory($requestTime)->getDatetime();
            $login = $request['requestedBy'] ?? '';

            $migrations[] = $this->migration->db->boundSql($sql, [$pluginName, $login, $requestedAt, $pluginName, $requestedAt]);
        }

        return $migrations;
    }
}
