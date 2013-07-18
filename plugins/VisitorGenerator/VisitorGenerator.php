<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_VisitorGenerator
 */
use Piwik\Core\Piwik;

/**
 *
 * @package Piwik_VisitorGenerator
 */
class Piwik_VisitorGenerator extends Piwik_Plugin
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

    public function addMenu()
    {
        Piwik_AddAdminSubMenu(
            'CoreAdminHome_MenuDiagnostic', 'VisitorGenerator_VisitorGenerator',
            array('module' => 'VisitorGenerator', 'action' => 'index'),
            Piwik::isUserIsSuperUser(),
            $order = 20
        );
    }
}
