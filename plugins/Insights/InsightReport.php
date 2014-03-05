<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights;

use Piwik\DataTable;

/**
 * API for plugin Insights
 *
 * @method static \Piwik\Plugins\Insights\API getInstance()
 */
class InsightReport
{
    const ORDER_BY_RELATIVE   = 'relative';
    const ORDER_BY_ABSOLUTE   = 'absolute';
    const ORDER_BY_IMPORTANCE = 'importance';

    /**
     * @param array $reportMetadata
     * @param string $period
     * @param string $date
     * @param string $lastDate
     * @param string $metric
     * @param DataTable $currentReport
     * @param DataTable $lastReport
     * @param int $totalValue
     * @param int $minVisitsMoversPercent      Exclude rows who moved and the difference is not at least min percent
     *                                         visits of totalVisits. -1 excludes movers.
     * @param int $minVisitsNewPercent         Exclude rows who are new and the difference is not at least min percent
     *                                         visits of totalVisits. -1 excludes all new.
     * @param int $minVisitsDisappearedPercent Exclude rows who are disappeared and the difference is not at least min
     *                                         percent visits of totalVisits. -1 excludes all disappeared.
     * @param int $minGrowthPercent            The actual growth of a row must be at least percent compared to the
     *                                         previous value (not total value)
     * @param string $orderBy                  Order by absolute, relative, importance
     * @param int $limitIncreaser
     * @param int $limitDecreaser
     * @return DataTable
     */
    public function generateInsight($reportMetadata, $period, $date, $lastDate, $metric, $currentReport, $lastReport, $totalValue, $minVisitsMoversPercent, $minVisitsNewPercent, $minVisitsDisappearedPercent, $minGrowthPercent, $orderBy, $limitIncreaser, $limitDecreaser)
    {
        $minChangeMovers = $this->getMinVisits($totalValue, $minVisitsMoversPercent);
        $minIncreaseNew = $this->getMinVisits($totalValue, $minVisitsNewPercent);
        $minDecreaseDisappeared = $this->getMinVisits($totalValue, $minVisitsDisappearedPercent);

        $dataTable = new DataTable();
        $dataTable->filter(
            'Piwik\Plugins\Insights\DataTable\Filter\Insight',
            array(
                $currentReport,
                $lastReport,
                $metric,
                $considerMovers = (-1 !== $minVisitsMoversPercent),
                $considerNew = (-1 !== $minVisitsNewPercent),
                $considerDisappeared = (-1 !== $minVisitsDisappearedPercent)
            )
        );

        $dataTable->filter(
            'Piwik\Plugins\Insights\DataTable\Filter\MinGrowth',
            array(
                'growth_percent_numeric',
                $minGrowthPercent,
            )
        );

        if ($minIncreaseNew) {
            $dataTable->filter(
                'Piwik\Plugins\Insights\DataTable\Filter\ExcludeLowValue',
                array(
                    'difference',
                    $minIncreaseNew,
                    'isNew'
                )
            );
        }

        if ($minChangeMovers) {
            $dataTable->filter(
                'Piwik\Plugins\Insights\DataTable\Filter\ExcludeLowValue',
                array(
                    'difference',
                    $minChangeMovers,
                    'isMover'
                )
            );
        }

        if ($minDecreaseDisappeared) {
            $dataTable->filter(
                'Piwik\Plugins\Insights\DataTable\Filter\ExcludeLowValue',
                array(
                    'difference',
                    $minDecreaseDisappeared,
                    'isDisappeared'
                )
            );
        }

        $dataTable->filter(
            'Piwik\Plugins\Insights\DataTable\Filter\OrderBy',
            array(
                $this->getOrderByColumn($orderBy),
                $orderBy === self::ORDER_BY_RELATIVE ? $this->getOrderByColumn(self::ORDER_BY_ABSOLUTE) : $this->getOrderByColumn(self::ORDER_BY_RELATIVE),
                $metric
            )
        );

        $dataTable->filter(
            'Piwik\Plugins\Insights\DataTable\Filter\Limit',
            array(
                'growth_percent_numeric',
                $limitIncreaser,
                $limitDecreaser
            )
        );

        $dataTable->setMetadataValues(array(
            'reportName' => $reportMetadata['name'],
            'metricName' => $reportMetadata['metrics'][$metric],
            'date' => $date,
            'lastDate' => $lastDate,
            'period' => $period,
            'report' => $reportMetadata,
            'totalValue' => $totalValue,
            'minChangeMovers' => $minChangeMovers,
            'minIncreaseNew' => $minIncreaseNew,
            'minDecreaseDisappeared' => $minDecreaseDisappeared,
            'minGrowthPercent' => $minGrowthPercent,
            'minVisitsMoversPercent' => $minVisitsMoversPercent,
            'minVisitsNewPercent' => $minVisitsNewPercent,
            'minVisitsDisappearedPercent' => $minVisitsDisappearedPercent
        ));

        return $dataTable;
    }

    private function getOrderByColumn($orderBy)
    {
        if (self::ORDER_BY_RELATIVE == $orderBy) {
            $orderByColumn = 'growth_percent_numeric';
        } elseif (self::ORDER_BY_ABSOLUTE == $orderBy) {
            $orderByColumn = 'difference';
        } elseif (self::ORDER_BY_IMPORTANCE == $orderBy) {
            $orderByColumn = 'importance';
        } else {
            throw new \Exception('Unsupported orderBy');
        }

        return $orderByColumn;
    }

    private function getMinVisits($totalValue, $percent)
    {
        if ($percent <= 0) {
            return 0;
        }

        $minVisits = ceil(($totalValue / 100) * $percent);

        return (int) $minVisits;
    }
}
