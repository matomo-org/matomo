<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\DevicesDetection\tests\System;

use Piwik\Plugins\DevicesDetection\tests\Fixtures\MultiDeviceGoalConversions;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 *
 * @group Plugins
 * @group DevicesDetection
 */
class GoalReportForDevicesTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }

    public function getApiForTesting()
    {
        $idSite   = self::$fixture->idSite;
        $dateTime = self::$fixture->dateTime;

        return array(
            array('DevicesDetection.getType', array('idSite'  => $idSite, 'date' => $dateTime)),
            array('DevicesDetection.getBrand', array('idSite' => $idSite, 'date' => $dateTime)),
            array('DevicesDetection.getModel', array('idSite' => $idSite, 'date' => $dateTime)),
        );
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }
}

GoalReportForDevicesTest::$fixture = new MultiDeviceGoalConversions();