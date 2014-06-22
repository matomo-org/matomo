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
use Piwik\Plugins\Actions\Columns\ExitPageTitle;

class GetExitPageTitles extends Base
{
    protected function init()
    {
        parent::init();

        $this->dimension     = new ExitPageTitle();
        $this->name          = Piwik::translate('Actions_ExitPageTitles');
        $this->documentation = Piwik::translate('Actions_EntryPageTitlesReportDocumentation', '<br />')
                             . ' ' . Piwik::translate('General_UsePlusMinusIconsDocumentation');

        $this->metrics = array('exit_nb_visits', 'nb_visits', 'exit_rate');
        $this->order   = 7;

        $this->actionToLoadSubTables = $this->action;

        $this->widgetTitle = 'Actions_WidgetExitPageTitles';
    }

    public function getMetrics()
    {
        $metrics = parent::getMetrics();
        $metrics['nb_visits'] = Piwik::translate('General_ColumnUniquePageviews');

        return $metrics;
    }

    protected function getMetricsDocumentation()
    {
        $metrics = parent::getMetricsDocumentation();
        $metrics['nb_visits'] = Piwik::translate('General_ColumnUniquePageviewsDocumentation');

        return $metrics;
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->addTranslations(array('label' => $this->dimension->getName()));

        $view->config->title = $this->name;
        $view->config->columns_to_display = array('label', 'exit_nb_visits', 'nb_visits', 'exit_rate');

        $this->addPageDisplayProperties($view);
        $this->addBaseDisplayProperties($view);
    }

    public function getRelatedReports()
    {
        return array(
            new GetPageTitles(),
            new GetExitPageUrls()
        );
    }
}
