<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour;

use Piwik\Piwik;

class Tour extends \Piwik\Plugin
{

    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'Dashboard.changeDefaultDashboardLayout' => 'changeDefaultDashboardLayout'
        );
    }

    public function changeDefaultDashboardLayout(&$defaultLayout)
    {
        if (Piwik::hasUserSuperUserAccess()) {
            $defaultLayout = json_decode($defaultLayout);
            $engagementWidget = array('uniqueId' => 'widgetTourgetEngagement', 'parameters' => array('module' => 'Tour', 'action' => 'getEngagement'));
            array_unshift($defaultLayout[0], $engagementWidget);

            $defaultLayout = json_encode($defaultLayout);
        }
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Tour/stylesheets/engagement.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Tour/javascripts/engagement.js";
    }
}
