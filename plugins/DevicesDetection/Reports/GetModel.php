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
use Piwik\Plugins\DevicesDetection\Columns\DeviceModel;
use Piwik\Plugins\DevicesDetection\DevicesDetection;
use Piwik\Plugins\DevicesDetection\Settings\DeviceModelDetectionDisabled;
use Piwik\Request;

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
        return $this->isEnabledForSite(Request::fromRequest()->getParameter('idSite', ''));
    }

    public function configureReportMetadata(&$availableReports, $infos)
    {
        // report metadata describes the site it is asked for, which need not be the one in the
        // request, as when it is requested with idSites rather than idSite
        if (!$this->isEnabledForSite($infos['idSite'] ?? '')) {
            return;
        }

        $report = $this->buildReportMetadata();

        if (!empty($report)) {
            $availableReports[] = $report;
        }
    }

    /**
     * @param mixed $idSite the requested site, as given in the request
     */
    private function isEnabledForSite($idSite): bool
    {
        // the report is offered wherever the instance-wide setting leaves device model detection on
        if (!DeviceModelDetectionDisabled::getInstance()->getValue()) {
            return true;
        }

        // A single site storing the device model for consented visitors has a report to offer.
        // Several sites, or all of them, are left as the instance-wide setting has it: finding
        // one that stores it would read every site's tracker cache, and build it where it is
        // cold, each time reports are listed.
        if (!is_numeric($idSite) || (string) (int) $idSite !== (string) $idSite) {
            return false;
        }

        return DevicesDetection::isDeviceModelStoredForConsentedVisits((int) $idSite);
    }
}
