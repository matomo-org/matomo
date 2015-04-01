<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings;

use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

/**
 *
 */
class UserSettings extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'Request.getRenamedModuleAndAction'    => 'renameDeprecatedModuleAndAction',
        );
    }

    /**
     * Maps the deprecated actions that were 'moved' to DevicesDetection plugin
     *
     * @deprecated since 2.10.0 and will be removed from May 1st 2015
     * @param $module
     * @param $action
     */
    public function renameDeprecatedModuleAndAction(&$module, &$action)
    {
        $movedMethods = array(
            'index' => 'software',
            'getBrowser' => 'getBrowsers',
            'getBrowserVersion' => 'getBrowserVersions',
            'getMobileVsDesktop' => 'getType',
            'getOS' => 'getOsVersions',
            'getOSFamily' => 'getOsFamilies',
            'getBrowserType' => 'getBrowserEngines',
        );

        if ($module == 'UserSettings' && array_key_exists($action, $movedMethods)) {
            $module = 'DevicesDetection';
            $action = $movedMethods[$action];
        }

        if ($module == 'UserSettings' && ($action == 'getResolution' || $action == 'getConfiguration')) {
            $module = 'Resolution';
        }

        if ($module == 'UserSettings' && ($action == 'getLanguage' || $action == 'getLanguageCode')) {
            $module = 'UserLanguage';
        }

        if ($module == 'UserSettings' && $action == 'getPlugin') {
            $module = 'DevicePlugins';
        }
    }
}
