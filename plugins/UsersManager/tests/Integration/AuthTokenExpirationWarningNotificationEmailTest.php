<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Integration;

use PHPMailer\PHPMailer\PHPMailer;
use Piwik\Config;
use Piwik\Date;
use Piwik\Plugins\UsersManager\tests\Fixtures\ExpiringTokens as ExpiringTokensFixture;
use Piwik\Plugins\UsersManager\TokenNotifications\TokenNotifierTask;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group UsersManager
 * @group AuthTokenExpirationWarningNotificationEmailTest
 * @group TokenNotifications
 * @group Plugins
 */
class AuthTokenExpirationWarningNotificationEmailTest extends IntegrationTestCase
{
    /**
     * @var ExpiringTokensFixture
     */
    public static $fixture;

    protected $capturedNotifications = [];

    protected $task;

    /**
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        Fixture::loadAllTranslations();

        $this->task = new TokenNotifierTask();
    }

    /**
     * @throws \Exception
     */
    private function clearCaptureAndDispatch(bool $resetTsNotified = false): void
    {
        if ($resetTsNotified) {
            self::$fixture->resetTsExpirationNotification();
        }
        $this->capturedNotifications = [];
        $this->task->dispatchNotifications();
    }

    /**
     * @throws \Exception
     */
    public function testDispatchAuthTokenExpirationWarningNotificationEmail()
    {
        Date::$now = Date::factory('2025-04-01')->getTimestamp();

        // for 30 days, there should be 6 per user, <30 days, =30 days and already expired (<0 days)
        $this->clearCaptureAndDispatch();
        self::assertEquals(12, count($this->capturedNotifications));
        self::assertEquals(
            [
                'user1',
                'user1',
                'user1',
                'user1',
                'user1',
                'user1',
                'user2',
                'user2',
                'user2',
                'user2',
                'user2',
                'user2'
            ],
            array_column($this->capturedNotifications, 1)
        );
        self::assertEquals(
            [
                '2025-04-03 00:00:00',
                '2025-04-03 00:00:00',
                '2025-04-30 00:00:00',
                '2025-04-30 00:00:00',
                '2025-02-01 00:00:00',
                '2025-02-01 00:00:00',
                '2025-04-03 00:00:00',
                '2025-04-03 00:00:00',
                '2025-04-30 00:00:00',
                '2025-04-30 00:00:00',
                '2025-02-01 00:00:00',
                '2025-02-01 00:00:00',
            ],
            array_column($this->capturedNotifications, 3)
        );

        // all notifications sent already, should be zero now
        $this->clearCaptureAndDispatch();
        self::assertEquals(0, count($this->capturedNotifications));

        // after removing the notification timestamp, we should get the same 12 notifications
        $this->clearCaptureAndDispatch(true);
        self::assertEquals(12, count($this->capturedNotifications));
        self::assertEquals(
            [
                '2025-04-03 00:00:00',
                '2025-04-03 00:00:00',
                '2025-04-30 00:00:00',
                '2025-04-30 00:00:00',
                '2025-02-01 00:00:00',
                '2025-02-01 00:00:00',
                '2025-04-03 00:00:00',
                '2025-04-03 00:00:00',
                '2025-04-30 00:00:00',
                '2025-04-30 00:00:00',
                '2025-02-01 00:00:00',
                '2025-02-01 00:00:00',
            ],
            array_column($this->capturedNotifications, 3)
        );

        // change expiration notification period to 60 days
        Config::getInstance()->General['auth_token_expiration_notification_days'] = 60;

        // after changing the date, we get extra four notifications
        $this->clearCaptureAndDispatch();
        self::assertEquals(4, count($this->capturedNotifications));
        self::assertEquals(
            [
                '2025-05-29 00:00:00',
                '2025-05-29 00:00:00',
                '2025-05-29 00:00:00',
                '2025-05-29 00:00:00'
            ],
            array_column($this->capturedNotifications, 3)
        );
    }

    /**
     * @throws \Exception
     */
    public function testNoNotificationIsSentWhenTodayIsBeforeTokensCreated()
    {
        // change expiration notification period to 1 day and date before first token was created
        Date::$now = Date::factory('2025-01-01')->getTimestamp();
        Config::getInstance()->General['auth_token_expiration_notification_days'] = 1;

        $this->clearCaptureAndDispatch(true);
        self::assertEquals(0, count($this->capturedNotifications));
    }

    /**
     * @throws \Exception
     */
    public function testNoExpirationNotificationIsSentWhenFeatureDisabled()
    {
        Date::$now = Date::factory('2025-12-01')->getTimestamp();
        Config::getInstance()->General['auth_token_expiration_notification_days'] = -1;

        $this->clearCaptureAndDispatch(true);

        self::assertEquals(0, count($this->capturedNotifications));
    }

    public function provideContainerConfig(): array
    {
        return [
            'Piwik\Access' => new FakeAccess(),
            'observers.global' => \Piwik\DI::add([
                ['Test.Mail.send', \Piwik\DI::value(function (PHPMailer $mail) {
                    $body = $mail->createBody();
                    $body = preg_replace('/=[\r\n]+/', '', $body);
                    preg_match('|Hello, (.*?)!.*your <strong>(.*)</strong>.*expire on <strong>([0-9-:\s]+)</strong>|isu', $body, $matches);
                    if (count($matches) === 4) {
                        unset($matches[0]);
                        $this->capturedNotifications[] = $matches;
                    }
                })],
            ]),
        ];
    }
}

AuthTokenExpirationWarningNotificationEmailTest::$fixture = new ExpiringTokensFixture();
