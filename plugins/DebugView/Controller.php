<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView;

use Piwik\Config;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Live\Live;
use Piwik\View;

class Controller extends \Piwik\Plugin\ControllerAdmin
{
    public function index()
    {
        $this->checkSitePermission();

        $noticeMessage = null;
        if (!Manager::getInstance()->isPluginActivated('Live')) {
            $noticeMessage = Piwik::translate('DebugView_LivePluginDisabledMessage');
        } elseif (!Live::isVisitorLogEnabled($this->idSite)) {
            $noticeMessage = Piwik::translate('DebugView_VisitsLogDisabledMessage');
        }

        if ($noticeMessage !== null) {
            $view = new View('@DebugView/disabled');
            $this->setBasicVariablesView($view);
            $view->noticeMessage = $noticeMessage;

            return $view->render();
        }

        $view = new View('@DebugView/index');
        $this->setBasicVariablesView($view);

        $view->idSite = $this->idSite;
        $view->refreshInterval = (int) (Config::getInstance()->General['live_widget_refresh_after_seconds'] ?? 5);
        $view->lastMinutes = 30;

        return $view->render();
    }
}
