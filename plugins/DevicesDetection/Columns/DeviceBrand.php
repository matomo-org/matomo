<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Columns;

use DeviceDetector\Parser\Device\DeviceParserAbstract;
use Piwik\Piwik;
use Piwik\Plugins\DevicesDetection\Segment;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class DeviceBrand extends Base
{
    protected $columnName = 'config_device_brand';
    protected $columnType = 'VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL';

    public function getName()
    {
        return Piwik::translate('DevicesDetection_DeviceBrand');
    }

    protected function configureSegments()
    {
        $brands = DeviceParserAbstract::$deviceBrands;
        $brandList = implode(", ", $brands);

        $segment = new Segment();
        $segment->setSegment('deviceBrand');
        $segment->setName('DevicesDetection_DeviceBrand');
        $segment->setAcceptedValues($brandList);
        $segment->setSqlFilter(function ($brand) use ($brandList, $brands) {
            if ($brand == Piwik::translate('General_Unknown')) {
                return '';
            }
            $index = array_search(trim(urldecode($brand)), $brands);
            if ($index === false) {
                throw new \Exception("deviceBrand segment must be one of: $brandList");
            }
            return $index;
        });
        $this->addSegment($segment);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $userAgent = $request->getUserAgent();
        $parser    = $this->getUAParser($userAgent);

        return $parser->getBrand();
    }
}
