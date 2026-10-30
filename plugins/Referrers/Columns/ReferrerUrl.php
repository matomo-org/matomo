<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\Columns;

use Piwik\Common;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class ReferrerUrl extends Base
{
    public const MAX_LEN = 1500;
    protected $columnName = 'referer_url';
    protected $type = self::TYPE_TEXT;
    protected $segmentName = 'referrerUrl';
    protected $nameSingular = 'Live_Referrer_URL';
    protected $namePlural = 'Referrers_ReferrerURLs';
    protected $category = 'Referrers_Referrers';
    protected $acceptValues = 'http%3A%2F%2Fwww.example.org%2Freferer-page.htm';

    public function __construct()
    {
        $this->columnType = 'VARCHAR(' . self::MAX_LEN . ') NULL';
    }

    /**
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
        if (!$this->mayRelaxForThisVisit($request, $visitor, $action)) {
            return false;
        }

        $information = $this->getReferrerInformationFromRequest($request, $visitor);

        // the policy shortened the URL this visit arrived from. Consent reaching the visit while
        // the visitor is still on that page relaxes it to what the site configured for itself,
        // and the request carries the referrer again to record it from
        if (
            '' !== (string) $information['referer_url']
            && $this->shouldCompleteWithheldReferrer($visitor, $information, $request)
        ) {
            return $this->trimUrl($information['referer_url']);
        }

        if (
            $this->isCurrentReferrerDirectEntry($visitor)
            && $information['referer_type'] != Common::REFERRER_TYPE_DIRECT_ENTRY
        ) {
            return $this->trimUrl($information['referer_url']);
        }

        return false;
    }
}
