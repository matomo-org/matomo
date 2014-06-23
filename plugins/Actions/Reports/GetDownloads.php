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
use Piwik\Plugins\Actions\Columns\DownloadUrl;

class GetDownloads extends Base
{
    protected function init()
    {
        parent::init();

        $this->dimension     = new DownloadUrl();
        $this->name          = Piwik::translate('General_Downloads');
        $this->documentation = Piwik::translate('Actions_DownloadsReportDocumentation', '<br />');
        $this->metrics       = array('nb_visits', 'nb_hits');

        $this->actionToLoadSubTables = $this->action;
        $this->order = 9;

        $this->menuTitle    = 'General_Downloads';
        $this->widgetTitle  = 'General_Downloads';
    }

    public function getMetrics()
    {
        return array(
            'nb_visits' => Piwik::translate('Actions_ColumnUniqueDownloads'),
            'nb_hits'   => Piwik::translate('General_Downloads')
        );
    }

    protected function getMetricsDocumentation()
    {
        return array(
            'nb_visits' => Piwik::translate('Actions_ColumnUniqueClicksDocumentation'),
            'nb_hits'   => Piwik::translate('Actions_ColumnClicksDocumentation')
        );
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->addTranslations(array('label' => $this->dimension->getName()));

        $view->config->columns_to_display = array('label', 'nb_visits', 'nb_hits');
        $view->config->show_exclude_low_population = false;

        $this->addBaseDisplayProperties($view);
    }
}
