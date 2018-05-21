<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\ProfessionalServices;

use Piwik\Config;
use Piwik\ProfessionalServices\Advertising;
use Piwik\Tests\Framework\Mock\FakeConfig;
use Piwik\Tests\Framework\Mock\Plugin\Manager;

/**
 * @group ProfessionalServices
 * @group Advertising
 * @group Integration
 */
class AdvertisingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Advertising
     */
    private $advertising;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Manager
     */
    private $pluginManager;

    private $exampleUrl = 'https://piwik.xyz/test';

    public function setUp()
    {
        $this->config = new FakeConfig(array('General' => array('piwik_professional_support_ads_enabled' => '1')));
        $this->pluginManager = new Manager();

        $this->advertising = $this->buildAdvertising($this->config);
    }

    public function test_areAdsForProfessionalServicesEnabled_ActuallyEnabled()
    {
        $enabled = $this->advertising->areAdsForProfessionalServicesEnabled();

        $this->assertTrue($enabled);
    }

    public function test_areAdsForProfessionalServicesEnabled_Disabled()
    {
        $this->config->General = array('piwik_professional_support_ads_enabled' => '0');

        $enabled = $this->advertising->areAdsForProfessionalServicesEnabled();

        $this->assertFalse($enabled);
    }

    public function test_areAdsForProfessionalServicesEnabled_UsingPreviousSettingName()
    {
        $this->config->General = array('piwik_pro_ads_enabled' => '1');

        $enabled = $this->advertising->areAdsForProfessionalServicesEnabled();

        $this->assertTrue($enabled);
    }


    public function test_shouldBeEnabledByDefault()
    {
        $enabled = $this->buildAdvertising(Config::getInstance());

        $this->assertTrue($enabled->areAdsForProfessionalServicesEnabled());
    }

    public function test_addPromoCampaignParametersToUrl_withoutContentWithoutQuery()
    {
        $link = $this->advertising->addPromoCampaignParametersToUrl($this->exampleUrl, 'MyName', 'Installation_Start');

        $this->assertSame($this->exampleUrl . '?pk_campaign=MyName&pk_medium=Installation_Start&pk_source=Piwik_App', $link);
    }

    public function test_addPromoCampaignParametersToUrl_withContentWithoutQuery()
    {
        $link = $this->advertising->addPromoCampaignParametersToUrl($this->exampleUrl, 'MyName', 'Installation_Start', 'MyContent');

        $this->assertSame($this->exampleUrl . '?pk_campaign=MyName&pk_medium=Installation_Start&pk_source=Piwik_App&pk_content=MyContent', $link);
    }

    public function test_addPromoCampaignParametersToUrl_withQuery()
    {
        $url = $this->exampleUrl . '?foo=bar';
        $link = $this->advertising->addPromoCampaignParametersToUrl($url, 'MyName', 'Installation_Start');

        $this->assertSame($url . '&pk_campaign=MyName&pk_medium=Installation_Start&pk_source=Piwik_App', $link);
    }

    private function buildAdvertising($config)
    {
        return new Advertising($this->pluginManager, $config);
    }
}
