<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Columns;

use Piwik\Piwik;
use Piwik\Plugins\Referrers\Segment;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class ReferrerType extends Base
{
    protected $columnName = 'referer_type';
    protected $columnType = 'TINYINT(1) UNSIGNED NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('referrerType');
        $segment->setName('Referrers_Type');
        $segment->setSqlFilterValue('Piwik\Plugins\Referrers\getReferrerTypeFromShortName');
        $segment->setAcceptedValues('direct, search, website, campaign');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('Referrers_Type');
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $information = $this->getReferrerInformationFromRequest($request);

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
