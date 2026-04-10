<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Resolution\tests\System;

use Exception;
use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Plugins\Resolution\tests\Fixtures\MultiSiteResolutionReport;
use Piwik\Policy\CnilPolicy;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Plugins
 * @group Resolution
 */
class ResolutionReportTest extends SystemTestCase
{
    public static $fixture = null;

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }

    private function setSiteCompliancePolicy(int $idSite, bool $isActive): void
    {
        CnilPolicy::setActiveStatus($idSite, $isActive);
    }

    /**
     * @return list<string>
     */
    private function getResolutionLabelsForSiteRequest(string $idSite): array
    {
        /** @var DataTable|DataTable\Map $report */
        $report = Request::processRequest('Resolution.getResolution', [
            'idSite' => $idSite,
            'period' => 'day',
            'date' => self::$fixture->dateTime,
            'flat' => '1',
        ]);

        return array_values($report->getColumn('label'));
    }

    /**
     * @return list<string>
     */
    private function getAvailableReportsForSiteRequest(string $idSite): array
    {
        $reports = Request::processRequest('API.getReportMetadata', [
            'idSite' => $idSite,
            'period' => 'day',
            'date' => self::$fixture->dateTime,
        ]);

        return array_map(static function (array $report): string {
            return $report['module'] . '.' . $report['action'];
        }, $reports);
    }

    public function testGetResolutionReturnsDataForSingleSiteWhenNoneDisabled(): void
    {
        $this->assertSame(['100x100'], $this->getResolutionLabelsForSiteRequest((string) self::$fixture->idSite));
    }

    public function testGetResolutionReportMetadataHidesOnlyRelevantReportWhenPolicyEnabledGlobally(): void
    {
        CnilPolicy::setActiveStatus(null, true);

        try {
            $availableReports = $this->getAvailableReportsForSiteRequest((string) self::$fixture->idSite);

            $this->assertContains('Resolution.getConfiguration', $availableReports);
            $this->assertNotContains('Resolution.getResolution', $availableReports);
        } finally {
            CnilPolicy::setActiveStatus(null, false);
        }
    }

    public function testGetResolutionReturnsErrorWhenSingleRequestedSiteIsDisallowed(): void
    {
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);

        try {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage('Screen resolution report is disabled by compliance policy.');

            $this->getResolutionLabelsForSiteRequest((string) self::$fixture->idSite);
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
        }
    }
}

ResolutionReportTest::$fixture = new MultiSiteResolutionReport();
