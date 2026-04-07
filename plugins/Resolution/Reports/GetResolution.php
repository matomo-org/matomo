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
use Piwik\Plugins\Resolution\Resolution as ResolutionPlugin;
use Piwik\Plugins\Resolution\Columns\Resolution;
use Piwik\Plugin\ReportsProvider;
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
        $idSite = Request::fromRequest()->getParameter('idSite', '0');
        $idSites = Site::getIdSitesFromIdSitesString($idSite);

        foreach ($idSites as $siteId) {
            if (ResolutionPlugin::isScreenResolutionDetectionDisabledByCompliancePolicy((int) $siteId)) {
                return false;
            }
        }

        return true;
    }
}
