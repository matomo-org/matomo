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

/**
 * Lets a plugin request be for an install as well as a trial, carry a note, and end with a superuser's decision.
 */
class Updates_6_1_0_b1 extends Updates
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
                // the defaults make every request stored so far a pending trial request without a note
                $this->migration->db->addColumns('plugin_trial_request', [
                    'request_type' => "VARCHAR(10) NOT NULL DEFAULT 'trial'",
                    'message' => 'TEXT NULL',
                    'iddecision' => 'INTEGER UNSIGNED NULL',
                ]),
                $this->migration->db->addIndex('plugin_trial_request', ['iddecision', 'plugin_name'], 'index_iddecision_plugin_name'),
                $this->migration->db->createTable('plugin_request_decision', [
                    'iddecision' => 'INTEGER UNSIGNED NOT NULL AUTO_INCREMENT',
                    'plugin_name' => 'VARCHAR(60) NOT NULL',
                    'decision' => 'VARCHAR(10) NOT NULL',
                    'decided_by' => 'VARCHAR(100) NULL',
                    'note' => 'TEXT NULL',
                    'num_requests' => 'INTEGER UNSIGNED NOT NULL',
                    'ts_decided' => 'DATETIME NOT NULL',
                ], ['iddecision']),
                $this->migration->db->addIndex('plugin_request_decision', ['ts_decided'], 'index_ts_decided'),
            ],
            $this->getFulfilledRequestMigrations()
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }

    /**
     * Until now a request ended only when its plugin was activated, which cleared the pending option and left the
     * plugin's rows in the history. Those requests are recorded as approved automatically, as activating a plugin now
     * does, so they are not shown as pending. A plugin requested again since keeps the requests made from the time its
     * pending option was stored.
     *
     * @return Migration[]
     */
    private function getFulfilledRequestMigrations(): array
    {
        $optionPrefix = 'Marketplace.PluginTrialRequest.';
        $pendingSince = [];

        foreach (Option::getLike($optionPrefix . '%') as $optionName => $value) {
            $requestTime = (int) (json_decode($value, true)['requestTime'] ?? 0);
            if ($requestTime >= Date::FIRST_WEBSITE_TIMESTAMP) {
                $pendingSince[substr($optionName, strlen($optionPrefix))] = Date::factory($requestTime)->getDatetime();
            }
        }

        $decidedAt = Date::now()->getDatetime();

        $migrations = $this->getAutomaticApprovalMigrations(
            empty($pendingSince) ? '1 = 1' : 'r.plugin_name NOT IN (' . Common::getSqlStringFieldsArray($pendingSince) . ')',
            array_keys($pendingSince),
            $decidedAt
        );

        foreach ($pendingSince as $pluginName => $requestedAt) {
            $migrations = array_merge($migrations, $this->getAutomaticApprovalMigrations(
                'r.plugin_name = ? AND r.ts_requested < ?',
                [$pluginName, $requestedAt],
                $decidedAt
            ));
        }

        return $migrations;
    }

    /**
     * Only undecided requests made before the update are matched, so a request made while it runs stays pending, and
     * a re-run after both statements succeeded records no decision twice.
     *
     * @param list<string> $bind for $condition
     * @return Migration[]
     */
    private function getAutomaticApprovalMigrations(string $condition, array $bind, string $decidedAt): array
    {
        $requestTable = Common::prefixTable('plugin_trial_request');
        $decisionTable = Common::prefixTable('plugin_request_decision');

        return [
            $this->migration->db->boundSql(
                "INSERT INTO `$decisionTable` (plugin_name, decision, decided_by, note, num_requests, ts_decided)
                 SELECT r.plugin_name, 'approved', NULL, NULL, COUNT(*), ?
                 FROM `$requestTable` r
                 WHERE r.iddecision IS NULL AND r.ts_requested <= ? AND $condition
                 GROUP BY r.plugin_name",
                array_merge([$decidedAt, $decidedAt], $bind)
            ),
            $this->migration->db->boundSql(
                "UPDATE `$requestTable` r
                 SET r.iddecision = (SELECT MAX(d.iddecision) FROM `$decisionTable` d WHERE d.plugin_name = r.plugin_name)
                 WHERE r.iddecision IS NULL AND r.ts_requested <= ? AND $condition",
                array_merge([$decidedAt], $bind)
            ),
        ];
    }
}
