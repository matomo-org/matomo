<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Menu;

use Piwik\Plugins\SitesManager\API;
use Piwik\Singleton;
use Piwik\Plugin\Manager as PluginManager;

/**
 * Base class for classes that manage one of Piwik's menus.
 *
 * There are three menus in Piwik, the main menu, the top menu and the admin menu.
 * Each menu has a class that manages the menu's content. Each class invokes
 * a different event to allow plugins to add new menu items.
 *
 * @static \Piwik\Menu\MenuAbstract getInstance()
 */
abstract class MenuAbstract extends Singleton
{

    protected $menu = null;
    protected $menuEntries = array();
    protected $menuEntriesToRemove = array();
    protected $edits = array();
    protected $renames = array();
    protected $orderingApplied = false;
    protected $menuIcons = array();
    protected static $menus = array();

    /**
     * Builds the menu, applies edits, renames
     * and orders the entries.
     *
     * @return Array
     */
    public function getMenu()
    {
        $this->buildMenu();
        $this->applyEdits();
        $this->applyRenames();
        $this->applyRemoves();
        $this->applyOrdering();
        return $this->menu;
    }

    /**
     * Let's you register a menu icon for a certain menu category to replace the default arrow icon.
     *
     * @param string $menuName The translation key of a main menu category, eg 'Dashboard_Dashboard'
     * @param string $iconCssClass   The css class name of an icon, eg 'icon-user'
     */
    public function registerMenuIcon($menuName, $iconCssClass)
    {
        $this->menuIcons[$menuName] = $iconCssClass;
    }

    /**
     * Returns a list of available plugin menu instances.
     *
     * @return \Piwik\Plugin\Menu[]
     */
    protected function getAllMenus()
    {
        if (!empty(self::$menus)) {
            return self::$menus;
        }

        self::$menus = PluginManager::getInstance()->findComponents('Menu', 'Piwik\\Plugin\\Menu');

        return self::$menus;
    }

    /**
     * To use only for tests.
     *
     * @deprecated The whole $menus cache should be replaced by a real transient cache
     */
    public static function clearMenus()
    {
        self::$menus = array();
    }

    /**
     * Adds a new entry to the menu.
     *
     * @param string $menuName The menu's category name. Can be a translation token.
     * @param string $subMenuName The menu item's name. Can be a translation token.
     * @param string|array $url The URL the admin menu entry should link to, or an array of query parameters
     *                          that can be used to build the URL.
     * @param boolean $displayedForCurrentUser Whether this menu entry should be displayed for the
     *                                         current user. If false, the entry will not be added.
     * @param int $order The order hint.
     * @param bool|string $tooltip An optional tooltip to display or false to display the tooltip.
     *
     * @deprecated since 2.7.0 Use {@link addItem() instead}. Method will be removed in Piwik 3.0
     */
    public function add($menuName, $subMenuName, $url, $displayedForCurrentUser = true, $order = 50, $tooltip = false)
    {
        if (!$displayedForCurrentUser) {
            return;
        }

        $this->addItem($menuName, $subMenuName, $url, $order, $tooltip);
    }

    /**
     * Adds a new entry to the menu.
     *
     * @param string $menuName The menu's category name. Can be a translation token.
     * @param string $subMenuName The menu item's name. Can be a translation token.
     * @param string|array $url The URL the admin menu entry should link to, or an array of query parameters
     *                          that can be used to build the URL.
     * @param int $order The order hint.
     * @param bool|string $tooltip An optional tooltip to display or false to display the tooltip.
     * @since 2.7.0
     * @api
     */
    public function addItem($menuName, $subMenuName, $url, $order = 50, $tooltip = false)
    {
        // make sure the idSite value used is numeric (hack-y fix for #3426)
        if (isset($url['idSite']) && !is_numeric($url['idSite'])) {
            $idSites = API::getInstance()->getSitesIdWithAtLeastViewAccess();
            $url['idSite'] = reset($idSites);
        }

        $this->menuEntries[] = array(
            $menuName,
            $subMenuName,
            $url,
            $order,
            $tooltip
        );
    }

    /**
     * Removes an existing entry from the menu.
     *
     * @param string      $menuName    The menu's category name. Can be a translation token.
     * @param bool|string $subMenuName The menu item's name. Can be a translation token.
     * @api
     */
    public function remove($menuName, $subMenuName = false)
    {
        $this->menuEntriesToRemove[] = array(
            $menuName,
            $subMenuName
        );
    }

    /**
     * Builds a single menu item
     *
     * @param string $menuName
     * @param string $subMenuName
     * @param string $url
     * @param int $order
     * @param bool|string $tooltip Tooltip to display.
     */
    private function buildMenuItem($menuName, $subMenuName, $url, $order = 50, $tooltip = false)
    {
        if (!isset($this->menu[$menuName])) {
            $this->menu[$menuName] = array(
                '_hasSubmenu' => false,
                '_order' => $order
            );
        }

        if (empty($subMenuName)) {
            $this->menu[$menuName]['_url']   = $url;
            $this->menu[$menuName]['_order'] = $order;
            $this->menu[$menuName]['_name']  = $menuName;
            $this->menu[$menuName]['_tooltip'] = $tooltip;
            if (!empty($this->menuIcons[$menuName])) {
                $this->menu[$menuName]['_icon'] = $this->menuIcons[$menuName];
            } else {
                $this->menu[$menuName]['_icon'] = '';
            }
        }
        if (!empty($subMenuName)) {
            $this->menu[$menuName][$subMenuName]['_url'] = $url;
            $this->menu[$menuName][$subMenuName]['_order'] = $order;
            $this->menu[$menuName][$subMenuName]['_name'] = $subMenuName;
            $this->menu[$menuName][$subMenuName]['_tooltip'] = $tooltip;
            $this->menu[$menuName]['_hasSubmenu'] = true;

            if (!array_key_exists('_tooltip', $this->menu[$menuName])) {
                $this->menu[$menuName]['_tooltip'] = $tooltip;
            }
        }
    }

    /**
     * Builds the menu from the $this->menuEntries variable.
     */
    private function buildMenu()
    {
        foreach ($this->menuEntries as $menuEntry) {
            $this->buildMenuItem($menuEntry[0], $menuEntry[1], $menuEntry[2], $menuEntry[3], $menuEntry[4]);
        }
    }

    /**
     * Renames a single menu entry.
     *
     * @param $mainMenuOriginal
     * @param $subMenuOriginal
     * @param $mainMenuRenamed
     * @param $subMenuRenamed
     * @api
     */
    public function rename($mainMenuOriginal, $subMenuOriginal, $mainMenuRenamed, $subMenuRenamed)
    {
        $this->renames[] = array($mainMenuOriginal, $subMenuOriginal,
                                 $mainMenuRenamed, $subMenuRenamed);
    }

    /**
     * Edits a URL of an existing menu entry.
     *
     * @param $mainMenuToEdit
     * @param $subMenuToEdit
     * @param $newUrl
     * @api
     */
    public function editUrl($mainMenuToEdit, $subMenuToEdit, $newUrl)
    {
        $this->edits[] = array($mainMenuToEdit, $subMenuToEdit, $newUrl);
    }

    /**
     * Applies all edits to the menu.
     */
    private function applyEdits()
    {
        foreach ($this->edits as $edit) {
            $mainMenuToEdit = $edit[0];
            $subMenuToEdit  = $edit[1];
            $newUrl         = $edit[2];

            if ($subMenuToEdit === null) {
                if (isset($this->menu[$mainMenuToEdit])) {
                    $menuDataToEdit = &$this->menu[$mainMenuToEdit];
                } else {
                    $menuDataToEdit = null;
                }
            } else {
                if (isset($this->menu[$mainMenuToEdit][$subMenuToEdit])) {
                    $menuDataToEdit = &$this->menu[$mainMenuToEdit][$subMenuToEdit];
                } else {
                    $menuDataToEdit = null;
                }
            }

            if (empty($menuDataToEdit)) {
                $this->buildMenuItem($mainMenuToEdit, $subMenuToEdit, $newUrl);
            } else {
                $menuDataToEdit['_url'] = $newUrl;
            }
        }
    }

    private function applyRemoves()
    {
        foreach ($this->menuEntriesToRemove as $menuToDelete) {
            if (empty($menuToDelete[1])) {
                // Delete Main Menu
                if (isset($this->menu[$menuToDelete[0]])) {
                    unset($this->menu[$menuToDelete[0]]);
                }
            } else {
                // Delete Sub Menu
                if (isset($this->menu[$menuToDelete[0]][$menuToDelete[1]])) {
                    unset($this->menu[$menuToDelete[0]][$menuToDelete[1]]);
                }
            }
        }
    }
    /**
     * Applies renames to the menu.
     */
    private function applyRenames()
    {
        foreach ($this->renames as $rename) {
            $mainMenuOriginal = $rename[0];
            $subMenuOriginal  = $rename[1];
            $mainMenuRenamed  = $rename[2];
            $subMenuRenamed   = $rename[3];

            // Are we changing a submenu?
            if (!empty($subMenuOriginal)) {
                if (isset($this->menu[$mainMenuOriginal][$subMenuOriginal])) {
                    $save = $this->menu[$mainMenuOriginal][$subMenuOriginal];
                    $save['_name'] = $subMenuRenamed;
                    unset($this->menu[$mainMenuOriginal][$subMenuOriginal]);
                    $this->menu[$mainMenuRenamed][$subMenuRenamed] = $save;
                }
            } // Changing a first-level element
            elseif (isset($this->menu[$mainMenuOriginal])) {
                $save = $this->menu[$mainMenuOriginal];
                $save['_name'] = $mainMenuRenamed;
                unset($this->menu[$mainMenuOriginal]);
                $this->menu[$mainMenuRenamed] = $save;
            }
        }
    }

    /**
     * Orders the menu according to their order.
     */
    private function applyOrdering()
    {
        if (empty($this->menu)
            || $this->orderingApplied
        ) {
            return;
        }

        uasort($this->menu, array($this, 'menuCompare'));
        foreach ($this->menu as $key => &$element) {
            if (is_null($element)) {
                unset($this->menu[$key]);
            } elseif ($element['_hasSubmenu']) {
                uasort($element, array($this, 'menuCompare'));
            }
        }

        $this->orderingApplied = true;
    }

    /**
     * Compares two menu entries. Used for ordering.
     *
     * @param array $itemOne
     * @param array $itemTwo
     * @return boolean
     */
    protected function menuCompare($itemOne, $itemTwo)
    {
        if (!is_array($itemOne) && !is_array($itemTwo)) {
            return 0;
        }

        if (!is_array($itemOne) && is_array($itemTwo)) {
            return -1;
        }

        if (is_array($itemOne) && !is_array($itemTwo)) {
            return 1;
        }

        if (!isset($itemOne['_order']) && !isset($itemTwo['_order'])) {
            return 0;
        }

        if (!isset($itemOne['_order']) && isset($itemTwo['_order'])) {
            return -1;
        }

        if (isset($itemOne['_order']) && !isset($itemTwo['_order'])) {
            return 1;
        }

        if ($itemOne['_order'] == $itemTwo['_order']) {
            return strcmp(
                @$itemOne['_name'],
                @$itemTwo['_name']);
        }

        return ($itemOne['_order'] < $itemTwo['_order']) ? -1 : 1;
    }
}
