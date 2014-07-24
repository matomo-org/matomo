<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Referrers\Columns\SearchEngine;

class GetSearchEnginesFromKeywordId extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new SearchEngine();
        $this->name          = Piwik::translate('Referrers_Keywords');
        $this->documentation = Piwik::translate('Referrers_KeywordsReportDocumentation', '<br />');
        $this->isSubtableReport = true;
        $this->order = 4;
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', $this->dimension->getName());
    }

}
