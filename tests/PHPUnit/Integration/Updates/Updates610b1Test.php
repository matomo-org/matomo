<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Updates;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Option;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates\Updates_6_1_0_b1;

require_once __DIR__ . '/../../../../core/Updates/6.1.0-b1.php';

/**
 * @group Updates
 */
class Updates610b1Test extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // the request table as the 6.0.0-b6 update created it
        $requestTable = Common::prefixTable('plugin_trial_request');
        Db::query('DROP TABLE IF EXISTS ' . Common::prefixTable('plugin_request_decision'));
        Db::query("ALTER TABLE `$requestTable` DROP INDEX index_iddecision_plugin_name, DROP COLUMN request_type, DROP COLUMN message, DROP COLUMN iddecision");
    }

    public function tearDown(): void
    {
        // recreated from the install schema, so a failed update does not leave the old columns to the tests after this one
        Db::query('DROP TABLE IF EXISTS ' . Common::prefixTable('plugin_trial_request') . ', ' . Common::prefixTable('plugin_request_decision'));
        DbHelper::createTables();

        parent::tearDown();
    }

    public function testRequestsBecomePendingTrialsAndThoseOfActivatedPluginsAreApproved(): void
    {
        $this->addRequest('PendingPlugin', 'alice', '2026-10-05 10:00:00');
        $this->addRequest('PendingPlugin', 'bob', '2026-10-06 10:00:00');
        // fulfilled before the plugin was requested again on 2026-10-05
        $this->addRequest('RequestedAgainPlugin', 'alice', '2026-09-01 10:00:00');
        $this->addRequest('RequestedAgainPlugin', 'bob', '2026-10-05 10:00:00');
        $this->addRequest('ActivatedPlugin', 'alice', '2026-09-01 10:00:00');
        $this->addRequest('ActivatedPlugin', null, '2026-09-02 10:00:00');

        Option::set('Marketplace.PluginTrialRequest.PendingPlugin', json_encode(['requestTime' => strtotime('2026-10-05 10:00:00')]));
        Option::set('Marketplace.PluginTrialRequest.RequestedAgainPlugin', json_encode(['requestTime' => strtotime('2026-10-05 10:00:00')]));

        $this->runUpdate();
        $this->runUpdate();

        $decisions = Db::fetchAll(
            'SELECT iddecision, plugin_name, decision, decided_by, note, num_requests FROM ' . Common::prefixTable('plugin_request_decision')
            . ' ORDER BY plugin_name'
        );
        self::assertSame(
            [
                ['plugin_name' => 'ActivatedPlugin', 'decision' => 'approved', 'decided_by' => null, 'note' => null, 'num_requests' => 2],
                ['plugin_name' => 'RequestedAgainPlugin', 'decision' => 'approved', 'decided_by' => null, 'note' => null, 'num_requests' => 1],
            ],
            array_map(fn ($decision) => array_diff_key(array_replace($decision, ['num_requests' => (int) $decision['num_requests']]), ['iddecision' => 1]), $decisions)
        );

        $decisionIds = array_map('intval', array_column($decisions, 'iddecision', 'plugin_name'));
        self::assertSame(
            [
                ['ActivatedPlugin', null, 'trial', null, $decisionIds['ActivatedPlugin']],
                ['ActivatedPlugin', 'alice', 'trial', null, $decisionIds['ActivatedPlugin']],
                ['PendingPlugin', 'alice', 'trial', null, null],
                ['PendingPlugin', 'bob', 'trial', null, null],
                ['RequestedAgainPlugin', 'alice', 'trial', null, $decisionIds['RequestedAgainPlugin']],
                ['RequestedAgainPlugin', 'bob', 'trial', null, null],
            ],
            array_map(fn ($request) => [
                $request['plugin_name'],
                $request['login'],
                $request['request_type'],
                $request['message'],
                $request['iddecision'] === null ? null : (int) $request['iddecision'],
            ], Db::fetchAll(
                'SELECT plugin_name, login, request_type, message, iddecision FROM ' . Common::prefixTable('plugin_trial_request')
                . ' ORDER BY plugin_name, login'
            ))
        );
    }

    private function addRequest(string $pluginName, ?string $login, string $requestedAt): void
    {
        Db::query(
            'INSERT INTO ' . Common::prefixTable('plugin_trial_request') . ' (plugin_name, login, ts_requested) VALUES (?, ?, ?)',
            [$pluginName, $login, $requestedAt]
        );
    }

    private function runUpdate(): void
    {
        $update = new Updates_6_1_0_b1(StaticContainer::get(MigrationFactory::class));
        $update->doUpdate(new Updater());
    }
}
