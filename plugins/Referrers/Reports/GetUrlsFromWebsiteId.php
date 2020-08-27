<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Referrers\Columns\WebsitePage;

class GetUrlsFromWebsiteId extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new WebsitePage();
        $this->name          = Piwik::translate('CorePluginsAdmin_Websites');
        $this->documentation = Piwik::translate('Referrers_WebsitesReportDocumentation', '<br />');
        $this->isSubtableReport = true;
        $this->order = 6;
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->tooltip_metadata_name       = 'url';
        $view->config->addTranslation('label', $this->dimension->getName());
    }

}
