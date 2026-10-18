<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration;

use Piwik\Access;
use Piwik\Cache;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\DI;
use Piwik\FrontController;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Plugins\CorePluginsAdmin\PluginInstaller;
use Piwik\Plugins\Marketplace\Api\Service\Exception as ServiceException;
use Piwik\Plugins\Marketplace\Controller;
use Piwik\Plugins\Marketplace\LicenseKey;
use Piwik\Plugins\Marketplace\PluginTrial\UnsubscribeToken;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Service;
use Piwik\Plugins\Marketplace\UserSettings;
use Piwik\Plugins\UsersManager\Model as UsersModel;
use Piwik\Settings\Storage\UserScopedSettingsAccessManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    /**
     * @var array
     */
    private $originalGet = [];

    /**
     * @var Service
     */
    private $service;

    /**
     * @var string the list the 'plugins' action answers with, so a test can pick one that suits it
     */
    private $pluginsFixture = 'v2.0_plugins.json';

    private $invalidLicensesCacheKey = 'Marketplace_ExpiredPlugins';

    /**
     * @var array<string, string|\Exception> what a plugin's info request is answered with, either
     *      the name of a fixture or the exception the request fails with
     */
    private $pluginInfoAnswers = [];

    /**
     * @var string[] the plugins the installer was asked to install or update, in order
     */
    private $installedPlugins = [];

    public function setUp(): void
    {
        parent::setUp();

        // without this Piwik::translate() hands back the bare key, so a test cannot tell a resolved
        // message from a missing one
        Fixture::loadAllTranslations();

        $this->originalGet = $_GET;
    }

    public function tearDown(): void
    {
        // these tests drive controller actions through $_GET, which outlives the test otherwise
        $_GET = $this->originalGet;

        Fixture::resetTranslations();

        parent::tearDown();
    }

    public function testSearchPluginsReturnsExactlyTheCardFieldAllowList()
    {
        $plugins = $this->searchPlugins();

        self::assertNotEmpty($plugins);

        // asserting the exact set, not just the absence of the heavy fields: a field added to the
        // Marketplace API or to the allow list has to be a deliberate change, since anything the
        // cards do not render is paid for by every visitor. See Controller::keepPluginCardFields().
        $always = [
            'canBeUpdated',
            'canTrialBeRequested',
            'categories',
            'consumer',
            'coverImage',
            'createdDateTime',
            'description',
            'displayName',
            'hasDownloadLink',
            'hasExceededLicense',
            'isActivated',
            'isDownloadable',
            'isEligibleForFreeTrial',
            'isFree',
            'isInstalled',
            'isInvalid',
            'isMissingLicense',
            'isNewBundle',
            'isPaid',
            'isTheme',
            'isTrialPending',
            'isTrialRequested',
            'keywords',
            'lastUpdated',
            'lastUpdatedRaw',
            'licenseStatus',
            'missingRequirements',
            'name',
            'numDownloads',
            'numDownloadsPretty',
            'owner',
            'priceFrom',
            'promotions',
        ];
        $conditional = ['downloadNonce', 'isBundle', 'bundleSeats'];

        foreach ($plugins as $plugin) {
            $keys = array_keys($plugin);
            sort($keys);

            self::assertSame([], array_diff($keys, array_merge($always, $conditional)), sprintf(
                'unexpected field(s) in the plugin list for %s: %s',
                $plugin['name'] ?? '?',
                implode(', ', array_diff($keys, array_merge($always, $conditional)))
            ));
            self::assertSame([], array_diff($always, $keys), sprintf(
                'missing card field(s) for %s: %s',
                $plugin['name'] ?? '?',
                implode(', ', array_diff($always, $keys))
            ));
        }
    }

    public function testSearchPluginsKeepsTheFieldsTheModalNeedsWhenItsOwnRequestFails()
    {
        // the modal renders the card row when getPluginDetails fails, and read a bundle as an
        // ordinary plugin while these were trimmed away
        $this->pluginsFixture = 'system_v2.0_plugins_sort-lastupdated.json';

        $sent = json_decode($this->service->getFixtureContent($this->pluginsFixture), true);
        $bundles = array_column(array_filter($sent['plugins'], static function (array $plugin) {
            return !empty($plugin['isBundle']);
        }), 'name');

        self::assertNotEmpty($bundles, 'the fixture no longer carries a bundle to check');

        $cards = array_column($this->searchPlugins(), null, 'name');

        foreach ($bundles as $name) {
            self::assertArrayHasKey($name, $cards);
            self::assertTrue($cards[$name]['isBundle'] ?? false, "$name reaches the modal as a plugin");
        }
    }

    public function testSearchPluginsCarriesTheSeatTierEachBundleIsSoldAt()
    {
        // these three bundles are one product per tier, and the list repeats that tier across a
        // product's variations - two billing periods in two currencies - so the label the card
        // shows must not depend on which of them addPriceFrom() picked
        $this->pluginsFixture = 'system_v2.0_plugins_sort-lastupdated.json';

        $cards = array_column($this->searchPlugins(), null, 'name');

        self::assertSame(4, $cards['TeamBundle']['bundleSeats'] ?? null);
        self::assertSame(20, $cards['BusinessBundle']['bundleSeats'] ?? null);
        self::assertSame(50, $cards['EnterpriseBundle']['bundleSeats'] ?? null);
    }

    public function testSearchPluginsCarriesTheCategorySlugsTheTabBarIsBuiltFrom()
    {
        // the tab bar, the section stack and the card chips are all derived from this one field,
        // client-side, so nothing else on the page can stand in for it
        $this->pluginsFixture = 'system_v2.0_plugins_sort-lastupdated.json';

        $cards = array_column($this->searchPlugins(), 'categories', 'name');

        self::assertNotEmpty($cards);
        self::assertSame(['security'], $cards['SecurityInfo'] ?? null);
        self::assertSame(['database'], $cards['CustomAlerts'] ?? null);
        // a plugin no category claims reaches the client as an empty list, never as a missing key
        self::assertSame([], $cards['WooCommerceAnalytics'] ?? null);

        $slugs = array_unique(array_merge(...array_values($cards)));
        sort($slugs);

        self::assertSame(
            ['customisation', 'database', 'development', 'insights', 'integration', 'security'],
            $slugs
        );
    }

    public function testSearchPluginsCarriesThePromotionPositionsTheHomeSectionsAreOrderedBy()
    {
        // the Featured and Best selling rows are built and ordered client-side from this field
        // alone; the position is the plugin's place in the list the Marketplace keeps
        $this->pluginsFixture = 'system_v2.0_plugins_sort-lastupdated.json';

        $cards = array_column($this->searchPlugins(), 'promotions', 'name');

        self::assertNotEmpty($cards);
        self::assertSame(['featured' => 0, 'bestselling' => 1], $cards['CustomReports'] ?? null);
        self::assertSame(['bestselling' => 4], $cards['UsersFlow'] ?? null);
        // a plugin in no list reaches the client as an empty map, never as a missing key
        self::assertSame([], $cards['WooCommerceAnalytics'] ?? null);
    }

    public function testGetPluginDetailsReturnsTheFieldsTheListOmits()
    {
        // the overview runs first in a browser, so the list the modal reads from is already cached
        $this->searchPlugins();

        $plugin = $this->getPluginDetails('TreemapVisualization');

        self::assertSame('TreemapVisualization', $plugin['name']);

        foreach (['versions', 'shop', 'screenshots', 'support', 'authors', 'changelog'] as $field) {
            self::assertArrayHasKey($field, $plugin, "the modal renders $field");
        }
    }

    public function testGetPluginDetailsReturnsTheLatestVersionOnly()
    {
        $this->searchPlugins();

        $plugin = $this->getPluginDetails('TreemapVisualization');

        // only the latest version is rendered and each version carries its own readme HTML, which
        // is what made the list response large in the first place
        self::assertCount(1, $plugin['versions']);
        self::assertSame($plugin['latestVersion'], $plugin['versions'][0]['name']);
    }

    public function testGetPluginDetailsAnswersAnUnknownPluginAsAJsonErrorRatherThanThrowing()
    {
        // module=Marketplace is not an API request, so throwing here would render the HTML error
        // page with a 500 and log a stack trace, and AjaxHelper would show its own literal instead
        // of this message. A result=error body is what carries the text to the modal.
        $response = $this->dispatch('getPluginDetails', ['pluginName' => 'NotOnTheMarketplace']);
        $decoded = json_decode($response, true);

        self::assertSame('error', $decoded['result']);
        self::assertNotEmpty($decoded['message']);
        self::assertStringContainsString('NotOnTheMarketplace', $decoded['message']);
    }

    public function testGetPluginDetailsAnswersAnUnreachableMarketplaceAsAJsonErrorRatherThanThrowing()
    {
        // the most likely way this action fails, since it runs every time a modal is opened. Left
        // to throw it would render the HTML error page with a 500 and log a stack trace at ERROR,
        // and AjaxHelper would show its own literal instead of a message naming the plugin.
        $this->service->throwException(new ServiceException('Marketplace could not be reached'));

        $response = $this->dispatch('getPluginDetails', ['pluginName' => 'TreemapVisualization']);
        $decoded = json_decode($response, true);

        self::assertSame('error', $decoded['result']);
        self::assertStringContainsString('TreemapVisualization', $decoded['message']);
    }

    /**
     * @return array<string, mixed>
     */
    private function getPluginDetails(string $pluginName): array
    {
        return json_decode($this->dispatch('getPluginDetails', ['pluginName' => $pluginName]), true);
    }

    public function testUpdatePluginUpdatesTheRemainingPluginsWhenAnInfoRequestFails()
    {
        // a plugin the license key no longer covers is answered with an error on its info request,
        // which is raised before the plugin is downloaded and used to end the whole run there
        $this->pluginInfoAnswers = [
            'SecurityInfo' => new ServiceException('Requested plugin does not exist.', ServiceException::API_ERROR),
            'TreemapVisualization' => 'v2.0_plugins_TreemapVisualization_info.json',
        ];

        $response = $this->dispatchUpdate('SecurityInfo,TreemapVisualization');

        self::assertSame(['TreemapVisualization'], $this->installedPlugins);
        self::assertStringContainsString('Requested plugin does not exist.', $response);
        self::assertStringContainsString(
            Piwik::translate('Marketplace_PluginsCouldNotBeUpdated', 'SecurityInfo'),
            $response
        );
    }

    public function testUpdatePluginNotifiesOnlyOnceAboutAFailureThatHitsEveryPlugin()
    {
        // a Marketplace or network error is answered to every info request alike, and a persistent
        // notification per selected plugin would only repeat the same message
        $unreachable = new ServiceException('Marketplace could not be reached', ServiceException::HTTP_ERROR);

        $this->pluginInfoAnswers = [
            'SecurityInfo' => $unreachable,
            'Provider' => $unreachable,
            'TreemapVisualization' => 'v2.0_plugins_TreemapVisualization_info.json',
        ];

        $response = $this->dispatchUpdate('SecurityInfo,Provider,TreemapVisualization');

        self::assertSame(['TreemapVisualization'], $this->installedPlugins);
        self::assertSame(1, substr_count($response, 'Marketplace could not be reached'));

        // both are still named as not updated, only the error itself is not repeated
        self::assertStringContainsString(
            Piwik::translate('Marketplace_PluginsCouldNotBeUpdated', 'SecurityInfo, Provider'),
            $response
        );
    }

    private function dispatchUpdate(string $pluginName): string
    {
        // the update view renders a full admin page, whose menu resolves a site
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }

        $_GET = [
            'module' => 'Marketplace',
            'action' => 'updatePlugin',
            'pluginName' => $pluginName,
            'nonce' => Nonce::getNonce(Controller::UPDATE_NONCE),
        ];

        return Access::doAsSuperUser(function () {
            return FrontController::getInstance()->fetchDispatch('Marketplace', 'updatePlugin');
        });
    }

    public function testSubscriptionOverviewClearsTheInvalidLicensesCache()
    {
        // this action renders a full admin page, whose menu resolves a site
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }

        // a license key is what makes the page fetch the consumer and render its license banners
        (new LicenseKey())->set('consumer2_paid1');

        $stale = ['exceeded' => ['FooPlugin'], 'expired' => [], 'noLicense' => []];

        Cache::getEagerCache()->save($this->invalidLicensesCacheKey, $stale);

        $this->dispatch('subscriptionOverview');

        // the page reads the licenses again while rendering its own banners, so the entry is
        // repopulated rather than absent - what matters is that the stale value did not survive
        self::assertNotSame($stale, Cache::getEagerCache()->fetch($this->invalidLicensesCacheKey));
    }

    public function testUnsubscribeTrialRequestEmailsAsksForConfirmationWithoutChangingTheSetting()
    {
        Fixture::createSuperUser();

        $html = $this->dispatchUnsubscribe(Fixture::ADMIN_USER_LOGIN, $this->unsubscribeToken(Fixture::ADMIN_USER_LOGIN));

        self::assertStringContainsString('Are you sure you want to unsubscribe from plugin request email notifications?', $html);
        self::assertStringContainsString('name="confirm"', $html);
        self::assertNull($this->getNotifySetting(Fixture::ADMIN_USER_LOGIN));
    }

    public function testUnsubscribeTrialRequestEmailsRejectsATokenForAnotherLogin()
    {
        Fixture::createSuperUser();

        $html = $this->dispatchUnsubscribe(Fixture::ADMIN_USER_LOGIN, $this->unsubscribeToken('someoneElse'), true);

        self::assertStringContainsString('This unsubscribe link is not valid.', $html);
        self::assertNull($this->getNotifySetting(Fixture::ADMIN_USER_LOGIN));
    }

    public function testUnsubscribeTrialRequestEmailsRejectsAValidTokenForAnUnknownUser()
    {
        $html = $this->dispatchUnsubscribe('unknownUser', $this->unsubscribeToken('unknownUser'));

        self::assertStringContainsString('This unsubscribe link is not valid.', $html);
    }

    public function testUnsubscribeTrialRequestEmailsTurnsTheEmailsOffForThatUserOnConfirm()
    {
        Fixture::createSuperUser();
        (new UsersModel())->addUser('otherUser', 'password', 'other@example.org', Date::now()->getDatetime());

        $html = $this->dispatchUnsubscribe(Fixture::ADMIN_USER_LOGIN, $this->unsubscribeToken(Fixture::ADMIN_USER_LOGIN), true);

        self::assertStringContainsString('You have been successfully unsubscribed from plugin request email notifications.', $html);
        self::assertFalse((bool) $this->getNotifySetting(Fixture::ADMIN_USER_LOGIN));
        self::assertNull($this->getNotifySetting('otherUser'));
    }

    public function testUnsubscribeTrialRequestEmailsPostsTheEventOnlyWhenItTurnsTheEmailsOff()
    {
        Fixture::createSuperUser();
        $token = $this->unsubscribeToken(Fixture::ADMIN_USER_LOGIN);

        $postedFor = [];
        Piwik::addAction('Marketplace.trialRequestEmailsUnsubscribed', function (string $login) use (&$postedFor) {
            $postedFor[] = $login;
        });

        $this->dispatchUnsubscribe(Fixture::ADMIN_USER_LOGIN, $token);
        self::assertSame([], $postedFor);

        $this->dispatchUnsubscribe(Fixture::ADMIN_USER_LOGIN, $token, true);
        self::assertSame([Fixture::ADMIN_USER_LOGIN], $postedFor);

        // eg the link in an older email, once the emails are already off
        $this->dispatchUnsubscribe(Fixture::ADMIN_USER_LOGIN, $token, true);
        self::assertSame([Fixture::ADMIN_USER_LOGIN], $postedFor);
    }

    private function dispatchUnsubscribe(string $login, string $token, bool $confirm = false): string
    {
        $_GET = [
            'module' => 'Marketplace',
            'action' => 'unsubscribeTrialRequestEmails',
            'login' => $login,
            'token' => $token,
        ];

        if ($confirm) {
            $_GET['confirm'] = '1';
            $_GET['nonce'] = Nonce::getNonce(Controller::UNSUBSCRIBE_TRIAL_REQUEST_EMAILS_NONCE);
        }

        // the link is opened from an email, so the action must work without a session
        Access::getInstance()->setSuperUserAccess(false);

        return FrontController::getInstance()->fetchDispatch('Marketplace', 'unsubscribeTrialRequestEmails');
    }

    private function unsubscribeToken(string $login): string
    {
        return StaticContainer::get(UnsubscribeToken::class)->generate($login);
    }

    /**
     * @return mixed the stored value, or null when the user never set it
     */
    private function getNotifySetting(string $login)
    {
        return StaticContainer::get(UserScopedSettingsAccessManager::class)
            ->get('Marketplace', $login, UserSettings::NOTIFY_ON_PLUGIN_REQUEST);
    }

    /**
     * @param array<string, string> $params
     */
    private function dispatch(string $action, array $params = []): string
    {
        $_GET = array_merge(['module' => 'Marketplace', 'action' => $action, 'format' => 'JSON'], $params);

        return Access::doAsSuperUser(function () use ($action) {
            return FrontController::getInstance()->fetchDispatch('Marketplace', $action);
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function searchPlugins(): array
    {
        $_GET['module'] = 'Marketplace';
        $_GET['action'] = 'searchPlugins';
        $_GET['format'] = 'JSON';

        $response = Access::doAsSuperUser(function () {
            return FrontController::getInstance()->fetchDispatch('Marketplace', 'searchPlugins');
        });

        return json_decode($response, true);
    }

    public function provideContainerConfig()
    {
        // these actions dispatch through the container, so the Client they reach has to answer out
        // of the fixtures served by this mock service rather than the real Marketplace.
        $this->service = new Service();
        $this->service->setOnDownloadCallback(function ($action) {
            return $this->service->getFixtureContent($this->fixtureFor($action));
        });
        $this->service->setOnFetchCallback(function ($action) {
            foreach ($this->pluginInfoAnswers as $pluginName => $answer) {
                if ($action !== sprintf('plugins/%s/info', $pluginName)) {
                    continue;
                }

                if ($answer instanceof \Exception) {
                    throw $answer;
                }

                return json_decode($this->service->getFixtureContent($answer), true);
            }

            // anything else is answered out of the fixtures fixtureFor() wires up
            return null;
        });

        $installer = $this->createMock(PluginInstaller::class);
        $installer->method('installOrUpdatePluginFromMarketplace')->willReturnCallback(
            function ($pluginName) {
                $this->installedPlugins[] = $pluginName;
            }
        );

        return [
            'dev.forced_plugin_update_result' => [],
            'Piwik\Plugins\Marketplace\Api\Service' => DI::value($this->service),
            'Piwik\Plugins\CorePluginsAdmin\PluginInstaller' => DI::value($installer),
        ];
    }

    private function fixtureFor(string $action): string
    {
        // exact matches, so a call these tests do not expect - 'plugins/checkUpdates' or a
        // per-plugin 'plugins/Foo/info' - fails loudly instead of being handed the list
        if ($action === 'themes') {
            return 'v2.0_themes.json';
        }

        if ($action === 'plugins') {
            return $this->pluginsFixture;
        }

        if ($action === 'consumer') {
            return 'v2.0_consumer-access_token-consumer2_paid1.json';
        }

        if ($action === 'info') {
            // the license banners an admin page renders resolve their login link from this
            return 'emptyObjectResponse.json';
        }

        if ($action === 'plugins/NotOnTheMarketplace/info') {
            // the one name a test deliberately asks for and the Marketplace does not have
            return 'emptyObjectResponse.json';
        }

        throw new \Exception(sprintf('No fixture wired up for the Marketplace action "%s"', $action));
    }
}
