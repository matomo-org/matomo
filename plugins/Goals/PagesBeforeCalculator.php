<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals;

use Piwik\Common;
use Piwik\Plugins\SitesManager\Model as SitesModel;
use Piwik\Plugins\Goals\Model as GoalsModel;
use Piwik\Db;

/**
 * Service that calculates the 'pages before' metric for existing conversions in the database.
 * Conversions created in versions >5.0.0 calculate and write this metric when the conversion is inserted.
 */
class PagesBeforeCalculator
{

    /**
     * Calculates the 'pages before' metric for conversions within the specified date range, belonging to the specified
     * site (if any) and specific goals (only if a single site is specified) Calculations are done in  chunks.
     *
     * @param string|null   $startDatetime A datetime string. Visits that occur at this time or after are deleted. If not supplied,
     *                                     visits from the beginning of time are deleted.
     * @param string|null   $endDatetime A datetime string. Visits that occur before this time are deleted. If not supplied,
     *                                   visits from the end of time are deleted.
     * @param string|null   $idSite The site for which to calculate, or list of comma separated sites
     * @param string|null   $idGoal The goal for which to calculate, or list of comma separated idgoals (only if single site)
     * @param callable|null $afterChunkCalculated Callback function to be called after a chunk of calculation is done
     *
     * @return int The number of conversions calculated
     */
    public function calculateFor(?string $startDatetime, ?string $endDatetime, ?string $idSite = null, ?string $idGoal = null, callable $afterChunkCalculated = null): int
    {

        $totalCalculated = 0;

        // Sites
        if ($idSite === null) {
            // All sites
            $sitesModel = new SitesModel();
            $sites = $sitesModel->getSitesId();
        } else {
            // Specific sites
            $sites = explode(',', $idSite);
        }

        foreach ($sites as $site) {

            if ($idGoal === null) {
                // All goals
                $goalsModel = new GoalsModel();
                $goals = array_column($goalsModel->getActiveGoals([$site]), 'idgoal');
            } else {
                // Specific goals
                $goals = explode(',', $idGoal);
            }

            $chunks = 0;
            foreach ($goals as $goal) {

                $done = false;
                while (!$done) {
                    $sql = "
                    UPDATE " . Common::prefixTable('log_conversion') . " c
                    LEFT JOIN (
                        SELECT COUNT(va.idvisit) AS pages_before, va.idvisit, va.server_time
                        FROM " . Common::prefixTable('log_link_visit_action') . " va
                        LEFT JOIN " . Common::prefixTable('log_action') . " a ON a.idaction = va.idaction_url AND a.type = 1
                        GROUP BY va.idvisit
                    ) AS a ON a.idvisit = c.idvisit AND a.server_time <= c.server_time
                    SET c.pageviews_before = a.pages_before
                    WHERE c.idsite = ? AND c.idgoal = ? AND c.pageviews_before IS NULL
                    ";

                    $bind = [$site, $goal];

                    if (!empty($startDatetime)) {
                        $sql .= " AND c.server_time >= ?";
                        $bind[] = $startDatetime;
                    }

                    if (!empty($endDatetime)) {
                        $sql .= " AND c.server_time <= ?";
                        $bind[] = $endDatetime;
                    }

                    $result = Db::query($sql, $bind);
                    $calcCount = $result->rowCount();

                    // Done with this site/goal if no records were updated or we've processed 100m records (sanity check)
                    if ($calcCount == 0 || $chunks > 10000) {
                        $done = true;
                    }

                    $chunks++;
                    $totalCalculated += $calcCount;

                    if (!empty($afterChunkCalculated)) {
                        $afterChunkCalculated($calcCount);
                    }
                }

            }
        }

        return $totalCalculated;
    }
}
