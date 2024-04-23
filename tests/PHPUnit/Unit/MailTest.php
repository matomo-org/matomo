<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Mail;

/**
 * @group Core
 */
class MailTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Mail
     */
    public $mail;

    protected function setUp(): void
    {
        $this->mail = new Mail();
    }

    public function testGetRecipients()
    {
        $result = $this->mail->getRecipients();
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testAddTo()
    {
        $recipients = [
            'test_recipient1@innocraft.com' => 'Test Recipient1',
            'test_recipient2@innocraft.com' => 'Test Recipient2',
            'test_recipient3@innocraft.com' => 'Test Recipient3',
        ];
        $result = $this->mail->getRecipients();
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        foreach ($recipients as $address => $name) {
            $this->mail->addTo($address, $name);
        }
        $result = $this->mail->getRecipients();
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertSame($recipients, $result);
    }

    public function testAddToNoName()
    {
        $recipients = [
            'test_recipient1@innocraft.com' => '',
            'test_recipient2@innocraft.com' => '',
            'test_recipient3@innocraft.com' => '',
        ];
        $result = $this->mail->getRecipients();
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        foreach ($recipients as $address => $name) {
            $this->mail->addTo($address);
        }
        $result = $this->mail->getRecipients();
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertSame($recipients, $result);
    }

    public function testGetBccs()
    {
        $result = $this->mail->getBccs();
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testAddBcc()
    {
        $bccs = [
            'test_bcc1@innocraft.com' => 'Test Bcc1',
            'test_bcc2@innocraft.com' => 'Test Bcc2',
            'test_bcc3@innocraft.com' => 'Test Bcc3',
        ];
        $result = $this->mail->getBccs();
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        foreach ($bccs as $address => $name) {
            $this->mail->addBcc($address, $name);
        }
        $result = $this->mail->getBccs();
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertSame($bccs, $result);
    }

    public function testAddBccNoName()
    {
        $bccs = [
            'test_bcc1@innocraft.com' => '',
            'test_bcc2@innocraft.com' => '',
            'test_bcc3@innocraft.com' => '',
        ];
        $result = $this->mail->getBccs();
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        foreach ($bccs as $address => $name) {
            $this->mail->addBcc($address);
        }
        $result = $this->mail->getBccs();
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertSame($bccs, $result);
    }

    public function testClearAllRecipients()
    {
        $recipients = [
            'test_recipient1@innocraft.com' => 'Test Recipient1',
            'test_recipient2@innocraft.com' => 'Test Recipient2',
            'test_recipient3@innocraft.com' => 'Test Recipient3',
        ];
        $result = $this->mail->getRecipients();
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        foreach ($recipients as $address => $name) {
            $this->mail->addTo($address, $name);
        }
        $result = $this->mail->getRecipients();
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertSame($recipients, $result);
        $bccs = [
            'test_bcc1@innocraft.com' => 'Test Bcc1',
            'test_bcc2@innocraft.com' => 'Test Bcc2',
            'test_bcc3@innocraft.com' => 'Test Bcc3',
        ];
        $result = $this->mail->getBccs();
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        foreach ($bccs as $address => $name) {
            $this->mail->addBcc($address, $name);
        }
        $result = $this->mail->getBccs();
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertSame($bccs, $result);
        $this->mail->clearAllRecipients();
        $result = $this->mail->getRecipients();
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $result = $this->mail->getBccs();
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }
}
