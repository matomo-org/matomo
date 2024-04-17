<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Mail;
use Piwik\Piwik;
use Piwik\Tests\Framework\TestCase\UnitTestCase;

class MailTest extends UnitTestCase
{
    /**
     * @var Mail[]
     */
    public $sentMails = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->sentMails = [];
    }

    public function tearDown(): void
    {
        $this->sentMails = [];
        parent::tearDown();
    }

    public function getEmailFilenames()
    {
        return array(
            array('January 3 – 9, 2010', 'January 3 - 9, 2010'),
            array('Report <The><< ’s Coves - week January 18 – 24, 2016', 'Report <The><< \'s Coves - week January 18 - 24, 2016'),
        );
    }

    /**
     * @dataProvider getEmailFilenames
     */
    public function test_EmailFilenamesAreSanitised($raw, $expected)
    {
        $mail = new Mail();
        $this->assertEquals($expected, $mail->sanitiseString($raw));
    }

    public function test_abortSendingMail()
    {
        $mail = new Mail();
        $result = $mail->send();

        $this->assertTrue($result);
        $this->assertCount(1, $this->sentMails);

        Piwik::addAction('Mail.shouldSend', function (&$shouldSend, $mail) {
            $shouldSend = false;
        });

        $mail2 = new Mail();
        $result = $mail2->send();

        $this->assertFalse($result);
        $this->assertCount(1, $this->sentMails);
    }

    protected function provideContainerConfig()
    {
        $mockTransport = new class ($this) extends Mail\Transport {
            private $testCase;

            public function __construct(MailTest $mailTest)
            {
                $this->testCase = $mailTest;
            }

            public function send(Mail $mail)
            {
                $this->testCase->sentMails[] = $mail;
                return true;
            }
        };

        return [
            Mail\Transport::class => $mockTransport,
        ];
    }
}
