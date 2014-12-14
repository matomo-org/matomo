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
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\TrackerConfig;
use Piwik\Tracker\Visitor;

class Campaign extends Base
{
    /**
     * Obtained from the `[Tracker] create_new_visit_when_campaign_changes` INI config option.
     * If true, will create new visits when campaign name changes.
     *
     * @var bool
     */
    protected $createNewVisitWhenCampaignChanges;

    public function __construct()
    {
        $this->createNewVisitWhenCampaignChanges = TrackerConfig::getConfigValue('create_new_visit_when_campaign_changes') == 1;
    }

    public function getName()
    {
        return Piwik::translate('Referrers_ColumnCampaign');
    }

    /**
     * If we should create a new visit when the campaign changes, check if the campaign info changed and if so
     * force the tracker to create a new visit.
     *
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return bool
     */
    public function shouldForceNewVisit(Request $request, Visitor $visitor, Action $action = null)
    {
        if (!$this->createNewVisitWhenCampaignChanges) {
            return false;
        }

        $information = $this->getReferrerInformationFromRequest($request);

        if ($information['referer_type'] == Common::REFERRER_TYPE_CAMPAIGN
            && $this->isReferrerInformationNew($visitor, $information)
        ) {
            Common::printDebug("Existing visit detected, but creating new visit because campaign information is different than last action.");

            return true;
        }

        return false;
    }
}