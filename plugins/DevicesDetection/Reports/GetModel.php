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
use Piwik\Plugins\DevicesDetection\Columns\DeviceModel;

class GetModel extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new DeviceModel();
        $this->name          = Piwik::translate('DevicesDetection_DeviceModel');
        $this->documentation = ''; // TODO
        $this->order = 2;
        $this->widgetTitle  = 'DevicesDetection_DeviceModel';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate("DevicesDetection_dataTableLabelModels"));
    }

}
