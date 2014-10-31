<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Actions\Actions;
use Piwik\Plugins\Actions\Columns\SearchCategory;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

class GetSiteSearchCategories extends SiteSearchBase
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new SearchCategory();
        $this->name          = Piwik::translate('Actions_WidgetSearchCategories');
        $this->documentation = Piwik::translate('Actions_SiteSearchCategories1') . '<br/>' . Piwik::translate('Actions_SiteSearchCategories2');
        $this->metrics       = array('nb_visits', 'nb_pages_per_search', 'exit_rate');
        $this->order = 17;
        $this->widgetTitle  = 'Actions_WidgetSearchCategories';
    }

    protected function isEnabledForIdSites($idSites, $idSite)
    {
        return parent::isEnabledForIdSites($idSites, $idSite) && Actions::isCustomVariablesPluginsEnabled();
    }

    public function getMetrics()
    {
        return array(
            'nb_visits'           => Piwik::translate('Actions_ColumnSearches'),
            'nb_pages_per_search' => Piwik::translate('Actions_ColumnPagesPerSearch'),
            'exit_rate'           => Piwik::translate('Actions_ColumnSearchExits'),
        );
    }

    protected function getMetricsDocumentation()
    {
        return array(
            'nb_visits'           => Piwik::translate('Actions_ColumnSearchesDocumentation'),
            'nb_pages_per_search' => Piwik::translate('Actions_ColumnPagesPerSearchDocumentation'),
            'exit_rate'           => Piwik::translate('Actions_ColumnSearchExitsDocumentation'),
        );
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->addTranslations(array('label' => $this->dimension->getName()));

        $view->config->columns_to_display     = array('label', 'nb_visits', 'nb_pages_per_search');
        $view->config->show_table_all_columns = false;
        $view->config->show_bar_chart         = false;

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->disable_row_evolution = false;
        }
    }
}
