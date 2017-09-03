<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class VisitorReturning extends VisitDimension
{
    const IS_RETURNING_CUSTOMER = 2;
    const IS_RETURNING = 1;
    const IS_NEW = 0;

    protected $columnName = 'visitor_returning';
    protected $columnType = 'TINYINT(1) NULL';
    protected $segmentName = 'visitorType';
    protected $nameSingular = 'General_VisitType';
    protected $namePlural = 'General_VisitTypes';
    protected $conversionField = true;
    protected $type = self::TYPE_ENUM;

    public function __construct()
    {
        $this->acceptValues  = 'new, returning, returningCustomer. ';
        $this->acceptValues .= Piwik::translate('General_VisitTypeExample', '"&segment=visitorType==returning,visitorType==returningCustomer"');
        $this->sqlFilterValue = function ($type) {
            if (is_numeric($type)) {
                return $type;
            }
            return $type == "new" ? 0 : ($type == "returning" ? 1 : 2);
        };
    }

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        if ($value === 1 || $value === '1' || $value === 'returning') {
            return Piwik::translate('CoreHome_VisitTypeReturning');
        } elseif ($value === 2 || $value === '2' || $value === 'returningCustomer'){
            return Piwik::translate('CoreHome_VisitTypeReturningCustomer');
        } elseif ($value === 0 || $value === '0' || $value === 'new'){
            return Piwik::translate('General_New');
        }

        return $value;
    }

    public function getEnumColumnValues()
    {
        return array(
            self::IS_RETURNING_CUSTOMER => 'returningCustomer',
            self::IS_RETURNING => 'returning',
            self::IS_NEW => 'new',
        );
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $daysSinceLastOrder = $request->getDaysSinceLastOrder();
        $isReturningCustomer = ($daysSinceLastOrder !== false);

        if ($isReturningCustomer) {
            return self::IS_RETURNING_CUSTOMER;
        }

        $visitCount = $request->getVisitCount();
        $daysSinceFirstVisit = $request->getDaysSinceFirstVisit();
        $daysSinceLastVisit = $request->getDaysSinceLastVisit();

        if ($visitCount > 1 || $visitor->isVisitorKnown() || $daysSinceFirstVisit > 0 || $daysSinceLastVisit > 0) {
            return self::IS_RETURNING;
        }

        return self::IS_NEW;
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