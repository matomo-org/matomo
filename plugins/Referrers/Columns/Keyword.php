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

class Keyword extends Base
{
    protected $fieldName = 'referer_keyword';
    protected $fieldType = 'VARCHAR(255) NULL';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('referrerKeyword');
        $segment->setName('General_ColumnKeyword');
        $segment->setAcceptValues('Encoded%20Keyword, keyword');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('General_ColumnKeyword');
    }

    public function onNewVisit(Request $request, $visit)
    {
        $referrerUrl = $request->getParam('urlref');
        $currentUrl  = $request->getParam('url');

        $information = $this->getReferrerInformation($referrerUrl, $currentUrl, $request->getIdSite());

        return $information['referer_keyword'];
    }
}
