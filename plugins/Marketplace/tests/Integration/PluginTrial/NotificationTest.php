<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\PluginTrial;

use Piwik\Notification\Manager;
use Piwik\Plugins\Marketplace\PluginTrial\Notification;
use Piwik\Plugins\Marketplace\PluginTrial\RequestHistory;
use Piwik\Plugins\Marketplace\PluginTrial\Storage;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group PluginTrial
 * @group Plugins
 */
class NotificationTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        \Zend_Session::$_unitTestEnabled = true;
        Manager::cancelAllNotifications();
    }

    public function testConstructorThrowsOnInvalidPluginName()
    {
        self::expectException(\Exception::class);

        $storageMock = self::createMock(Storage::class);

        $notification = new Notification('Inval%dPlu§1nName', $storageMock);
    }

    public function testCreateNotificationIfNeededNotRequested()
    {
        $storageMock = self::createMock(Storage::class);
        $storageMock->method('wasRequested')->willReturn(false);
        $storageMock->method('isNotificationDismissed')->willReturn(false);

        $notification = new Notification('PremiumPlugin', $storageMock);
        $notification->createNotificationIfNeeded();

        $this->checkNoNotificationWasCreated();
    }

    public function testCreateNotificationIfNeededAlreadyDismissed()
    {
        $storageMock = self::createMock(Storage::class);
        $storageMock->method('wasRequested')->willReturn(true);
        $storageMock->method('isNotificationDismissed')->willReturn(true);

        $notification = new Notification('PremiumPlugin', $storageMock);
        $notification->createNotificationIfNeeded();

        $this->checkNoNotificationWasCreated();
    }

    public function testCreateNotificationIfNeededCreatesNotification()
    {
        $storageMock = self::createMock(Storage::class);
        $storageMock->method('wasRequested')->willReturn(true);
        $storageMock->method('isNotificationDismissed')->willReturn(false);

        $notification = new Notification('PremiumPlugin', $storageMock);
        $notification->createNotificationIfNeeded();

        $this->checkNotificationWasCreated();
    }

    public function testCreateNotificationIfNeededAsksToInstallAPluginRequestedForInstall()
    {
        $storageMock = self::createMock(Storage::class);
        $storageMock->method('wasRequested')->willReturn(true);
        $storageMock->method('isNotificationDismissed')->willReturn(false);
        $storageMock->method('getDisplayName')->willReturn('Free Plugin');
        $storageMock->method('getRequestType')->willReturn(RequestHistory::REQUEST_TYPE_INSTALL);

        (new Notification('PremiumPlugin', $storageMock))->createNotificationIfNeeded();

        $this->checkNotificationWasCreated();
        $message = current(Manager::getPendingInMemoryNotifications())->message;
        self::assertStringContainsString('Marketplace_InstallRequestedNotification', $message);
        self::assertStringNotContainsString('Marketplace_TrialRequestedNotification1', $message);
    }

    public function testSetNotificationDismissed()
    {
        $storageMock = self::createMock(Storage::class);
        $storageMock->method('wasRequested')->willReturn(true);
        $storageMock->method('isNotificationDismissed')->willReturn(false);
        $storageMock->expects(self::once())->method('setNotificationDismissed');

        $notification = new Notification('PremiumPlugin', $storageMock);
        $notification->setNotificationDismissed();
    }

    private function checkNoNotificationWasCreated()
    {
        self::assertEmpty(Manager::getPendingInMemoryNotifications());
    }

    private function checkNotificationWasCreated()
    {
        $notifications = Manager::getPendingInMemoryNotifications();

        self::assertCount(1, $notifications);

        $expectedNotificationKey = 'Marketplace_PluginTrialRequest_' . md5(FakeAccess::$superUserLogin) . '_PremiumPlugin';

        self::assertArrayHasKey($expectedNotificationKey, $notifications);
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess(),
        );
    }
}
