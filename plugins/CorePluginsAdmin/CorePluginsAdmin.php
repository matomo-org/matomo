<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_CorePluginsAdmin
 */

/**
 *
 * @package Piwik_CorePluginsAdmin
 */
class Piwik_CorePluginsAdmin extends Piwik_Plugin
{
    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('CorePluginsAdmin_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

    function getListHooksRegistered()
    {
        return array('AdminMenu.add' => 'addMenu');
    }

    function addMenu()
    {
        Piwik_AddAdminSubMenu('CorePluginsAdmin_MenuPlugins', null, "", Piwik::isUserIsSuperUser(), $order = 15);
        Piwik_AddAdminSubMenu('CorePluginsAdmin_MenuPlugins', 'CorePluginsAdmin_MenuPluginsInstalled',
            array('module' => 'CorePluginsAdmin', 'action' => 'index'),
            Piwik::isUserIsSuperUser(),
            $order = 1);
    }
}
