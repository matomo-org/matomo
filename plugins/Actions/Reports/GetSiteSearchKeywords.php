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
use Piwik\Plugins\Actions\Columns\Keyword;
use Piwik\Plugins\Actions\Columns\Metrics\AveragePageGenerationTime;
use Piwik\Plugins\Actions\Columns\Metrics\AverageTimeOnPage;
use Piwik\Plugins\Actions\Columns\Metrics\BounceRate;
use Piwik\Plugins\Actions\Columns\Metrics\ExitRate;

class GetSiteSearchKeywords extends SiteSearchBase
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Keyword();
        $this->name          = Piwik::translate('Actions_WidgetSearchKeywords');
        $this->documentation = Piwik::translate('Actions_SiteSearchKeywordsDocumentation') . '<br/><br/>' . Piwik::translate('Actions_SiteSearchIntro') . '<br/><br/>'
                             . '<a href="http://piwik.org/docs/site-search/" rel="noreferrer"  target="_blank">' . Piwik::translate('Actions_LearnMoreAboutSiteSearchLink') . '</a>';
        $this->metrics       = array('nb_visits', 'nb_pages_per_search');
        $this->processedMetrics = array(
            new AverageTimeOnPage(),
            new BounceRate(),
            new ExitRate(),
            new AveragePageGenerationTime()
        );
        $this->order = 15;
        $this->widgetTitle  = 'Actions_WidgetSearchKeywords';
    }

    public function getMetrics()
    {
        return array(
            'nb_visits'           => Piwik::translate('Actions_ColumnSearches'),
            'nb_pages_per_search' => Piwik::translate('Actions_ColumnPagesPerSearch'),
        );
    }

    public function getProcessedMetrics()
    {
        return array(
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
        $view->config->addTranslation('label', $this->dimension->getName());
        $view->config->columns_to_display = array('label', 'nb_visits', 'nb_pages_per_search', 'exit_rate');

        $this->addSiteSearchDisplayProperties($view);
    }
}