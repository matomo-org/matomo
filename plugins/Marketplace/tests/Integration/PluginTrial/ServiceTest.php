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
use Piwik\Plugins\Marketplace\PluginTrial\RequestHistory;
use Piwik\Plugins\Marketplace\PluginTrial\Service;
use Piwik\Plugins\Marketplace\PluginTrial\Storage;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group PluginTrial
 * @group Plugins
 */
class ServiceTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::$identity = FakeAccess::$superUserLogin;
        \Zend_Session::$_unitTestEnabled = true;
        Manager::cancelAllNotifications();
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
