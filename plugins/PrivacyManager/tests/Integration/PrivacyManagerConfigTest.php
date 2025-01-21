<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests;

use Piwik\Option;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\PrivacyManager\ReferrerAnonymizer;
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

        $this->config = new PrivacyManagerConfig();
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

    public function testIpAddressMaskLength()
    {
        $this->assertSame(2, $this->config->ipAddressMaskLength);

        $this->config->ipAddressMaskLength = '19';

        $this->assertSame(19, $this->config->ipAddressMaskLength);
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
        $content = $this->config->setTrackerCacheGeneral(array('existingEntry' => 'test'));

        $expected = array(
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
        );

        $this->assertEquals($expected, $content);
    }

    public function testSetTrackerCacheContentShouldGetValuesFromConfig()
    {
        Option::set('PrivacyManager.ipAddressMaskLength', '232');

        $content = $this->config->setTrackerCacheGeneral(array('existingEntry' => 'test'));

        $this->assertEquals(232, $content['PrivacyManager.ipAddressMaskLength']);
    }
}
