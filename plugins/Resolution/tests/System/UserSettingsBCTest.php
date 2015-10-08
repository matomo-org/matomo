<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Resolution\tests\System;

use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Resolution
 * @group Resolution_System
 */
class UserSettingsBCTest extends SystemTestCase
{
    /**
     * @var OneVisitorTwoVisits
     */
    public static $fixture;

    /**
     * @dataProvider getApiForTesting
     */
    public function test_Api($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $dateTime = self::$fixture->dateTime;

        $api = array(
            'UserSettings.getResolution',
            'UserSettings.getConfiguration',
        );

        $result = array();


        foreach ($api as $method) {
            list($module, $action) = explode('.', $method);

            // api test (uses hack to test UserSettings which doesn't exist anymore. we say we're testing
            // against Resolution & overwrite the module & action w/ otherRequestParameters)
            $result[] = array('Resolution.getResolution', array('idSite' => $idSite,
                'date' => $dateTime,
                'periods' => array('day'),
                'testSuffix' => $module . '_' . $method . '_',
                'otherRequestParameters' => array(
                    'method' => $method,
                ),
            ));

            // api metadata tests
            $result[] = array('API.getMetadata', array(
                'idSite' => $idSite,
                'date' => $dateTime,
                'apiModule' => $module,
                'apiAction' => $action,
                'testSuffix' => $module . '_' . $method . '_',
            ));
        }

        return $result;
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

UserSettingsBCTest::$fixture = new OneVisitorTwoVisits();