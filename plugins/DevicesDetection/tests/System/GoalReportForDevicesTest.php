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
use Piwik\Config;
use Piwik\DataTable;
use Piwik\Plugins\DevicesDetection\tests\Fixtures\MultiDeviceGoalConversions;
use Piwik\Plugins\PrivacyManager\FeatureFlags\PrivacyCompliance;
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

    private function setComplianceFeatureFlag(bool $enableFlag): void
    {
        $config = Config::getInstance();
        $featureFlag = new PrivacyCompliance();
        $featureFlagConfig = $featureFlag->getName() . '_feature';

        if ($enableFlag) {
            $config->FeatureFlags = [$featureFlagConfig => 'enabled'];
        } else {
            $config->FeatureFlags = [$featureFlagConfig => 'disabled'];
        }
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
        $this->setComplianceFeatureFlag(true);
        CnilPolicy::setActiveStatus(null, true);

        $this->runApiTests('DevicesDetection.getModel', [
            'idSite' => self::$fixture->idSite,
            'date' => self::$fixture->dateTime,
            'testSuffix' => 'compliancePolicyEnforcedSystem',
        ]);

        CnilPolicy::setActiveStatus(null, false);
        $this->setComplianceFeatureFlag(false);
    }

    public function testGetModelReturnsOnlyAllowedSitesForSpecificSiteList(): void
    {
        $this->setComplianceFeatureFlag(true);
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);

        try {
            $this->assertSame(
                ['Samsung - Galaxy S5'],
                $this->getModelLabelsForSiteRequest(self::$fixture->idSite . ',' . self::$fixture->idSite2)
            );
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
            $this->setComplianceFeatureFlag(false);
        }
    }

    public function testGetModelReturnsOnlyAllowedSitesForAll(): void
    {
        $this->setComplianceFeatureFlag(true);
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);

        try {
            $this->assertSame(['Samsung - Galaxy S5'], $this->getModelLabelsForSiteRequest('all'));
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
            $this->setComplianceFeatureFlag(false);
        }
    }

    public function testGetModelReturnsErrorWhenSingleRequestedSiteIsDisallowed(): void
    {
        $this->setComplianceFeatureFlag(true);
        $this->setSiteCompliancePolicy(self::$fixture->idSite, true);

        try {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage('Device model report is disabled by compliance policy.');

            $this->getModelLabelsForSiteRequest((string) self::$fixture->idSite);
        } finally {
            $this->setSiteCompliancePolicy(self::$fixture->idSite, false);
            $this->setComplianceFeatureFlag(false);
        }
    }
}

GoalReportForDevicesTest::$fixture = new MultiDeviceGoalConversions();
