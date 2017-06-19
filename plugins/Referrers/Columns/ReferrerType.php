<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Columns;

use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class ReferrerType extends Base
{
    protected $columnName = 'referer_type';
    protected $columnType = 'TINYINT(1) UNSIGNED NULL';
    protected $type = self::TYPE_ENUM;
    protected $segmentName = 'referrerType';
    protected $nameSingular = 'Referrers_Type';
    protected $sqlFilterValue = 'Piwik\Plugins\Referrers\getReferrerTypeFromShortName';
    protected $acceptValues = 'direct, search, website, campaign';
    protected $category = 'Referrers_Referrers';

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $information = $this->getReferrerInformationFromRequest($request, $visitor);

        return $information['referer_type'];
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onAnyGoalConversion(Request $request, Visitor $visitor, $action)
    {
        return $this->getValueForRecordGoal($request, $visitor);
    }
}
