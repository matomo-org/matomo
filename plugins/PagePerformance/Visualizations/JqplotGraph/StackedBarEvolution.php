<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\PagePerformance\Visualizations\JqplotGraph;

use Piwik\Common;
use Piwik\Period\Range;
use Piwik\Plugins\PagePerformance\JqplotDataGenerator;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution;
use Piwik\Site;

/**
 * Visualization that renders HTML for a line graph using jqPlot.
 *
 * @property Evolution\Config $config
 */
class StackedBarEvolution extends Evolution
{
    const ID = 'graphStackedBarEvolution';
    const FOOTER_ICON_TITLE = '';
    const FOOTER_ICON = '';

    public function beforeRender()
    {
        parent::beforeRender();

        $this->checkRequestIsOnlyForMultiplePeriods();

        $this->config->show_flatten_table = false;
        $this->config->show_limit_control = false;
        $this->config->datatable_js_type = 'JqplotStackedBarEvolutionGraphDataTable';
    }

    public function beforeLoadDataTable()
    {
        $this->calculateEvolutionDateRange();

        parent::beforeLoadDataTable();

        // period will be overridden when 'range' is requested in the UI
        // but the graph will display for each day of the range.
        // Default 'range' behavior is to return the 'sum' for the range
        if (Common::getRequestVar('period', false) == 'range') {
            $this->requestConfig->request_parameters_to_modify['period'] = 'day';
        }

        $this->config->custom_parameters['columns'] = $this->config->columns_to_display;
    }

    protected function makeDataGenerator($properties)
    {
        return new JqplotDataGenerator\StackedBarEvolution($properties, 'evolution', $this);
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

        $defaultLastN = self::getDefaultLastN($period);
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

            $lastNParamName = self::getLastNParamName($period);
            $this->config->custom_parameters[$lastNParamName] = $lastN;
        }
    }

    public function supportsComparison()
    {
        return false;
    }
}
