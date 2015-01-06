<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Reports;

use Piwik\API\Proxy;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Site;
use Piwik\ViewDataTable\Factory as ViewDataTableFactory;
use Piwik\WidgetsList;

abstract class Base extends Report
{
    protected function init()
    {
        $this->module   = 'Goals';
        $this->category = 'Goals_Ecommerce';
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
        $idSites = $infos['idSites'];

        if (count($idSites) != 1) {
            return false;
        }

        $idSite = reset($idSites);

        return $this->isEcommerceEnabled($idSite);
    }

    private function isEcommerceEnabled($idSite)
    {
        $site = new Site($idSite);

        return $site->isEcommerceEnabled();
    }

    /**
     * Renders a report depending on the configured ViewDataTable see {@link configureView()} and
     * {@link getDefaultTypeViewDataTable()}. If you want to customize the render process or just render any custom view
     * you can overwrite this method.
     *
     * @return string
     * @throws \Exception In case the given API action does not exist yet.
     * @api
     */
    public function render()
    {
        $apiProxy = Proxy::getInstance();

        if (!$apiProxy->isExistingApiAction($this->module, $this->action)) {
            throw new \Exception("Invalid action name '$this->action' for '$this->module' plugin.");
        }

        $apiAction = $apiProxy->buildApiActionName($this->module, $this->action);

        $view      = ViewDataTableFactory::build(null, $apiAction, 'Ecommerce.' . $this->action);
        $rendered  = $view->render();

        return $rendered;
    }

    public function configureWidget(WidgetsList $widget)
    {
        if ($this->widgetTitle) {
            $params = array();
            if (!empty($this->widgetParams) && is_array($this->widgetParams)) {
                $params = $this->widgetParams;
            }
            $widget->add($this->category, $this->widgetTitle, 'Ecommerce', $this->action, $params);
        }
    }

}
