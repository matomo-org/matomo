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
        $this->name          = Piwik::translate('DevicesDetection_BrowserVersion');
        $this->documentation = ''; // TODO
        $this->order = 2;
        $this->widgetTitle  = 'DevicesDetection_BrowserVersion';
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
            self::factory('DevicesDetection', 'getBrowsers'),
        );
    }
}
