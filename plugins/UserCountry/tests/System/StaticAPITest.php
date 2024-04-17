<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountry\tests\System;

use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group UserCountry
 * @group ApiTest
 * @group Plugins
 */
class StaticApiTest extends SystemTestCase
{
    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $apiToTest   = array();
        $apiToTest[] = array(array('UserCountry.getCountryCodeMapping'),
                             array(
                                 'language' => 'en',
                             )
        );

        return $apiToTest;
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}
