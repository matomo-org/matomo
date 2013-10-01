<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik_Menu
 */
namespace Piwik\Menu;

use Piwik\Menu\MenuAbstract;
use Piwik\Piwik;

/**
 * @package Piwik_Menu
 */
class Admin extends MenuAbstract
{
    static private $instance = null;

    /**
     * @return \Piwik\Menu\Admin
     */
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Triggers the AdminMenu.addMenuEntries hook and returns the menu.
     *
     * @return Array
     */
    public function get()
    {
        if (!$this->menu) {
            Piwik_PostEvent('AdminMenu.addMenuEntries');
        }
        return parent::get();
    }

    /**
     * Returns the current AdminMenu name
     *
     * @return boolean
     */
    function getCurrentAdminMenuName()
    {
        $menu = Piwik_GetAdminMenu();
        $currentModule = Piwik::getModule();
        $currentAction = Piwik::getAction();
        foreach ($menu as $name => $submenu) {
            foreach ($submenu as $subMenuName => $parameters) {
                if (strpos($subMenuName, '_') !== 0 &&
                    $parameters['_url']['module'] == $currentModule
                    && $parameters['_url']['action'] == $currentAction
                ) {
                    return $subMenuName;
                }
            }
        }
        return false;
    }
}

