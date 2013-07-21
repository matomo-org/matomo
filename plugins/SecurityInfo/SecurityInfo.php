<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_SecurityInfo
 */
use Piwik\Piwik;
use Piwik\Plugin;

/**
 *
 * @package Piwik_SecurityInfo
 */
class Piwik_SecurityInfo extends Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AdminMenu.add' => 'addMenu',
        );
    }

    function addMenu()
    {
        Piwik_AddAdminSubMenu('CoreAdminHome_MenuDiagnostic', 'SecurityInfo_Security',
            array('module' => 'SecurityInfo', 'action' => 'index'),
            Piwik::isUserIsSuperUser(),
            $order = 10);
    }
}
