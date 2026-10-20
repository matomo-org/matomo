<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Plugins\Goals\Goals;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ReportPeriod;

/**
 * Triggers when a goal converts often and no single channel can claim the credit.
 *
 * Attribution only has something to say where several channels each contribute a real
 * share, so a goal converting through one dominant channel does not qualify however many
 * conversions it has.
 *
 * Reads last week's goals and referrer types from the existing archive only, and caches
 * the outcome for the day.
 */
class MultipleConversionChannelsTrigger extends GoalBackedTrigger
{
    public const NAME = 'multiple_conversion_channels';

    public const MINIMUM_CONVERSIONS = 500;

    public const MINIMUM_CHANNELS = 3;

    /**
     * The share of a goal's conversions a channel has to carry to count as contributing.
     */
    public const MINIMUM_CHANNEL_SHARE = 0.10;

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getRequiredArchives(): array
    {
        return ['Referrers', 'Goals'];
    }

    protected function deriveContext(array $metrics, int $idSite): ?array
    {
        // Busiest first, so the goal with the strongest case is the one reported.
        $candidates = array_filter($metrics['goals'], static function (array $goal): bool {
            return $goal['nbConversions'] >= self::MINIMUM_CONVERSIONS;
        });
        usort($candidates, static function (array $a, array $b): int {
            return $b['nbConversions'] <=> $a['nbConversions'];
        });

        foreach ($candidates as $goal) {
            $channels = $this->countContributingChannels($idSite, (int) $goal['idGoal'], (int) $goal['nbConversions']);

            if ($channels >= self::MINIMUM_CHANNELS) {
                return [
                    'goalId' => (int) $goal['idGoal'],
                    'goalName' => (string) $goal['name'],
                    'count' => (int) $goal['nbConversions'],
                    'numChannels' => $channels,
                ];
            }
        }

        return null;
    }

    /**
     * How many referrer types carried at least the minimum share of one goal's
     * conversions.
     */
    private function countContributingChannels(int $idSite, int $idGoal, int $nbConversions): int
    {
        if ($nbConversions <= 0) {
            return 0;
        }

        $referrerTypes = Request::processRequest('Referrers.getReferrerType', [
            'idSite' => $idSite,
            'period' => ReportPeriod::PERIOD,
            'date' => ReportPeriod::DATE,
            'format_metrics' => 0,
            // `Referrers.getReferrerType()` declares no idGoal of its own, but the API's
            // post-processor reads one from the request to decide which goal's columns to
            // add ({@see \Piwik\API\DataTablePostProcessor}), which is what puts
            // `goal_<idGoal>_nb_conversions` on each row.
            'idGoal' => $idGoal,
            'filter_update_columns_when_show_all_goals' => 1,
            'filter_limit' => -1,
        ], []);

        if (!$referrerTypes instanceof DataTable) {
            return 0;
        }

        return $this->countChannelsOverShare($referrerTypes, $idGoal, $nbConversions);
    }

    /**
     * How many referrer types carried at least the minimum share of one goal's conversions.
     *
     * Reads the goal's own column rather than the row's plain `nb_conversions`. On a site
     * with more than one goal that plain column is the total across every goal, so
     * dividing it by a single goal's conversions overstates each channel's share and the
     * promotion fires for sites where no channel really contributes a tenth of this goal.
     *
     * @param int $nbConversions the goal's conversions across every channel
     */
    public function countChannelsOverShare(DataTable $referrerTypes, int $idGoal, int $nbConversions): int
    {
        if ($nbConversions <= 0) {
            return 0;
        }

        $column = Goals::makeGoalColumn($idGoal, 'nb_conversions');
        $channels = 0;

        foreach ($referrerTypes->getRows() as $row) {
            // Deliberately no fallback to the plain column: without the goal's own figure
            // there is nothing to compare, and guessing with the all-goals total is the
            // bug this replaced.
            $conversions = (int) $row->getColumn($column);

            if (($conversions / $nbConversions) >= self::MINIMUM_CHANNEL_SHARE) {
                $channels++;
            }
        }

        return $channels;
    }
}
