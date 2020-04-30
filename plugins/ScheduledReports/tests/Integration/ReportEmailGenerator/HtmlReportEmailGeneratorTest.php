<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ScheduledReports\tests\Integration\ReportEmailGenerator;

use Piwik\Mail;
use Piwik\Plugins\ScheduledReports\GeneratedReport;
use Piwik\Plugins\ScheduledReports\ReportEmailGenerator\HtmlReportEmailGenerator;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class HtmlReportEmailGeneratorTest extends IntegrationTestCase
{
    /**
     * @var HtmlReportEmailGenerator
     */
    private $testInstance;

    public function setUp(): void
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
        $this->assertEquals(Mail::CONTENT_TYPE_MULTIPART_RELATED, $mail->ContentType);
        $this->assertEquals('report contents', $mail->getBodyHtml());
    }
}
