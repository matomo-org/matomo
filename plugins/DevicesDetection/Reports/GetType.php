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
use Piwik\Plugins\DevicesDetection\Columns\DeviceType;

class GetType extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new DeviceType();
        $this->name          = Piwik::translate('DevicesDetection_DeviceType');
        $this->documentation = Piwik::translate('DevicesDetection_DeviceTypeReportDocumentation');
        $this->order = 0;
        $this->hasGoalMetrics = true;
        $this->subcategoryId = 'DevicesDetection_Devices';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate("DevicesDetection_dataTableLabelTypes"));
    }

}
