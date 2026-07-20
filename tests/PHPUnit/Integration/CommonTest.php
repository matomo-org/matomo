<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Plugins\PrivacyManager\Settings\CampaignParameterValuesMasked;
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
    public function testGetCampaignParameters(string $policyClass, bool $policyEnabled, array $expectedCampaignParameters)
    {
        $idSite = 1;
        PolicyManager::setPolicyActiveStatus($policyClass, $policyEnabled, $idSite);
        $this->assertSame($expectedCampaignParameters, Common::getCampaignParameters());
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
        yield [CnilPolicy::class, false, $fullCampaignParameters];
        yield [CnilPolicy::class, true, $fullCampaignParameters];
    }

    public function testCampaignPlaceholderHelpers()
    {
        $placeholder = CampaignParameterValuesMasked::DISCARDED_CAMPAIGN_PLACEHOLDER;

        $this->assertSame($placeholder, CampaignParameterValuesMasked::maskValue('newsletter'));
        $this->assertSame('', CampaignParameterValuesMasked::maskValue(''));
        $this->assertTrue(CampaignParameterValuesMasked::isPlaceholderValue($placeholder));
        $this->assertFalse(CampaignParameterValuesMasked::isPlaceholderValue('newsletter'));
        $this->assertSame(
            Piwik::translate('PrivacyManager_CampaignParameterDiscarded'),
            CampaignParameterValuesMasked::formatValue($placeholder)
        );
    }
}
