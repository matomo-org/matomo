<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Config\GeneralConfig;
use Piwik\Date;
use Piwik\Db;
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
                ], ['idrequest']),
                // straight after the table, so a duplicate request made during the update has the least time to block it
                $this->migration->db->addUniqueKey('plugin_trial_request', ['login', 'plugin_name'], 'index_login_plugin_name'),
                $this->migration->db->addIndex('plugin_trial_request', ['plugin_name', 'ts_requested'], 'index_plugin_name_ts_requested'),
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
        $optionTable = Common::prefixTable('option');
        // the option_value condition leaves an option alone if it changed between preview and execution
        $deleteOptionSql = "DELETE FROM `$optionTable` WHERE option_name = ? AND option_value = ?";
        $updateOptionSql = "UPDATE `$optionTable` SET option_value = ? WHERE option_name = ? AND option_value = ?";
        $expirationInDays = GeneralConfig::getIntegerConfigValue('plugin_trial_request_expiration_in_days', 0);

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

            // Matomo until now only deleted an expired request when it next read it, so drop any that are left
            // -1 turns trial requests off rather than setting an expiry
            if ($expirationInDays >= 0 && $requestTime < time() - $expirationInDays * 24 * 3600) {
                $migrations[] = $this->migration->db->boundSql($deleteOptionSql, [$optionName, $value]);
                continue;
            }

            $pluginName = substr($optionName, strlen($optionPrefix));
            $requestedAt = Date::factory($requestTime)->getDatetime();
            $login = $request['requestedBy'] ?? '';

            $migrations[] = $this->migration->db->boundSql($sql, [$pluginName, $login, $requestedAt, $pluginName, $requestedAt]);

            $anonymizedRequest = $this->withoutDeletedLogins($request);
            if ($anonymizedRequest !== $request) {
                $migrations[] = $this->migration->db->boundSql($updateOptionSql, [json_encode($anonymizedRequest), $optionName, $value]);
            }
        }

        return $migrations;
    }

    /**
     * Removes the users deleted since the request, as Marketplace now does when a user is deleted.
     *
     * @param array<string, mixed> $request
     * @return array<string, mixed>
     */
    private function withoutDeletedLogins(array $request): array
    {
        $dismissed = is_array($request['dismissed'] ?? null) ? $request['dismissed'] : [];
        $logins = array_values(array_filter(array_merge([$request['requestedBy'] ?? null], $dismissed), 'is_string'));
        if (empty($logins)) {
            return $request;
        }

        $existingLogins = Db::fetchAll(
            'SELECT login FROM `' . Common::prefixTable('user') . '` WHERE login IN (' . Common::getSqlStringFieldsArray($logins) . ')',
            $logins
        );
        $existingLogins = array_column($existingLogins, 'login');

        if (isset($request['requestedBy']) && !in_array($request['requestedBy'], $existingLogins, true)) {
            $request['requestedBy'] = null;
        }
        if (!empty($dismissed)) {
            $request['dismissed'] = array_values(array_intersect($dismissed, $existingLogins));
        }

        return $request;
    }
}
