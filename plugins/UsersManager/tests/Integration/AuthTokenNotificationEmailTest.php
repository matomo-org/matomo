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
use Piwik\Plugins\UsersManager\tests\Fixtures\Tokens as TokensFixture;
use Piwik\Plugins\UsersManager\TokenNotifications\TokenNotifierTask;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group UsersManager
 * @group AuthTokenNotificationEmailTest
 * @group TokenNotifications
 * @group Plugins
 */
class AuthTokenNotificationEmailTest extends IntegrationTestCase
{
    /**
     * @var Fixture
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
            self::$fixture->resetTsRotationNotification();
        }
        $this->capturedNotifications = [];
        $this->task->dispatchNotifications();
    }

    /**
     * @throws \Exception
     */
    public function testDispatchAuthTokenNotificationEmail()
    {
        Date::$now = Date::factory('2025-04-01')->getTimestamp();

        // for standard 180 days, we have one token notification for each of two users
        $this->clearCaptureAndDispatch();
        self::assertEquals(2, count($this->capturedNotifications));
        self::assertEquals(['user1', 'user2'], array_column($this->capturedNotifications, 1));
        self::assertEquals(['2024-01-01 00:00:00', '2024-01-01 00:00:00'], array_column($this->capturedNotifications, 3));

        // all notifications sent already, should be zero now
        $this->clearCaptureAndDispatch();
        self::assertEquals(0, count($this->capturedNotifications));

        // after removing the notification timestamp, we should have two notifications again, both in 2024
        $this->clearCaptureAndDispatch(true);
        self::assertEquals(2, count($this->capturedNotifications));
        self::assertEquals(['2024-01-01 00:00:00', '2024-01-01 00:00:00'], array_column($this->capturedNotifications, 3));

        // change rotation notification interval to 30 days
        Config::getInstance()->General['auth_token_rotation_notification_days'] = 30;

        // after changing the date, we get extra two notifications in 2025
        $this->clearCaptureAndDispatch();
        self::assertEquals(2, count($this->capturedNotifications));
        self::assertEquals(['2025-01-01 00:00:00', '2025-01-01 00:00:00'], array_column($this->capturedNotifications, 3));
    }

    /**
     * @throws \Exception
     */
    public function testNoNotificationIsSentWhenTodayIsBeforeTokensCreated()
    {
        // change rotation notification interval to 1 day and date before first token was created
        Date::$now = Date::factory('2023-12-01')->getTimestamp();
        Config::getInstance()->General['auth_token_rotation_notification_days'] = 1;

        $this->clearCaptureAndDispatch(true);
        self::assertEquals(0, count($this->capturedNotifications));
    }

    /**
     * @throws \Exception
     */
    public function testNoNotificationIsSentWhenFeatureDisabled()
    {
        Date::$now = Date::factory('2025-12-01')->getTimestamp();
        Config::getInstance()->General['auth_token_rotation_notification_days'] = 0;

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
                    $body = preg_replace("/=[\r\n]+/", '', $body);
                    preg_match('|Hello, (.*?)!.*your <strong>(.*)</strong>.*since <strong>([0-9-:\s]+)</strong>|isu', $body, $matches);
                    if (count($matches) === 4) {
                        unset($matches[0]);
                        $this->capturedNotifications[] = $matches;
                    }
                })],
            ]),
        ];
    }
}

AuthTokenNotificationEmailTest::$fixture = new TokensFixture();
