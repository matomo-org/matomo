<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ScheduledReports\tests\Unit;

use Piwik\Mail;
use Piwik\Plugins\ScheduledReports\GeneratedReport;
use Piwik\Plugins\ScheduledReports\ReportEmailGenerator;

class TestReportEmailGenerator extends ReportEmailGenerator
{
    protected function configureEmail(Mail $mail, GeneratedReport $report)
    {
        // empty
    }
}

class ReportEmailGeneratorTest extends \PHPUnit_Framework_TestCase
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
                0 => 'Piwik Reports <noreply@piwik>',
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