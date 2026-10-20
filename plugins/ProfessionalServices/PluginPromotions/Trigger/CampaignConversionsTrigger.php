<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

use Piwik\DataTable;

/**
 * Triggers when a tracked campaign is converting often enough for those conversions to be
 * worth feeding back to the ad platform that produced them.
 */
class CampaignConversionsTrigger extends ReportBackedTrigger
{
    public const NAME = 'campaign_conversions';

    public const MINIMUM_CONVERSIONS = 200;

    private const ROWS_TO_INSPECT = 100;

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * Campaign rows only carry conversions once the goal reports have been archived too.
     */
    protected function getRequiredArchives(): array
    {
        return ['Referrers', 'Goals'];
    }

    protected function getApiMethod(): string
    {
        return 'Referrers.getCampaigns';
    }

    protected function getApiParameters(): array
    {
        return [
            // Asks for the goal columns across every goal, which is what puts
            // `nb_conversions` on each campaign row.
            'idGoal' => 0,
            'filter_update_columns_when_show_all_goals' => 1,
            // Sorted by conversions, not visits. The trigger wants the best converting
            // campaign, so with any other ordering the row it wants can fall outside the
            // limit below and the promotion names the wrong campaign, or none.
            'filter_sort_column' => 'nb_conversions',
            'filter_sort_order' => 'desc',
            'filter_limit' => self::ROWS_TO_INSPECT,
        ];
    }

    protected function deriveContext(DataTable $report): ?array
    {
        return $this->findBestConvertingCampaign($report);
    }

    /**
     * Returns the campaign with the most conversions, or null when none converted enough.
     *
     * The rows are asked for in conversion order, so the first qualifying row is already
     * the answer. They are still all scanned rather than trusting that ordering, which
     * costs nothing on at most one page of rows.
     *
     * @return array{name: string, count: int}|null
     */
    public function findBestConvertingCampaign(DataTable $campaigns): ?array
    {
        $best = null;

        foreach ($campaigns->getRows() as $row) {
            $conversions = (int) $row->getColumn('nb_conversions');

            if ($conversions < self::MINIMUM_CONVERSIONS) {
                continue;
            }

            if (null !== $best && $conversions <= $best['count']) {
                continue;
            }

            $best = ['name' => (string) $row->getColumn('label'), 'count' => $conversions];
        }

        return $best;
    }
}
