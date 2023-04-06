<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations;
use Piwik\Date;
use Piwik\Period;
use Piwik\Period\Factory;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines\Config;

class EvolutionPeriodSelector
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getNumDaysDifference(Date $date1, Date $date2): int
    {
        $days = abs($date1->getTimestamp() - $date2->getTimestamp()) / 60 / 60 / 24;
        return (int) round($days);
    }

    public function setDatePeriods($params, Period $originalPeriod, $comparisonPeriods, $isComparing): array
    {
        $highestPeriodInCommon = $this->getHighestPeriodInCommon($originalPeriod, $comparisonPeriods);

        if ($isComparing) {
            // when not comparing we usually show the last30 data points from the end date. However, when comparing dates,
            // or periods then we don't want to do this and rather only draw the evolution of the selected range. This way
            // you can better compare the two specific date ranges and how they change over time.
            // Otherwise, if you were to compare for example today vs yesterday, then you would see the trends for today
            // over last 30 days vs yesterday over the last 30 days which isn't very helpful to compare.
            // that means when you compare month of Dec 2022 with Dec 2021, then the shown sparkline should be comparing
            // Dec 1st 2022,Dec 31st 2022 with Dec 1st 2021,Dec31st 2021. And not show eg the last 30 months.

            // we want to use the higest possible period we can for best performance and for best detecting trends
            $params['period'] = $highestPeriodInCommon;
            $params['date'] = $originalPeriod->getRangeString();

            $params['compareDates'] = [];
            $params['comparePeriods'] = [];

            foreach ($comparisonPeriods as $period) {
                $params['compareDates'][] = $period->getRangeString();
                $params['comparePeriods'][] = $params['period']; // need to ensure to use same period for both the base and the comparison period
            }
        } else {
            // when not comparing we select the last 30 days/weeks/months/years.
            // For period range it will select the selected range with a day metric
            $params = $this->config->getGraphParamsModified($params);
            if ($originalPeriod->getLabel() === 'range') {
                // when a longer range is selected, then we select a higher period for improved performance and also to see trends better
                // eg when selecting a range of 2 years then we rather show 24 months instead of 730 day points.
                $params['period'] = $highestPeriodInCommon;
                $params['date'] = $originalPeriod->getRangeString();
            }
        }

        return $params;
    }

    public function getComparisonPeriodObjects($comparePeriods, $compareDates): array
    {
        $periods = [];
        if (!empty($comparePeriods)) {
            foreach ($comparePeriods as $periodIndex => $period) {
                $date = $compareDates[$periodIndex];
                $periods[] = Factory::build($period, $date);
            }
        }
        return $periods;
    }

    /**
     * Given all the periods, determine what is the lowest period we can use. For example if someone compares
     * a range of 2 years with a range of 2 days, then we still need to use "day" periods in the sparkline as otherwise
     * you would not be able to compare the 2 different sparklines when they use different periods.
     *
     * For best performance and for better detecting trends we try to select the highest period if all the periods given
     * allow it.
     *
     * @param Period $originalPeriod
     * @param $comparisonPeriods
     * @return string
     */
    public function getHighestPeriodInCommon(Period $originalPeriod, $comparisonPeriods): string
    {
        $lowestNumDaysInRange = $this->getNumDaysDifference($originalPeriod->getDateStart(), $originalPeriod->getDateEnd());

        if (!empty($comparisonPeriods)) {
            foreach ($comparisonPeriods as $period) {
                $numDaysInRange = $this->getNumDaysDifference($period->getDateStart(), $period->getDateEnd());
                if ($numDaysInRange < $lowestNumDaysInRange) {
                    $lowestNumDaysInRange = $numDaysInRange;
                }
            }
        }

        $periodToUse = 'day';
        if ($lowestNumDaysInRange >= 7 * 365) {
            $periodToUse = 'year';
        } elseif ($lowestNumDaysInRange >= 2 * 365) {
            $periodToUse = 'month';
        } elseif ($lowestNumDaysInRange >= 180) {
            $periodToUse = 'week';
        }

        return $periodToUse;
    }

}
