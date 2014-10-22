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
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Pie;
use Piwik\Plugins\DevicesDetection\Columns\BrowserEngine;

class GetBrowserEngines extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new BrowserEngine();
        $this->name          = Piwik::translate('DevicesDetection_BrowserEngines');
        $this->documentation = Piwik::translate('DevicesDetection_BrowserEngineDocumentation', '<br />');
        $this->order = 7;
        $this->widgetTitle  = 'DevicesDetection_BrowserEngines';
    }

    public function getDefaultTypeViewDataTable()
    {
        return Pie::ID;
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', $this->dimension->getName());
    }
}
