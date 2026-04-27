<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;

use Piwik\Archive\ArchiveState;
use Piwik\Archive\DataTableFactory;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Period;
use Piwik\Site;

/**
 * Computes per-tick forecast values for incomplete-period data points on evolution-style series.
 *
 * The builder is stateless and reusable: it inspects each tick's archive state, blends a linear
 * elapsed-ratio extrapolation with a same-weekday historical average, and decides per tick
 * whether to render the forecast. Period bounds and the archive timestamp are read from
 * DataTable metadata, so any caller producing comparable DataTable maps can reuse it.
 */
class ForecastBuilder
{
    private const MIN_FORECAST_RATIO = 0.05;

    /**
     * @param array<string, array<int, float|int>> $allSeriesData
     * @param array<DataTable> $dataTables
     * @param array<int, string> $dataStates
     * @param array<string, string|false> $seriesUnits
     * @param array<string, array<int, bool>> $allSeriesDataAvailability
     * @return array<int, array<int, float|null>>
     */
    public function build(
        array $allSeriesData,
        array $dataTables,
        array $dataStates,
        array $seriesUnits,
        array $allSeriesDataAvailability = []
    ): array {
        if ([] === $allSeriesData || [] === $dataTables || [] === $dataStates) {
            return [];
        }

        /** @var Site|null $site */
        $site = reset($dataTables)->getMetadata(DataTableFactory::TABLE_METADATA_SITE_INDEX);
        if (empty($site)) {
            return [];
        }

        $dataTableList = array_values($dataTables);
        $seriesDataList = array_values($allSeriesData);
        $seriesUnitsList = array_values($seriesUnits);
        $seriesDataAvailabilityList = array_values($allSeriesDataAvailability);

        $forecastData = [];

        foreach ($seriesDataList as $seriesIndex => $seriesData) {
            $seriesForecasts = [];
            // Reset on every non-rendered tick so a suppressed or skipped forecast does not
            // bridge into later zero-data ticks; later ticks must restart from historical priors.
            $previousForecastValue = null;
            $isPercentSeries = ($seriesUnitsList[$seriesIndex] ?? false) === '%';
            $seriesDataAvailability = $seriesDataAvailabilityList[$seriesIndex] ?? [];

            foreach ($seriesData as $tickIndex => $currentValueRaw) {
                $state = $dataStates[$tickIndex] ?? ArchiveState::COMPLETE;

                if (ArchiveState::INCOMPLETE !== $state) {
                    $seriesForecasts[] = null;
                    $previousForecastValue = null;
                    continue;
                }

                $currentValue = (float) $currentValueRaw;
                $dataTable = $dataTableList[$tickIndex] ?? null;

                if (empty($dataTable)) {
                    $seriesForecasts[] = null;
                    $previousForecastValue = null;
                    continue;
                }

                $elapsedRatio = $this->getElapsedRatio($dataTable, $site);
                $ratio = max($elapsedRatio, self::MIN_FORECAST_RATIO);
                $linearForecast = $currentValue / $ratio;

                $pastValues = $this->getHistoricalSamplesForSeries(
                    $seriesData,
                    $dataTableList,
                    $dataStates,
                    $tickIndex,
                    $dataTable,
                    $seriesDataAvailability
                );
                $baseForecast = $linearForecast;
                $priorForecast = $linearForecast;

                if ([] !== $pastValues) {
                    $priorForecast = array_sum($pastValues) / count($pastValues);
                }

                $periodLabel = $this->getPeriodLabel($dataTable);
                $weight = $this->getPriorForecastWeight(count($pastValues), $periodLabel);

                $hasCurrentData = $seriesDataAvailability[$tickIndex] ?? true;
                $preferHistoricalBase = $isPercentSeries && !$hasCurrentData;

                if ($currentValue <= 0) {
                    if ($preferHistoricalBase && [] !== $pastValues) {
                        $baseForecast = $priorForecast;
                    } elseif ($previousForecastValue !== null) {
                        $baseForecast = $previousForecastValue;
                    } elseif ($preferHistoricalBase) {
                        // Percent series with no current data, no historical samples,
                        // and no carry-forward state: the synthetic 0 carries no signal.
                        $seriesForecasts[] = null;
                        $previousForecastValue = null;
                        continue;
                    }
                }

                $forecastValue = $this->blendForecastValue($baseForecast, $priorForecast, $weight);

                if ($baseForecast >= 0 && $priorForecast >= 0) {
                    $forecastValue = max(0, $forecastValue);
                }

                if (!$this->shouldRenderForecastValue($forecastValue, $currentValue)) {
                    $seriesForecasts[] = null;
                    $previousForecastValue = null;
                    continue;
                }

                $roundedForecast = round($forecastValue, 4);
                $seriesForecasts[] = $roundedForecast;
                $previousForecastValue = $roundedForecast;
            }

            $forecastData[] = $seriesForecasts;
        }

        return $forecastData;
    }

    /**
     * @param array<int, float|int> $seriesData
     * @param array<int, DataTable> $dataTableList
     * @param array<int, string> $dataStates
     * @param array<int, bool> $seriesDataAvailability
     * @return array<int, float>
     */
    private function getHistoricalSamplesForSeries(
        array $seriesData,
        array $dataTableList,
        array $dataStates,
        int $currentTickIndex,
        DataTable $currentDataTable,
        array $seriesDataAvailability = []
    ): array {
        $samples = [];
        $periodLabel = $this->getPeriodLabel($currentDataTable);
        $currentWeekDay = (int) $this->getPeriodStartDayOfWeek($currentDataTable);

        for ($tickIndex = 0; $tickIndex < $currentTickIndex; ++$tickIndex) {
            if (($dataStates[$tickIndex] ?? null) !== ArchiveState::COMPLETE) {
                continue;
            }

            if (!isset($seriesData[$tickIndex])) {
                continue;
            }

            if (($seriesDataAvailability[$tickIndex] ?? true) === false) {
                continue;
            }

            $value = (float) $seriesData[$tickIndex];
            $dataTable = $dataTableList[$tickIndex] ?? null;

            if (empty($dataTable)) {
                continue;
            }

            if ('day' === $periodLabel) {
                if ((int) $this->getPeriodStartDayOfWeek($dataTable) !== $currentWeekDay) {
                    continue;
                }
            }

            $samples[] = $value;
        }

        return $this->removeLeadingZeroSamples($samples);
    }

    /**
     * @param array<int, float> $samples
     * @return array<int, float>
     */
    private function removeLeadingZeroSamples(array $samples): array
    {
        while ([] !== $samples && 0.0 === (float) reset($samples)) {
            array_shift($samples);
        }

        return array_values($samples);
    }

    private function shouldRenderForecastValue(float $forecastValue, float $currentDisplayValue): bool
    {
        return $forecastValue >= $currentDisplayValue;
    }

    private function blendForecastValue(float $baseForecast, float $priorForecast, float $weight): float
    {
        return ((1 - $weight) * $baseForecast) + ($weight * $priorForecast);
    }

    private function getPriorForecastWeight(int $sampleCount, string $periodLabel): float
    {
        if ($sampleCount <= 0) {
            return 0.0;
        }

        if ('day' === $periodLabel) {
            return min(0.7, $sampleCount / 5);
        }

        return min(0.5, $sampleCount / 4);
    }

    private function getElapsedRatio(DataTable $dataTable, Site $site): float
    {
        /** @var Period $period */
        $period = $dataTable->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX);
        $siteTz = $site->getTimezone();

        // date1/date2 are site-local wall-clock; setTimezone($siteTz)->getTimestamp() returns
        // the real UTC instant of that wall-clock moment.
        $startTs = $period->getDateTimeStart()->setTimezone($siteTz)->getTimestamp();
        $endTs = $period->getDateTimeEnd()->setTimezone($siteTz)->getTimestamp();

        // ts_archived is stored as UTC, so getTimestampUTC() keeps it real UTC; cap by current
        // real UTC so the ratio reflects actual elapsed time in the site's day.
        $elapsedTs = Date::now()->getTimestamp();
        $archivedDateStr = $dataTable->getMetadata(DataTable::ARCHIVED_DATE_METADATA_NAME);
        if (!empty($archivedDateStr)) {
            $archivedTs = Date::factory($archivedDateStr)->getTimestampUTC();
            if ($archivedTs < $elapsedTs) {
                $elapsedTs = $archivedTs;
            }
        }

        $elapsedTs = min($elapsedTs, $endTs);

        if ($elapsedTs <= $startTs || $endTs <= $startTs) {
            return 0.0;
        }

        return min(1.0, max(0.0, ($elapsedTs - $startTs) / ($endTs - $startTs)));
    }

    private function getPeriodLabel(DataTable $dataTable): string
    {
        /** @var Period $period */
        $period = $dataTable->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX);
        return $period->getLabel();
    }

    private function getPeriodStartDayOfWeek(DataTable $dataTable): string
    {
        /** @var Period $period */
        $period = $dataTable->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX);
        return $period->getDateStart()->toString('N');
    }
}
