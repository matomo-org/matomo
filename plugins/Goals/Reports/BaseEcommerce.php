<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\Reports;

use Piwik\Common;
use Piwik\Plugin\Report;
use Piwik\Site;

abstract class BaseEcommerce extends Report
{
    protected function init()
    {
        $this->category = 'Goals_Ecommerce';
    }

    public function isEnabled()
    {
        $idSite = Common::getRequestVar('idSite', false, 'int');

        if (empty($idSite)) {
            return false;
        }

        $site = new Site($idSite);
        return $site->isEcommerceEnabled();
    }

    public function configureReportMetadata(&$availableReports, $infos)
    {
        if ($this->isEcommerceEnabled($infos)) {
            $availableReports[] = $this->buildReportMetadata();
        }
    }

    private function isEcommerceEnabled($infos)
    {
        $idSites = $infos['idSites'];

        if (count($idSites) != 1) {
            return false;
        }

        $idSite = reset($idSites);
        $site   = new Site($idSite);

        return $site->isEcommerceEnabled();
    }
}
