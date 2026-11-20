<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

use Piwik\Plugins\ProfessionalServices\PluginPromotions\ActiveSitesCount;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ReportPeriod;

/**
 * Shared by the promotions pitched on how many websites the user watches rather than on
 * anything about the one on screen.
 *
 * The counting itself lives in {@see ActiveSitesCount}, which both subclasses are handed
 * the same instance of, so a dashboard asks the question once no matter how many of these
 * promotions are registered.
 *
 * Deliberately outside {@see \Piwik\Plugins\ProfessionalServices\PluginPromotions\DailyTriggerCache}.
 * That cache is keyed on a trigger and one website, which suits an answer that depends on
 * that website's reports. This answer depends on which websites the *user* may see, so a
 * cached one would be handed to the next user whatever their access. The cost is held down
 * by the per-request memo in `ActiveSitesCount` instead.
 */
abstract class ActiveSitesTrigger implements PromotionTrigger
{
    private ActiveSitesCount $activeSites;

    private ReportPeriod $reportPeriod;

    public function __construct(ActiveSitesCount $activeSites, ReportPeriod $reportPeriod)
    {
        $this->activeSites = $activeSites;
        $this->reportPeriod = $reportPeriod;
    }

    public function evaluate(int $idSite): TriggerResult
    {
        $period = $this->reportPeriod->forSite($idSite);
        $periodStart = $period->getDateStart()->toString();
        $periodEnd = $period->getDateEnd()->toString();

        $qualifying = $this->activeSites->countQualifyingSites();

        if ($qualifying < ActiveSitesCount::MINIMUM_SITES) {
            return TriggerResult::notTriggered($periodStart, $periodEnd);
        }

        return TriggerResult::triggered(['count' => $qualifying], $periodStart, $periodEnd);
    }
}
