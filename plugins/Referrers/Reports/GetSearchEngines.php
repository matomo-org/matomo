<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Reports;

use Piwik\EventDispatcher;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\Referrers\Columns\SearchEngine;

class GetSearchEngines extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new SearchEngine();
        $this->name          = Piwik::translate('Referrers_SearchEngines');
        $this->documentation = Piwik::translate('Referrers_SearchEnginesReportDocumentation', '<br />');
        $this->actionToLoadSubTables = 'getKeywordsFromSearchEngineId';
        $this->hasGoalMetrics = true;
        $this->order = 7;

        $this->subcategoryId = 'Referrers_SubmenuSearchEngines';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_exclude_low_population = false;
        $view->config->show_search = false;
        $view->config->addTranslation('label', $this->dimension->getName());

        $view->requestConfig->filter_limit = 25;

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->disable_subtable_when_show_goals = true;
        }
        $this->configureFooterMessage($view);
    }

    private function configureFooterMessage(ViewDataTable $view)
    {
        $out = '';
        EventDispatcher::getInstance()->postEvent('Template.afterSearchEngines', array(&$out));
        $view->config->show_footer_message = $out;
    }
}
