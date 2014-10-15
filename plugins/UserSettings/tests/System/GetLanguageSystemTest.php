<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings\tests\System;


use Piwik\Plugins\UserSettings\tests\Fixtures\LanguageFixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * Class GetLanguageSystemTest
 * @package Piwik\Plugins\UserSettings\tests
 * @group GetLanguageSystemTest
 * @group Plugins
 * @group UserSettings
 */
class GetLanguageSystemTest extends SystemTestCase {

    public static $fixture = null;

    public static function getOutputPrefix()
    {
        return '';
    }

    /**
     * @param $api
     * @param $params
     * @dataProvider    getApiForTesting
     * @group           GetLanguageSystemTest
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    /**
     * @return array
     */
    public function getApiForTesting()
    {
        $apiToCall = array(
            "UserSettings.getLanguage",
            "UserSettings.getLanguageCode"
        );

        $apiToTest = array();

        $apiToTest[] = array(
                            $apiToCall,
                            array(
                                'idSite'  => self::$fixture->idSite,
                                'date'    => self::$fixture->dateTime,
                                'periods' => array('day')
                            )
                       );

        return $apiToTest;
    }


    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }

}

GetLanguageSystemTest::$fixture = new LanguageFixture();