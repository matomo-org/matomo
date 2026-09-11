<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions;

use Piwik\DataTable;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Plugins\Goals\Archiver as GoalsArchiver;

/**
 * Reads last week's conversion metrics for every configured goal of a website.
 *
 * The numeric archive records are read directly rather than through `Goals.get`, which
 * issues three sub requests (all visits, new visitors, returning visitors) and formats
 * percentages into strings. Only existing archives are read; if last week was never
 * archived the result is simply empty.
 *
 * Note that a goal's conversion rate in Matomo is the goal's converted visits divided by
 * the *website's* visits, so `nb_visits` below is the same figure for every goal and is
 * used as a traffic gate, not as a per goal metric.
 */
class WeeklyGoalMetrics
{
    private ArchivedReportReader $reader;

    private Manager $pluginManager;

    public function __construct(ArchivedReportReader $reader, Manager $pluginManager)
    {
        $this->reader = $reader;
        $this->pluginManager = $pluginManager;
    }

    /**
     * @return array{siteVisits: int, goals: array<int, array{idGoal: int, name: string, nbConversions: int, nbVisitsConverted: int, conversionRate: float}>}
     */
    public function read(int $idSite): array
    {
        $empty = ['siteVisits' => 0, 'goals' => []];

        if (!$this->pluginManager->isPluginActivated('Goals')) {
            return $empty;
        }

        $goals = GoalsApi::getInstance()->getGoals($idSite);
        if (empty($goals)) {
            return $empty;
        }

        $recordNames = ['nb_visits'];
        foreach ($goals as $goal) {
            $idGoal = (int) $goal['idgoal'];
            $recordNames[] = GoalsArchiver::getRecordName('nb_conversions', $idGoal);
            $recordNames[] = GoalsArchiver::getRecordName('nb_visits_converted', $idGoal);
        }

        $archive = $this->reader->buildArchive($idSite, ReportPeriod::PERIOD, ReportPeriod::DATE);
        $dataTable = $archive->getDataTableFromNumeric($recordNames);
        $row = $dataTable instanceof DataTable ? $dataTable->getFirstRow() : false;

        if (empty($row)) {
            return $empty;
        }

        $siteVisits = (int) $row->getColumn('nb_visits');
        if ($siteVisits <= 0) {
            return $empty;
        }

        $metrics = [];
        foreach ($goals as $goal) {
            $idGoal = (int) $goal['idgoal'];
            $nbVisitsConverted = (int) $row->getColumn(GoalsArchiver::getRecordName('nb_visits_converted', $idGoal));

            $metrics[] = [
                'idGoal' => $idGoal,
                'name' => (string) $goal['name'],
                'nbConversions' => (int) $row->getColumn(GoalsArchiver::getRecordName('nb_conversions', $idGoal)),
                'nbVisitsConverted' => $nbVisitsConverted,
                'conversionRate' => (float) ($nbVisitsConverted / $siteVisits),
            ];
        }

        return ['siteVisits' => $siteVisits, 'goals' => $metrics];
    }

    /**
     * Picks the goal converting for the smallest share of visits, which is the one a
     * funnel would help most with.
     *
     * @param array<int, array{idGoal: int, name: string, nbConversions: int, nbVisitsConverted: int, conversionRate: float}> $candidates
     * @return array{idGoal: int, name: string, nbConversions: int, nbVisitsConverted: int, conversionRate: float}|null
     */
    public static function pickGoalWithLowestConversionRate(array $candidates): ?array
    {
        return self::pickGoalByConversionRate($candidates, true);
    }

    /**
     * Picks the goal converting for the largest share of visits, which is the one worth
     * experimenting on.
     *
     * @param array<int, array{idGoal: int, name: string, nbConversions: int, nbVisitsConverted: int, conversionRate: float}> $candidates
     * @return array{idGoal: int, name: string, nbConversions: int, nbVisitsConverted: int, conversionRate: float}|null
     */
    public static function pickGoalWithHighestConversionRate(array $candidates): ?array
    {
        return self::pickGoalByConversionRate($candidates, false);
    }

    /**
     * Two goals can share a conversion rate, because it is measured against the website's
     * visits rather than each goal's own. Conversions do differ per goal, so they break
     * the tie, and the goal with more of them wins whichever direction is being picked.
     *
     * @param array<int, array{idGoal: int, name: string, nbConversions: int, nbVisitsConverted: int, conversionRate: float}> $candidates
     * @return array{idGoal: int, name: string, nbConversions: int, nbVisitsConverted: int, conversionRate: float}|null
     */
    private static function pickGoalByConversionRate(array $candidates, bool $lowest): ?array
    {
        $best = null;
        foreach ($candidates as $candidate) {
            if (null === $best) {
                $best = $candidate;
                continue;
            }

            if ($candidate['conversionRate'] === $best['conversionRate']) {
                if ($candidate['nbConversions'] > $best['nbConversions']) {
                    $best = $candidate;
                }

                continue;
            }

            $isBetter = $lowest
                ? $candidate['conversionRate'] < $best['conversionRate']
                : $candidate['conversionRate'] > $best['conversionRate'];

            if ($isBetter) {
                $best = $candidate;
            }
        }

        return $best;
    }
}
