<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreHome\tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\Notification;
use Piwik\Notification\Manager as NotificationManager;
use Piwik\Plugins\CoreHome\Controller;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CoreHome
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    private bool $sessionWasEnabled;

    public function setUp(): void
    {
        parent::setUp();

        // notifications are only kept while the session is usable, which the CLI doesn't mark it as
        $this->sessionWasEnabled = \Zend_Session::isWritable() && \Zend_Session::isReadable();
        $_SESSION = [];
        $this->setSessionEnabled(true);

        $notification = new Notification('A stored notification');
        $notification->type = Notification::TYPE_PERSISTENT;
        NotificationManager::notify('storedNotification', $notification);
    }

    public function tearDown(): void
    {
        NotificationManager::cancelAllNotifications();
        $this->setSessionEnabled($this->sessionWasEnabled);

        parent::tearDown();
    }

    public function testMarkNotificationAsReadRemovesTheStoredNotification(): void
    {
        $_POST['notificationId'] = 'storedNotification';

        self::assertSame('true', $this->makeController()->markNotificationAsRead());
        self::assertArrayNotHasKey('storedNotification', NotificationManager::getAllNotificationsToDisplay());
    }

    /**
     * @dataProvider getIdsOfNoStoredNotification
     */
    public function testMarkNotificationAsReadIgnoresAnIdNoStoredNotificationCanHave(?string $notificationId): void
    {
        if (null !== $notificationId) {
            $_POST['notificationId'] = $notificationId;
        }

        self::assertSame('true', $this->makeController()->markNotificationAsRead());
        self::assertArrayHasKey('storedNotification', NotificationManager::getAllNotificationsToDisplay());
    }

    public static function getIdsOfNoStoredNotification(): iterable
    {
        yield 'client-side notification' => ['ManageGoals.create'];
        yield 'empty' => [''];
        yield 'missing' => [null];
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(true),
        ];
    }

    private function makeController(): Controller
    {
        return StaticContainer::getContainer()->make(Controller::class);
    }

    private function setSessionEnabled(bool $enabled): void
    {
        foreach (['_readable', '_writable'] as $flag) {
            (new \ReflectionProperty(\Zend_Session_Abstract::class, $flag))->setValue(null, $enabled);
        }
    }
}
