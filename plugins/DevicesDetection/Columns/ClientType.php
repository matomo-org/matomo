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

class ClientType extends Base
{
    protected $columnName = 'config_client_type';
    protected $columnType = 'TINYINT( 1 ) NULL DEFAULT NULL';
    //protected $segmentName = 'clientType';
    protected $type = self::TYPE_ENUM;
    protected $nameSingular = 'DevicesDetection_ClientType';
    protected $namePlural = 'DevicesDetection_ClientTypes';

    public function __construct()
    {
        $clientTypes = \Piwik\Plugins\DevicesDetection\getClientTypeMapping();
        $clientTypeList = implode(", ", $clientTypes);

        $this->acceptValues = $clientTypeList;
    }

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        return \Piwik\Plugins\DevicesDetection\getClientTypeLabel($value);
    }

    public function getEnumColumnValues()
    {
        return \Piwik\Plugins\DevicesDetection\getClientTypeMapping();
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

        $clientTypes = \Piwik\Plugins\DevicesDetection\getClientTypeMapping();

        return array_search($parser->getClient('type'), $clientTypes) ?: null;
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
