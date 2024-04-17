<?php

/**
 * Copyright (C) InnoCraft Ltd - All rights reserved.
 *
 * NOTICE:  All information contained herein is, and remains the property of InnoCraft Ltd.
 * The intellectual and technical concepts contained herein are protected by trade secret or copyright law.
 * Redistribution of this information or reproduction of this material is strictly forbidden
 * unless prior written permission is obtained from InnoCraft Ltd.
 *
 * You shall use this code only in accordance with the license agreement obtained from InnoCraft Ltd.
 *
 * @link https://www.innocraft.com/
 * @license For license details see https://www.innocraft.com/license
 */

namespace Piwik\Plugins\ScheduledReports\tests\Integration;

use PHPMailer\PHPMailer\PHPMailer;
use Piwik\Mail;
use Piwik\Plugins\ScheduledReports\GeneratedReport;
use Piwik\Plugins\ScheduledReports\ReportEmailGenerator;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class TestReportEmailGenerator extends ReportEmailGenerator
{
    protected function configureEmail(Mail $mail, GeneratedReport $report)
    {
        // empty
    }
}

/**
 * @group ReportEmailGeneratorTest
 * @group ScheduledReports
 * @group Plugins
 */
class ReportEmailGeneratorTest extends IntegrationTestCase
{
    /**
     * @var TestReportEmailGenerator
     */
    private $testInstance;

    /**
     * @var PHPMailer
     */
    private $mail;

    public function setUp(): void
    {
        parent::setUp();

        $this->testInstance = new TestReportEmailGenerator();
    }

    public function test_makeEmail_CreatesCorrectlyConfiguredMailInstance()
    {
        $reportDetails = [];

        $generatedReport = new GeneratedReport(
            $reportDetails,
            'report',
            'pretty date',
            'report contents',
            [
                [
                    'mimeType' => 'mimetype1',
                    'content' => 'content 1',
                    'filename' => 'file1.txt',
                ],
                [
                    'cid' => 'file1',
                    'mimeType' => 'mimetype2',
                    'content' => 'content 2',
                    'filename' => 'file2.txt',
                ],
            ]
        );

        $mail = $this->testInstance->makeEmail($generatedReport);

        $this->assertEquals('General_Report report - pretty date', $mail->getSubject());

        $attachments = $mail->getAttachments();
        $this->assertEquals([
            [
                'content' => 'content 1',
                'filename' => 'file1.txt',
                'mimetype' => 'mimetype1',
                'cid' => null
            ],
            [
                'content' => 'content 2',
                'filename' => 'file2.txt',
                'mimetype' => 'mimetype2',
                'cid' => 'file1'
            ],
        ], $attachments);
    }

    public function test_makeEmail_UsesCustomReplyTo_IfSupplied()
    {
        $reportDetails = [];

        $generatedReport = new GeneratedReport(
            $reportDetails,
            'report',
            'pretty date',
            'report contents',
            []
        );

        $mail = $this->testInstance->makeEmail($generatedReport, [
            'email' => 'test@testytesterson.com',
            'login' => 'test person',
        ]);
        $mail->send();

        $this->assertEquals('General_Report report - pretty date', $mail->getSubject());
        $this->assertEquals(['test@testytesterson.com'], array_keys($mail->getReplyTos()));
        $header = $this->mail->createHeader();
        $this->assertStringContainsString('From: TagManager_MatomoTagName <noreply@', $header);
        $this->assertStringContainsString('Reply-To: test person <test@testytesterson.com>', $header);
        $this->assertEquals([], $mail->getAttachments());
    }


    public function provideContainerConfig()
    {
        return [
            'observers.global' => \Piwik\DI::add([
                ['Test.Mail.send', \Piwik\DI::value(function (PHPMailer $mail) {
                    $this->mail = $mail;
                })],
            ]),
        ];
    }
}
