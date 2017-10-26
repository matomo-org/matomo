<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ScheduledReports\tests\Unit\ReportEmailGenerator;

use Piwik\Plugins\ScheduledReports\GeneratedReport;
use Piwik\Plugins\ScheduledReports\ReportEmailGenerator\AttachedFileReportEmailGenerator;

class AttachedFileReportEmailGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttachedFileReportEmailGenerator
     */
    private $testInstance;

    public function setUp()
    {
        parent::setUp();

        $this->testInstance = new AttachedFileReportEmailGenerator('.thing', 'generic/thing');
    }

    public function test_makeEmail_ReturnsCorrectlyConfiguredEmailInstance()
    {
        $reportDetails = [
            'format' => 'html',
            'period' => 'day',
        ];

        $generatedReport = new GeneratedReport(
            $reportDetails,
            'report',
            'pretty date',
            'report contents',
            []
        );

        $mail = $this->testInstance->makeEmail($generatedReport);

        $this->assertEquals('General_Report report - pretty date', $mail->getSubject());
        $this->assertEquals('=0AScheduledReports_PleaseFindAttachedFile=0AScheduledReports_SentFromX', $mail->getBodyText()->getContent());
        $this->assertEquals('Content-Type: text/plain; charset=utf-8
Content-Transfer-Encoding: quoted-printable
Content-Disposition: inline
', $mail->getBodyText()->getHeaders());

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
        ];

        $generatedReport = new GeneratedReport(
            $reportDetails,
            'report',
            'pretty date',
            'report contents',
            []
        );

        $mail = $this->testInstance->makeEmail($generatedReport);

        $this->assertEquals('=0AScheduledReports_PleaseFindAttachedFile=0A', $mail->getBodyText()->getContent());
        $this->assertEquals('Content-Type: text/plain; charset=utf-8
Content-Transfer-Encoding: quoted-printable
Content-Disposition: inline
', $mail->getBodyText()->getHeaders());
    }

}
