<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

use Piwik\Plugin\Manager;
use Piwik\Plugins\ScheduledReports\API as ScheduledReportsApi;

/**
 * Triggers when the current user has at least three scheduled reports for the currently
 * selected website.
 *
 * Evaluated against the current state rather than a report, so it is not cached.
 */
class ScheduledReportsTrigger implements PromotionTrigger
{
    public const NAME = 'scheduled_reports';

    public const MINIMUM_REPORTS = 3;

    private Manager $pluginManager;

    public function __construct(Manager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function evaluate(int $idSite): TriggerResult
    {
        if (!$this->pluginManager->isPluginActivated('ScheduledReports')) {
            return TriggerResult::notTriggered();
        }

        // The fourth argument restricts the result to the current user's own reports even
        // for super users, who would otherwise see every user's reports for this site.
        $reports = ScheduledReportsApi::getInstance()->getReports($idSite, false, false, true);
        $numReports = count($reports);

        if ($numReports < self::MINIMUM_REPORTS) {
            return TriggerResult::notTriggered();
        }

        return TriggerResult::triggered(['count' => $numReports]);
    }
}
