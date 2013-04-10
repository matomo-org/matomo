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

/**
 * @package Piwik_Menu
 */
class Piwik_Menu_Admin extends Piwik_Menu_Abstract
{
    static private $instance = null;

    /**
     * @return Piwik_Menu_Admin
     */
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Triggers the AdminMenu.add hook and returns the menu.
     *
     * @return Array
     */
    public function get()
    {
        if (!$this->menu) {
            Piwik_PostEvent('AdminMenu.add');
        }
        return parent::get();
    }
}

/**
 * Returns the current AdminMenu name
 *
 * @return boolean
 */
function Piwik_GetCurrentAdminMenuName()
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

/**
 * Returns the AdminMenu
 *
 * @return Array
 */
function Piwik_GetAdminMenu()
{
    return Piwik_Menu_Admin::getInstance()->get();
}

/**
 * Adds a new AdminMenu entry.
 *
 * @param string $adminMenuName
 * @param string $url
 * @param boolean $displayedForCurrentUser
 * @param int $order
 */
function Piwik_AddAdminMenu($adminMenuName, $url, $displayedForCurrentUser = true, $order = 10)
{
    Piwik_Menu_Admin::getInstance()->add('General_Settings', $adminMenuName, $url, $displayedForCurrentUser, $order);
}

/**
 * Adds a new AdminMenu entry with a submenu.
 *
 * @param string $adminMenuName
 * @param string $adminSubMenuName
 * @param string $url
 * @param boolean $displayedForCurrentUser
 * @param int $order
 */
function Piwik_AddAdminSubMenu($adminMenuName, $adminSubMenuName, $url, $displayedForCurrentUser = true, $order = 10)
{
    Piwik_Menu_Admin::getInstance()->add($adminMenuName, $adminSubMenuName, $url, $displayedForCurrentUser, $order);
}

/**
 * Renames an AdminMenu entry.
 *
 * @param string $adminMenuOriginal
 * @param string $adminMenuRenamed
 */
function Piwik_RenameAdminMenuEntry($adminMenuOriginal, $adminMenuRenamed)
{
    Piwik_Menu_Admin::getInstance()->rename($adminMenuOriginal, null, $adminMenuRenamed, null);
}
