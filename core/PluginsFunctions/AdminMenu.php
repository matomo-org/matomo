<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package PluginsFunctions
 */

/**
 * @package PluginsFunctions
 */
class Piwik_AdminMenu
{
	private $adminMenu = null;
	private $adminMenuOrdered = null;
	static private $instance = null;
	
	/**
	 * @return Piwik_AdminMenu
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
	 * @return array
	 */
	public function get()
	{
		if(!is_null($this->adminMenuOrdered))
		{
			return $this->adminMenuOrdered;
		}
		
		Piwik_PostEvent('AdminMenu.add');

		$this->adminMenuOrdered = array();
		ksort($this->adminMenu);
		foreach($this->adminMenu as $order => $menu)
		{
			foreach($menu as $key => &$element)
    		{
    			if(!is_null($element))
    			{
    				$this->adminMenuOrdered[$key] = $element;
    			}
    		}
		}
		return $this->adminMenuOrdered;
	}
	
	/*
	 *
	 */
	public function add($adminMenuName, $url, $displayedForCurrentUser, $order)
	{
		if($displayedForCurrentUser
			&& !isset($this->adminMenu[$adminMenuName]))
		{
			$this->adminMenu[$order][$adminMenuName] = $url;
		}
	}
	
	/*
	 *
	 */
	public function rename($adminMenuOriginal, $adminMenuRenamed)
	{
		$save = $this->adminMenu[$adminMenuOriginal];
		unset($this->adminMenu[$adminMenuOriginal]);
		$this->adminMenu[$adminMenuRenamed] = $save;
	}
}
function Piwik_GetCurrentAdminMenuName()
{
	$menu = Piwik_GetAdminMenu();
	$currentModule = Piwik::getModule();
	$currentAction = Piwik::getAction();
	foreach($menu as $name => $parameters)
	{
		if($parameters['module'] == $currentModule
			&& $parameters['action'] == $currentAction)
		{
			return $name;
		}
	}
	return false;
}

function Piwik_GetAdminMenu()
{
	return Piwik_AdminMenu::getInstance()->get();
}

function Piwik_AddAdminMenu( $adminMenuName, $url, $displayedForCurrentUser = true, $order = 10 )
{
	return Piwik_AdminMenu::getInstance()->add($adminMenuName, $url, $displayedForCurrentUser, $order);
}

function Piwik_RenameAdminMenuEntry($adminMenuOriginal, $adminMenuRenamed)
{
	Piwik_AdminMenu::getInstance()->rename($adminMenuOriginal, $adminMenuRenamed);
}
