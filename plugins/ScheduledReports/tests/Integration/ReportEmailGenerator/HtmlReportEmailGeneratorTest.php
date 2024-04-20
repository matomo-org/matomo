<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ScheduledReports\tests\Integration\ReportEmailGenerator;

use PHPMailer\PHPMailer\PHPMailer;
use Piwik\Plugins\ScheduledReports\GeneratedReport;
use Piwik\Plugins\ScheduledReports\ReportEmailGenerator\HtmlReportEmailGenerator;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group HtmlReportEmailGeneratorTest
 */
class HtmlReportEmailGeneratorTest extends IntegrationTestCase
{
    /**
     * @var HtmlReportEmailGenerator
     */
    private $testInstance;

    /**
     * @var PHPMailer
     */
    private $mail;

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
        $mail->addTo('noreply@localhost');
        $mail->send();

        $this->assertEquals('General_Report report - pretty date', $this->mail->Subject);
        $this->assertEquals(PHPMailer::CONTENT_TYPE_MULTIPART_ALTERNATIVE, $this->mail->ContentType);
        $this->assertEquals('report contents', $this->mail->Body);
    }


    public function provideContainerConfig()
    {
        return [
            'observers.global' => \Piwik\DI::add([
                ['Test.Mail.send', \Piwik\DI::value(function (PHPMailer $mail) {
                    $this->mail = $mail;
                    $this->mail->preSend();
                })],
            ]),
        ];
    }
}
