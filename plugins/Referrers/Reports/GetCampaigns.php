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
use Piwik\Plugins\Referrers\Columns\Campaign;

class GetCampaigns extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Campaign();
        $this->name          = Piwik::translate('Referrers_Campaigns');
        $this->documentation = Piwik::translate('Referrers_CampaignsReportDocumentation',
                               array('<br />', '<a href="http://piwik.org/docs/tracking-campaigns/" target="_blank">', '</a>'));
        $this->actionToLoadSubTables = 'getKeywordsFromCampaignId';
        $this->hasGoalMetrics = true;
        $this->order = 9;
        $this->widgetTitle  = 'Referrers_Campaigns';
        $this->menuTitle    = 'Referrers_Campaigns';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', $this->dimension->getName());

        $view->requestConfig->filter_limit = 25;
    }

}
