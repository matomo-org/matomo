<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
class AdvertisingTest extends \PHPUnit\Framework\TestCase
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

    private $exampleUrl = 'https://matomo.org/test';

    public function setUp(): void
    {
        $this->config = new FakeConfig(['General' => array('piwik_professional_support_ads_enabled' => '1')]);
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
        $this->config->General = ['piwik_professional_support_ads_enabled' => '0'];

        $enabled = $this->advertising->areAdsForProfessionalServicesEnabled();

        $this->assertFalse($enabled);
    }

    public function test_areAdsForProfessionalServicesEnabled_UsingPreviousSettingName()
    {
        $this->config->General = ['piwik_pro_ads_enabled' => '1'];

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
        $link = $this->advertising->addPromoCampaignParametersToUrl($this->exampleUrl, 'MyName', 'Installation_Start', '', 'MySource');

        $this->assertSame($this->exampleUrl . '?mtm_campaign=MyName&mtm_source=MySource&mtm_medium=Installation_Start', $link);
    }

    public function test_addPromoCampaignParametersToUrl_withContentWithoutQuery()
    {
        $link = $this->advertising->addPromoCampaignParametersToUrl($this->exampleUrl, 'MyName', 'Installation_Start',
            'MyContent', 'MySource');

        $this->assertSame($this->exampleUrl . '?mtm_campaign=MyName&mtm_source=MySource&mtm_medium=Installation_Start.MyContent', $link);
    }

    public function test_addPromoCampaignParametersToUrl_withQuery()
    {
        $url = $this->exampleUrl . '?foo=bar';
        $link = $this->advertising->addPromoCampaignParametersToUrl($url, 'MyName', 'Installation_Start', '', 'MySource');

        $this->assertSame($url . '&mtm_campaign=MyName&mtm_source=MySource&mtm_medium=Installation_Start', $link);
    }

    private function buildAdvertising($config)
    {
        return new Advertising($this->pluginManager, $config);
    }
}
