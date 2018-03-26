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
use Piwik\Plugins\ScheduledReports\ReportEmailGenerator\HtmlReportEmailGenerator;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Zend_Mime;

class HtmlReportEmailGeneratorTest extends IntegrationTestCase
{
    /**
     * @var HtmlReportEmailGenerator
     */
    private $testInstance;

    public function setUp()
    {
        parent::setUp();
        $this->testInstance = new HtmlReportEmailGenerator();
    }

    public function test_makeEmail_ReturnsCorrectlyConfiguredEmailInstance()
    {
        $reportDetails = [
            'format' => 'html',
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
        $this->assertEquals(Zend_Mime::MULTIPART_RELATED, $mail->getType());
        $this->assertEquals('report contents', $mail->getBodyHtml()->getContent());
        $this->assertEquals('Content-Type: text/html; charset=utf-8
Content-Transfer-Encoding: quoted-printable
Content-Disposition: inline
', $mail->getBodyHtml()->getHeaders());
    }
}
