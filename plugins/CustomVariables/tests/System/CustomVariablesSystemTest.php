<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomVariables\tests\System;

use Piwik\Plugins\CustomVariables\tests\Fixtures\VisitWithManyCustomVariables;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group CustomVariables
 * @group CustomVariablesSystemTest
 * @group Plugins
 */
class CustomVariablesSystemTest extends SystemTestCase
{
    /**
     * @var VisitWithManyCustomVariables
     */
    public static $fixture = null; // initialized below class definition

    public static function getOutputPrefix()
    {
        return 'CustomVariablesSystemTest';
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $apiToCall = array('CustomVariables.getCustomVariables', 'Live.getLastVisitsDetails');

        return array(
            array($apiToCall, array(
                'idSite'  => self::$fixture->idSite,
                'date'    => self::$fixture->dateTime,
                'periods' => array('day'),
            )),
        );
    }

    /**
     * Path where expected/processed output files are stored.
     */
    public static function getPathToTestDirectory()
    {
        return __DIR__;
    }
}

CustomVariablesSystemTest::$fixture = new VisitWithManyCustomVariables();