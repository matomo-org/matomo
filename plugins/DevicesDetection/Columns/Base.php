<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Columns;

use Piwik\DeviceDetectorFactory;
use Piwik\Plugin\Dimension\VisitDimension;

abstract class Base extends VisitDimension
{
    protected function getUAParser($userAgent)
    {
        return DeviceDetectorFactory::getInstance($userAgent);
    }
}
