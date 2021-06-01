<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Columns;

use Piwik\Common;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class ReferrerUrl extends Base
{
    const MAX_LEN = 1500;
    protected $columnName = 'referer_url';
    protected $type = self::TYPE_TEXT;
    protected $segmentName = 'referrerUrl';
    protected $nameSingular = 'Live_Referrer_URL';
    protected $namePlural = 'Referrers_ReferrerURLs';
    protected $category = 'Referrers_Referrers';
    protected $acceptValues = 'http%3A%2F%2Fwww.example.org%2Freferer-page.htm';

    public function __construct()
    {
        $this->columnType = 'VARCHAR('.self::MAX_LEN.') NULL';
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

        return $this->trimUrl($information['referer_url']);
    }

    private function trimUrl($url)
    {
        if (!empty($url) && is_string($url) && mb_strlen($url) > self::MAX_LEN) {
            return mb_substr($url, 0, self::MAX_LEN);
        }
        return $url;
    }

    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        $information = $this->getReferrerInformationFromRequest($request, $visitor);
        if ($this->isCurrentReferrerDirectEntry($visitor)
            && $information['referer_type'] != Common::REFERRER_TYPE_DIRECT_ENTRY
        ) {
            return $this->trimUrl($information['referer_url']);
        }

        return false;
    }
}
