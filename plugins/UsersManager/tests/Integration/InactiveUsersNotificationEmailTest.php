<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Integration;

use PHPMailer\PHPMailer\PHPMailer;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Plugins\UsersManager\SystemSettings;
use Piwik\Plugins\UsersManager\tests\Fixtures\Tokens as TokensFixture;
use Piwik\Plugins\UsersManager\UserNotifications\UserNotifierTask;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group UsersManager
 * @group InactiveUsersNotificationEmailTest
 * @group UserNotifications
 * @group Plugins
 */
class InactiveUsersNotificationEmailTest extends IntegrationTestCase
{
    /**
     * @var TokensFixture
     */
    public static $fixture;

    protected $capturedNotifications = [];

    protected $task;

    protected $systemSetting;

    /**
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        Fixture::loadAllTranslations();

        $this->task = new UserNotifierTask();

        $settings = StaticContainer::get(SystemSettings::class);
        $this->systemSetting = $settings->enableInactiveUsersNotifications;

        $this->enableInactiveUsersNotifications();
    }

    public function tearDown(): void
    {
        $this->disableInactiveUsersNotifications();
    }

    private function enableInactiveUsersNotifications(): void
    {
        $this->systemSetting->setValue(true);
    }

    private function disableInactiveUsersNotifications(): void
    {
        $this->systemSetting->setValue(false);
    }

    /**
     * @throws \Exception
     */
    private function clearCaptureAndDispatch(bool $resetTsNotified = false): void
    {
        if ($resetTsNotified) {
            self::$fixture->resetTsInactivityNotification();
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

        // for standard 180 days, we have one notification for each of two superusers
        $this->clearCaptureAndDispatch();
        self::assertEquals(2, count($this->capturedNotifications));

        // all notifications sent already, should be zero now
        $this->clearCaptureAndDispatch();
        self::assertEquals(0, count($this->capturedNotifications));

        // after removing the notification timestamp, we should have two notifications again, both in 2024
        $this->clearCaptureAndDispatch(true);
        self::assertEquals(2, count($this->capturedNotifications));

        // TODO: add assertions on the actual table data for user names, last seen and last activity
    }

    /**
     * @throws \Exception
     */
    public function testNoNotificationIsSentWhenTodayIsBeforeUsersCreated()
    {
        Date::$now = Date::factory('2023-12-01')->getTimestamp();
        $this->clearCaptureAndDispatch(true);
        self::assertEquals(0, count($this->capturedNotifications));
    }

    /**
     * @throws \Exception
     */
    public function testNoNotificationIsSentWhenFeatureDisabled()
    {
        $this->disableInactiveUsersNotifications();
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
                    preg_match('|Hello, (.*?)!.*<tbody>(.*)</tbody>|isu', $body, $matches);
                    if (count($matches) === 3) {
                        $superuser = $matches[1];
                        preg_match_all('|<tr>(.*?)</tr>|isu', $matches[2], $tableRows);
                        if (count($tableRows) > 0) {
                            foreach ($tableRows[1] as $tableRow) {
                                preg_match_all('|<td>(.*?)</td>|isu', $tableRow, $tableCells);
                                if (count($tableCells) === 2) {
                                    $inactiveUserInfo = [
                                        'login' => $tableCells[1][0],
                                        'last_seen' => $tableCells[1][1],
                                        'last_token_activity' => $tableCells[1][2],
                                    ];
                                    $this->capturedNotifications[$superuser][] = $inactiveUserInfo;
                                }
                            }
                        }
                    }
                })],
            ]),
        ];
    }
}

InactiveUsersNotificationEmailTest::$fixture = new TokensFixture();
