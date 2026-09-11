<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

use Piwik\Access;
use Piwik\DataTable;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ArchivedReportReader;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ReportPeriod;

/**
 * Shared by the promotions pitched on how many websites the user watches rather than on
 * anything about the one on screen.
 *
 * Deliberately not cached. {@see \Piwik\Plugins\ProfessionalServices\PluginPromotions\DailyTriggerCache}
 * is keyed on the trigger and one website, which suits an outcome that depends only on
 * that website's reports. This outcome depends on which websites the *user* may see, so a
 * cached answer would be handed to the next user whatever their access. The cost is kept
 * down instead by reading only existing archives, stopping as soon as enough websites
 * qualify, and never looking at more than {@see MAXIMUM_SITES_INSPECTED}.
 */
abstract class ActiveSitesTrigger implements PromotionTrigger
{
    public const MINIMUM_SITES = 5;

    public const MINIMUM_VISITS_PER_SITE = 100;

    /**
     * An instance can have thousands of websites, and the answer is the same once enough
     * of them qualify, so there is no reason to read every archive to find out.
     */
    public const MAXIMUM_SITES_INSPECTED = 100;

    private ArchivedReportReader $reader;

    private ReportPeriod $reportPeriod;

    public function __construct(ArchivedReportReader $reader, ReportPeriod $reportPeriod)
    {
        $this->reader = $reader;
        $this->reportPeriod = $reportPeriod;
    }

    public function evaluate(int $idSite): TriggerResult
    {
        $period = $this->reportPeriod->forSite($idSite);
        $periodStart = $period->getDateStart()->toString();
        $periodEnd = $period->getDateEnd()->toString();

        $idSites = Access::getInstance()->getSitesIdWithAtLeastViewAccess();

        if (count($idSites) < self::MINIMUM_SITES) {
            return TriggerResult::notTriggered($periodStart, $periodEnd);
        }

        $qualifying = 0;
        $inspected = 0;

        foreach ($idSites as $candidate) {
            if ($inspected >= self::MAXIMUM_SITES_INSPECTED) {
                break;
            }

            $inspected++;

            if ($this->getVisits((int) $candidate) >= self::MINIMUM_VISITS_PER_SITE) {
                $qualifying++;
            }

            // Nothing above the threshold changes the answer or the copy, which names the
            // number of websites rather than ranking them.
            if ($qualifying >= self::MINIMUM_SITES) {
                return TriggerResult::triggered(['count' => $qualifying], $periodStart, $periodEnd);
            }
        }

        return TriggerResult::notTriggered($periodStart, $periodEnd);
    }

    /**
     * Last week's visits for one website, read straight from the numeric archive so that
     * counting a portfolio costs one cheap read per website and never builds anything.
     */
    private function getVisits(int $idSite): int
    {
        $archive = $this->reader->buildArchive($idSite, ReportPeriod::PERIOD, ReportPeriod::DATE);
        $dataTable = $archive->getDataTableFromNumeric(['nb_visits']);
        $row = $dataTable instanceof DataTable ? $dataTable->getFirstRow() : false;

        return empty($row) ? 0 : (int) $row->getColumn('nb_visits');
    }
}
