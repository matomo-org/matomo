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
}
