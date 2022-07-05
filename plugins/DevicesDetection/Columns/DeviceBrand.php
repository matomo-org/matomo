<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Columns;

use DeviceDetector\Parser\Device\AbstractDeviceParser;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class DeviceBrand extends Base
{
    protected $columnName = 'config_device_brand';
    protected $columnType = 'VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL';
    protected $type = self::TYPE_TEXT;
    protected $nameSingular = 'DevicesDetection_DeviceBrand';
    protected $namePlural = 'DevicesDetection_DeviceBrands';
    protected $segmentName = 'deviceBrand';


    public function formatValue($value, $idSite, Formatter $formatter)
    {
        return \Piwik\Plugins\DevicesDetection\getDeviceBrandLabel($value);
    }

    public function __construct()
    {
        $brands = AbstractDeviceParser::$deviceBrands;
        natcasesort ($brands);
        $brandList = implode(", ", $brands);
        $this->acceptValues = $brandList;

        $this->sqlFilter = function ($brand) use ($brandList, $brands) {
            if ($brand == Piwik::translate('General_Unknown')) {
                return '';
            }
            $index = array_search(trim(urldecode($brand)), $brands);
            if ($index === false) {
                throw new \Exception("deviceBrand segment must be one of: $brandList");
            }
            return $index;
        };
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $parser    = $this->getUAParser($request->getUserAgent(), $request->getClientHints());

        return $parser->getBrand();
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onAnyGoalConversion(Request $request, Visitor $visitor, $action)
    {
        return $visitor->getVisitorColumn($this->columnName);
    }
}
