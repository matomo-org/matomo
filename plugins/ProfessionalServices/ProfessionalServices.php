<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices;

class ProfessionalServices extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Request.getRenamedModuleAndAction' => 'renameProfessionalServicesModule',
            'Template.afterGoalConversionOverviewReport' => 'getGoalOverviewPromo',
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = 'plugins/ProfessionalServices/stylesheets/widget.less';
    }

    /**
     * @deprecated Can be removed in Piwik 3.0
     * @param $module
     * @param $action
     */
    public function renameProfessionalServicesModule(&$module, &$action)
    {
        if ($module == 'ProfessionalServices') {
            $module = 'ProfessionalServices';

            if($action == 'promoPiwikPro') {
                $action = 'promoServices';
            }

            if($action == 'rssPiwikPro') {
                $action = 'rss';
            }
        }
    }

    public function getGoalOverviewPromo()
    {
        echo '
            <p style="margin-top:3em" class=" alert-info alert">Did you know?
                With <a target="_blank" href="?module=Proxy&action=redirect&url=https://piwik.org/blog/2016/11/ab-testing-platform-piwik-analytics/">A/B Testing for Piwik</a> you can immediately increase conversions and sales by creating different versions of a page to see which grows your business.
            </p>
            ';
    }
}
