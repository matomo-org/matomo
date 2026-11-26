<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Plugin;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Notification;
use Piwik\Notification\Manager as NotificationManager;
use Piwik\Plugin\ControllerAdmin;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Request\AuthenticationToken;
use Piwik\Session;
use Piwik\Session\SessionNamespace;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use ReflectionMethod;

/**
 * @group Core
 */
class ControllerAdminSmtpBasicAuthRetirementTest extends IntegrationTestCase
{
    private const NOTIFICATION_ID = 'SmtpBasicAuthRetirementMicrosoft';

    public function setUp(): void
    {
        parent::setUp();

        Fixture::loadAllTranslations();
        // before the warning expires at the end of December 2026
        Date::$now = Date::factory('2026-12-31 23:59:59')->getTimestamp();
        $_SESSION = [];
        NotificationManager::cancelAllNotifications();
        FakeAccess::clearAccess(true);

        Config::getInstance()->mail = [
            'transport' => 'smtp',
            'host'      => 'smtp.office365.com',
            'type'      => 'Login',
            'username'  => 'reports@example.org',
            'password'  => 'secret',
        ];
        $this->setGeneralConfig([
            'enable_general_settings_admin' => 1,
            'multi_server_environment'      => 0,
        ]);
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();
        Date::$now = null;

        parent::tearDown();
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }

    public function testPostsAClosablePersistentWarningForASuperUserWhoLogsInWithAPassword(): void
    {
        $this->notify();

        $notification = $this->getWarning();
        $this->assertNotNull($notification, 'the warning was not posted');
        $this->assertSame('Microsoft 365 email authentication is changing', $notification->title);
        $this->assertSame(Notification::CONTEXT_WARNING, $notification->context);
        $this->assertSame(Notification::TYPE_PERSISTENT, $notification->type);
        $this->assertSame(0, $notification->hasNoClear(), 'the warning has no close button');
        $this->assertTrue($notification->raw);
        $this->assertSame(Date::factory('2027-01-01')->getTimestamp(), $notification->expiresAt);

        $this->assertStringContainsString('From the end of December 2026, Microsoft will disable', $notification->message);
        $this->assertMatchesRegularExpression(
            '/<a href="index\.php\?[^"]*module=CoreAdminHome&amp;action=generalSettings[^"]*#\/mailSettings">your email settings<\/a>/',
            $notification->message
        );
        $this->assertStringContainsString(
            'target="_blank" rel="noreferrer noopener" href="https://techcommunity.microsoft.com/blog/exchange/updated-exchange-online-smtp-auth-basic-authentication-deprecation-timeline/4489835">Learn more</a>',
            $notification->message
        );
    }

    public function testDoesNotShowTheWarningFromJanuary2027(): void
    {
        $this->notify();
        $this->assertNotNull($this->getWarning(), 'the warning was not posted to begin with');

        Date::$now = Date::factory('2027-01-01')->getTimestamp();
        $this->startNextRequest();
        $this->notify();

        $this->assertNull($this->getWarning());
    }

    public function testPostsTheWarningWhateverTheSmtpServerIs(): void
    {
        Config::getInstance()->mail = ['host' => 'mail.example.org'] + Config::getInstance()->mail;

        $this->notify();

        $this->assertNotNull($this->getWarning());
    }

    /**
     * @dataProvider getChangesThatHideTheWarning
     */
    public function testCancelsAPostedWarningOnceItNoLongerApplies(array $mail, array $general, bool $isSuperUser): void
    {
        $this->notify();
        $this->assertNotNull($this->getWarning(), 'the warning was not posted to begin with');

        Config::getInstance()->mail = $mail + Config::getInstance()->mail;
        $this->setGeneralConfig($general);
        if (!$isSuperUser) {
            FakeAccess::clearAccess(false, [1], [], 'adminUser');
        }
        $this->notify();

        $this->assertNull($this->getWarning());
    }

    public function getChangesThatHideTheWarning(): iterable
    {
        yield 'login method switched to OAuth 2.0' => [['type' => 'Xoauth2'], [], true];
        yield 'no login method' => [['type' => ''], [], true];
        yield 'mail() instead of SMTP' => [['transport' => ''], [], true];
        yield 'warning turned off in the config file' => [[], ['disabled_notifications' => [self::NOTIFICATION_ID]], true];
        yield 'user without super user access' => [[], [], false];
    }

    /**
     * @dataProvider getSetupsWhereTheEmailSettingsAreNotInTheUi
     */
    public function testPointsToTheConfigFileWhenTheEmailSettingsCannotBeChangedInTheUi(array $general): void
    {
        $this->setGeneralConfig($general);

        $this->notify();

        $notification = $this->getWarning();
        $this->assertNotNull($notification, 'the warning was not posted');
        $this->assertSame('Microsoft 365 email authentication is changing', $notification->title);
        $this->assertStringContainsString(
            'update your authentication method to OAuth 2.0 in the [mail] section of your config/config.ini.php file',
            $notification->message
        );
        $this->assertStringNotContainsString('generalSettings', $notification->message);
        $this->assertStringContainsString(
            'href="https://techcommunity.microsoft.com/blog/exchange/updated-exchange-online-smtp-auth-basic-authentication-deprecation-timeline/4489835">Learn more</a>',
            $notification->message
        );
    }

    public function getSetupsWhereTheEmailSettingsAreNotInTheUi(): iterable
    {
        yield 'general settings admin turned off' => [['enable_general_settings_admin' => 0]];
        yield 'multi server environment' => [['multi_server_environment' => 1]];
    }

    public function testCancelsTheWarningOnMatomoCloud(): void
    {
        $this->notify();
        $this->assertNotNull($this->getWarning(), 'the warning was not posted to begin with');

        $pluginManager = $this->getMockBuilder(PluginManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isPluginActivated'])
            ->getMock();
        $pluginManager->method('isPluginActivated')->willReturnCallback(function (string $name): bool {
            return $name === 'Cloud';
        });
        StaticContainer::getContainer()->set(PluginManager::class, $pluginManager);
        $this->notify();

        $this->assertNull($this->getWarning());
    }

    public function testDoesNotPostTheWarningToTheAnonymousUser(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'anonymous');

        $this->notify();

        $this->assertNull($this->getWarning());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDoesNotPostTheWarningOnARequestAuthenticatedWithATokenAuth(): void
    {
        $this->enableSession();
        $authenticationToken = $this->createMock(AuthenticationToken::class);
        $authenticationToken->method('getAuthToken')->willReturn('a_token_auth');
        StaticContainer::getContainer()->set(AuthenticationToken::class, $authenticationToken);

        $this->notify();

        $this->assertNull($this->getWarning());
        $this->assertSame([], $_SESSION, 'a token_auth request must not remember the warning as shown');
    }

    /**
     * A fresh process, so no other test has turned the session on.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testPostsTheWarningWithoutTurningOnASessionWhenThereIsNone(): void
    {
        $this->notify();

        $this->assertNotNull($this->getWarning());
        $this->assertFalse(Session::isWritable(), 'the warning turned on the session');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAClosedWarningStaysClosedUntilTheNextSession(): void
    {
        $this->enableSession();
        $this->notify();
        $this->assertNotNull($this->getWarning(), 'the warning was not posted to begin with');

        // what CoreHome.markNotificationAsRead does when the close button is clicked
        NotificationManager::cancel(self::NOTIFICATION_ID);
        $this->startNextRequest();
        $this->notify();

        $this->assertNull($this->getWarning(), 'the closed warning came back in the same session');

        // signing out expires the session, so the next sign-in starts with an empty one
        $_SESSION = [];
        $this->startNextRequest();
        $this->notify();

        $this->assertNotNull($this->getWarning(), 'the warning did not come back after signing in again');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAWarningThatWasNotClosedIsKeptAcrossRequests(): void
    {
        $this->enableSession();
        $this->notify();
        $this->startNextRequest();
        $this->notify();

        $this->assertNotNull($this->getWarning());
    }

    /**
     * In CLI this turns on session storage for the rest of the process, so tests that call it run in a process of
     * their own.
     */
    private function enableSession(): void
    {
        new SessionNamespace('ControllerAdminSmtpBasicAuthRetirementTest');
    }

    private function setGeneralConfig(array $values): void
    {
        Config::getInstance()->General = $values + Config::getInstance()->General;
    }

    private function notify(): void
    {
        $method = new ReflectionMethod(ControllerAdmin::class, 'notifyWhenSmtpBasicAuthIsRetiredByMicrosoft');
        $method->setAccessible(true);
        $method->invoke(null);
    }

    /**
     * Looks in memory too, as nothing is read from the session until a posted warning has turned it on.
     */
    private function getWarning(): ?Notification
    {
        return NotificationManager::getPendingInMemoryNotifications()[self::NOTIFICATION_ID]
            ?? NotificationManager::getAllNotificationsToDisplay()[self::NOTIFICATION_ID]
            ?? null;
    }

    /**
     * A new request starts with no notifications in memory and reads them back from the session.
     */
    private function startNextRequest(): void
    {
        NotificationManager::cancelAllNotifications();
    }
}
