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
                    'encoding' => Mail::ENCODING_8BIT,
                    'content' => 'content 1',
                    'filename' => 'file1.txt',
                ],
                [
                    'cid' => 'file1',
                    'mimeType' => 'mimetype2',
                    'encoding' => Mail::ENCODING_BASE64,
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
                'content 1',
                'file1.txt',
                'file1.txt',
                '8bit',
                'mimetype1',
                true,
                'attachment',
                0
            ],
            [
                'content 2',
                'file2.txt',
                'file2.txt',
                'base64',
                'mimetype2',
                true,
                'inline',
                'file1'
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

        $this->assertEquals('General_Report report - pretty date', $mail->getSubject());
        $this->assertEquals(['test@testytesterson.com'], array_keys($mail->getReplyToAddresses()));
        $header = $mail->createHeader();
        $this->assertStringContainsString('From: TagManager_MatomoTagName <noreply@localhost>', $header);
        $this->assertStringContainsString('Reply-To: test person <test@testytesterson.com>', $header);
        $this->assertEquals([], $mail->getAttachments());
    }
}
