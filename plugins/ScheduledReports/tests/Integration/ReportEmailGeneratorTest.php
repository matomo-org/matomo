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


class ReportEmailGeneratorTest extends IntegrationTestCase
{
    /**
     * @var TestReportEmailGenerator
     */
    private $testInstance;

    public function setUp()
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
                    'encoding' => 'utf-8',
                    'content' => 'content 1',
                    'filename' => 'file1.txt',
                    'cid' => 'cid1',
                ],
                [
                    'mimeType' => 'mimetype2',
                    'encoding' => 'utf-8',
                    'content' => 'content 2',
                    'filename' => 'file2.txt',
                    'cid' => 'cid2',
                ],
            ]
        );

        $mail = $this->testInstance->makeEmail($generatedReport);

        $this->assertEquals('General_Report report - pretty date', $mail->getSubject());

        $parts = array_map(function (\Zend_Mime_Part $part) {
            return [
                'content' => $part->getContent(),
                'headers' => $part->getHeaders(),
            ];
        }, $mail->getParts());
        $this->assertEquals([
            [
                'content' => 'content 1',
                'headers' => 'Content-Type: mimetype1
Content-Transfer-Encoding: utf-8
Content-ID: <cid1>
Content-Disposition: inline; filename="file1.txt"
',
            ],
            [
                'content' => 'content 2',
                'headers' => 'Content-Type: mimetype2
Content-Transfer-Encoding: utf-8
Content-ID: <cid2>
Content-Disposition: inline; filename="file2.txt"
',
            ],
        ], $parts);
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
            'alias' => 'test person',
        ]);

        $this->assertEquals('General_Report report - pretty date', $mail->getSubject());
        $this->assertEquals('test@testytesterson.com', $mail->getReplyTo());
        $this->assertEquals([
            'From' => [
                0 => 'ScheduledReports_PiwikReports <noreply@localhost>',
                'append' => true,
            ],
            'Subject' => [
                0 => 'General_Report report - pretty date',
            ],
            'Reply-To' => [
                0 => 'test person <test@testytesterson.com>',
                'append' => true,
            ],
        ], $mail->getHeaders());
        $this->assertEquals([], $mail->getParts());
    }
}
