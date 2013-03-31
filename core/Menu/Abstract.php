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
abstract class Piwik_Menu_Abstract
{

    protected $menu = null;
    protected $menuEntries = array();
    protected $edits = array();
    protected $renames = array();
    protected $orderingApplied = false;

    /*
     * Can't enforce static function in 5.2.
     */
    //abstract static public function getInstance();

    /**
     * Builds the menu, applies edits, renames
     * and orders the entries.
     *
     * @return Array
     */
    public function get()
    {
        $this->buildMenu();
        $this->applyEdits();
        $this->applyRenames();
        $this->applyOrdering();
        return $this->menu;
    }

    /**
     * Adds a new entry to the menu.
     *
     * @param string      $menuName
     * @param string      $subMenuName
     * @param string      $url
     * @param bool        $displayedForCurrentUser
     * @param int         $order
     * @param bool|string $tooltip Tooltip to display.
     */
    public function add($menuName, $subMenuName, $url, $displayedForCurrentUser, $order = 50, $tooltip = false)
    {
        if ($displayedForCurrentUser) {
            // make sure the idSite value used is numeric (hack-y fix for #3426)
            if (!is_numeric(Piwik_Common::getRequestVar('idSite', false))) {
                $idSites = Piwik_SitesManager_API::getInstance()->getSitesIdWithAtLeastViewAccess();
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
    }

    /**
     * Builds a single menu item
     *
     * @param string      $menuName
     * @param string      $subMenuName
     * @param string      $url
     * @param int         $order
     * @param bool|string $tooltip Tooltip to display.
     */
    private function buildMenuItem($menuName, $subMenuName, $url, $order = 50, $tooltip = false)
    {
        if (!isset($this->menu[$menuName]) || empty($subMenuName)) {
            $this->menu[$menuName]['_url'] = $url;
            if (empty($subMenuName)) {
                $this->menu[$menuName]['_order'] = $order;
            }
            $this->menu[$menuName]['_name'] = $menuName;
            $this->menu[$menuName]['_hasSubmenu'] = false;
            $this->menu[$menuName]['_tooltip'] = $tooltip;
        }
        if (!empty($subMenuName)) {
            $this->menu[$menuName][$subMenuName]['_url'] = $url;
            $this->menu[$menuName][$subMenuName]['_order'] = $order;
            $this->menu[$menuName][$subMenuName]['_name'] = $subMenuName;
            $this->menu[$menuName]['_hasSubmenu'] = true;
            $this->menu[$menuName]['_tooltip'] = $tooltip;
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
            $subMenuToEdit = $edit[1];
            $newUrl = $edit[2];
            if (!isset($this->menu[$mainMenuToEdit][$subMenuToEdit])) {
                $this->buildMenuItem($mainMenuToEdit, $subMenuToEdit, $newUrl);
            } else {
                $this->menu[$mainMenuToEdit][$subMenuToEdit]['_url'] = $newUrl;
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
            $subMenuOriginal = $rename[1];
            $mainMenuRenamed = $rename[2];
            $subMenuRenamed = $rename[3];
            // Are we changing a submenu?
            if (!empty($subMenuOriginal)) {
                if (isset($this->menu[$mainMenuOriginal][$subMenuOriginal])) {
                    $save = $this->menu[$mainMenuOriginal][$subMenuOriginal];
                    $save['_name'] = $subMenuRenamed;
                    unset($this->menu[$mainMenuOriginal][$subMenuOriginal]);
                    $this->menu[$mainMenuRenamed][$subMenuRenamed] = $save;
                }
            } // Changing a first-level element
            else if (isset($this->menu[$mainMenuOriginal])) {
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
            } else if ($element['_hasSubmenu']) {
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
        if (!is_array($itemOne) || !is_array($itemTwo)
            || !isset($itemOne['_order']) || !isset($itemTwo['_order'])
        ) {
            return 0;
        }

        if ($itemOne['_order'] == $itemTwo['_order']) {
            return strcmp($itemOne['_name'], $itemTwo['_name']);
        }
        return ($itemOne['_order'] < $itemTwo['_order']) ? -1 : 1;
    }
}
