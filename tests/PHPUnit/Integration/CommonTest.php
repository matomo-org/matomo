<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Common
 */
class CommonTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Fixture::createWebsite('2014-01-01 00:00:00');
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @dataProvider getExpectedCampaignParameters
     */
    public function testGetCampaignParameters(string $policyClass, bool $policyEnabled, bool $skipCompliancePolicyCheck, array $expectedCampaignParameters)
    {
        $container = StaticContainer::getContainer();
        $container->get(Config::class)->FeatureFlags = ['PrivacyCompliance_feature' => 'enabled'];

        $idSite = 1;
        PolicyManager::setPolicyActiveStatus($policyClass, $policyEnabled, $idSite);
        $this->assertSame($expectedCampaignParameters, Common::getCampaignParameters($idSite, $skipCompliancePolicyCheck));
    }

    public function getExpectedCampaignParameters()
    {
        $fullCampaignParameters = [
           [
            'pk_cpn',
            'pk_campaign',
            'piwik_campaign',
            'mtm_campaign',
            'matomo_campaign',
            'utm_campaign',
            'utm_source',
            'utm_medium',
           ],
           [
            'pk_kwd',
            'pk_keyword',
            'piwik_kwd',
            'mtm_kwd',
            'mtm_keyword',
            'matomo_kwd',
            'utm_term',
           ],
        ];
        $emptyCampaignParameters = [[], []];

        yield [CnilPolicy::class, false, false, $fullCampaignParameters];
        yield [CnilPolicy::class, false, true, $fullCampaignParameters];
        yield [CnilPolicy::class, true, false, $emptyCampaignParameters];
        yield [CnilPolicy::class, true, true, $fullCampaignParameters];
    }
}
