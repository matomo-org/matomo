<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\Referrers\Columns\Keyword;

class GetKeywords extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Keyword();
        $this->name          = Piwik::translate('Referrers_Keywords');
        $this->documentation = Piwik::translate('Referrers_KeywordsReportDocumentation', '<br />');
        $this->actionToLoadSubTables = 'getSearchEnginesFromKeywordId';
        $this->order = 3;
        $this->widgetTitle  = 'Referrers_WidgetKeywords';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->subtable_controller_action  = 'getSearchEnginesFromKeywordId';
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate('General_ColumnKeyword'));
        $view->config->show_goals = true;

        $view->requestConfig->filter_limit = 25;

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->disable_subtable_when_show_goals = true;
        }
    }

}
