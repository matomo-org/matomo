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

abstract class SiteSearchBase extends Base
{
    protected function init()
    {
        parent::init();
        $this->category = 'Actions_SubmenuSitesearch';
    }

    public function isEnabled()
    {
        $actions = new Actions();
        return $actions->isSiteSearchEnabled();
    }

    protected function addSiteSearchDisplayProperties(ViewDataTable $view)
    {
        $view->config->addTranslations(array(
            'nb_visits'           => Piwik::translate('Actions_ColumnSearches'),
            'exit_rate'           => str_replace("% ", "%&nbsp;", Piwik::translate('Actions_ColumnSearchExits')),
            'nb_pages_per_search' => Piwik::translate('Actions_ColumnPagesPerSearch')
        ));

        $view->config->show_bar_chart         = false;
        $view->config->show_table_all_columns = false;
    }
}
