<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Reports;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Actions\Actions;

abstract class SiteSearchBase extends Base
{
    protected function init()
    {
        parent::init();
        $this->categoryId = 'General_Actions';
        $this->onlineGuideUrl = 'https://matomo.org/docs/site-search/';
    }

    public function isEnabled()
    {
        $idSites = Common::getRequestVar('idSites', '', 'string');
        $idSite  = Common::getRequestVar('idSite', 0, 'int');

        return $this->isEnabledForIdSites($idSites, $idSite);
    }

    protected function isEnabledForIdSites($idSites, $idSite)
    {
        $actions = new Actions();
        return $actions->isSiteSearchEnabled($idSites, $idSite);
    }

    public function configureReportMetadata(&$availableReports, $infos)
    {
        $idSite = array($infos['idSite']);

        if (!$this->isEnabledForIdSites($idSite, Common::getRequestVar('idSite', 0, 'int'))) {
            return;
        }

        $report = $this->buildReportMetadata();

        if (!empty($report)) {
            $availableReports[] = $report;
        }
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
