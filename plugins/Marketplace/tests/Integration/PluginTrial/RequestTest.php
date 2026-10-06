<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\PluginTrial;

use Piwik\Mail;
use Piwik\Piwik;
use Piwik\Plugins\Marketplace\Emails\RequestTrialNotificationEmail;
use Piwik\Plugins\Marketplace\PluginTrial\Request;
use Piwik\Plugins\Marketplace\PluginTrial\Storage;
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
        $storageMock->method('wasRequested')->willReturn(true);
        $storageMock->expects(self::never())->method('setRequested');
        Piwik::addAction('Marketplace.pluginTrialRequested', function () {
            self::fail('A repeated request must not post the event');
        });

        $request = new Request('PremiumPlugin', $storageMock);
        $request->create();
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
        $storageMock->method('wasRequested')->willReturn(false);
        $storageMock->expects(self::once())->method('setRequested');

        Piwik::addAction('Marketplace.pluginTrialRequested', function (...$args) use (&$eventArgs) {
            $eventArgs = $args;
        });

        $request = new Request('PremiumPlugin', $storageMock);
        $request->create('Premium Plugin');

        self::assertSame(['PremiumPlugin', 'Premium Plugin'], $eventArgs);
        self::assertNotNull($sentMail);
        self::assertInstanceOf(RequestTrialNotificationEmail::class, $sentMail);
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
        $storageMock->method('wasRequested')->willReturn(false);

        (new Request('PremiumPlugin', $storageMock))->create('Premium Plugin');
    }
}
