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
use Piwik\Plugins\Resolution\Reports\GetResolution;
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

    private function isResolutionReportEnabledForSiteRequest(string $idSite): bool
    {
        $_GET['idSite'] = $idSite;

        return (new GetResolution())->isEnabled();
    }

    public function testGetResolutionReturnsDataForSingleSiteWhenNoneDisabled(): void
    {
        $this->assertSame(['100x100'], $this->getResolutionLabelsForSiteRequest((string) self::$fixture->idSite));
    }

    public function testGetResolutionReportIsEnabledForSingleSiteWhenNoneDisabled(): void
    {
        $this->assertTrue($this->isResolutionReportEnabledForSiteRequest((string) self::$fixture->idSite));
    }

    public function testGetResolutionReportIsDisabledForSingleSiteWhenRequestedSiteDisabled(): void
    {
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);

        try {
            $this->assertFalse($this->isResolutionReportEnabledForSiteRequest((string) self::$fixture->idSite));
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
        }
    }

    public function testGetResolutionReportIsEnabledForSpecificSiteListWhenNoneDisabled(): void
    {
        $this->assertTrue(
            $this->isResolutionReportEnabledForSiteRequest(self::$fixture->idSite . ',' . self::$fixture->idSite2)
        );
    }

    public function testGetResolutionReportIsDisabledForSpecificSiteListWhenOneSiteDisabled(): void
    {
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);

        try {
            $this->assertFalse(
                $this->isResolutionReportEnabledForSiteRequest(self::$fixture->idSite . ',' . self::$fixture->idSite2)
            );
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
        }
    }

    public function testGetResolutionReportIsDisabledForAllWhenOneSiteDisabled(): void
    {
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);

        try {
            $this->assertFalse($this->isResolutionReportEnabledForSiteRequest('all'));
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
        }
    }

    public function testGetResolutionReportIsDisabledForAllWhenAllSitesDisabled(): void
    {
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);
        $this->setSiteCompliancePolicy(self::$fixture->idSite2, true);

        try {
            $this->assertFalse($this->isResolutionReportEnabledForSiteRequest('all'));
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
            $this->setSiteCompliancePolicy(self::$fixture->idSite2, false);
        }
    }

    public function testGetResolutionReturnsOnlyAllowedSitesForSpecificSiteList(): void
    {
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);

        try {
            $this->assertSame(
                ['200x200'],
                $this->getResolutionLabelsForSiteRequest(self::$fixture->idSite . ',' . self::$fixture->idSite2)
            );
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
        }
    }

    public function testGetResolutionReturnsOnlyAllowedSitesForAll(): void
    {
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);

        try {
            $this->assertSame(['200x200'], $this->getResolutionLabelsForSiteRequest('all'));
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
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

    public function testGetResolutionReturnsErrorWhenAllRequestedSitesAreDisallowedForSpecificSiteList(): void
    {
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);
        $this->setSiteCompliancePolicy(self::$fixture->idSite2, true);

        try {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage('Screen resolution report is disabled by compliance policy.');

            $this->getResolutionLabelsForSiteRequest(self::$fixture->idSite . ',' . self::$fixture->idSite2);
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
            $this->setSiteCompliancePolicy(self::$fixture->idSite2, false);
        }
    }

    public function testGetResolutionReturnsErrorWhenAllRequestedSitesAreDisallowedForAll(): void
    {
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);
        $this->setSiteCompliancePolicy(self::$fixture->idSite2, true);

        try {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage('Screen resolution report is disabled by compliance policy.');

            $this->getResolutionLabelsForSiteRequest('all');
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
            $this->setSiteCompliancePolicy(self::$fixture->idSite2, false);
        }
    }
}

ResolutionReportTest::$fixture = new MultiSiteResolutionReport();
