<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Notification;

use PHPUnit\Framework\TestCase;
use Piwik\Notification;
use Piwik\Notification\Manager;
use Piwik\Session\SessionNamespace;

class ManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Manager::cancelAllNotifications();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Manager::cancelAllNotifications();
        $this->disableSession();
    }

    public function testNotifyAddsNotificationToNotificationArray()
    {
        $notification = new Notification('abcdefg');
        $result = Manager::notify('testid', $notification);

        $this->assertTrue($result);

        $notificationsInArray = Manager::getPendingInMemoryNotifications();

        $expected = [
            'testid' => $notification,
        ];
        $this->assertEquals($expected, $notificationsInArray);
    }

    /**
     * @dataProvider getTestDataForNotify
     */
    public function testNotifyThrowsWhenAnInvalidIdIsUsed($id, $expectedMessage)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($expectedMessage);

        Manager::notify($id, new Notification('sldjfksdf'));
    }

    public function getTestDataForNotify()
    {
        return [
            ['', 'Notification ID is empty.'],
            ['aabcd a a k', 'Invalid Notification ID given. Only word characters (AlNum + underscore) allowed.'],
            ['a23%$%', 'Invalid Notification ID given. Only word characters (AlNum + underscore) allowed.'],
        ];
    }

    /**
     * Also the guard that keeps testARequestWithoutNotificationsDoesNotWriteToTheSession honest:
     * if the session were not enabled, both tests would pass for the wrong reason.
     */
    public function testNotificationIsKeptInTheSessionForTheNextRequest()
    {
        $this->enableSession();

        Manager::notify('alpha', new Notification('hello'));

        $this->assertNotSame([], $_SESSION, 'the notification never reached the session');

        // the next request starts with nothing in memory and reads the session back
        Manager::cancelAllNotifications();

        $toDisplay = Manager::getAllNotificationsToDisplay();

        $this->assertArrayHasKey('alpha', $toDisplay);
        $this->assertSame('hello', $toDisplay['alpha']->message);
    }

    public function testARequestWithoutNotificationsDoesNotWriteToTheSession()
    {
        $this->enableSession();

        $this->assertSame([], Manager::getAllNotificationsToDisplay());
        Manager::cancelAllNonPersistent();

        $this->assertSame([], $_SESSION, 'an empty render must leave the session untouched');
    }

    private function enableSession(): void
    {
        $_SESSION = [];

        // in CLI this only flips Zend's readable/writable flags, which is what Manager checks
        new SessionNamespace('notification');
    }

    /**
     * Those flags and the manager's cached namespace are static, so without this the rest of the
     * suite would run as if a session were open.
     */
    private function disableSession(): void
    {
        $_SESSION = [];

        foreach (['_readable', '_writable'] as $flag) {
            $property = new \ReflectionProperty(\Zend_Session_Abstract::class, $flag);
            $property->setAccessible(true);
            $property->setValue(null, false);
        }

        $session = new \ReflectionProperty(Manager::class, 'session');
        $session->setAccessible(true);
        $session->setValue(null, null);
    }

    public function testNotifyDoesNotAddNotificationIfThereAreAlreadyMoreThanThirty()
    {
        for ($i = 0; $i < Manager::MAX_NOTIFICATIONS_IN_SESSION; ++$i) {
            Manager::notify('not' . $i, new Notification('message ' . $i));
        }

        $notificationsInArray = Manager::getPendingInMemoryNotifications();

        $notification = new Notification('abcdefg');
        $result = Manager::notify('testid', $notification);

        $this->assertFalse($result);

        $notificationsInArray = Manager::getPendingInMemoryNotifications();
        $this->assertCount(30, $notificationsInArray);
        $this->assertArrayNotHasKey('testid', $notificationsInArray);
    }
}
