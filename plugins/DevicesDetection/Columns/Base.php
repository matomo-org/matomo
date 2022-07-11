<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Columns;

use Piwik\Container\StaticContainer;
use Piwik\DeviceDetector\DeviceDetectorFactory;
use Piwik\Plugin\Dimension\VisitDimension;

abstract class Base extends VisitDimension
{
    protected function getUAParser($userAgent, $clientHints)
    {
        return StaticContainer::get(DeviceDetectorFactory::class)->makeInstance($userAgent, $clientHints);
    }
}
