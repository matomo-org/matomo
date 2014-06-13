<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Columns;

use Piwik\Piwik;
use Piwik\Plugin\Segment;
use Piwik\Tracker\Request;
use DeviceDetector;
use Exception;

class DeviceType extends Base
{
    protected $fieldName = 'config_device_type';
    protected $fieldType = 'TINYINT( 100 ) NULL DEFAULT NULL';

    protected function init()
    {
        $deviceTypeList = implode(", ", DeviceDetector::$deviceTypes);

        $segment = new Segment();
        $segment->setCategory('General_Visit');
        $segment->setSegment('deviceType');
        $segment->setName('DevicesDetection_DeviceType');
        $segment->setAcceptValues($deviceTypeList);
        $segment->setSqlFilter(function ($type) use ($deviceTypeList) {
            $index = array_search(strtolower(trim(urldecode($type))), DeviceDetector::$deviceTypes);
            if ($index === false) {
                throw new Exception("deviceType segment must be one of: $deviceTypeList");
            }
            return $index;
        });

        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('DevicesDetection_DeviceType');
    }

    public function onNewVisit(Request $request, $visit)
    {
        $userAgent = $request->getUserAgent();
        $parser    = $this->getUAParser($userAgent);

        return $parser->getDevice();
    }
}
