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
use Piwik\Plugins\DevicesDetection\Columns\BrowserVersion;

class GetBrowserVersions extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new BrowserVersion();
        $this->name          = Piwik::translate('DevicesDetection_BrowserVersions');
        $this->documentation = ''; // TODO
        $this->order = 6;
        $this->widgetTitle  = 'DevicesDetection_BrowserVersions';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate("UserSettings_ColumnBrowserVersion"));
    }

    public function getRelatedReports()
    {
        return array(
            new GetBrowserFamilies()
        );
    }
}
