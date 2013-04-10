<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Provider
 */

/**
 *
 * @package Piwik_Provider
 */
class Piwik_Provider_Controller extends Piwik_Controller
{
    /**
     * Provider
     * @param bool $fetch
     * @return string|void
     */
    function getProvider($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__, "Provider.getProvider");

        $this->setPeriodVariablesView($view);
        $column = 'nb_visits';
        if ($view->period == 'day') {
            $column = 'nb_uniq_visitors';
        }
        $view->setColumnsToDisplay(array('label', $column));
        $view->setColumnTranslation('label', Piwik_Translate('Provider_ColumnProvider'));
        $view->setSortedColumn($column);
        $view->setLimit(5);
        $this->setMetricsVariablesView($view);
        return $this->renderView($view, $fetch);
    }

}

