<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ScheduledReports\tests\Integration\ReportEmailGenerator;


use Piwik\Plugins\ScheduledReports\GeneratedReport;
use Piwik\Plugins\ScheduledReports\ReportEmailGenerator\AttachedFileReportEmailGenerator;
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
    }

    public function test_makeEmail_AddsSegmentInformation_IfReportIsForSavedSegment()
    {
        $idsegment = APISegmentEditor::getInstance()->add('testsegment', 'browserCode==ff');

        $reportDetails = [
            'format' => 'html',
            'period' => 'week',
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

        $this->assertEquals("=0AScheduledReports_PleaseFindAttachedFile=0AScheduledReports_SentFromX=\n"
            . ' ScheduledReports_SegmentAppliedToReports', $mail->getBodyText()->getContent());
        $this->assertEquals('Content-Type: text/plain; charset=utf-8
Content-Transfer-Encoding: quoted-printable
Content-Disposition: inline
', $mail->getBodyText()->getHeaders());
    }}