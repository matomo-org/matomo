<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\PluginTrial;

use Piwik\Config\GeneralConfig;
use Piwik\Notification\Manager;
use Piwik\Option;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\Marketplace\PluginTrial\RequestHistory;
use Piwik\Plugins\Marketplace\PluginTrial\Service;
use Piwik\Plugins\Marketplace\PluginTrial\Storage;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Plugins\UsersManager\Model as UsersModel;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group PluginTrial
 * @group Plugins
 */
class ServiceTest extends IntegrationTestCase
{
    private bool $sessionWasEnabled;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        FakeAccess::$identity = FakeAccess::$superUserLogin;
        \Zend_Session::$_unitTestEnabled = true;
        $_SESSION = [];
        $this->sessionWasEnabled = \Zend_Session::isWritable() && \Zend_Session::isReadable();
        $this->setSessionEnabled(false);
        Manager::cancelAllNotifications();
    }

    public function tearDown(): void
    {
        $this->setSessionEnabled($this->sessionWasEnabled);

        parent::tearDown();
    }

    public function testRequestDisabled()
    {
        GeneralConfig::setConfigValue('plugin_trial_request_expiration_in_days', -1);

        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');

        $this->assertRequested(false);
    }

    public function testRequestSucceeds()
    {
        $this->assertRequested(false);

        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');

        $this->assertRequested(true);
    }

    public function testWasRequestedDisabled()
    {
        GeneralConfig::setConfigValue('plugin_trial_request_expiration_in_days', -1);

        $service = new Service();
        self::assertFalse($service->wasRequested('PremiumPlugin'));
    }

    public function testWasNotRequested()
    {
        $service = new Service();
        self::assertFalse($service->wasRequested('PremiumPlugin'));
    }

    public function testWasRequested()
    {
        $this->setRequested();

        $service = new Service();
        self::assertTrue($service->wasRequested('PremiumPlugin'));
    }

    public function testEachUserCanRequestAPluginOnce()
    {
        $service = new Service();

        FakeAccess::$identity = 'alice';
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        self::assertTrue($service->wasRequested('PremiumPlugin'));

        FakeAccess::$identity = 'bob';
        self::assertFalse($service->wasRequested('PremiumPlugin'));
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        self::assertTrue($service->wasRequested('PremiumPlugin'));

        $logins = array_column((new RequestHistory())->getRequests('PremiumPlugin'), 'login');
        sort($logins);
        self::assertSame(['alice', 'bob'], $logins);
    }

    public function testIsPendingForEveryUserUntilTheRequestEnds()
    {
        $service = new Service();
        FakeAccess::$identity = 'alice';
        self::assertFalse($service->isPending('PremiumPlugin'));
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');

        FakeAccess::$identity = 'bob';
        self::assertTrue($service->isPending('PremiumPlugin'));

        $service->cancelRequest('PremiumPlugin');
        self::assertFalse($service->isPending('PremiumPlugin'));
    }

    public function testIsPendingDisabled()
    {
        $this->setRequested();
        GeneralConfig::setConfigValue('plugin_trial_request_expiration_in_days', -1);

        self::assertFalse((new Service())->isPending('PremiumPlugin'));
    }

    public function testCancelEndsThePendingRequestButEachUsersRequestStaysPermanent()
    {
        $service = new Service();
        FakeAccess::$identity = 'alice';
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        FakeAccess::$identity = 'bob';
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');

        $service->cancelRequest('PremiumPlugin');

        $this->assertRequested(false);
        self::assertTrue($service->wasRequested('PremiumPlugin'));
        FakeAccess::$identity = 'alice';
        self::assertTrue($service->wasRequested('PremiumPlugin'));

        FakeAccess::$identity = 'carol';
        self::assertFalse($service->wasRequested('PremiumPlugin'));
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        $this->assertRequested(true);
    }

    public function testCreateAndDismissNotifications()
    {
        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        $service->request('PremiumPlugin2', 'Pretty Premium Plugin 2');

        $service->createNotificationsIfNeeded();

        $notifications = Manager::getPendingInMemoryNotifications();

        self::assertCount(2, $notifications);

        Manager::cancelAllNotifications();

        $service->dismissNotification('Marketplace_PluginTrialRequest_' . md5(FakeAccess::$superUserLogin) . '_PremiumPlugin2');
        $service->createNotificationsIfNeeded();

        $notifications = Manager::getPendingInMemoryNotifications();

        self::assertCount(1, $notifications);
    }

    public function testCreateNotificationsEndsARequestForAPluginAlreadyActivated()
    {
        $service = new Service();
        $service->request('CoreHome', 'Core Home');

        $service->createNotificationsIfNeeded();

        self::assertCount(0, Manager::getPendingInMemoryNotifications());
        self::assertFalse((new Storage('CoreHome'))->wasRequested());
        self::assertCount(1, (new RequestHistory())->getRequests('CoreHome'));
    }

    public function testCreateNotificationsShowsTheOtherPluginsWhenEndingARequestFails()
    {
        // a malformed requester makes ending the request throw a TypeError, an Error rather than an Exception
        Option::set('Marketplace.PluginTrialRequest.CoreHome', json_encode([
            'requestTime' => time(),
            'displayName' => 'Core Home',
            'dismissed' => [],
            'requestedBy' => ['olaf'],
        ]));
        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');

        $service->createNotificationsIfNeeded();

        self::assertCount(1, Manager::getPendingInMemoryNotifications());
        self::assertTrue((new Storage('CoreHome'))->wasRequested());
    }

    public function testCreateNotificationsIsSkippedUntilARequestChanges()
    {
        $this->setSessionEnabled(true);
        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        $service->createNotificationsIfNeeded();
        Manager::cancelAllNotifications();

        $service->createNotificationsIfNeeded();
        self::assertCount(0, Manager::getPendingInMemoryNotifications());

        $service->request('PremiumPlugin2', 'Pretty Premium Plugin 2');
        $service->createNotificationsIfNeeded();
        self::assertCount(2, Manager::getPendingInMemoryNotifications());
    }

    public function testCreateNotificationsChecksAgainOnceTheLastCheckIsTenMinutesOld()
    {
        $this->setSessionEnabled(true);
        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        $service->createNotificationsIfNeeded();
        Manager::cancelAllNotifications();

        foreach ($_SESSION as &$namespace) {
            if (is_array($namespace) && isset($namespace['checkedAt'])) {
                $namespace['checkedAt'] -= 600;
            }
        }
        unset($namespace);
        $service->createNotificationsIfNeeded();

        self::assertCount(1, Manager::getPendingInMemoryNotifications());
    }

    public function testCreateNotificationsChecksAgainForAnotherSuperUserInTheSameSession()
    {
        $this->setSessionEnabled(true);
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        (new UsersModel())->setSuperUserAccess('alice', true);
        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        $service->createNotificationsIfNeeded();
        Manager::cancelAllNotifications();

        FakeAccess::$identity = 'alice';
        $service->createNotificationsIfNeeded();

        self::assertCount(1, Manager::getPendingInMemoryNotifications());
    }

    public function testCreateNotificationsChecksAgainForASuperUserTheRequestWasHiddenFrom()
    {
        $this->setSessionEnabled(true);
        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        (new UsersModel())->setSuperUserAccess('alice', true);

        FakeAccess::$identity = 'alice';
        $service->createNotificationsIfNeeded();
        self::assertCount(0, Manager::getPendingInMemoryNotifications());

        (new UsersModel())->setSuperUserAccess(FakeAccess::$superUserLogin, false);
        $service->createNotificationsIfNeeded();

        self::assertCount(1, Manager::getPendingInMemoryNotifications());
    }

    public function testCreateNotificationsChecksAgainOnceARequestedPluginIsActivatedWithoutTheEvent()
    {
        $this->setSessionEnabled(true);
        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        $service->createNotificationsIfNeeded();

        $pluginsToLoad = new \ReflectionProperty(PluginManager::class, 'pluginsToLoad');
        $activatedPlugins = $pluginsToLoad->getValue(PluginManager::getInstance());
        $pluginsToLoad->setValue(PluginManager::getInstance(), array_merge($activatedPlugins, ['PremiumPlugin']));
        try {
            $service->createNotificationsIfNeeded();
        } finally {
            $pluginsToLoad->setValue(PluginManager::getInstance(), $activatedPlugins);
        }

        self::assertFalse((new Storage('PremiumPlugin'))->wasRequested());
    }

    public function testTheNotificationLinkCarriesOnlyTheSiteOfTheRequestThatBuiltIt()
    {
        Fixture::createWebsite('2024-01-01 00:00:00');
        Fixture::loadAllTranslations();
        $_GET = ['module' => 'Widgetize', 'action' => 'iframe', 'widget' => '1', 'idSite' => '1'];
        try {
            (new Service())->request('PremiumPlugin', 'Pretty Premium Plugin');
            (new Service())->createNotificationsIfNeeded();
        } finally {
            $_GET = [];
            Fixture::resetTranslations();
        }

        $message = array_values(Manager::getPendingInMemoryNotifications())[0]->message;
        self::assertStringContainsString('href="?idSite=1&module=Marketplace&action=overview#?showPlugin=PremiumPlugin"', $message);
    }

    public function testCreateNotificationsChecksAgainAfterEndingARequestFailed()
    {
        $this->setSessionEnabled(true);
        Option::set('Marketplace.PluginTrialRequest.CoreHome', json_encode([
            'requestTime' => time(),
            'displayName' => 'Core Home',
            'dismissed' => [],
            'requestedBy' => ['olaf'],
        ]));
        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        $service->createNotificationsIfNeeded();
        Manager::cancelAllNotifications();

        $service->createNotificationsIfNeeded();

        self::assertCount(1, Manager::getPendingInMemoryNotifications());
    }

    public function testCreateNotificationsChecksOnEveryRequestWithoutASession()
    {
        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        $service->createNotificationsIfNeeded();
        Manager::cancelAllNotifications();

        $service->createNotificationsIfNeeded();

        self::assertCount(1, Manager::getPendingInMemoryNotifications());
    }

    /**
     * Under the CLI the session is only marked usable once a namespace is created, which the web front controller
     * does by starting it
     */
    private function setSessionEnabled(bool $enabled): void
    {
        foreach (['_readable', '_writable'] as $flag) {
            (new \ReflectionProperty(\Zend_Session_Abstract::class, $flag))->setValue(null, $enabled);
        }
    }

    protected function assertRequested(bool $expected): void
    {
        $storage = new Storage('PremiumPlugin');
        self::assertEquals($expected, $storage->wasRequested());
    }

    protected function setRequested(): void
    {
        $storage = new Storage('PremiumPlugin');
        $storage->setRequested();
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess(),
        );
    }
}
