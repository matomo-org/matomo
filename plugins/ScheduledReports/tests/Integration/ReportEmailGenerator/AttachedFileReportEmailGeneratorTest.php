<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ScheduledReports\tests\Integration\ReportEmailGenerator;


use Piwik\Mail;
use Piwik\Plugins\ScheduledReports\GeneratedReport;
use Piwik\Plugins\ScheduledReports\ReportEmailGenerator\AttachedFileReportEmailGenerator;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\SegmentEditor\API as APISegmentEditor;

class AttachedFileReportEmailGeneratorTest extends IntegrationTestCase
{
    /**
     * @var AttachedFileReportEmailGenerator
     */
    private $testInstance;

    public function setUp()
    {
        parent::setUp();

        $this->testInstance = new AttachedFileReportEmailGenerator('.thing', 'generic/thing');

        Fixture::createWebsite('2011-01-01 00:00:00', $ecommerce = 0, 'sitename');
    }

    public function test_makeEmail_ReturnsCorrectlyConfiguredEmailInstance()
    {
        $reportDetails = [
            'format' => 'html',
            'period' => 'day',
            'idsite' => '1',
        ];

        $generatedReport = new GeneratedReport(
            $reportDetails,
            'report',
            'pretty date',
            'report contents',
            []
        );

        $mail = $this->testInstance->makeEmail($generatedReport);
        $mailContent = $this->getMailContent($mail);

        $this->assertStringStartsWith('=0A<html', $mail->getBodyHtml()->getContent());
        $this->assertEquals('General_Report report - pretty date', $mail->getSubject());
        $this->assertContains('ScheduledReports_PleaseFindAttachedFile', $mailContent);
        $this->assertContains('ScheduledReports_SentFromX', $mailContent);
        $this->assertEquals('Content-Type: text/html; charset=utf-8
Content-Transfer-Encoding: quoted-printable
Content-Disposition: inline
', $mail->getBodyHtml()->getHeaders());

        $parts = array_map(function (\Zend_Mime_Part $part) {
            return [
                'content' => $part->getContent(),
                'headers' => $part->getHeaders(),
            ];
        }, $mail->getParts());
        $this->assertEquals([
            [
                'content' => 'cmVwb3J0IGNvbnRlbnRz',
                'headers' => 'Content-Type: generic/thing
Content-Transfer-Encoding: base64
Content-Disposition: inline; filename="General_Report report - pretty date.thing"
',
            ],
        ], $parts);
    }

    public function test_makeEmail_OmitsSentFrom_IfPiwikUrlDoesNotExist()
    {
        $this->testInstance = new AttachedFileReportEmailGenerator('.thing', 'generic/thing', false);

        $reportDetails = [
            'format' => 'html',
            'period' => 'week',
            'idsite' => '1',
        ];

        $generatedReport = new GeneratedReport(
            $reportDetails,
            'report',
            'pretty date',
            'report contents',
            []
        );

        $mail = $this->testInstance->makeEmail($generatedReport);
        $mailContent = $this->getMailContent($mail);

        $this->assertStringStartsWith('=0A<html', $mailContent);
        $this->assertContains('ScheduledReports_PleaseFindAttachedFile', $mailContent);
        $this->assertEquals('Content-Type: text/html; charset=utf-8
Content-Transfer-Encoding: quoted-printable
Content-Disposition: inline
', $mail->getBodyHtml()->getHeaders());
    }

    public function test_makeEmail_AddsSegmentInformation_IfReportIsForSavedSegment()
    {
        $idsegment = APISegmentEditor::getInstance()->add('testsegment', 'browserCode==ff');

        $reportDetails = [
            'format' => 'html',
            'period' => 'week',
            'idsite' => '1',
            'idsegment' => $idsegment,
        ];

        $generatedReport = new GeneratedReport(
            $reportDetails,
            'report',
            'pretty date',
            'report contents',
            []
        );

        $mail = $this->testInstance->makeEmail($generatedReport);
        $mailContent = $this->getMailContent($mail);

        $this->assertStringStartsWith('=0A<html', $mailContent);
        $this->assertContains("ScheduledReports_PleaseFindAttachedFile", $mailContent);
        $this->assertContains('ScheduledReports_SentFromX=', $mailContent);
        $this->assertContains('ScheduledReports_CustomVisitorSegment', $mailContent);
        $this->assertEquals('Content-Type: text/html; charset=utf-8
Content-Transfer-Encoding: quoted-printable
Content-Disposition: inline
', $mail->getBodyHtml()->getHeaders());
    }

    private function getMailContent(Mail $mail)
    {
        return str_replace("=\n", '', $mail->getBodyHtml()->getContent());
    }
}