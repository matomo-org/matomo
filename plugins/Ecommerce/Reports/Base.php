<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Reports;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Site;

abstract class Base extends Report
{
    protected function init()
    {
        $this->module   = 'Goals';
        $this->categoryId = 'Goals_Ecommerce';
        $this->onlineGuideUrl = 'https://matomo.org/docs/ecommerce-analytics/';
    }

    public function isEnabled()
    {
        $idSite = Common::getRequestVar('idSite', false, 'int');

        if (empty($idSite)) {
            return false;
        }

        return $this->isEcommerceEnabled($idSite);
    }

    public function checkIsEnabled()
    {
        if (!$this->isEnabled()) {
            $message = Piwik::translate('General_ExceptionReportNotEnabled');

            if (Piwik::hasUserSuperUserAccess()) {
                $message .= ' Most likely Ecommerce is not enabled for the requested site.';
            }

            throw new \Exception($message);
        }
    }

    public function configureReportMetadata(&$availableReports, $infos)
    {
        if ($this->isEcommerceEnabledByInfos($infos)) {
            $availableReports[] = $this->buildReportMetadata();
        }
    }

    private function isEcommerceEnabledByInfos($infos)
    {
        $idSite = $infos['idSite'];

        if (empty($idSite) || !is_numeric($idSite)) {
            return false;
        }

        return $this->isEcommerceEnabled($idSite);
    }

    private function isEcommerceEnabled($idSite)
    {
        $site = new Site($idSite);

        return $site->isEcommerceEnabled();
    }

}
