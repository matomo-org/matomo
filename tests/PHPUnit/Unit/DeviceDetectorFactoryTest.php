<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\DeviceDetector\DeviceDetectorFactory;

class DeviceDetectorFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $userAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10; rv:33.0) Gecko/20100101 Firefox/33.0";
        $deviceDetection = DeviceDetectorFactory::getInstance($userAgent);
        $expected = array(
            'type' => 'browser',
            'name' => 'Firefox',
            'short_name' => 'FF',
            'version' => '33.0',
            'engine' => 'Gecko',
            'engine_version' => ''
        );
        $this->assertEquals($expected, $deviceDetection->getClient());
    }
}