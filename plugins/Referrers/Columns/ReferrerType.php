<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Columns;

use Piwik\Piwik;
use Piwik\Plugins\Referrers\Segment;
use Piwik\Tracker\Request;

class ReferrerType extends Base
{
    protected $fieldName = 'referer_type';
    protected $fieldType = 'TINYINT(1) UNSIGNED NULL';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('referrerType');
        $segment->setName('Referrers_Type');
        $segment->setSqlFilterValue('Piwik\Plugins\Referrers\getReferrerTypeFromShortName');
        $segment->setAcceptValues('direct, search, website, campaign');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('Referrers_Type');
    }

    public function onNewVisit(Request $request, $visit)
    {
        $referrerUrl = $request->getParam('urlref');
        $currentUrl  = $request->getParam('url');

        $information = $this->getReferrerInformation($referrerUrl, $currentUrl, $request->getIdSite());

        return $information['referer_type'];
    }
}
