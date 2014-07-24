<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\DevicesDetection\Columns\BrowserName;

class GetBrowserFamilies extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new BrowserName();
        $this->name          = Piwik::translate('UserSettings_BrowserFamilies');
        $this->documentation = ''; // TODO
        $this->order = 5;
        $this->widgetTitle  = 'UserSettings_BrowserFamilies';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->title = $this->name;
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate("DevicesDetection_dataTableLabelBrowserFamily"));
    }

    public function getRelatedReports()
    {
        return array(
            new GetBrowserVersions()
        );
    }
}
