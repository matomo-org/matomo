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
 * page and its menu item
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

    public function testInstallsAreNotRequestableWhenPluginsCannotBeInstalled(): void
    {
        Config::getInstance()->General['enable_plugins_admin'] = 0;

        self::assertFalse($this->searchPlugins()['Barometer']['canInstallBeRequested']);
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
        yield 'flag off' => [false, false];
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

    public function testManageRequestsIsUnderPersonalForUsersWhileRequestsAreOn(): void
    {
        self::assertStringContainsString('Marketplace_ManageRequests', $this->getAdminMenu());
    }

    /**
     * @dataProvider dataUsersWithoutRequestState
     */
    public function testManageRequestsIsNotInTheMenuOfSuperUsersOrWhileRequestsAreOff(bool $isSuperUser, bool $areRequestsOn): void
    {
        $isSuperUser ? FakeAccess::clearAccess(true) : $this->setUser();
        $this->setPluginRequests($areRequestsOn);

        self::assertStringNotContainsString('Marketplace_ManageRequests', $this->getAdminMenu());
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
        Config::getInstance()->FeatureFlags = ['PluginRequests_feature' => $isOn ? 'enabled' : 'disabled'];
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
