<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Columns;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\Referrers\Segment;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class Keyword extends Base
{
    protected $columnName = 'referer_keyword';
    protected $columnType = 'VARCHAR(255) NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('referrerKeyword');
        $segment->setName('General_ColumnKeyword');
        $segment->setAcceptedValues('Encoded%20Keyword, keyword');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('General_ColumnKeyword');
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $information = $this->getReferrerInformationFromRequest($request, $visitor);

        if (!empty($information['referer_keyword'])) {
            return Common::mb_substr($information['referer_keyword'], 0, 255);
        }

        return $information['referer_keyword'];
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
