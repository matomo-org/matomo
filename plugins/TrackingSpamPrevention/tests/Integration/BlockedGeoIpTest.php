<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\tests\Integration;

use Piwik\Plugins\TrackingSpamPrevention\BlockedGeoIp;
use Piwik\Plugins\TrackingSpamPrevention\SystemSettings;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group TrackingSpamPrevention
 * @group BlockedGeoIpTest
 * @group Plugins
 */
class BlockedGeoIpTest extends IntegrationTestCase
{
    /**
     * @var BlockedGeoIp
     */
    private $blockedGeoIp;

    public function setUp(): void
    {
        parent::setUp();

        $settings = new SystemSettings();
        $settings->cloudBlockingMode->setValue(SystemSettings::CLOUD_BLOCKING_CUSTOM_LIST);
        $settings->organisationBlockList->setValue(['mytest']);

        $this->blockedGeoIp = new BlockedGeoIp($settings);
    }

    public function testDetectLocation()
    {
        $this->assertEquals([  'country_code' => 'xx',
        'continent_code' => 'unk',
        'continent_name' => 'General_Unknown',
        'country_name' => 'General_Unknown'], $this->blockedGeoIp->detectLocation('127.0.0.1', 'en'));
    }

    public function testIsExcludedCountryNoCountriesGiven()
    {
        $this->assertFalse($this->blockedGeoIp->isExcludedCountry('127.0.0.1', 'en', [], []));
    }

    public function testIsExcludedCountryExcludedCountriesGiven()
    {
        // this IP matches country "xx"
        $this->assertTrue($this->blockedGeoIp->isExcludedCountry('127.0.0.1', 'en', ['fr', 'xx'], []));
        $this->assertFalse($this->blockedGeoIp->isExcludedCountry('127.0.0.1', 'en', ['de', 'nz'], []));
    }

    public function testIsExcludedCountryIncludedCountriesGiven()
    {
        // this IP matches country "xx"
        $this->assertFalse($this->blockedGeoIp->isExcludedCountry('127.0.0.1', 'en', [], ['fr', 'xx']));
        $this->assertTrue($this->blockedGeoIp->isExcludedCountry('127.0.0.1', 'en', [], ['de', 'nz']));
    }

    public function testIsExcluded()
    {
        $this->assertFalse($this->blockedGeoIp->isExcludedProvider('127.0.0.1', 'en'));
    }

    public function testIsExcludedProviderMatchesTheCustomList()
    {
        $settings = new SystemSettings();
        $settings->cloudBlockingMode->setValue(SystemSettings::CLOUD_BLOCKING_CUSTOM_LIST);
        $settings->organisationBlockList->setValue(['mytest']);

        $this->assertTrue($this->makeGeoIpDetecting('MyTest Hosting Ltd', $settings)->isExcludedProvider('127.0.0.1', 'en'));
    }

    public function testIsExcludedProviderMatchesTheDefaultList()
    {
        $settings = new SystemSettings();
        $settings->cloudBlockingMode->setValue(SystemSettings::CLOUD_BLOCKING_DEFAULT_LIST);
        $settings->organisationBlockList->setValue(['mytest']);

        // the custom list is ignored, the default list is matched instead
        $this->assertFalse($this->makeGeoIpDetecting('MyTest Hosting Ltd', $settings)->isExcludedProvider('127.0.0.1', 'en'));
        $this->assertTrue($this->makeGeoIpDetecting('Hetzner Online GmbH', $settings)->isExcludedProvider('127.0.0.1', 'en'));
    }

    public function testIsExcludedProviderIgnoresOrganisationsWhenBlockingIsOff()
    {
        $settings = new SystemSettings();
        $settings->cloudBlockingMode->setValue(SystemSettings::CLOUD_BLOCKING_OFF);
        $settings->organisationBlockList->setValue(['mytest']);

        $this->assertFalse($this->makeGeoIpDetecting('MyTest Hosting Ltd', $settings)->isExcludedProvider('127.0.0.1', 'en'));
    }

    public function testIsExcludedWhenUserCountryPluginIsDisabled()
    {
        \Piwik\Plugin\Manager::getInstance()->deactivatePlugin('UserCountry');
        $this->assertFalse($this->blockedGeoIp->isExcludedProvider('127.0.0.1', 'en'));
    }

    /**
     * The test geolocation provider reports no organisation, so the organisation has to be supplied
     * to exercise the matching at all.
     */
    private function makeGeoIpDetecting(string $organisation, SystemSettings $settings): BlockedGeoIp
    {
        return new class ($settings, $organisation) extends BlockedGeoIp {
            private $organisation;

            public function __construct(SystemSettings $settings, string $organisation)
            {
                parent::__construct($settings);
                $this->organisation = $organisation;
            }

            public function detectLocation($ip, $language)
            {
                return [LocationProvider::ORG_KEY => $this->organisation];
            }
        };
    }
}
