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

class ReferrerName extends Base
{
    protected $fieldName = 'referer_name';
    protected $fieldType = 'VARCHAR(70) NULL';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('referrerName');
        $segment->setName('Referrers_ReferrerName');
        $segment->setAcceptValues('twitter.com, www.facebook.com, Bing, Google, Yahoo, CampaignName');
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

        if (!empty($information['referer_name'])) {

            return substr($information['referer_name'], 0, 70);
        }

        return $information['referer_name'];
    }
}
