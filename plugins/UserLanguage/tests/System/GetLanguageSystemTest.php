<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserLanguage\tests\System;


use Piwik\Plugins\UserLanguage\tests\Fixtures\LanguageFixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * Class GetLanguageSystemTest
 * @package Piwik\Plugins\UserLanguage\tests
 * @group GetLanguageSystemTest
 * @group Plugins
 * @group UserLanguage
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
            "UserLanguage.getLanguage",
            "UserLanguage.getLanguageCode"
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