<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Notification;

use PHPUnit\Framework\TestCase;
use Piwik\Config;
use Piwik\Date;
use Piwik\Notification;
use Piwik\Notification\Manager;
use Piwik\Session\SessionNamespace;

class ManagerTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private $originalGeneralConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalGeneralConfig = Config::getInstance()->General;
        Manager::cancelAllNotifications();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Config::getInstance()->General = $this->originalGeneralConfig;
        Manager::cancelAllNotifications();
        Date::$now = null;
    }

    /**
     * @dataProvider getDisabledNotificationsConfigValues
     * @param mixed $configValue null removes the setting
     */
    public function testIsDisabled($configValue, bool $expected): void
    {
        $general = Config::getInstance()->General;
        unset($general['disabled_notifications']);
        if ($configValue !== null) {
            $general['disabled_notifications'] = $configValue;
        }
        Config::getInstance()->General = $general;

        $this->assertSame($expected, Manager::isDisabled('testid'));
    }

    public function getDisabledNotificationsConfigValues(): iterable
    {
        yield 'listed' => [['otherid', 'testid'], true];
        yield 'not listed' => [['otherid'], false];
        yield 'missing setting' => [null, false];
        // written without [], so the INI reader gives a string, not a list
        yield 'not a list' => ['testid', false];
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
     * @dataProvider getExpiryTimes
     */
    public function testNotifyPostsANotificationOnlyBeforeItExpires(?int $expiresAt, bool $isPosted): void
    {
        Date::$now = 1000;
        $notification = new Notification('abcdefg');
        $notification->expiresAt = $expiresAt;

        $this->assertSame($isPosted, Manager::notify('testid', $notification));
        $this->assertSame($isPosted, array_key_exists('testid', Manager::getPendingInMemoryNotifications()));
    }

    public function getExpiryTimes(): iterable
    {
        yield 'no expiry' => [null, true];
        yield 'expires later' => [1001, true];
        yield 'expires now' => [1000, false];
        yield 'expired before' => [999, false];
    }

    public function testNotifyRemovesAnEarlierNotificationWithTheSameIdWhenTheNewOneHasExpired(): void
    {
        Date::$now = 1000;
        Manager::notify('testid', new Notification('earlier'));
        $expired = new Notification('expired');
        $expired->expiresAt = 1000;

        $this->assertFalse(Manager::notify('testid', $expired));
        $this->assertArrayNotHasKey('testid', Manager::getPendingInMemoryNotifications());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testANotificationThatExpiresAfterItWasPostedIsNotShownAndIsRemovedFromTheSession(): void
    {
        $this->enableSession();

        Date::$now = 1000;
        $expiring = new Notification('expiring');
        $expiring->expiresAt = 2000;
        Manager::notify('expiring', $expiring);
        Manager::notify('lasting', new Notification('lasting'));

        // the next request, once the expiry time has passed
        Manager::cancelAllNotifications();
        Date::$now = 2000;

        $this->assertSame(['lasting'], array_keys(Manager::getAllNotificationsToDisplay()));

        Date::$now = 1000;
        $this->assertSame(
            ['lasting'],
            array_keys(Manager::getAllNotificationsToDisplay()),
            'the expired notification was not removed from the session'
        );
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
     * @dataProvider getNotificationIds
     */
    public function testIsValidId(string $id, bool $expected): void
    {
        $this->assertSame($expected, Manager::isValidId($id));
    }

    public function getNotificationIds(): iterable
    {
        yield 'word characters' => ['Plugin_notification1', true];
        yield 'empty' => ['', false];
        yield 'zero' => ['0', false];
        yield 'dot' => ['ManageGoals.create', false];
        yield 'space' => ['a b', false];
    }

    /**
     * Also the guard that keeps testARequestWithoutNotificationsDoesNotWriteToTheSession honest:
     * if the session were not enabled, both tests would pass for the wrong reason.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
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

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
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
