<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\PluginTrial;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Mail;
use Piwik\Piwik;
use Piwik\Plugins\Marketplace\Emails\RequestTrialNotificationEmail;
use Piwik\Plugins\Marketplace\PluginTrial\Request;
use Piwik\Plugins\Marketplace\PluginTrial\Storage;
use Piwik\Plugins\Marketplace\PluginTrial\UnsubscribeToken;
use Piwik\Plugins\Marketplace\UserSettings;
use Piwik\Plugins\UsersManager\Model as UsersModel;
use Piwik\Settings\Storage\UserScopedSettingsAccessManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group PluginTrial
 * @group Plugins
 */
class RequestTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testConstructorThrowsOnInvalidPluginName()
    {
        self::expectException(\Exception::class);

        $storageMock = self::createMock(Storage::class);

        $notification = new Request('Inval%dPlu§1nName', $storageMock);
    }

    public function testCreateAlreadyRequested()
    {
        $storageMock = self::createMock(Storage::class);
        $storageMock->method('wasRequestedByCurrentUser')->willReturn(true);
        $storageMock->expects(self::never())->method('setRequested');
        Piwik::addAction('Marketplace.pluginTrialRequested', function () {
            self::fail('A repeated request must not post the event');
        });

        $request = new Request('PremiumPlugin', $storageMock);
        $request->create();
    }

    public function testCreateWhenAnotherUserAlreadyRequestedPostsTheEventWithoutMailingAgain()
    {
        Fixture::createSuperUser();
        Piwik::addAction('Mail.send', function () {
            self::fail('A plugin with a pending request must not mail super users again');
        });
        Piwik::addAction('Marketplace.pluginTrialRequested', function (...$args) use (&$eventArgs) {
            $eventArgs = $args;
        });

        $storageMock = self::createMock(Storage::class);
        $storageMock->method('wasRequested')->willReturn(true);
        $storageMock->method('wasRequestedByCurrentUser')->willReturn(false);
        $storageMock->expects(self::once())->method('setRequested')->willReturn(Storage::REQUEST_ADDED_TO_PENDING);

        $request = new Request('PremiumPlugin', $storageMock);
        $request->create('Premium Plugin');

        self::assertSame(['PremiumPlugin', 'Premium Plugin'], $eventArgs);
    }

    public function testCreateDoesNothingMoreWhenAConcurrentRequestByTheSameUserWasRecordedFirst()
    {
        Fixture::createSuperUser();
        Piwik::addAction('Mail.send', function () {
            self::fail('A repeated request must not mail super users');
        });
        Piwik::addAction('Marketplace.pluginTrialRequested', function () {
            self::fail('A repeated request must not post the event');
        });

        $storageMock = self::createMock(Storage::class);
        $storageMock->method('wasRequestedByCurrentUser')->willReturn(false);
        $storageMock->expects(self::once())->method('setRequested')->willReturn(Storage::REQUEST_ALREADY_RECORDED);

        (new Request('PremiumPlugin', $storageMock))->create('Premium Plugin');
    }

    public function testCancel()
    {
        $storageMock = self::createMock(Storage::class);
        $storageMock->method('wasRequested')->willReturn(true);
        $storageMock->expects(self::once())->method('setFulfilled');

        $request = new Request('PremiumPlugin', $storageMock);
        $request->cancel();
    }

    public function testCreateSucceedsAndSendsMail()
    {
        Fixture::createSuperUser();

        Piwik::addAction('Mail.send', function (Mail $mail) use (&$sentMail) {
            $sentMail = $mail;
        });

        $storageMock = self::createMock(Storage::class);
        $storageMock->method('wasRequestedByCurrentUser')->willReturn(false);
        $storageMock->expects(self::once())->method('setRequested')->willReturn(Storage::REQUEST_PENDING);

        Piwik::addAction('Marketplace.pluginTrialRequested', function (...$args) use (&$eventArgs) {
            $eventArgs = $args;
        });

        $request = new Request('PremiumPlugin', $storageMock);
        $request->create('Premium Plugin');

        self::assertSame(['PremiumPlugin', 'Premium Plugin'], $eventArgs);
        self::assertNotNull($sentMail);
        self::assertInstanceOf(RequestTrialNotificationEmail::class, $sentMail);
    }

    public function testCreateSkipsSuperUsersWhoTurnedTheEmailsOff()
    {
        Fixture::createSuperUser();
        $usersModel = new UsersModel();
        $usersModel->addUser('optedOut', 'password', 'opted-out@example.org', Date::now()->getDatetime());
        $usersModel->setSuperUserAccess('optedOut', true);
        StaticContainer::get(UserScopedSettingsAccessManager::class)
            ->set('Marketplace', 'optedOut', UserSettings::NOTIFY_ON_PLUGIN_REQUEST, false);

        $recipients = [];
        Piwik::addAction('Mail.send', function (Mail $mail) use (&$recipients) {
            $recipients = array_merge($recipients, array_keys($mail->getRecipients()));
        });

        $this->createRequest();

        self::assertSame(['hello@example.org'], $recipients);
    }

    public function testCreateSkipsSuperUsersWhoseUnsubscribeLinkCannotBeSigned()
    {
        Fixture::createSuperUser();
        $usersModel = new UsersModel();
        $usersModel->addUser('deletedMeanwhile', 'password', 'deleted@example.org', Date::now()->getDatetime());
        $usersModel->setSuperUserAccess('deletedMeanwhile', true);

        // stands in for the user being deleted between the recipient lookup and the send
        StaticContainer::getContainer()->set(UnsubscribeToken::class, new class extends UnsubscribeToken {
            public function generate(string $login): ?string
            {
                return $login === 'deletedMeanwhile' ? null : parent::generate($login);
            }
        });

        $recipients = [];
        Piwik::addAction('Mail.send', function (Mail $mail) use (&$recipients) {
            $recipients = array_merge($recipients, array_keys($mail->getRecipients()));
        });

        $this->createRequest();

        self::assertSame(['hello@example.org'], $recipients);
    }

    public function testCreateSendsMailWhenAnEventObserverFails()
    {
        Fixture::createSuperUser();

        Piwik::addAction('Mail.send', function (Mail $mail) use (&$sentMail) {
            $sentMail = $mail;
        });
        Piwik::addAction('Marketplace.pluginTrialRequested', function () {
            throw new \RuntimeException('observer failed');
        });

        try {
            $this->createRequest();
            self::fail('Expected the observer failure to be rethrown');
        } catch (\RuntimeException $e) {
            self::assertSame('observer failed', $e->getMessage());
        }
        self::assertInstanceOf(RequestTrialNotificationEmail::class, $sentMail);
    }

    public function testCreatePostsTheEventWhenTheMailFails()
    {
        Fixture::createSuperUser();

        Piwik::addAction('Mail.send', function () {
            throw new \RuntimeException('mail failed');
        });
        Piwik::addAction('Marketplace.pluginTrialRequested', function (...$args) use (&$eventArgs) {
            $eventArgs = $args;
        });

        $this->createRequest();
        self::assertSame(['PremiumPlugin', 'Premium Plugin'], $eventArgs);
    }

    private function createRequest(): void
    {
        $storageMock = self::createMock(Storage::class);
        $storageMock->method('wasRequestedByCurrentUser')->willReturn(false);
        $storageMock->method('setRequested')->willReturn(Storage::REQUEST_PENDING);

        (new Request('PremiumPlugin', $storageMock))->create('Premium Plugin');
    }
}
