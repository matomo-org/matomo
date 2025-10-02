<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Integration;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Option;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\PrivacyManager\API;
use Piwik\Plugins\PrivacyManager\ReferrerAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Plugins
 */
class PrivacyManagerConfigTest extends IntegrationTestCase
{
    /**
     * @var PrivacyManagerConfig
     */
    private $config;

    public function setUp(): void
    {
        parent::setUp();
        Fixture::createWebsite('2024-01-01 00:00:00');
        Fixture::createWebsite('2025-01-01 00:00:00');

        $this->config = new PrivacyManagerConfig();
    }

    private function setConfigSiteId(?int $siteId): void
    {
        $this->config->setIdSite($siteId);
    }

    public function testUseAnonymizedIpForVisitEnrichment()
    {
        $this->assertFalse($this->config->useAnonymizedIpForVisitEnrichment);

        $this->config->useAnonymizedIpForVisitEnrichment = true;

        $this->assertTrue($this->config->useAnonymizedIpForVisitEnrichment);

        $this->config->useAnonymizedIpForVisitEnrichment = false;

        $this->assertFalse($this->config->useAnonymizedIpForVisitEnrichment);
    }

    public function testDoNotTrackEnabled()
    {
        $this->assertFalse($this->config->doNotTrackEnabled);

        $this->config->doNotTrackEnabled = true;

        $this->assertTrue($this->config->doNotTrackEnabled);

        $this->config->doNotTrackEnabled = false;

        $this->assertFalse($this->config->doNotTrackEnabled);
    }

    public function testIpAnonymizerEnabled()
    {
        $this->assertTrue($this->config->ipAnonymizerEnabled);

        $this->config->ipAnonymizerEnabled = false;

        $this->assertFalse($this->config->ipAnonymizerEnabled);
    }

    public function testIpAnonymizerEnabledCnilPolicyDisabled()
    {
        $container = StaticContainer::getContainer();
        $container->get(Config::class)->FeatureFlags = ['PrivacyCompliance_feature' => 'enabled'];

        API::getInstance()->setComplianceStatus('all', 'cnil_v1', $enabled = false);
        $this->assertTrue($this->config->ipAnonymizerEnabled);

        $this->config->ipAnonymizerEnabled = false;
        $this->assertFalse($this->config->ipAnonymizerEnabled);

        $this->setConfigSiteId(2);
        // site specific value missing, fallback to global
        $this->assertFalse($this->config->ipAnonymizerEnabled);
    }

    public function testIpAnonymizerEnabledCnilPolicyEnabled()
    {
        $container = StaticContainer::getContainer();
        $container->get(Config::class)->FeatureFlags = ['PrivacyCompliance_feature' => 'enabled'];

        API::getInstance()->setComplianceStatus('all', 'cnil_v1', $enabled = true);
        $this->assertTrue($this->config->ipAnonymizerEnabled);

        $this->config->ipAnonymizerEnabled = false;
        $this->assertTrue($this->config->ipAnonymizerEnabled);

        $this->setConfigSiteId(2);
        // site specific value missing, fallback to global previously set to false, but policy overridden to true again
        $this->assertTrue($this->config->ipAnonymizerEnabled);

        $this->config->ipAnonymizerEnabled = false;
        $this->assertTrue($this->config->ipAnonymizerEnabled);
    }

    public function testIpAddressMaskLength()
    {
        $this->assertSame(2, $this->config->ipAddressMaskLength);

        $this->config->ipAddressMaskLength = 19;

        $this->assertSame(19, $this->config->ipAddressMaskLength);
    }

    public function testIpAddressMaskLengthCnilPolicyDisabled()
    {
        $this->setConfigSiteId(null);

        $container = StaticContainer::getContainer();
        $container->get(Config::class)->FeatureFlags = ['PrivacyCompliance_feature' => 'enabled'];

        API::getInstance()->setComplianceStatus('all', 'cnil_v1', $enabled = false);
        $this->assertSame(2, $this->config->ipAddressMaskLength);

        $this->config->ipAddressMaskLength = 1;
        $this->assertSame(1, $this->config->ipAddressMaskLength);

        $this->config->ipAddressMaskLength = 3;
        $this->setConfigSiteId(2);
        // site specific value missing, fallback to global
        $this->assertSame(3, $this->config->ipAddressMaskLength);

        // set weaker value than the policy requires
        $this->config->ipAddressMaskLength = 1;
        $this->assertSame(1, $this->config->ipAddressMaskLength);
    }

    public function testIpAddressMaskLengthCnilPolicyEnabled()
    {
        $container = StaticContainer::getContainer();
        $container->get(Config::class)->FeatureFlags = ['PrivacyCompliance_feature' => 'enabled'];

        API::getInstance()->setComplianceStatus('all', 'cnil_v1', $enabled = true);
        $this->assertSame(2, $this->config->ipAddressMaskLength);

        $this->config->ipAddressMaskLength = 1;
        $this->assertSame(2, $this->config->ipAddressMaskLength);

        $this->setConfigSiteId(2);
        // set stronger value than the policy requires
        $this->config->ipAddressMaskLength = 3;
        $this->assertSame(3, $this->config->ipAddressMaskLength);

        // set weaker value than the policy requires
        $this->config->ipAddressMaskLength = 1;
        $this->assertSame(2, $this->config->ipAddressMaskLength);
    }

    public function testAnonymizeOrderId()
    {
        $this->assertFalse($this->config->anonymizeOrderId);

        $this->config->anonymizeOrderId = true;

        $this->assertTrue($this->config->anonymizeOrderId);
    }

    public function testAnonymizeUserId()
    {
        $this->assertFalse($this->config->anonymizeUserId);

        $this->config->anonymizeUserId = true;

        $this->assertTrue($this->config->anonymizeUserId);
    }

    public function testAnonymizeReferrer()
    {
        $this->assertSame('', $this->config->anonymizeReferrer);

        $this->config->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_PATH;

        $this->assertSame(ReferrerAnonymizer::EXCLUDE_PATH, $this->config->anonymizeReferrer);
    }

    public function testSetTrackerCacheContent()
    {
        $trackerCache = ['existingEntry' => 'test'];
        $content = $this->config->setTrackerCache($trackerCache);

        $expected = [
            'existingEntry' => 'test',
            'PrivacyManager.ipAddressMaskLength' => 2,
            'PrivacyManager.ipAnonymizerEnabled' => true,
            'PrivacyManager.doNotTrackEnabled'   => false,
            'PrivacyManager.anonymizeUserId'     => false,
            'PrivacyManager.anonymizeOrderId'    => false,
            'PrivacyManager.anonymizeReferrer'   => '',
            'PrivacyManager.useAnonymizedIpForVisitEnrichment' => false,
            'PrivacyManager.forceCookielessTracking' => false,
            'PrivacyManager.randomizeConfigId' => false,
        ];

        $this->assertEquals($expected, $content);
    }

    public function testSetTrackerCacheContentShouldGetValuesFromConfig()
    {
        Option::set('PrivacyManager.ipAddressMaskLength', '232');

        $trackerCache = ['existingEntry' => 'test'];
        $content = $this->config->setTrackerCache($trackerCache);

        $this->assertEquals(232, $content['PrivacyManager.ipAddressMaskLength']);
    }

    public function testSetTrackerCacheContentShouldGetValuesFromConfigForSite()
    {
        Option::set('PrivacyManager.idSite(1).ipAddressMaskLength', '345');

        $trackerCache = ['existingEntry' => 'test'];
        $this->config->setIdSite(1);
        $content = $this->config->setTrackerCache($trackerCache);

        $this->assertEquals(345, $content['PrivacyManager.ipAddressMaskLength']);
    }

    public function testSetTrackerCacheContentForSiteShouldFallbackToGlobalSettings()
    {
        Option::set('PrivacyManager.anonymizeReferrer', ReferrerAnonymizer::EXCLUDE_QUERY);

        $trackerCache = ['existingEntry' => 'test'];
        $this->config->setIdSite(1);
        $content = $this->config->setTrackerCache($trackerCache);

        $this->assertEquals(ReferrerAnonymizer::EXCLUDE_QUERY, $content['PrivacyManager.anonymizeReferrer']);
    }

    public function testDirectSettingGlobalConfigIsReflectedViaFallbackForSite()
    {
        $this->config->setIdSite(null);
        $this->config->randomizeConfigId = true;
        $this->config->setIdSite(2);

        $this->assertEquals(true, $this->config->randomizeConfigId);
    }
}
