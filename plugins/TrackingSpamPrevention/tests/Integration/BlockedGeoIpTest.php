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

    public function testIsExcludedWhenUserCountryPluginIsDisabled()
    {
        \Piwik\Plugin\Manager::getInstance()->deactivatePlugin('UserCountry');
        $this->assertFalse($this->blockedGeoIp->isExcludedProvider('127.0.0.1', 'en'));
    }
}
