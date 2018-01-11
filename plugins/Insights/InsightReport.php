<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights;

use Piwik\DataTable;
use Piwik\Piwik;

/**
 * Insight report generator
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
     * @param int $lastTotalValue
     * @param string $orderBy
     * @param int $limitIncreaser
     * @param int $limitDecreaser
     * @return DataTable
     */
    public function generateMoverAndShaker($reportMetadata, $period, $date, $lastDate, $metric, $currentReport, $lastReport, $totalValue, $lastTotalValue, $orderBy, $limitIncreaser, $limitDecreaser)
    {
        $totalEvolution = $this->getTotalEvolution($totalValue, $lastTotalValue);

        $minMoversPercent = 1;

        if ($totalEvolution >= 100) {
            // eg change from 50 to 150 = 200%
            $factor = (int) ceil($totalEvolution / 500);
            $minGrowthPercentPositive = $totalEvolution + ($factor * 40); // min +240%
            $minGrowthPercentNegative = -70;         // min -70%
            $minDisappearedPercent = 8;              // min 12
            $minNewPercent = min(($totalEvolution / 100) * 3, 10);    // min 6% = min 10 of total visits up to max 10%

        } elseif ($totalEvolution >= 0) {
            // eg change from 50 to 75 = 50%
            $minGrowthPercentPositive = $totalEvolution + 20;  // min 70%
            $minGrowthPercentNegative = -1 * $minGrowthPercentPositive;  // min -70%
            $minDisappearedPercent = 7;
            $minNewPercent         = 5;
        } else {
            // eg change from 50 to 25 = -50%
            $minGrowthPercentNegative = $totalEvolution - 20;                  // min -70%
            $minGrowthPercentPositive = abs($minGrowthPercentNegative); // min 70%
            $minDisappearedPercent = 7;
            $minNewPercent         = 5;
        }

        if ($totalValue < 200 && $totalValue != 0) {
            // force at least a change of 2 visits
            $minMoversPercent = (int) ceil(2 / ($totalValue / 100));
            $minNewPercent    = max($minNewPercent, $minMoversPercent);
            $minDisappearedPercent = max($minDisappearedPercent, $minMoversPercent);
        }

        $dataTable = $this->generateInsight($reportMetadata, $period, $date, $lastDate, $metric, $currentReport, $lastReport, $totalValue, $minMoversPercent, $minNewPercent, $minDisappearedPercent, $minGrowthPercentPositive, $minGrowthPercentNegative, $orderBy, $limitIncreaser, $limitDecreaser);

        $this->addMoversAndShakersMetadata($dataTable, $totalValue, $lastTotalValue);

        return $dataTable;
    }

    /**
     * Extends an already generated insight report by adding a column "isMoverAndShaker" whether a row is also a
     * "Mover and Shaker" or not.
     *
     * Avoids the need to fetch all reports again when we already have the currentReport/lastReport
     */
    public function markMoversAndShakers(DataTable $insight, $currentReport, $lastReport, $totalValue, $lastTotalValue)
    {
        if (!$insight->getRowsCount()) {
            return;
        }

        $limitIncreaser = max($insight->getRowsCount(), 3);
        $limitDecreaser = max($insight->getRowsCount(), 3);

        $lastDate = $insight->getMetadata('lastDate');
        $date     = $insight->getMetadata('date');
        $period   = $insight->getMetadata('period');
        $metric   = $insight->getMetadata('metric');
        $orderBy  = $insight->getMetadata('orderBy');
        $reportMetadata = $insight->getMetadata('report');

        $shakers = $this->generateMoverAndShaker($reportMetadata, $period, $date, $lastDate, $metric, $currentReport, $lastReport, $totalValue, $lastTotalValue, $orderBy, $limitIncreaser, $limitDecreaser);

        foreach ($insight->getRows() as $row) {
            $label = $row->getColumn('label');

            if ($shakers->getRowFromLabel($label)) {
                $row->setColumn('isMoverAndShaker', true);
            } else {
                $row->setColumn('isMoverAndShaker', false);
            }
        }

        $this->addMoversAndShakersMetadata($insight, $totalValue, $lastTotalValue);
    }

    /**
     * @param array $reportMetadata
     * @param string $period
     * @param string $date
     * @param string $lastDate
     * @param string $metric
     * @param DataTable $currentReport
     * @param DataTable $lastReport
     * @param int $totalValue
     * @param int $minMoversPercent      Exclude rows who moved and the difference is not at least min percent
     *                                         visits of totalVisits. -1 excludes movers.
     * @param int $minNewPercent         Exclude rows who are new and the difference is not at least min percent
     *                                         visits of totalVisits. -1 excludes all new.
     * @param int $minDisappearedPercent Exclude rows who are disappeared and the difference is not at least min
     *                                         percent visits of totalVisits. -1 excludes all disappeared.
     * @param int $minGrowthPercentPositive    The actual growth of a row must be at least percent compared to the
     *                                         previous value (not total value)
     * @param int $minGrowthPercentNegative    The actual growth of a row must be lower percent compared to the
     *                                         previous value (not total value)
     * @param string $orderBy                  Order by absolute, relative, importance
     * @param int $limitIncreaser
     * @param int $limitDecreaser
     *
     * @return DataTable
     */
    public function generateInsight($reportMetadata, $period, $date, $lastDate, $metric, $currentReport, $lastReport, $totalValue, $minMoversPercent, $minNewPercent, $minDisappearedPercent, $minGrowthPercentPositive, $minGrowthPercentNegative, $orderBy, $limitIncreaser, $limitDecreaser)
    {
        $minChangeMovers = $this->getMinVisits($totalValue, $minMoversPercent);
        $minIncreaseNew = $this->getMinVisits($totalValue, $minNewPercent);
        $minDecreaseDisappeared = $this->getMinVisits($totalValue, $minDisappearedPercent);

        $dataTable = new DataTable();
        $dataTable->filter(
            'Piwik\Plugins\Insights\DataTable\Filter\Insight',
            array(
                $currentReport,
                $lastReport,
                $metric,
                $considerMovers = (-1 !== $minMoversPercent),
                $considerNew = (-1 !== $minNewPercent),
                $considerDisappeared = (-1 !== $minDisappearedPercent)
            )
        );

        $dataTable->filter(
            'Piwik\Plugins\Insights\DataTable\Filter\MinGrowth',
            array(
                'growth_percent_numeric',
                $minGrowthPercentPositive,
                $minGrowthPercentNegative
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

        $metricName = $metric;
        if (!empty($reportMetadata['metrics'][$metric])) {
            $metricName = $reportMetadata['metrics'][$metric];
        }

        $dataTable->setMetadataValues(array(
            'reportName' => $reportMetadata['name'],
            'metricName' => $metricName,
            'date' => $date,
            'lastDate' => $lastDate,
            'period' => $period,
            'report' => $reportMetadata,
            'totalValue' => $totalValue,
            'orderBy' => $orderBy,
            'metric' => $metric,
            'minChangeMovers' => $minChangeMovers,
            'minIncreaseNew' => $minIncreaseNew,
            'minDecreaseDisappeared' => $minDecreaseDisappeared,
            'minGrowthPercentPositive' => $minGrowthPercentPositive,
            'minGrowthPercentNegative' => $minGrowthPercentNegative,
            'minMoversPercent' => $minMoversPercent,
            'minNewPercent' => $minNewPercent,
            'minDisappearedPercent' => $minDisappearedPercent
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

    private function addMoversAndShakersMetadata(DataTable $dataTable, $totalValue, $lastTotalValue)
    {
        $totalEvolution = $this->getTotalEvolution($totalValue, $lastTotalValue);

        $dataTable->setMetadata('lastTotalValue', $lastTotalValue);
        $dataTable->setMetadata('evolutionTotal', $totalEvolution);
        $dataTable->setMetadata('evolutionDifference', $totalValue - $lastTotalValue);
    }

    private function getTotalEvolution($totalValue, $lastTotalValue)
    {
        return Piwik::getPercentageSafe($totalValue - $lastTotalValue, $lastTotalValue, 1);
    }
}
