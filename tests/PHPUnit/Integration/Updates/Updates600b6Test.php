<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Updates;

use Piwik\Common;
use Piwik\Config\GeneralConfig;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Db;
use Piwik\Option;
use Piwik\Plugins\Marketplace\PluginTrial\RequestHistory;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates\Updates_6_0_0_b6;

require_once __DIR__ . '/../../../../core/Updates/6.0.0-b6.php';

/**
 * @group Updates
 */
class Updates600b6Test extends IntegrationTestCase
{
    public function testPendingTrialRequestsAreCopiedOnceWithDeletedRequestersAnonymised(): void
    {
        GeneralConfig::setConfigValue('plugin_trial_request_expiration_in_days', 28);
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');

        $pendingTime = time() - 3600;
        $lapsedTime = time() - 30 * 24 * 3600;
        Option::set('Marketplace.PluginTrialRequest.PremiumPlugin', json_encode([
            'requestTime' => $pendingTime,
            'displayName' => 'Premium Plugin',
            'dismissed' => [],
            'requestedBy' => 'alice',
        ]));
        Option::set('Marketplace.PluginTrialRequest.OtherPlugin', json_encode([
            'requestTime' => $lapsedTime,
            'displayName' => 'Other Plugin',
            'dismissed' => [],
            'requestedBy' => 'since-deleted-user',
        ]));
        Option::set('Marketplace.PluginTrialRequest.BrokenPlugin', 'not json');
        Option::set('Marketplace.PluginTrialRequest.BadTimePlugin', json_encode(['requestTime' => 'yesterday', 'requestedBy' => 'alice']));

        Db::query('DROP TABLE ' . Common::prefixTable('plugin_trial_request'));

        $this->runUpdate();
        $this->runUpdate();

        $history = new RequestHistory();
        self::assertSame(
            [['plugin_name' => 'PremiumPlugin', 'login' => 'alice', 'ts_requested' => Date::factory($pendingTime)->getDatetime(), 'ts_fulfilled' => null, 'ts_expired' => null]],
            $history->getRequests('PremiumPlugin')
        );
        self::assertSame(
            [[
                'plugin_name' => 'OtherPlugin',
                'login' => null,
                'ts_requested' => Date::factory($lapsedTime)->getDatetime(),
                'ts_fulfilled' => null,
                'ts_expired' => Date::factory($lapsedTime + 28 * 24 * 3600)->getDatetime(),
            ]],
            $history->getRequests('OtherPlugin')
        );
        self::assertSame([], $history->getRequests('BrokenPlugin'));
        self::assertSame([], $history->getRequests('BadTimePlugin'));
    }

    private function runUpdate(): void
    {
        $update = new Updates_6_0_0_b6(StaticContainer::get(MigrationFactory::class));
        $update->doUpdate(new Updater());
    }
}
