<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountry\tests\Integration;

use Piwik\Plugin\Manager;
use Piwik\Plugins\UserCountry\LocationProvider\DefaultProvider;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group bla
 */
class DefaultLocationProviderTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Manager::getInstance()->activatePlugin('Provider');
    }

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

        self::assertEquals('de', $result[DefaultProvider::COUNTRY_CODE_KEY]);
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

        self::assertEquals('fr', $result[DefaultProvider::COUNTRY_CODE_KEY]);
    }
}
