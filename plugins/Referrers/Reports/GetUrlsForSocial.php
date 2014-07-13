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
use Piwik\Plugins\Referrers\Columns\WebsitePage;

class GetUrlsForSocial extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new WebsitePage();
        $this->name          = Piwik::translate('Referrers_Socials');
        $this->documentation = Piwik::translate('Referrers_WebsitesReportDocumentation', '<br />');
        $this->isSubtableReport = true;
        $this->order = 12;
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_goals = true;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', $this->dimension->getName());

        $view->requestConfig->filter_limit = 10;
    }

}
