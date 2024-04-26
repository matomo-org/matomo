<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

        $request = new Request('PremiumPlugin', $storageMock);
        $request->create();
    }

    public function testCancel()
    {
        $storageMock = self::createMock(Storage::class);
        $storageMock->method('wasRequested')->willReturn(true);
        $storageMock->expects(self::once())->method('clearStorage');

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

        $request = new Request('PremiumPlugin', $storageMock);
        $request->create();

        self::assertNotNull($sentMail);
        self::assertInstanceOf(RequestTrialNotificationEmail::class, $sentMail);
    }
}
