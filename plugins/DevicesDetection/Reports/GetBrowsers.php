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
use Piwik\Plugins\DevicesDetection\Columns\BrowserName;
use Piwik\Plugin\ReportsProvider;

class GetBrowsers extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new BrowserName();
        $this->name          = Piwik::translate('DevicesDetection_Browsers');
        $this->documentation = Piwik::translate('DevicesDetection_WidgetBrowsersDocumentation');
        $this->order = 5;
        $this->subcategoryId = 'DevicesDetection_Software';
        $this->hasGoalMetrics = true;
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->title = $this->name;
        $view->config->show_search = true;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', $this->dimension->getName());
    }

    public function getRelatedReports()
    {
        return array(
            ReportsProvider::factory('DevicesDetection', 'getBrowserVersions'),
        );
    }
}
