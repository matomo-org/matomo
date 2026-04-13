<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DevicesDetection\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Policy\CnilPolicy;
use Piwik\Plugins\DevicesDetection\Columns\DeviceModel;
use Piwik\Policy\PolicyManager;

class GetModel extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new DeviceModel();
        $this->name          = Piwik::translate('DevicesDetection_DeviceModel');
        $this->documentation = Piwik::translate('DevicesDetection_DeviceModelReportDocumentation');
        $this->order = 2;
        $this->hasGoalMetrics = true;
        $this->subcategoryId = 'DevicesDetection_Devices';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_search = true;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate("DevicesDetection_dataTableLabelModels"));
    }

    public function isEnabled()
    {
        // Metadata visibility is global-only here, so check the policy state directly.
        return !PolicyManager::isPolicyActive(CnilPolicy::class, $idSite = null);
    }
}
