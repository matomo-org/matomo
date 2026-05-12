<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph;

use Piwik\API\Request as ApiRequest;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Period\Factory;
use Piwik\Period\Range;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\Evolution as JqplotEvolutionData;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastSeriesState;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph;
use Piwik\Plugins\CoreVisualizations\Visualizations\EvolutionPeriodSelector;
use Piwik\Site;

/**
 * Visualization that renders HTML for a line graph using jqPlot.
 *
 * @property Evolution\Config $config
 */
class Evolution extends JqplotGraph
{
    public const ID = 'graphEvolution';
    public const SERIES_COLOR_COUNT = 8;

    /**
     * Precomputed forecast values, keyed by series index then tick index. Populated
     * by afterAllFiltersAreApplied() so beforeRender() can decide whether to expose
     * the forecast toggle, and so the data generator can reuse the same result.
     *
     * @var array<int, array<int, float|null>>
     */
    private $forecastData = [];

    /**
     * Per-series state collected by JqplotDataGenerator\Evolution::precomputeForecast()
     * so the later initChartObjectData() pass can skip its row × column loop. Null when
     * no precompute ran.
     *
     * @var ForecastSeriesState|null
     */
    private $forecastSeriesState = null;

    public static function getDefaultConfig()
    {
        return new Evolution\Config();
    }

    /**
     * @return array<int, array<int, float|null>>
     */
    public function getForecastData(): array
    {
        return $this->forecastData;
    }

    public function setForecastSeriesState(?ForecastSeriesState $state): void
    {
        $this->forecastSeriesState = $state;
    }

    public function getForecastSeriesState(): ?ForecastSeriesState
    {
        return $this->forecastSeriesState;
    }

    public function beforeRender()
    {
        parent::beforeRender();

        $this->checkRequestIsOnlyForMultiplePeriods();

        $this->config->show_flatten_table = false;
        $this->config->datatable_js_type = 'JqplotEvolutionGraphDataTable';

        if (!$this->isComparing() && $this->shouldShowForecastToggle()) {
            $this->config->datatable_actions[] = [
                'id' => 'dataTableShowForecast',
                'title' => $this->config->show_forecast
                    ? \Piwik\Piwik::translate('CoreHome_HideForecast')
                    : \Piwik\Piwik::translate('CoreHome_ShowForecast'),
                'icon' => $this->config->show_forecast ? 'icon-show' : 'icon-hide',
            ];
        }
    }

    public function beforeLoadDataTable()
    {
        $isComparingDatesOrPeriods = $this->isComparingDatesOrPeriods();

        if (!$this->isComparing() || !$isComparingDatesOrPeriods) {
            $this->calculateEvolutionDateRange();
        }

        parent::beforeLoadDataTable();

        // fetch archive states for incomplete data point visualization
        $this->requestConfig->request_parameters_to_modify['fetch_archive_state'] = true;

        // period will be overridden when 'range' is requested in the UI.
        // The graph will display the range in the most suitable period and
        // it won't show historical data before the range.
        $period = Common::getRequestVar('period', false);
        $selector = StaticContainer::get(EvolutionPeriodSelector::class);

        if ($period === 'range') {
            $date = Common::getRequestVar('date', false);
            $requestingPeriod = Factory::build($period, $date);

            // if a larger date range is selected, then for better performance and for seeing trends better we want to use
            // a suitable period (rather than always using for example the day range)
            $this->requestConfig->request_parameters_to_modify['period'] = $selector->getHighestPeriodInCommon($requestingPeriod, []);
            $this->requestConfig->request_parameters_to_modify['date'] = $requestingPeriod->getRangeString();
        }

        // Forecast values can only be drawn by the LineRenderer. Force-off when the viz is in
        // bar mode (subclass override or ?show_line_graph=0 query param) so a stale
        // show_forecast=1 cannot sneak forecast computation into a bar-mode render.
        if (!$this->config->show_line_graph) {
            $this->config->show_forecast = false;
        }

        $this->config->custom_parameters['columns'] = $this->config->columns_to_display;
        $this->config->custom_parameters['show_forecast'] = (int) $this->config->show_forecast;

        if ($this->isComparing() && $isComparingDatesOrPeriods) {
            $this->config->show_limit_control = false; // since we always show the evolution over the period, there's no point in changing the limit
            $this->config->show_periods = false; // the periods can't be changed as they are always fixed when comparing

            $requestArray = $this->request->getRequestArray();
            $requestArray = ApiRequest::getRequestArrayFromString($requestArray);

            $requestingPeriod = Factory::build($requestArray['period'], $requestArray['date']);

            $comparisonPeriods = [];
            if (!empty($requestArray['comparePeriods'])) {
                $comparisonPeriods = $selector->getComparisonPeriodObjects($requestArray['comparePeriods'], $requestArray['compareDates']);
            }

            $this->requestConfig->request_parameters_to_modify = $selector->setDatePeriods(
                $this->requestConfig->request_parameters_to_modify,
                $requestingPeriod,
                $comparisonPeriods,
                true
            );
        }
    }

    private function isComparingDatesOrPeriods(): bool
    {
        $comparePeriods = Common::getRequestVar('comparePeriods', [], 'array');
        $compareDates = Common::getRequestVar('compareDates', [], 'array');

        return !empty($comparePeriods) || !empty($compareDates);
    }

    public function afterAllFiltersAreApplied()
    {
        parent::afterAllFiltersAreApplied();

        if (false === $this->config->x_axis_step_size) {
            $rowCount = $this->dataTable->getRowsCount();

            $this->config->x_axis_step_size = $this->getDefaultXAxisStepSize($rowCount);
        }

        // Only pay for the per-series builder when the user has the forecast turned on.
        // When it is off, the toggle visibility falls back to the cheap "any incomplete tick"
        // check below so dashboards full of evolution widgets do not run the regression on
        // every render just to size an action button.
        if ($this->config->show_forecast && !$this->config->disable_forecast) {
            $this->forecastData = $this->precomputeForecastData();
        }
    }

    protected function makeDataGenerator($properties)
    {
        return JqplotDataGenerator::factory('evolution', $properties, $this);
    }

    /**
     * @return array<int, array<int, float|null>>
     */
    private function precomputeForecastData(): array
    {
        if ($this->isComparing()) {
            return [];
        }

        /** @var DataTable|DataTable\Map|null $dataTable */
        $dataTable = $this->dataTable;

        if (!$dataTable instanceof DataTable\Map) {
            return [];
        }

        // Same merge order as Visualization::render() when it populates
        // $view->properties, so the precomputed forecast sees the same property
        // set the rendered chart will.
        $properties = array_merge(
            $this->requestConfig->getProperties(),
            $this->config->getProperties()
        );

        /** @var JqplotDataGenerator\Evolution $dataGenerator */
        $dataGenerator = $this->makeDataGenerator($properties);

        return $dataGenerator->precomputeForecast($dataTable);
    }

    private function shouldShowForecastToggle(): bool
    {
        // Forecast values are only meaningful for line charts. Bar-mode evolution (either via
        // ?show_line_graph=0 on this viz, or a bar-rendering subclass like StackedBarEvolution)
        // has no LineRenderer to draw forecast points onto, so suppress the toggle entirely.
        if (!$this->config->show_line_graph) {
            return false;
        }

        // Contexts that fan out into label-filtered inner API calls (row evolution popovers)
        // opt out of forecast entirely: each forecast render would trigger 70 days of daily
        // plus multi-year monthly sub-period fetches with the label filter still attached,
        // pulling subtable blobs for every tick.
        if ($this->config->disable_forecast) {
            return false;
        }

        // When forecast is on we already paid for precompute; honour its verdict so the
        // "Hide forecast" action does not appear on graphs where every value got suppressed.
        // The asymmetry with the show_forecast=0 branch below is intentional: when every
        // forecast value is null there is nothing rendered for "Hide forecast" to hide, so a
        // visible action would be a no-op click. The saved show_forecast=1 param survives and
        // re-engages automatically once the user navigates to a graph or date range where the
        // algorithm produces at least one renderable value.
        if ($this->config->show_forecast) {
            return $this->hasAnyForecastValue();
        }

        // When forecast is off, decide visibility from the cheapest possible signal.
        // Feasibility is re-checked at render time once the user toggles the action on.
        return $this->hasAnyIncompleteTick();
    }

    private function hasAnyForecastValue(): bool
    {
        foreach ($this->forecastData as $seriesValues) {
            foreach ($seriesValues as $value) {
                if (null !== $value) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasAnyIncompleteTick(): bool
    {
        /** @var DataTable|DataTable\Map|null $dataTable */
        $dataTable = $this->dataTable;

        if (!$dataTable instanceof DataTable\Map) {
            return false;
        }

        // Defer to the shared classifier so the toggle-visibility check uses the same
        // rule as the per-tick state computed during render. Without the siteToday
        // branch the toggle stays hidden on low-volume reports (Goals/Ecommerce on
        // days with no qualifying events yet) where archive_state metadata is absent
        // even though the period covering today is, by definition, still in progress.
        $idSite = Common::getRequestVar('idSite', 0, 'int');
        if ($idSite <= 0) {
            return false;
        }
        $siteToday = Date::factoryInTimezone('today', Site::getTimezoneFor($idSite))->getTimestamp();

        foreach ($dataTable->getDataTables() as $childTable) {
            if (JqplotEvolutionData::isIncompleteTick($childTable, $siteToday)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Based on the period, date and evolution_{$period}_last_n query parameters,
     * calculates the date range this evolution chart will display data for.
     */
    private function calculateEvolutionDateRange()
    {
        $period = Common::getRequestVar('period');
        $idSite = Common::getRequestVar('idSite');
        $timezone = Site::getTimezoneFor($idSite);

        $lastNParamName = self::getLastNParamName($period);
        $defaultLastN = $this->config->custom_parameters[$lastNParamName] ?? self::getDefaultLastN($period);
        $originalDate = Common::getRequestVar('date', 'last' . $defaultLastN, 'string');

        if ('range' != $period) { // show evolution limit if the period is not a range
            // set the evolution_{$period}_last_n query param
            if (Range::parseDateRange($originalDate)) {
                // if a multiple period

                // overwrite last_n param using the date range
                $oPeriod = new Range($period, $originalDate, $timezone);
                $lastN   = count($oPeriod->getSubperiods());
            } else {
                // if not a multiple period
                list($newDate, $lastN) = self::getDateRangeAndLastN($period, $originalDate, $defaultLastN);
                $this->requestConfig->request_parameters_to_modify['date'] = $newDate;
                $this->config->custom_parameters['dateUsedInGraph'] = $newDate;
            }

            $this->config->custom_parameters[$lastNParamName] = $lastN;
        }
    }

    /**
     * Returns the entire date range and lastN value for the current request, based on
     * a period type and end date.
     *
     * @param string $period The period type, 'day', 'week', 'month' or 'year'
     * @param string $endDate The end date.
     * @param int|null $defaultLastN The default lastN to use. If null, the result of
     *                               getDefaultLastN is used.
     * @param int|null $idSite, the id of the site which provides the timezone
     * @return array An array w/ two elements. The first is a whole date range and the second
     *               is the lastN number used, ie, array('2010-01-01,2012-01-02', 2).
     */
    public static function getDateRangeAndLastN($period, $endDate, $defaultLastN = null, ?int $idSite = null)
    {
        if ($defaultLastN === null) {
            $defaultLastN = self::getDefaultLastN($period);
        }

        $lastNParamName = self::getLastNParamName($period);
        $lastN = Common::getRequestVar($lastNParamName, $defaultLastN, 'int');

        $idSite = $idSite ?? Common::getRequestVar('idSite');
        $site = new Site($idSite);

        $dateRange = Range::getRelativeToEndDate($period, 'last' . $lastN, $endDate, $site);

        return array($dateRange, $lastN);
    }

    /**
     * Returns the default last N number of dates to display for a given period.
     *
     * @param string $period 'day', 'week', 'month' or 'year'
     * @return int
     */
    public static function getDefaultLastN($period)
    {
        switch ($period) {
            case 'week':
                return 26;
            case 'month':
                return 24;
            case 'year':
                return 5;
            case 'day':
            default:
                return 30;
        }
    }

    /**
     * Returns the query parameter that stores the lastN number of periods to get for
     * the evolution graph.
     *
     * @param string $period The period type, 'day', 'week', 'month' or 'year'.
     * @return string
     */
    public static function getLastNParamName($period)
    {
        return "evolution_{$period}_last_n";
    }

    public function getDefaultXAxisStepSize($countGraphElements)
    {
        // when the number of elements plotted can be small, make sure the X legend is useful
        if ($countGraphElements <= 7) {
            return 1;
        }

        $periodLabel = Common::getRequestVar('period');

        switch ($periodLabel) {
            case 'day':
            case 'range':
                $steps = 5;
                break;
            case 'week':
                $steps = 4;
                break;
            case 'month':
                $steps = 5;
                break;
            case 'year':
                $steps = 5;
                break;
            default:
                $steps = 5;
                break;
        }

        $paddedCount = $countGraphElements + 2; // pad count so last label won't be cut off

        return ceil($paddedCount / $steps);
    }

    public function supportsComparison()
    {
        return true;
    }

    protected function ensureValidColumnsToDisplay()
    {
        parent::ensureValidColumnsToDisplay();

        $columnsToDisplay = $this->config->columns_to_display;

        // Use a sensible default if the columns_to_display is empty
        $this->config->columns_to_display = $columnsToDisplay ? : array('nb_visits');
    }
}
