<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Exception\NoPrivilegesException;
use Piwik\FrontController;
use Piwik\Menu\MenuAdmin;
use Piwik\NoAccessException;
use Piwik\Plugins\Marketplace\Controller;
use Piwik\Plugins\Marketplace\Menu;
use Piwik\Plugins\Marketplace\PluginTrial\RequestHistory;
use Piwik\Plugins\Marketplace\PluginTrial\Storage;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Service;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * What users who are not super users see of their plugin requests: the state of each plugin card, the Manage Requests
 * page and its menu item. Also the Manage Requests page of super users, with the requests to decide on and the decisions.
 *
 * @group Marketplace
 * @group PluginTrial
 * @group Plugins
 */
class ManageRequestsTest extends IntegrationTestCase
{
    private const REQUEST_FIELDS = ['canInstallBeRequested', 'requestType', 'requestStatus'];

    /**
     * @var array<string, mixed>
     */
    private array $originalGet = [];

    /**
     * @var array<string, string>|null what the Marketplace says Barometer requires, null for the fixture's
     */
    private static ?array $barometerRequires = null;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        Fixture::createWebsite('2026-01-01 00:00:00');
        $this->originalGet = $_GET;
        $this->setUser();
        $this->setPluginRequests(true);
    }

    public function tearDown(): void
    {
        $_GET = $this->originalGet;

        parent::tearDown();
    }

    public function testCardsCarryTheUsersRequestOfEachPlugin(): void
    {
        $history = StaticContainer::get(RequestHistory::class);
        $history->add('Barometer', 'aUser', time(), RequestHistory::REQUEST_TYPE_INSTALL);
        $history->add('PaidPlugin1', 'aUser', time(), RequestHistory::REQUEST_TYPE_TRIAL);
        $history->add('Chat', 'anotherUser', time(), RequestHistory::REQUEST_TYPE_INSTALL);
        FakeAccess::clearAccess(true);
        (new Storage('PaidPlugin1'))->setDecided(RequestHistory::DECISION_DECLINED);
        $this->setUser();

        $cards = $this->searchPlugins();

        self::assertSame(['install', 'pending', false], $this->getRequestFields($cards['Barometer']));
        self::assertSame(['trial', 'declined', false], $this->getRequestFields($cards['PaidPlugin1']));
        // another user's request is not this user's
        self::assertSame([null, null, true], $this->getRequestFields($cards['Chat']));
    }

    public function testInstallsAreOnlyRequestableForFreePluginsThatAreNotInstalled(): void
    {
        $cards = $this->searchPlugins();

        self::assertTrue($cards['Barometer']['canInstallBeRequested']);
        self::assertFalse($cards['PaidPlugin1']['canInstallBeRequested']);
        $installed = array_filter($cards, fn (array $card) => $card['isInstalled']);
        self::assertNotEmpty($installed);
        self::assertSame([false], array_unique(array_column($installed, 'canInstallBeRequested')));
    }

    /**
     * @dataProvider dataConfigsWherePluginsCannotBeInstalled
     */
    public function testInstallsAreNotRequestableWhenPluginsCannotBeInstalled(string $setting, int $value): void
    {
        Config::getInstance()->General[$setting] = $value;

        self::assertFalse($this->searchPlugins()['Barometer']['canInstallBeRequested']);
    }

    public function dataConfigsWherePluginsCannotBeInstalled(): iterable
    {
        yield 'plugins admin disabled' => ['enable_plugins_admin', 0];
        // the Super User is shown "Cannot install" without auto update
        yield 'auto update disabled' => ['enable_auto_update', 0];
        yield 'multi server environment' => ['multi_server_environment', 1];
    }

    /**
     * The Super User is shown such a plugin as "Cannot install", so could not approve the request
     */
    public function testInstallsAreNotRequestableForAPluginWhoseRequirementsAreMissing(): void
    {
        self::$barometerRequires = ['php' => '>=99.0.0'];

        try {
            $card = $this->searchPlugins()['Barometer'];
        } finally {
            self::$barometerRequires = null;
        }

        self::assertNotEmpty($card['missingRequirements']);
        self::assertFalse($card['canInstallBeRequested']);
    }

    /**
     * @dataProvider dataUsersWithoutRequestState
     */
    public function testCardsCarryNoRequestStateForSuperUsersOrWhileRequestsAreOff(bool $isSuperUser, bool $areRequestsOn): void
    {
        $isSuperUser ? FakeAccess::clearAccess(true) : $this->setUser();
        $this->setPluginRequests($areRequestsOn);

        foreach ($this->searchPlugins() as $card) {
            self::assertSame([], array_intersect(self::REQUEST_FIELDS, array_keys($card)), $card['name']);
        }
    }

    public function dataUsersWithoutRequestState(): iterable
    {
        yield 'super user' => [true, true];
        yield 'requests off' => [false, false];
    }

    public function testManageRequestsListsTheUsersRequests(): void
    {
        StaticContainer::get(RequestHistory::class)->add('PaidPlugin1', 'aUser', time(), RequestHistory::REQUEST_TYPE_TRIAL, 'For <b>videos</b>');

        $html = $this->dispatch('manageRequests');

        self::assertStringContainsString('vue-entry="Marketplace.ManageRequests"', $html);
        preg_match('/requests="([^"]*)"/', $html, $matches);
        $requests = json_decode(html_entity_decode($matches[1]), true);
        self::assertSame(['PaidPlugin1'], array_column($requests, 'pluginName'));
        self::assertSame('For <b>videos</b>', $requests[0]['message']);
        self::assertNotEmpty($requests[0]['requestedAtPretty']);
    }

    public function testManageRequestsIsRefusedWhileRequestsAreOff(): void
    {
        $this->setPluginRequests(false);

        // answered with 403 as a feature that is off, not 500
        $this->expectException(NoPrivilegesException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('Plugin requests are not enabled');

        // called directly, as a dispatch that fails leaves its output buffer open
        StaticContainer::get(Controller::class)->manageRequests();
    }

    /**
     * @dataProvider dataIsSuperUser
     */
    public function testManageRequestsIsUnderPersonalWhileRequestsAreOn(bool $isSuperUser): void
    {
        $isSuperUser ? FakeAccess::clearAccess(true) : $this->setUser();

        self::assertStringContainsString('Marketplace_ManageRequests', $this->getAdminMenu());
    }

    /**
     * @dataProvider dataIsSuperUser
     */
    public function testManageRequestsIsNotInTheMenuWhileRequestsAreOff(bool $isSuperUser): void
    {
        $isSuperUser ? FakeAccess::clearAccess(true) : $this->setUser();
        $this->setPluginRequests(false);

        self::assertStringNotContainsString('Marketplace_ManageRequests', $this->getAdminMenu());
    }

    public function dataIsSuperUser(): iterable
    {
        yield 'user' => [false];
        yield 'super user' => [true];
    }

    public function testManageRequestsListsThePendingRequestsAndDecisionsForSuperUsers(): void
    {
        FakeAccess::clearAccess(true);
        $now = time();
        $requestHistory = StaticContainer::get(RequestHistory::class);
        $requestHistory->add('PaidPlugin1', 'aUser', $now - 60, RequestHistory::REQUEST_TYPE_TRIAL, 'For <b>videos</b>');
        $requestHistory->add('PaidPlugin1', 'anotherUser', $now, RequestHistory::REQUEST_TYPE_TRIAL);
        $requestHistory->add('Barometer', 'aUser', $now, RequestHistory::REQUEST_TYPE_INSTALL);
        $requestHistory->addDecision('Barometer', RequestHistory::DECISION_DECLINED, 'superUserLogin', 'Not now', $now);

        $html = $this->dispatch('manageRequests');

        self::assertStringContainsString('vue-entry="Marketplace.ManageRequestsSuperUser"', $html);
        self::assertStringNotContainsString('vue-entry="Marketplace.ManageRequests"', $html);

        $pending = $this->getVueProp($html, 'pending-requests');
        self::assertSame(['PaidPlugin1'], array_column($pending, 'pluginName'));
        self::assertSame(2, $pending[0]['numRequests']);
        self::assertSame(gmdate('Y-m-d', $now), $pending[0]['lastRequestedAtPretty']);
        self::assertSame(['anotherUser', 'aUser'], array_column($pending[0]['requesters'], 'login'));
        self::assertSame('For <b>videos</b>', $pending[0]['requesters'][1]['message']);
        self::assertSame(gmdate('Y-m-d', $now), $pending[0]['requesters'][0]['requestedAtPretty']);
        self::assertSame(gmdate('Y-m-d', $now - 60), $pending[0]['requesters'][1]['requestedAtPretty']);

        $history = $this->getVueProp($html, 'history');
        self::assertSame(1, $history['total']);
        self::assertSame('Barometer', $history['decisions'][0]['pluginName']);
        self::assertSame('Not now', $history['decisions'][0]['note']);
        self::assertSame(gmdate('Y-m-d', $now), $history['decisions'][0]['decidedAtPretty']);

        self::assertSame(5, $this->getVueProp($html, 'history-page-size'));
        self::assertTrue($this->getVueProp($html, 'is-plugins-admin-enabled'));
        self::assertNotEmpty($this->getVueProp($html, 'install-requested-nonce'));
        self::assertIsInt($this->getVueProp($html, 'num-users'));
        self::assertSame(50, $this->getVueProp($html, 'shop-user-limit'));
    }

    public function testRequestHistoryIsPagedByFive(): void
    {
        FakeAccess::clearAccess(true);
        $now = time();
        $requestHistory = StaticContainer::get(RequestHistory::class);
        foreach (['Barometer', 'PaidPlugin1', 'PaidPlugin2', 'PaidPlugin3', 'TreemapVisualization', 'Funnels'] as $i => $pluginName) {
            $requestHistory->add($pluginName, 'aUser', $now - 600 + $i, RequestHistory::REQUEST_TYPE_TRIAL);
            $requestHistory->addDecision($pluginName, RequestHistory::DECISION_DECLINED, 'superUserLogin', null, $now - 600 + $i);
        }

        $_GET['offset'] = '5';
        $history = json_decode(StaticContainer::get(Controller::class)->getPluginRequestHistoryPage(), true);

        self::assertSame(6, $history['total']);
        self::assertCount(1, $history['decisions']);
        self::assertSame(gmdate('Y-m-d', $now - 600), $history['decisions'][0]['decidedAtPretty']);
    }

    public function testRequestHistoryPageIsRefusedToUsers(): void
    {
        $this->expectException(NoAccessException::class);

        StaticContainer::get(Controller::class)->getPluginRequestHistoryPage();
    }

    public function testRequestHistoryPageIsRefusedWhileRequestsAreOff(): void
    {
        FakeAccess::clearAccess(true);
        $this->setPluginRequests(false);

        $this->expectException(NoPrivilegesException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('Plugin requests are not enabled');

        StaticContainer::get(Controller::class)->getPluginRequestHistoryPage();
    }

    /**
     * @param array<string, mixed> $card
     * @return array{mixed, mixed, mixed}
     */
    private function getRequestFields(array $card): array
    {
        return [$card['requestType'], $card['requestStatus'], $card['canInstallBeRequested']];
    }

    /**
     * @return mixed the JSON value of the Vue entry's attribute
     */
    private function getVueProp(string $html, string $attribute)
    {
        self::assertSame(1, preg_match('/ ' . preg_quote($attribute, '/') . '="([^"]*)"/', $html, $matches), $attribute);

        return json_decode(html_entity_decode($matches[1]), true);
    }

    /**
     * @return array<string, array<string, mixed>> keyed by plugin name
     */
    private function searchPlugins(): array
    {
        $_GET['format'] = 'JSON';

        return array_column(json_decode($this->dispatch('searchPlugins'), true), null, 'name');
    }

    private function dispatch(string $action): string
    {
        $_GET['module'] = 'Marketplace';
        $_GET['action'] = $action;

        return FrontController::getInstance()->fetchDispatch('Marketplace', $action);
    }

    private function getAdminMenu(): string
    {
        $menu = MenuAdmin::getInstance();
        StaticContainer::get(Menu::class)->configureAdminMenu($menu);

        return json_encode($menu->getMenu());
    }

    private function setUser(): void
    {
        FakeAccess::clearAccess(false);
        FakeAccess::$idSitesView = [1];
        FakeAccess::$identity = 'aUser';
    }

    private function setPluginRequests(bool $isOn): void
    {
        Config::getInstance()->General['plugin_trial_request_expiration_in_days'] = $isOn ? 28 : -1;
    }

    public function provideContainerConfig()
    {
        $service = new Service();
        $service->setOnDownloadCallback(static function ($action) use ($service) {
            if ($action === 'plugins') {
                $plugins = json_decode($service->getFixtureContent('v2.0_plugins.json'), true);
                foreach ($plugins['plugins'] as &$plugin) {
                    if ($plugin['name'] === 'Barometer' && self::$barometerRequires !== null) {
                        foreach ($plugin['versions'] as &$version) {
                            $version['requires'] = self::$barometerRequires;
                        }
                    }
                }

                return json_encode($plugins);
            }
            if ($action === 'themes') {
                return $service->getFixtureContent('v2.0_themes.json');
            }

            return $service->getFixtureContent('emptyObjectResponse.json');
        });

        return [
            'Piwik\Access' => new FakeAccess(),
            'Piwik\Plugins\Marketplace\Api\Service' => \Piwik\DI::value($service),
        ];
    }
}
