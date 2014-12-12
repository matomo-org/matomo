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

class Website extends Base
{
    /**
     * Set using the `[Tracker] create_new_visit_when_website_referrer_changes` INI config option.
     * If true, will force new visits if the referrer website changes.
     *
     * @var bool
     */
    protected $createNewVisitWhenWebsiteReferrerChanges;

    public function __construct()
    {
        $this->createNewVisitWhenWebsiteReferrerChanges = TrackerConfig::getConfigValue('create_new_visit_when_website_referrer_changes') == 1;
    }

    public function getName()
    {
        return Piwik::translate('General_Website');
    }

    public function shouldForceNewVisit(Request $request, Visitor $visitor, Action $action = null)
    {
        if (!$this->createNewVisitWhenWebsiteReferrerChanges) {
            return false;
        }

        $information = $this->getReferrerInformationFromRequest($request);

        if ($information['referer_type'] == Common::REFERRER_TYPE_WEBSITE
            && $this->isReferrerInformationNew($visitor, $information)
        ) {
            Common::printDebug("Existing visit detected, but creating new visit because website referrer information is different than last action.");

            return true;
        }

        return false;

    }
}