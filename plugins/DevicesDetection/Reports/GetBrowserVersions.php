<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\DevicesDetection\Columns\BrowserVersion;
use Piwik\Plugin\ReportsProvider;

class GetBrowserVersions extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new BrowserVersion();
        $this->name          = Piwik::translate('DevicesDetection_BrowserVersion');
        $this->documentation = Piwik::translate('DevicesDetection_WidgetBrowserVersionsDocumentation');
        $this->order = 6;
        $this->subcategoryId = 'DevicesDetection_Software';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_search = true;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', $this->dimension->getName());
    }

    public function getRelatedReports()
    {
        return array(
            ReportsProvider::factory('DevicesDetection', 'getBrowsers'),
        );
    }
}
