<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions;

use Piwik\Period;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Site;

/**
 * The reporting period the report based promotion triggers look at: the last completed
 * calendar week.
 *
 * Matomo's `week` period always runs Monday to Sunday, and `lastWeek` resolves to exactly
 * seven days ago, which always falls inside the previous completed week whatever weekday
 * it is evaluated on. A rolling range must not be used instead: archiving is force
 * enabled for `range` periods regardless of the browser trigger setting, which would let
 * a dashboard request build an archive.
 */
class ReportPeriod
{
    public const PERIOD = 'week';

    public const DATE = 'lastWeek';

    public function forSite(int $idSite): Period
    {
        return PeriodFactory::makePeriodFromQueryParams(Site::getTimezoneFor($idSite), self::PERIOD, self::DATE);
    }

    /**
     * Start of the last completed week, as `YYYY-MM-DD`.
     */
    public function getStartDate(int $idSite): string
    {
        return $this->forSite($idSite)->getDateStart()->toString();
    }

    /**
     * End of the last completed week, as `YYYY-MM-DD`.
     */
    public function getEndDate(int $idSite): string
    {
        return $this->forSite($idSite)->getDateEnd()->toString();
    }
}
