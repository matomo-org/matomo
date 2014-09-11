<?php
/**
 * Created by PhpStorm.
 * User: patryk
 * Date: 10.09.14
 * Time: 14:49
 */

namespace Piwik\Plugins\UserSettings\tests;


use Piwik\Plugins\UserSettings\tests\Fixtures\LanguageFixture;
use Piwik\Tests\IntegrationTestCase;

/**
 * Class GetLanguageIntegrationTest
 * @package Piwik\Plugins\UserSettings\tests
 * @group UserSettings
 * @group GetLanguageIntegrationTest
 * @group Plugins
 */
class GetLanguageIntegrationTest extends IntegrationTestCase {

    public static $fixture = null;

    public static function getOutputPrefix()
    {
        return '';
    }

    /**
     * @param $api
     * @param $params
     * @dataProvider    getApiForTesting
     * @group           GetLanguageIntegrationTest
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
                                'idSite' => self::$fixture->idSite,
                                'date' => self::$fixture->dateTime,
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

GetLanguageIntegrationTest::$fixture = new LanguageFixture();