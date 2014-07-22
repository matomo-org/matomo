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
use Piwik\Plugins\Actions\Columns\EntryPageTitle;

class GetEntryPageTitles extends Base
{
    protected function init()
    {
        parent::init();

        $this->dimension     = new EntryPageTitle();
        $this->name          = Piwik::translate('Actions_EntryPageTitles');
        $this->documentation = Piwik::translate('Actions_ExitPageTitlesReportDocumentation', '<br />')
                             . ' ' . Piwik::translate('General_UsePlusMinusIconsDocumentation');
        $this->metrics = array('entry_nb_visits', 'entry_bounce_count', 'bounce_rate');
        $this->order   = 6;
        $this->actionToLoadSubTables = $this->action;

        $this->widgetTitle = 'Actions_WidgetEntryPageTitles';
    }

    protected function getMetricsDocumentation()
    {
        $metrics = parent::getMetricsDocumentation();
        $metrics['bounce_rate'] = Piwik::translate('General_ColumnBounceRateForPageDocumentation');

        return $metrics;
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->addTranslations(array('label' => $this->dimension->getName()));

        $view->config->columns_to_display = array('label', 'entry_nb_visits', 'entry_bounce_count', 'bounce_rate');
        $view->config->title = $this->name;

        $view->requestConfig->filter_sort_column = 'entry_nb_visits';

        $this->addPageDisplayProperties($view);
        $this->addBaseDisplayProperties($view);
    }

    public function getRelatedReports()
    {
        return array(
            new GetPageTitles(),
            new GetEntryPageUrls()
        );
    }
}
