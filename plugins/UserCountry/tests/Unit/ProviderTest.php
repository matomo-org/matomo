<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\tests\Unit;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Piwik\Plugins\UserCountry\LocationProvider;

abstract class ProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return PHPUnit_Framework_MockObject_MockObject|LocationProvider
     */
    protected function getProviderMock()
    {
        return $this->getMockBuilder('\Piwik\Plugins\UserCountry\LocationProvider')
            ->setMethods(array('getId', 'getLocation', 'isAvailable', 'isWorking', 'getSupportedLocationInfo'))
            ->disableOriginalConstructor()
            ->getMock();
    }
} 
