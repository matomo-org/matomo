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
class Piwik_Menu_Main extends Piwik_Menu_Abstract
{
    static private $instance = null;

    /**
     * @return Piwik_Menu_Abstract
     */
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Returns if the URL was found in the menu.
     *
     * @param string $url
     * @return boolean
     */
    public function isUrlFound($url)
    {
        $menu = Piwik_Menu_Main::getInstance()->get();

        foreach ($menu as $mainMenuName => $subMenus) {
            foreach ($subMenus as $subMenuName => $menuUrl) {
                if (strpos($subMenuName, '_') !== 0 && $menuUrl['_url'] == $url) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Triggers the Menu.add hook and returns the menu.
     *
     * @return Array
     */
    public function get()
    {
        // We trigger the Event only once!
        if (!$this->menu) {
            Piwik_PostEvent('Menu.add');
        }
        return parent::get();
    }

}

/**
 * Checks if an entry uses the URL $url.
 *
 * @param string $url
 * @return boolean
 */
function Piwik_IsMenuUrlFound($url)
{
    return Piwik_Menu_Main::getInstance()->isUrlFound($url);
}

/**
 * Returns the MainMenu as array.
 *
 * @return array
 */
function Piwik_GetMenu()
{
    return Piwik_Menu_Main::getInstance()->get();
}

/**
 * Adds a new entry to the MainMenu.
 *
 * @param string $mainMenuName
 * @param string $subMenuName
 * @param string $url
 * @param boolean $displayedForCurrentUser
 * @param int $order
 */
function Piwik_AddMenu($mainMenuName, $subMenuName, $url, $displayedForCurrentUser = true, $order = 10)
{
    Piwik_Menu_Main::getInstance()->add($mainMenuName, $subMenuName, $url, $displayedForCurrentUser, $order);
}

/**
 * Renames a menu entry.
 *
 * @param string $mainMenuOriginal
 * @param string $subMenuOriginal
 * @param string $mainMenuRenamed
 * @param string $subMenuRenamed
 */
function Piwik_RenameMenuEntry($mainMenuOriginal, $subMenuOriginal,
                               $mainMenuRenamed, $subMenuRenamed)
{
    Piwik_Menu_Main::getInstance()->rename($mainMenuOriginal, $subMenuOriginal, $mainMenuRenamed, $subMenuRenamed);
}

/**
 * Edits the URL of a menu entry.
 *
 * @param string $mainMenuToEdit
 * @param string $subMenuToEdit
 * @param string $newUrl
 */
function Piwik_EditMenuUrl($mainMenuToEdit, $subMenuToEdit, $newUrl)
{
    Piwik_Menu_Main::getInstance()->editUrl($mainMenuToEdit, $subMenuToEdit, $newUrl);
}
