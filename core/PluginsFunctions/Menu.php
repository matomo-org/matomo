<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik
 */

function Piwik_GetMenu()
{
	return Piwik_Menu::getInstance()->get();
}

function Piwik_AddMenu( $mainMenuName, $subMenuName, $url )
{
	Piwik_Menu::getInstance()->add($mainMenuName, $subMenuName, $url);
}

function Piwik_RenameMenuEntry($mainMenuOriginal, $subMenuOriginal, 
								$mainMenuRenamed, $subMenuRenamed)
{
	Piwik_Menu::getInstance()->rename($mainMenuOriginal, $subMenuOriginal, $mainMenuRenamed, $subMenuRenamed);
}

function Piwik_EditMenuUrl( $mainMenuToEdit, $subMenuToEdit, $newUrl )
{
	Piwik_Menu::getInstance()->editUrl($mainMenuToEdit, $subMenuToEdit, $newUrl);
} 

class Piwik_Menu
{
	protected $menu = null;
	protected $edits = array();
	protected $renames = array();
	static private $instance = null;
	
	/**
	 * @return Piwik_Menu
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	/*
	 * @return void
	 */
	protected function __construct()
	{
		// we setup the main categories in a specific order
		$this->menu['Dashboard_Dashboard'] = null;
		$this->menu['General_Visitors'] = null;
		$this->menu['Actions_Actions'] = null;
		$this->menu['Referers_Referers'] = null;
	}

	/*
	 * @return void
	 */
	function add( $mainMenuName, $subMenuName, $url )
	{
		if(!isset($this->menu[$mainMenuName]))
		{
			$this->menu[$mainMenuName]['_url'] = $url;
		}
		if(!empty($subMenuName))
		{
			$this->menu[$mainMenuName][$subMenuName] = $url;
		}
	}
	
	/*
	 * @return void
	 */
	function rename($mainMenuOriginal, $subMenuOriginal, $mainMenuRenamed, $subMenuRenamed)
	{
		$this->renames[] = array($mainMenuOriginal, $subMenuOriginal, 
								$mainMenuRenamed, $subMenuRenamed);
	}
	
	/*
	 * @return void
	 */
	function editUrl($mainMenuToEdit, $subMenuToEdit, $newUrl )
	{
		$this->edits[] = array($mainMenuToEdit, $subMenuToEdit, $newUrl);
		
	}
	
	/*
	 * @return array
	 */
	function get()
	{
		Piwik_PostEvent('Menu.add');
		$this->applyEdits();	
		$this->applyRenames();		
		$this->applyOrdering();
		return $this->menu;
	}
	
	/*
	 * @return void
	 */
	private function applyEdits()
	{
		foreach($this->edits as $edit)
		{
			$mainMenuToEdit = $edit[0];
			$subMenuToEdit = $edit[1];
			$newUrl = $edit[2];
			if(!isset($this->menu[$mainMenuToEdit][$subMenuToEdit]))
			{
				Piwik_AddMenu($mainMenuToEdit, $subMenuToEdit, $newUrl);
			}
			else
			{
				$this->menu[$mainMenuToEdit][$subMenuToEdit] = $newUrl;
			}
		}
	}
	
	/*
	 * @return void
	 */
	private function applyRenames()
	{
		foreach($this->renames as $rename)
		{
			$mainMenuOriginal = $rename[0];
			$subMenuOriginal = $rename[1];
			$mainMenuRenamed = $rename[2];
			$subMenuRenamed = $rename[3];
			if(isset($this->menu[$mainMenuOriginal][$subMenuOriginal]))
			{
				$save = $this->menu[$mainMenuOriginal][$subMenuOriginal];
				unset($this->menu[$mainMenuOriginal][$subMenuOriginal]);
				$this->menu[$mainMenuRenamed][$subMenuRenamed] = $save;
			}
		}
	}
	
	/*
	 * @return void
	 */
	private function applyOrdering()
	{
		// we now do some cleaning on the menu
		foreach($this->menu as $key => &$element)
		{
			if(is_null($element))
			{
				unset($this->menu[$key]);
			}
			else
			{			
				// we want to move some submenus in the first position
				foreach($element as $nameSubmenu => $submenu)
				{
					if(ereg('Evolution', $nameSubmenu) !== false
						|| ereg('Overview', $nameSubmenu) !== false)
					{
						$newElement = array($nameSubmenu => $submenu);
						unset($element[$nameSubmenu]);
						$element = $newElement + $element;
						break;
					}
				}
				$element['_url'] = reset($element);
			}
		}
	}
}
