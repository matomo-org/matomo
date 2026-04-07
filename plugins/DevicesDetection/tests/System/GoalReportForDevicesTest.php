<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DevicesDetection\tests\System;

use Exception;
use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Plugins\DevicesDetection\Reports\GetModel;
use Piwik\Plugins\DevicesDetection\tests\Fixtures\MultiDeviceGoalConversions;
use Piwik\Policy\CnilPolicy;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 *
 * @group Plugins
 * @group DevicesDetection
 */
class GoalReportForDevicesTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

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
    private function getModelLabelsForSiteRequest(string $idSite): array
    {
        /** @var DataTable|DataTable\Map $report */
        $report = Request::processRequest('DevicesDetection.getModel', [
            'idSite' => $idSite,
            'period' => 'day',
            'date' => self::$fixture->dateTime,
            'flat' => '1',
        ]);

        return array_values($report->getColumn('label'));
    }

    private function isModelReportEnabledForSiteRequest(string $idSite): bool
    {
        $_GET['idSite'] = $idSite;

        return (new GetModel())->isEnabled();
    }

    public function getApiForTesting()
    {
        $idSite   = self::$fixture->idSite;
        $dateTime = self::$fixture->dateTime;

        return [
            ['DevicesDetection.getType', ['idSite'  => $idSite, 'date' => $dateTime]],
            ['DevicesDetection.getOsVersions', ['idSite'  => $idSite, 'date' => $dateTime]],
            ['DevicesDetection.getBrand', ['idSite' => $idSite, 'date' => $dateTime]],
            ['DevicesDetection.getModel', ['idSite' => $idSite, 'date' => $dateTime]],
        ];
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function testGetModelDoesNotReturnDataWhenPolicyEnforced(): void
    {
        CnilPolicy::setActiveStatus(null, true);

        $this->runApiTests('DevicesDetection.getModel', [
            'idSite' => self::$fixture->idSite,
            'date' => self::$fixture->dateTime,
            'testSuffix' => 'compliancePolicyEnforcedSystem',
        ]);

        CnilPolicy::setActiveStatus(null, false);
    }

    public function testGetModelReportIsEnabledForSingleSiteWhenNoneDisabled(): void
    {
        $this->assertTrue($this->isModelReportEnabledForSiteRequest((string) self::$fixture->idSite));
    }

    public function testGetModelReportIsDisabledForSingleSiteWhenRequestedSiteDisabled(): void
    {
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);

        try {
            $this->assertFalse($this->isModelReportEnabledForSiteRequest((string) self::$fixture->idSite));
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
        }
    }

    public function testGetModelReportIsEnabledForSpecificSiteListWhenNoneDisabled(): void
    {
        $this->assertTrue(
            $this->isModelReportEnabledForSiteRequest(self::$fixture->idSite . ',' . self::$fixture->idSite2)
        );
    }

    public function testGetModelReportIsDisabledForSpecificSiteListWhenOneSiteDisabled(): void
    {
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);

        try {
            $this->assertFalse(
                $this->isModelReportEnabledForSiteRequest(self::$fixture->idSite . ',' . self::$fixture->idSite2)
            );
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
        }
    }

    public function testGetModelReportIsDisabledForAllWhenOneSiteDisabled(): void
    {
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);

        try {
            $this->assertFalse($this->isModelReportEnabledForSiteRequest('all'));
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
        }
    }

    public function testGetModelReportIsDisabledForAllWhenAllSitesDisabled(): void
    {
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);
        $this->setSiteCompliancePolicy(self::$fixture->idSite2, true);

        try {
            $this->assertFalse($this->isModelReportEnabledForSiteRequest('all'));
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
            $this->setSiteCompliancePolicy(self::$fixture->idSite2, false);
        }
    }

    public function testGetModelReturnsOnlyAllowedSitesForSpecificSiteList(): void
    {
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);

        try {
            $this->assertSame(
                ['Samsung - Galaxy S5'],
                $this->getModelLabelsForSiteRequest(self::$fixture->idSite . ',' . self::$fixture->idSite2)
            );
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
        }
    }

    public function testGetModelReturnsOnlyAllowedSitesForAll(): void
    {
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);

        try {
            $this->assertSame(['Samsung - Galaxy S5'], $this->getModelLabelsForSiteRequest('all'));
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
        }
    }

    public function testGetModelReturnsErrorWhenSingleRequestedSiteIsDisallowed(): void
    {
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);

        try {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage('Device model report is disabled by compliance policy.');

            $this->getModelLabelsForSiteRequest((string) self::$fixture->idSite);
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
        }
    }

    public function testGetModelReturnsErrorWhenAllRequestedSitesAreDisallowedForSpecificSiteList(): void
    {
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);
        $this->setSiteCompliancePolicy(self::$fixture->idSite2, true);

        try {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage('Device model report is disabled by compliance policy.');

            $this->getModelLabelsForSiteRequest(self::$fixture->idSite . ',' . self::$fixture->idSite2);
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
            $this->setSiteCompliancePolicy(self::$fixture->idSite2, false);
        }
    }

    public function testGetModelReturnsErrorWhenAllRequestedSitesAreDisallowedForAll(): void
    {
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);
        $this->setSiteCompliancePolicy(self::$fixture->idSite2, true);

        try {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage('Device model report is disabled by compliance policy.');

            $this->getModelLabelsForSiteRequest('all');
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
            $this->setSiteCompliancePolicy(self::$fixture->idSite2, false);
        }
    }
}

GoalReportForDevicesTest::$fixture = new MultiDeviceGoalConversions();
