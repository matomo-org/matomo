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
use Piwik\Plugins\DevicesDetection\Columns\DeviceBrand;

class GetBrand extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new DeviceBrand();
        $this->name          = Piwik::translate('DevicesDetection_DeviceBrand');
        $this->documentation = Piwik::translate('DevicesDetection_DeviceBrandReportDocumentation');
        $this->order = 4;
        $this->hasGoalMetrics = true;
        $this->subcategoryId = 'DevicesDetection_Devices';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_search = true;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate("DevicesDetection_dataTableLabelBrands"));
    }

}
