<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Resolution\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Resolution\Columns\Resolution;
use Piwik\Plugin\ReportsProvider;
use Piwik\Plugins\Resolution\Resolution as ResolutionPlugin;
use Piwik\Plugins\Resolution\Settings\ScreenResolutionDetectionDisabled;
use Piwik\Request;
use Piwik\Site;

class GetResolution extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Resolution();
        $this->name          = Piwik::translate('Resolution_WidgetResolutions');
        $this->documentation = Piwik::translate('Resolution_WidgetResolutionsDocumentation');
        $this->order = 8;

        $this->subcategoryId = 'DevicesDetection_Devices';
    }

    public function configureView(ViewDataTable $view)
    {
        $this->getBasicResolutionDisplayProperties($view);
    }

    public function getRelatedReports()
    {
        return array(
            ReportsProvider::factory('Resolution', 'getConfiguration'),
        );
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
        // the report is offered wherever the instance-wide setting leaves screen resolution detection on
        if (!ScreenResolutionDetectionDisabled::getInstance()->getValue()) {
            return true;
        }

        // a site storing the screen resolution for consented visitors has a report to offer
        foreach (Site::getIdSitesFromIdSitesString($idSites) as $idSite) {
            if (ResolutionPlugin::isScreenResolutionStoredForConsentedVisits((int) $idSite)) {
                return true;
            }
        }

        return false;
    }
}
