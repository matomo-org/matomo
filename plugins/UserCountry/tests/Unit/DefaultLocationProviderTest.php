<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountry\tests\Unit;

use Piwik\Plugins\UserCountry\LocationProvider\DefaultProvider;

class DefaultLocationProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetCountryFromProvider()
    {
        $locationProvider = $this->getMockBuilder(DefaultProvider::class)->onlyMethods(['getHost'])->getMock();
        $locationProvider->expects($this->once())->method('getHost')->willReturn('p573a336f.dip0.t-ipconnect.de');

        $result = $locationProvider->getLocation(
            [
                'ip' => '123.123.123.123',
                'lang' => 'fr',
            ]
        );

        self::assertEquals('DE', $result[DefaultProvider::COUNTRY_CODE_KEY]);
    }

    public function testGetCountryFromLanguageIfProviderNotAvailable()
    {
        $locationProvider = $this->getMockBuilder(DefaultProvider::class)->onlyMethods(['getHost'])->getMock();
        $locationProvider->expects($this->once())->method('getHost')->willReturn('123.123.123.123');

        $result = $locationProvider->getLocation(
            [
                'ip' => '123.123.123.123',
                'lang' => 'fr',
            ]
        );

        self::assertEquals('FR', $result[DefaultProvider::COUNTRY_CODE_KEY]);
    }
}
