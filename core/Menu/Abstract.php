<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Piwik_Menu
 */

/**
 * @package Piwik_Menu
 */
abstract class Piwik_Menu_Abstract {

	protected $menu = null;
	protected $menuEntries = array();
	protected $edits = array();
	protected $renames = array();

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
	public function get() {
		$this->buildMenu();
		$this->applyEdits();
		$this->applyRenames();
		$this->applyOrdering();
		return $this->menu;
	}

	/**
	 * Adds a new entry to the menu.
	 */
	public function add($menuName, $subMenuName, $url, $displayedForCurrentUser, $order) {
		$this->menuEntries[] = array(
			$menuName,
			$subMenuName,
			$url,
			$displayedForCurrentUser,
			$order
		);
	}

	/**
	 * Builds the menu from the $this->menuEntries variable.
	 *
	 */
	private function buildMenu() {
		foreach ($this->menuEntries as $menuEntry) {
			$menuName = $menuEntry[0];
			$subMenuName = $menuEntry[1];

			if ($menuEntry[3]) {
				if (!isset($this->menu[$menuName]) || empty($subMenuName)) {
					$this->menu[$menuName]['_url'] = $menuEntry[2];
					$this->menu[$menuName]['_order'] = $menuEntry[4];
					$this->menu[$menuName]['_hasSubmenu'] = false;
				}
				if (!empty($subMenuName)) {
					$this->menu[$menuName][$subMenuName]['_url'] = $menuEntry[2];
					$this->menu[$menuName][$subMenuName]['_order'] = $menuEntry[4];
					$this->menu[$menuName]['_hasSubmenu'] = true;
				}
			}
		}
	}

	/**
	 * Renames a single menu entry.
	 *
	 */
	public function rename($mainMenuOriginal, $subMenuOriginal, $mainMenuRenamed, $subMenuRenamed) {
		$this->renames[] = array($mainMenuOriginal, $subMenuOriginal,
			$mainMenuRenamed, $subMenuRenamed);
	}

	/**
	 * Edits a URL of an existing menu entry.
	 *
	 */
	public function editUrl($mainMenuToEdit, $subMenuToEdit, $newUrl) {
		$this->edits[] = array($mainMenuToEdit, $subMenuToEdit, $newUrl);
	}

	/**
	 * Applies all edits to the menu.
	 *
	 */
	private function applyEdits() {
		foreach ($this->edits as $edit) {
			$mainMenuToEdit = $edit[0];
			$subMenuToEdit = $edit[1];
			$newUrl = $edit[2];
			if (!isset($this->menu[$mainMenuToEdit][$subMenuToEdit])) {
				$this->add($mainMenuToEdit, $subMenuToEdit, $newUrl, true);
			} else {
				$this->menu[$mainMenuToEdit][$subMenuToEdit]['_url'] = $newUrl;
			}
		}
	}

	/**
	 * Applies renames to the menu.
	 *
	 */
	private function applyRenames() {
		foreach ($this->renames as $rename) {
			$mainMenuOriginal = $rename[0];
			$subMenuOriginal = $rename[1];
			$mainMenuRenamed = $rename[2];
			$subMenuRenamed = $rename[3];
			// Are we changing a submenu?
			if (!empty($subMenuOriginal)) {
				if (isset($this->menu[$mainMenuOriginal][$subMenuOriginal])) {
					$save = $this->menu[$mainMenuOriginal][$subMenuOriginal];
					unset($this->menu[$mainMenuOriginal][$subMenuOriginal]);
					$this->menu[$mainMenuRenamed][$subMenuRenamed] = $save;
				}
			}
			// Changing a first-level element
			else {
				if (isset($this->menu[$mainMenuOriginal])) {
					$save = $this->menu[$mainMenuOriginal];
					unset($this->menu[$mainMenuOriginal]);
					$this->menu[$mainMenuRenamed] = $save;
				}
			}
		}
	}

	/**
	 * Orders the menu according to their order.
	 *
	 */
	private function applyOrdering() {
		uasort($this->menu, array($this, 'menuCompare'));
		foreach ($this->menu as $key => &$element) {
			if (is_null($element)) {
				unset($this->menu[$key]);
			} else {
				if ($element['_hasSubmenu']) {
					uasort($element, array($this, 'menuCompare'));
				}
			}
		}
	}

	/**
	 * Compares two menu entries. Used for ordering.
	 *
	 * @param <array> $itemOne
	 * @param <array> $itemTwo
	 * @return <boolean>
	 */
	protected function menuCompare($itemOne, $itemTwo) {
		if (!is_array($itemOne) || !is_array($itemTwo)
				|| !isset($itemOne['_order']) || !isset($itemTwo['_order'])) {
			return 0;
		}

		if ($itemOne['_order'] == $itemTwo['_order']) {
			return 0;
		}
		return ($itemOne['_order'] < $itemTwo['_order']) ? -1 : 1;
	}

}
?>
