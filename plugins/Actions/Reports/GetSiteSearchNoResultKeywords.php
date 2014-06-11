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
use Piwik\Plugins\Actions\Columns\KeywordwithNoSearchResult;

class GetSiteSearchNoResultKeywords extends SiteSearchBase
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new KeywordwithNoSearchResult();
        $this->name          = Piwik::translate('Actions_WidgetSearchNoResultKeywords');
        $this->documentation = Piwik::translate('Actions_SiteSearchIntro') . '<br /><br />' . Piwik::translate('Actions_SiteSearchKeywordsNoResultDocumentation');
        $this->metrics       = array('nb_visits', 'exit_rate');
        $this->order = 16;
        $this->widgetTitle  = 'Actions_WidgetSearchNoResultKeywords';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->addTranslation('label', $this->dimension->getName());
        $view->config->columns_to_display = array('label', 'nb_visits', 'exit_rate');

        $this->addSiteSearchDisplayProperties($view);
    }
}
