<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin\tests\System;

use Piwik\Config;
use Piwik\Plugins\PrivacyManager\FeatureFlags\PrivacyCompliance;
use Piwik\Policy\CnilPolicy;
use Piwik\Tests\Fixtures\EmptySite;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group CorePluginsAdmin
 * @group ApiTest
 */
class ApiTest extends SystemTestCase
{
    /**
     * @var EmptySite
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $apiToTest = [];
        $apiToTest[] = [['CorePluginsAdmin.getSystemSettings'], []];

        return $apiToTest;
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

    public function testGetSystemSettingsIfFeatureFlagEnabled(): void
    {
        $this->setComplianceFeatureFlag(true);

        $this->runApiTests('CorePluginsAdmin.getSystemSettings', [
            'testSuffix' => '_compliancePolicyFeatureFlagEnabled',
        ]);

        $this->setComplianceFeatureFlag(false);
    }

    public function testGetSiteSettingsIfFeatureFlagEnabledAndPolicyEnforced(): void
    {
        $this->setComplianceFeatureFlag(true);
        CnilPolicy::setActiveStatus(null, true);

        $this->runApiTests('CorePluginsAdmin.getSystemSettings', [
            'testSuffix' => '_compliancePolicyEnforced',
        ]);

        CnilPolicy::setActiveStatus(null, false);
        $this->setComplianceFeatureFlag(false);
    }

    public static function getOutputPrefix()
    {
        return 'CorePluginsAdmin';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

ApiTest::$fixture = new EmptySite();
