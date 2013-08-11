<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CorePluginsAdmin
 */
namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Piwik;

/**
 *
 * @package CorePluginsAdmin
 */
class CorePluginsAdmin extends \Piwik\Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array('AdminMenu.add' => 'addMenu');
    }

    function addMenu()
    {
        Piwik_AddAdminSubMenu('CorePluginsAdmin_MenuPlatform', null, "", Piwik::isUserIsSuperUser(), $order = 15);
        Piwik_AddAdminSubMenu('CorePluginsAdmin_MenuPlatform', 'CorePluginsAdmin_Plugins',
            array('module' => 'CorePluginsAdmin', 'action' => 'plugins'),
            Piwik::isUserIsSuperUser(),
            $order = 1);
        Piwik_AddAdminSubMenu('CorePluginsAdmin_MenuPlatform', 'CorePluginsAdmin_Themes',
            array('module' => 'CorePluginsAdmin', 'action' => 'themes'),
            Piwik::isUserIsSuperUser(),
            $order = 3);
        Piwik_AddAdminSubMenu('CorePluginsAdmin_MenuPlatform', 'CorePluginsAdmin_MenuExtend',
            array('module' => 'CorePluginsAdmin', 'action' => 'extend'),
            Piwik::isUserIsSuperUser(),
            $order = 5);
    }
}
