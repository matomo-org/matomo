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

    public function testSetNotificationDismissed()
    {
        $storageMock = self::createMock(Storage::class);
        $storageMock->method('wasRequested')->willReturn(true);
        $storageMock->method('isNotificationDismissed')->willReturn(false);
        $storageMock->expects(self::once())->method('setNotificationDismissed');

        $notification = new Notification('PremiumPlugin', $storageMock);
        $notification->setNotificationDismissed();
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess(),
        );
    }
}
