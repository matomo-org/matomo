<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Columns;

use Piwik\Metrics\Formatter;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;
use DeviceDetector\Parser\Device\AbstractDeviceParser as DeviceParser;

class DeviceType extends Base
{
    protected $columnName = 'config_device_type';
    protected $columnType = 'TINYINT( 100 ) NULL DEFAULT NULL';
    protected $segmentName = 'deviceType';
    protected $type = self::TYPE_ENUM;
    protected $nameSingular = 'DevicesDetection_DeviceType';
    protected $namePlural = 'DevicesDetection_DeviceTypes';

    public function __construct()
    {
        $deviceTypes    = DeviceParser::getAvailableDeviceTypeNames();
        $deviceTypeList = implode(", ", $deviceTypes);

        $this->acceptValues = $deviceTypeList;
    }

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        return \Piwik\Plugins\DevicesDetection\getDeviceTypeLabel($value);
    }

    public function getEnumColumnValues()
    {
        $values = DeviceParser::getAvailableDeviceTypes();
        return array_flip($values);
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

        return $parser->getDevice();
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