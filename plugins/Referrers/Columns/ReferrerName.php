<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\Columns;

use Piwik\Common;
use Piwik\Metrics\Formatter;
use Piwik\Plugins\PrivacyManager\Settings\CampaignParameterValuesMasked;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class ReferrerName extends Base
{
    protected $columnName = 'referer_name';
    protected $columnType = 'VARCHAR(255) NULL';
    protected $type = self::TYPE_TEXT;

    protected $nameSingular = 'Referrers_ReferrerName';
    protected $namePlural = 'Referrers_ReferrerNames';
    protected $segmentName = 'referrerName';
    protected $acceptValues = 'twitter.com, www.facebook.com, Bing, Google, Yahoo, CampaignName';
    protected $category = 'Referrers_Referrers';

    /**
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $information = $this->getReferrerInformationFromRequest($request, $visitor);
        return $information['referer_name'];
    }

    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        $information = $this->getReferrerInformationFromRequest($request, $visitor);

        // A campaign the policy discarded is a marker for "not collected", not a referrer the
        // visitor actually arrived from. When a later request in the same visit is allowed to
        // carry the real value - because the visitor has since consented - record it instead of
        // keeping the placeholder. Nothing is held back to make this work: the value arrives on
        // that request, which is why it only applies while the visitor is still on a URL carrying
        // it. If the request is not allowed to carry it, the incoming value is a placeholder too
        // and this does nothing.
        if (
            CampaignParameterValuesMasked::isPlaceholderValue($visitor->getVisitorColumn('referer_name'))
            && $information['referer_type'] == Common::REFERRER_TYPE_CAMPAIGN
            && !CampaignParameterValuesMasked::isPlaceholderValue($information['referer_name'])
        ) {
            return $information['referer_name'];
        }

        if (
            $this->isCurrentReferrerDirectEntry($visitor)
            && $information['referer_type'] != Common::REFERRER_TYPE_DIRECT_ENTRY
        ) {
            return $information['referer_name'];
        }

        return false;
    }

    /**
     * @param Action|null $action
     * @return mixed
     */
    public function onAnyGoalConversion(Request $request, Visitor $visitor, $action)
    {
        return $this->getValueForRecordGoal($request, $visitor);
    }

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        return CampaignParameterValuesMasked::formatValue($value);
    }
}
