<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DevicesDetection\tests\System;

use Piwik\Config;
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
}

GoalReportForDevicesTest::$fixture = new MultiDeviceGoalConversions();
