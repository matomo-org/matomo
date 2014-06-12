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
use Piwik\Plugins\Actions\Columns\PageUrl;

class GetPageUrls extends Base
{
    protected function init()
    {
        parent::init();

        $this->dimension     = new PageUrl();
        $this->name          = Piwik::translate('Actions_PageUrls');
        $this->title         = Piwik::translate('General_Pages');
        $this->documentation = Piwik::translate('Actions_PagesReportDocumentation', '<br />')
                             . '<br />' . Piwik::translate('General_UsePlusMinusIconsDocumentation');

        $this->actionToLoadSubTables = $this->action;
        $this->order   = 1;
        $this->metrics = array_keys($this->getMetrics());

        $this->segmentSql = 'log_visit.visit_entry_idaction_url';

        $this->menuTitle   = 'General_Pages';
        $this->widgetTitle = 'General_Pages';
    }

    protected function getMetrics()
    {
        return array(
            'nb_hits'             => Piwik::translate('General_ColumnPageviews'),
            'nb_visits'           => Piwik::translate('General_ColumnUniquePageviews'),
            'bounce_rate'         => Piwik::translate('General_ColumnBounceRate'),
            'avg_time_on_page'    => Piwik::translate('General_ColumnAverageTimeOnPage'),
            'exit_rate'           => Piwik::translate('General_ColumnExitRate'),
            'avg_time_generation' => Piwik::translate('General_ColumnAverageGenerationTime')
        );
    }

    protected function getMetricsDocumentation()
    {
        return array(
            'nb_hits'             => Piwik::translate('General_ColumnPageviewsDocumentation'),
            'nb_visits'           => Piwik::translate('General_ColumnUniquePageviewsDocumentation'),
            'bounce_rate'         => Piwik::translate('General_ColumnPageBounceRateDocumentation'),
            'avg_time_on_page'    => Piwik::translate('General_ColumnAverageTimeOnPageDocumentation'),
            'exit_rate'           => Piwik::translate('General_ColumnExitRateDocumentation'),
            'avg_time_generation' => Piwik::translate('General_ColumnAverageGenerationTimeDocumentation'),
        );
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->addTranslation('label', $this->dimension->getName());
        $view->config->columns_to_display = array('label', 'nb_hits', 'nb_visits', 'bounce_rate',
                                                  'avg_time_on_page', 'exit_rate', 'avg_time_generation');

        $this->addPageDisplayProperties($view);
        $this->addBaseDisplayProperties($view);
    }
}
