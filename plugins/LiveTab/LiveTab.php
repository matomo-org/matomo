<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_LiveTab
 */

namespace Piwik\Plugins\LiveTab;

use Piwik\Piwik;
use Piwik\Plugin;

/**
 *
 * @package Piwik_LiveTab
 */
class LiveTab extends Plugin
{
    public static $defaultRefreshInterval = 60;
    public static $defaultLastMinutes     = 30;
    public static $defaultMetricToDisplay = 'visits';

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getJsFiles' => 'getJsFiles',
            'AdminMenu.add'           => 'addMenu',
        );
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = 'plugins/LiveTab/javascripts/api.js';
        $jsFiles[] = 'plugins/LiveTab/javascripts/liveTab.js';
        $jsFiles[] = 'plugins/LiveTab/javascripts/liveTabAdmin.js';
    }

    public function addMenu()
    {
        Piwik_AddAdminMenu(
            'LiveTab_SettingsMenu',
            array('module' => 'LiveTab', 'action' => 'index'),
            true
        );
    }
}
