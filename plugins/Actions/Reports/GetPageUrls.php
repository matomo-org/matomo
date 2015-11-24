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
use Piwik\Plugins\Actions\Columns\Metrics\AveragePageGenerationTime;
use Piwik\Plugins\Actions\Columns\Metrics\BounceRate;
use Piwik\Plugins\Actions\Columns\PageUrl;
use Piwik\Plugins\Actions\Columns\Metrics\ExitRate;
use Piwik\Plugins\Actions\Columns\Metrics\AverageTimeOnPage;

class GetPageUrls extends Base
{
    protected function init()
    {
        parent::init();

        $this->dimension     = new PageUrl();
        $this->name          = Piwik::translate('Actions_PageUrls');
        $this->documentation = Piwik::translate('Actions_PagesReportDocumentation', '<br />')
                             . '<br />' . Piwik::translate('General_UsePlusMinusIconsDocumentation');

        $this->actionToLoadSubTables = $this->action;
        $this->order   = 2;
        $this->metrics = array('nb_hits', 'nb_visits');
        $this->processedMetrics = array(
            new AverageTimeOnPage(),
            new BounceRate(),
            new ExitRate(),
            new AveragePageGenerationTime()
        );

        $this->menuTitle   = 'General_Pages';
        $this->widgetTitle = 'General_Pages';
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
        $metrics['bounce_rate'] = Piwik::translate('General_ColumnPageBounceRateDocumentation');

        return $metrics;
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
