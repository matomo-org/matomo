<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\PluginTrial;

use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\Marketplace\PluginTrial\RequestHistory;
use Piwik\Plugins\Marketplace\PluginTrial\Service;
use Piwik\Plugins\Marketplace\PluginTrial\Storage;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group PluginTrial
 * @group Plugins
 */
class DecisionTest extends IntegrationTestCase
{
    /**
     * @var list<array{string, string, int}>
     */
    private array $postedEvents = [];

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        Piwik::addAction('Marketplace.pluginRequestDecided', function ($pluginName, $decision, $numRequests) {
            $this->postedEvents[] = [$pluginName, $decision, $numRequests];
        });
    }

    public function testRequestsStoreTheirTypeAndNote(): void
    {
        $this->requestAs('alice', 'PremiumPlugin', RequestHistory::REQUEST_TYPE_TRIAL, 'We need A/B tests');
        $this->requestAs('bob', 'FreePlugin', RequestHistory::REQUEST_TYPE_INSTALL);

        self::assertSame([
            ['plugin_name' => 'FreePlugin', 'login' => 'bob', 'request_type' => 'install', 'message' => null, 'iddecision' => null],
            ['plugin_name' => 'PremiumPlugin', 'login' => 'alice', 'request_type' => 'trial', 'message' => 'We need A/B tests', 'iddecision' => null],
        ], $this->getStoredRequests());
    }

    public function testDecliningRecordsOneDecisionForAllPendingRequests(): void
    {
        $this->requestAs('alice', 'PremiumPlugin');
        $this->requestAs('bob', 'PremiumPlugin');
        $this->requestAs('carol', 'PremiumPlugin');
        $this->requestAs('carol', 'OtherPlugin');

        $this->setSuperUser();
        self::assertSame(3, (new Service())->decide('PremiumPlugin', RequestHistory::DECISION_DECLINED, 'Not this year'));

        $decisions = $this->getStoredDecisions();
        self::assertCount(1, $decisions);
        self::assertSame(
            ['plugin_name' => 'PremiumPlugin', 'decision' => 'declined', 'decided_by' => FakeAccess::$superUserLogin, 'note' => 'Not this year', 'num_requests' => 3],
            array_diff_key(array_replace($decisions[0], ['num_requests' => (int) $decisions[0]['num_requests']]), ['iddecision' => 1, 'ts_decided' => 1])
        );

        $decisionIds = array_map(fn ($request) => $request['plugin_name'] . ':' . $request['login'] . ':' . $request['iddecision'], $this->getStoredRequests());
        $id = $decisions[0]['iddecision'];
        self::assertSame(['OtherPlugin:carol:', "PremiumPlugin:alice:$id", "PremiumPlugin:bob:$id", "PremiumPlugin:carol:$id"], $decisionIds);
        self::assertFalse((new Storage('PremiumPlugin'))->wasRequested());
        self::assertTrue((new Storage('OtherPlugin'))->wasRequested());
        self::assertSame([['PremiumPlugin', 'declined', 3]], $this->postedEvents);
    }

    public function testASecondDecisionStoresNothing(): void
    {
        $this->requestAs('alice', 'PremiumPlugin');

        $this->setSuperUser();
        (new Service())->decide('PremiumPlugin', RequestHistory::DECISION_DECLINED);

        try {
            (new Service())->decide('PremiumPlugin', RequestHistory::DECISION_APPROVED);
            self::fail('A second decision was accepted');
        } catch (\Exception $e) {
            self::assertSame('Marketplace_PluginRequestAlreadyDecided', $e->getMessage());
        }

        self::assertSame(['declined'], array_column($this->getStoredDecisions(), 'decision'));
        self::assertCount(1, $this->postedEvents);
    }

    public function testOnlySuperUsersDecide(): void
    {
        $this->requestAs('alice', 'PremiumPlugin');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('checkUserHasSuperUserAccess');

        (new Service())->decide('PremiumPlugin', RequestHistory::DECISION_DECLINED);
    }

    public function testRequestsMadeAfterADecisionArePendingAgain(): void
    {
        $this->requestAs('alice', 'PremiumPlugin');
        $this->setSuperUser();
        (new Service())->decide('PremiumPlugin', RequestHistory::DECISION_DECLINED);

        $this->requestAs('bob', 'PremiumPlugin');

        self::assertTrue((new Storage('PremiumPlugin'))->wasRequested());
        $this->setSuperUser();
        self::assertSame(1, (new Service())->decide('PremiumPlugin', RequestHistory::DECISION_APPROVED));
    }

    public function testActivatingAPluginApprovesItsPendingRequestsAutomatically(): void
    {
        $this->requestAs('alice', 'PremiumPlugin');
        $this->requestAs('bob', 'PremiumPlugin');

        Piwik::postEvent('PluginManager.pluginActivated', ['PremiumPlugin']);

        $decisions = $this->getStoredDecisions();
        self::assertCount(1, $decisions);
        self::assertSame('approved', $decisions[0]['decision']);
        self::assertNull($decisions[0]['decided_by']);
        self::assertSame(2, (int) $decisions[0]['num_requests']);
        self::assertSame([['PremiumPlugin', 'approved', 2]], $this->postedEvents);
    }

    public function testActivatingAPluginApprovesARequestRecordedWithoutThePendingOption(): void
    {
        // as a request made while a decision cleared the option can be left on a database reading only committed rows
        (new RequestHistory())->add('PremiumPlugin', 'alice', time());
        self::assertFalse((new Storage('PremiumPlugin'))->wasRequested());
        self::assertTrue((new Storage('PremiumPlugin'))->hasPendingRequests());

        Piwik::postEvent('PluginManager.pluginActivated', ['PremiumPlugin']);

        self::assertSame(['approved'], array_column($this->getStoredDecisions(), 'decision'));
        self::assertFalse((new Storage('PremiumPlugin'))->hasPendingRequests());
    }

    public function testActivatingAPluginWithoutPendingRequestsRecordsNoDecision(): void
    {
        $this->requestAs('alice', 'PremiumPlugin');
        $this->setSuperUser();
        (new Service())->decide('PremiumPlugin', RequestHistory::DECISION_DECLINED);

        Piwik::postEvent('PluginManager.pluginActivated', ['PremiumPlugin']);

        self::assertSame(['declined'], array_column($this->getStoredDecisions(), 'decision'));
    }

    public function testDeletingUsersKeepsTheDecisionsCountsWithoutTheirLogins(): void
    {
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        UsersManagerAPI::getInstance()->addUser('admin2', 'secret-password-2', 'admin2@example.com');
        UsersManagerAPI::getInstance()->setSuperUserAccess('admin2', true, Fixture::ADMIN_USER_PASSWORD);

        $this->requestAs('alice', 'PremiumPlugin');
        $this->setSuperUser('admin2');
        (new Service())->decide('PremiumPlugin', RequestHistory::DECISION_DECLINED);

        $this->setSuperUser();
        UsersManagerAPI::getInstance()->deleteUser('alice');
        UsersManagerAPI::getInstance()->deleteUser('admin2');

        self::assertNull($this->getStoredRequests()[0]['login']);
        self::assertNull($this->getStoredDecisions()[0]['decided_by']);
        self::assertSame(1, (int) $this->getStoredDecisions()[0]['num_requests']);
    }

    public function testDecidingBeforeTheUpdateAddsTheColumnsStoresNothing(): void
    {
        $this->requestAs('alice', 'PremiumPlugin');
        Db::query('ALTER TABLE ' . Common::prefixTable(RequestHistory::TABLE_NAME) . ' DROP COLUMN iddecision');

        try {
            // activating must keep working while the update has not run
            Piwik::postEvent('PluginManager.pluginActivated', ['PremiumPlugin']);
            self::assertFalse((new Storage('PremiumPlugin'))->wasRequested());
            self::assertSame([], $this->postedEvents);

            $this->requestAs('bob', 'OtherPlugin', RequestHistory::REQUEST_TYPE_TRIAL, 'Kept without its note');
            self::assertTrue((new Storage('OtherPlugin'))->wasRequested());
        } finally {
            Db::query('ALTER TABLE ' . Common::prefixTable(RequestHistory::TABLE_NAME) . ' ADD COLUMN iddecision INTEGER UNSIGNED NULL');
        }
    }

    private function requestAs(
        string $login,
        string $pluginName,
        string $requestType = RequestHistory::REQUEST_TYPE_TRIAL,
        ?string $message = null
    ): void {
        FakeAccess::clearAccess(false);
        FakeAccess::$identity = $login;
        (new Service())->request($pluginName, $pluginName, $requestType, $message);
    }

    private function setSuperUser(?string $login = null): void
    {
        FakeAccess::clearAccess(true);
        FakeAccess::$identity = $login ?? FakeAccess::$superUserLogin;
    }

    /**
     * @return list<array<string, string|null>> ordered by plugin and login
     */
    private function getStoredRequests(): array
    {
        return Db::fetchAll(
            'SELECT plugin_name, login, request_type, message, iddecision FROM ' . Common::prefixTable(RequestHistory::TABLE_NAME)
            . ' ORDER BY plugin_name, login'
        );
    }

    /**
     * @return list<array<string, string|null>> oldest first
     */
    private function getStoredDecisions(): array
    {
        return Db::fetchAll(
            'SELECT * FROM ' . Common::prefixTable(RequestHistory::DECISION_TABLE_NAME) . ' ORDER BY iddecision'
        );
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
