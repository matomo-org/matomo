<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountry\tests\Unit;

use Piwik\Container\StaticContainer;
use Piwik\Intl\Data\Provider\RegionDataProvider;

require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/UserCountry.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/functions.php';

class UserCountryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group Plugins
     */
    public function testGetFlagFromCode()
    {
        $flag = \Piwik\Plugins\UserCountry\getFlagFromCode("us");
        $this->assertEquals(basename($flag), "us.png");
    }

    /**
     * @group Plugins
     */
    public function testGetFlagFromInvalidCode()
    {
        $flag = \Piwik\Plugins\UserCountry\getFlagFromCode("foo");
        $this->assertEquals(basename($flag), "xx.png");
    }

    /**
     * @group Plugins
     */
    public function testFlagsAndContinents()
    {
        /** @var RegionDataProvider $dataProvider */
        $dataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider');

        $continents = $dataProvider->getContinentList();
        $countries = $dataProvider->getCountryList(true);

        // Get list of existing flag icons
        $flags = scandir(PIWIK_PATH_TEST_TO_ROOT . '/plugins/Morpheus/icons/dist/flags/');

        // Get list of countries
        foreach ($countries as $country => $continent) {
            // test continent
            self::assertTrue(in_array($continent, $continents));

            // test flag
            self::assertTrue(in_array($country . '.png', $flags));
        }

        foreach ($flags as $filename) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }

            $country = substr($filename, 0, strpos($filename, '.png'));

            // test country
            $this->assertArrayHasKey($country, $countries, $filename);
        }
    }
}
