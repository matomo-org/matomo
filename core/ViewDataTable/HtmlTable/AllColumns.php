<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\ViewDataTable\HtmlTable;

use Piwik\Controller;
use Piwik\ViewDataTable\HtmlTable;

/**
 * @package Piwik
 * @subpackage ViewDataTable
 */
class AllColumns extends HtmlTable
{
    /**
     * Returns dataTable id for view
     *
     * @return string
     */
    protected function getViewDataTableId()
    {
        return 'tableAllColumns';
    }

    public function main()
    {
        $this->viewProperties['show_exclude_low_population'] = true;
        parent::main();
    }

    protected function getRequestArray()
    {
        $requestArray = parent::getRequestArray();
        $requestArray['filter_add_columns_when_show_all_columns'] = 1;
        return $requestArray;
    }

    protected function postDataTableLoadedFromAPI()
    {
        $valid = parent::postDataTableLoadedFromAPI();
        if (!$valid) return false;

        $columnUniqueVisitors = false;
        if ($this->dataTableColumnsContains($this->dataTable->getColumns(), 'nb_uniq_visitors')) {
            $columnUniqueVisitors = 'nb_uniq_visitors';
        }

        // only display conversion rate for the plugins that do not provide "per goal" metrics
        // otherwise, conversion rate is meaningless as a whole (since we don't process 'cross goals' conversions)
        $columnConversionRate = false;
        if (empty($this->viewProperties['show_goals'])) {
            $columnConversionRate = 'conversion_rate';
        }
        $this->viewProperties['columns_to_display'] = array_filter(array(
            'label', 'nb_visits', $columnUniqueVisitors, 'nb_actions', 'nb_actions_per_visit', 'avg_time_on_site',
            'bounce_rate', $columnConversionRate
        ));
        $this->dataTable->filter('ColumnCallbackReplace', array('avg_time_on_site', create_function('$averageTimeOnSite',
            'return \Piwik\Piwik::getPrettyTimeFromSeconds($averageTimeOnSite);')));

        return true;
    }

    /**
     * Returns default css class for dataTable
     * @return string
     */
    public function getDefaultDataTableCssClass()
    {
        return 'dataTableAllColumns';
    }
}
