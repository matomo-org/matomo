<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Columns;

use Piwik\Plugins\Referrers\Segment;
use Piwik\Tracker\Request;

class ReferrerUrl extends Base
{
    protected $fieldName = 'referer_url';
    protected $fieldType = 'TEXT NOT NULL';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('referrerUrl');
        $segment->setName('Live_Referrer_URL');
        $segment->setAcceptValues('http%3A%2F%2Fwww.example.org%2Freferer-page.htm');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return '';
    }

    public function onNewVisit(Request $request, $visit)
    {
        $referrerUrl = $request->getParam('urlref');
        $currentUrl  = $request->getParam('url');

        $information = $this->getReferrerInformation($referrerUrl, $currentUrl, $request->getIdSite());

        return $information['referer_url'];
    }
}
