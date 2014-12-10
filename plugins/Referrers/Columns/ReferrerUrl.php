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

class ReferrerUrl extends Base
{
    protected $columnName = 'referer_url';
    protected $columnType = 'TEXT NOT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('referrerUrl');
        $segment->setName('Live_Referrer_URL');
        $segment->setAcceptedValues('http%3A%2F%2Fwww.example.org%2Freferer-page.htm');
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
        $information = $this->getReferrerInformationFromRequest($request);

        return $information['referer_url'];
    }
}
