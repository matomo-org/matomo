<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package VisitorGenerator
 */
namespace Piwik\Plugins\VisitorGenerator;

use Piwik\Piwik;

/**
 *
 * @package VisitorGenerator
 */
class VisitorGenerator extends \Piwik\Plugin
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
