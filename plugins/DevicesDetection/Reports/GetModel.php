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
use Piwik\Site;

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
        return $this->isEnabledForSites(Request::fromRequest()->getParameter('idSite', ''));
    }

    public function configureReportMetadata(&$availableReports, $infos)
    {
        // report metadata describes the site it is asked for, which need not be the one in the
        // request, as when a scheduled report is sent
        if (!$this->isEnabledForSites($infos['idSite'] ?? '')) {
            return;
        }

        $report = $this->buildReportMetadata();

        if (!empty($report)) {
            $availableReports[] = $report;
        }
    }

    /**
     * @param mixed $idSites as accepted by Site::getIdSitesFromIdSitesString()
     */
    private function isEnabledForSites($idSites): bool
    {
        // the report is offered wherever the instance-wide setting leaves device model detection on
        if (!DeviceModelDetectionDisabled::getInstance()->getValue()) {
            return true;
        }

        // a site storing the device model for consented visitors has a report to offer
        foreach (Site::getIdSitesFromIdSitesString($idSites) as $idSite) {
            if (DevicesDetection::isDeviceModelStoredForConsentedVisits((int) $idSite)) {
                return true;
            }
        }

        return false;
    }
}
