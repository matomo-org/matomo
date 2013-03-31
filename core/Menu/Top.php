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
class Piwik_Menu_Top extends Piwik_Menu_Abstract
{
    static private $instance = null;

    /**
     * @return Piwik_Menu_Top
     */
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Directly adds a menu entry containing html.
     *
     * @param string $menuName
     * @param string $data
     * @param boolean $displayedForCurrentUser
     * @param int $order
     * @param string $tooltip Tooltip to display.
     */
    public function addHtml($menuName, $data, $displayedForCurrentUser, $order, $tooltip)
    {
        if ($displayedForCurrentUser) {
            if (!isset($this->menu[$menuName])) {
                $this->menu[$menuName]['_html'] = $data;
                $this->menu[$menuName]['_order'] = $order;
                $this->menu[$menuName]['_hasSubmenu'] = false;
                $this->menu[$menuName]['_tooltip'] = $tooltip;
            }
        }
    }

    /**
     * Triggers the TopMenu.add hook and returns the menu.
     *
     * @return Array
     */
    public function get()
    {
        if (!$this->menu) {
            Piwik_PostEvent('TopMenu.add');
        }
        return parent::get();
    }
}

/**
 * Returns the TopMenu as an array.
 *
 * @return array
 */
function Piwik_GetTopMenu()
{
    return Piwik_Menu_Top::getInstance()->get();
}

/**
 * Adds a new entry to the TopMenu.
 *
 * @param string      $topMenuName
 * @param string      $data
 * @param boolean     $displayedForCurrentUser
 * @param int         $order
 * @param bool        $isHTML
 * @param bool|string $tooltip Tooltip to display.
 */
function Piwik_AddTopMenu($topMenuName, $data, $displayedForCurrentUser = true, $order = 10, $isHTML = false,
                          $tooltip = false)
{
    if ($isHTML) {
        Piwik_Menu_Top::getInstance()->addHtml($topMenuName, $data, $displayedForCurrentUser, $order, $tooltip);
    } else {
        Piwik_Menu_Top::getInstance()->add($topMenuName, null, $data, $displayedForCurrentUser, $order, $tooltip);
    }
}

/**
 * Renames a entry of the TopMenu
 *
 * @param string $topMenuOriginal
 * @param string $topMenuRenamed
 */
function Piwik_RenameTopMenuEntry($topMenuOriginal, $topMenuRenamed)
{
    Piwik_Menu_Top::getInstance()->rename($topMenuOriginal, null, $topMenuRenamed, null);
}
