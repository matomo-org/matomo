<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Actions\API;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Plugins\Actions\Columns\ExitPageTitle;
use Piwik\Plugins\Actions\Columns\PageTitle;

class GetExitPageTitles extends Base
{
    protected function init()
    {
        parent::init();

        $this->dimension     = new ExitPageTitle();
        $this->name          = Piwik::translate('Actions_ExitPageTitles');
        $this->documentation = Piwik::translate('Actions_EntryPageTitlesReportDocumentation', '<br />')
                             . ' ' . Piwik::translate('General_UsePlusMinusIconsDocumentation');

        $this->metrics = array_keys($this->getMetrics());
        $this->order   = 7;

        $this->actionToLoadSubTables = $this->action;

        $this->widgetTitle = 'Actions_WidgetExitPageTitles';
    }

    protected function getMetrics()
    {
        return array(
            'exit_nb_visits' => Piwik::translate('General_ColumnExits'),
            'nb_visits'      => Piwik::translate('General_ColumnUniquePageviews'),
            'exit_rate'      => Piwik::translate('General_ColumnExitRate')
        );
    }

    protected function getMetricsDocumentation()
    {
        return array(
            'exit_nb_visits' => Piwik::translate('General_ColumnExitsDocumentation'),
            'nb_visits'      => Piwik::translate('General_ColumnUniquePageviewsDocumentation'),
            'exit_rate'      => Piwik::translate('General_ColumnExitRateDocumentation')
        );
    }

    public function configureView(ViewDataTable $view)
    {
        $exitPageUrlAction =
            Common::getRequestVar('widget', false) === false ? 'indexExitPageUrls' : 'getExitPageUrls';

        $view->config->addTranslations(array(
            'label'          => $this->dimension->getName(),
            'exit_nb_visits' => Piwik::translate('General_ColumnExits'),
        ));
        $view->config->addRelatedReports(array(
            'Actions.getPageTitles'      => Piwik::translate('Actions_SubmenuPageTitles'),
            "Actions.$exitPageUrlAction" => Piwik::translate('Actions_SubmenuPagesExit'),
        ));

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
