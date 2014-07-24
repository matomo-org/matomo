<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Columns;

use Piwik\Plugins\Referrers\Segment;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class ReferrerName extends Base
{
    protected $columnName = 'referer_name';
    protected $columnType = 'VARCHAR(70) NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('referrerName');
        $segment->setName('Referrers_ReferrerName');
        $segment->setAcceptedValues('twitter.com, www.facebook.com, Bing, Google, Yahoo, CampaignName');
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
        $referrerUrl = $request->getParam('urlref');
        $currentUrl  = $request->getParam('url');

        $information = $this->getReferrerInformation($referrerUrl, $currentUrl, $request->getIdSite());

        if (!empty($information['referer_name'])) {

            return substr($information['referer_name'], 0, 70);
        }

        return $information['referer_name'];
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
