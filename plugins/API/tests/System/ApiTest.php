<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests\System;

use Piwik\Cache;
use Piwik\Config;
use Piwik\Plugins\PrivacyManager\FeatureFlags\PrivacyCompliance;
use Piwik\Plugins\SitesManager\tests\Fixtures\ManySites;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group API
 * @group ApiTest
 * @group Plugins
 */
class ApiTest extends SystemTestCase
{
    /**
     * @var ManySites
     */
    public static $fixture = null; // initialized below class definition

    public function setUp(): void
    {
        parent::setUp();
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

    public function testGetSegmentsMetadataIfFeatureFlagEnabled(): void
    {
        $this->setComplianceFeatureFlag(true);

        $this->runApiTests('API.getSegmentsMetadata', [
            'testSuffix' => '_compliancePolicyFeatureFlagEnabled',
            'otherRequestParameters' => [
                'idSite' => '1',
            ],
        ]);

        $this->setComplianceFeatureFlag(false);
    }

    public function testGetSegmentsMetadataIfFeatureFlagEnabledAndPolicyEnforced(): void
    {
        Cache::getTransientCache()->flushAll();
        $this->setComplianceFeatureFlag(true);
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true);

        $this->runApiTests('API.getSegmentsMetadata', [
            'testSuffix' => '_compliancePolicyEnforced',
            'otherRequestParameters' => [
                'idSite' => '1',
            ],
        ]);

        $this->setComplianceFeatureFlag(false);
    }
}

ApiTest::$fixture = new ManySites();
