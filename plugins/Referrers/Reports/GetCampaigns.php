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
use Piwik\Plugins\Referrers\Columns\Campaign;

class GetCampaigns extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Campaign();
        $this->name          = Piwik::translate('Referrers_Campaigns');
        $this->documentation = Piwik::translate('Referrers_CampaignsReportDocumentation');
        $this->onlineGuideUrl = 'https://matomo.org/docs/tracking-campaigns/';
        $this->actionToLoadSubTables = 'getKeywordsFromCampaignId';
        $this->hasGoalMetrics = true;
        $this->order = 9;

        $this->subcategoryId = 'Referrers_Campaigns';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', $this->dimension->getName());

        $view->requestConfig->filter_limit = 25;

        $this->configureFooterMessage($view);
    }


    protected function configureFooterMessage(ViewDataTable $view)
    {
        if ($this->isSubtableReport) {
            // no footer message for subtables
            return;
        }

        $out = '';
        EventDispatcher::getInstance()->postEvent('Template.afterCampaignsReport', array(&$out));
        $view->config->show_footer_message = $out;
    }
}
