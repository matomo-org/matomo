<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Columns;

use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class DeviceModel extends Base
{
    protected $columnName = 'config_device_model';
    protected $columnType = 'VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL';
    protected $type = self::TYPE_TEXT;
    protected $nameSingular = 'DevicesDetection_DeviceModel';
    protected $namePlural = 'DevicesDetection_DeviceModels';
    protected $segmentName = 'deviceModel';
    protected $acceptValues = 'iPad, Nexus 5, Galaxy S5, Fire TV, etc.';

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $parser    = $this->getUAParser($request->getUserAgent(), $request->getClientHints());

        $model = $parser->getModel();

        if (!empty($model)) {
            return $model;
        }

        $deviceType = $parser->getDeviceName();

        if (!empty($deviceType)) {
            return 'generic ' . $deviceType;
        }

        if ($parser->isMobile()) {
            return 'generic mobile';
        }

        return '';
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
