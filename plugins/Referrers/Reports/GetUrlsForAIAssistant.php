<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Referrers\Columns\WebsitePage;

class GetUrlsForAIAssistant extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new WebsitePage();
        $this->name = Piwik::translate('Referrers_AIAssistants');
        $this->documentation = Piwik::translate('Referrers_WebsitesReportDocumentation', '<br />');
        $this->isSubtableReport = true;
        $this->order = 14;
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_goals = true;
        $view->config->show_exclude_low_population = false;

        $view->requestConfig->filter_limit = 10;
    }
}
