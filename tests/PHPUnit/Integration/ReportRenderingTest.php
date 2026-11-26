<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\ArchiveProcessor\Rules;
use Piwik\CronArchive;
use Piwik\FrontController;
use Piwik\Http\BadRequestException;
use Piwik\Option;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class ReportRenderingTest extends IntegrationTestCase
{
    public function tearDown(): void
    {
        $_GET = [];
        parent::tearDown();
    }

    public function testEcommerceLogIgnoresArrayFilterLimit()
    {
        $idSite = Fixture::createWebsite('2012-01-02 03:04:44', $ecommerce = 1);

        $_GET['idSite'] = $idSite;
        $_GET['date'] = '2012-05-06';
        $_GET['period'] = 'day';
        $_GET['filter_limit'] = ['1'];

        $response = FrontController::getInstance()->dispatch('Ecommerce', 'getEcommerceLog');

        self::assertStringContainsString('dataTableVizVisitorLog', $response);
    }

    /**
     * @dataProvider getArrayDateOrPeriodParams
     */
    public function testSparklinesRejectArrayDateOrPeriod(string $name, array $value)
    {
        $idSite = Fixture::createWebsite('2012-01-02 03:04:44');

        $_GET['idSite'] = $idSite;
        $_GET['date'] = '2012-05-06';
        $_GET['period'] = 'day';
        $_GET[$name] = $value;

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage("The parameter '$name' doesn't have a correct type");

        FrontController::getInstance()->dispatch('Referrers', 'getSparklines');
    }

    public function getArrayDateOrPeriodParams(): iterable
    {
        yield 'period' => ['period', ['day']];
        yield 'date' => ['date', ['yesterday']];
    }

    public function testReportHasCorrectNotificationWhenReportHasNoDataAndArchivingHasNotRunRecently()
    {
        $idSite = Fixture::createWebsite('2012-01-02 03:04:44');
        Option::set(CronArchive::OPTION_ARCHIVING_FINISHED_TS, time() - 120000);
        Option::set(Rules::OPTION_BROWSER_TRIGGER_ARCHIVING, 0);

        $_GET['idSite'] = $idSite;
        $_GET['date'] = '2012-05-06';
        $_GET['period'] = 'day';

        $frontController = FrontController::getInstance();
        $response = $frontController->dispatch('DevicesDetection', 'getBrand');

        self::assertStringContainsString('Diagnostics_NoDataForReportArchivingNotRun', $response);
    }
}
