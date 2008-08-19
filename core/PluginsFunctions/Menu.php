<?php
static $mainMenu = array();
static $menuEditsToApply = array();
static $menuRenameToApply = array();

// we setup the main categories in a specific order
$mainMenu['Dashboard_Dashboard'] = null;
$mainMenu['General_Visitors'] = null;
$mainMenu['Actions_Actions'] = null;
$mainMenu['Referers_Referers'] = null;

function Piwik_GetMenu()
{
	global $mainMenu;
	global $menuEditsToApply;
	global $menuRenameToApply;
	
	// we apply the list of edits we've registered so far
	foreach($menuEditsToApply as $edit)
	{
		$mainMenuToEdit = $edit[0];
		$subMenuToEdit = $edit[1];
		$newUrl = $edit[2];
		if(!isset($mainMenu[$mainMenuToEdit][$subMenuToEdit]))
		{
			Piwik_AddMenu($mainMenuToEdit, $subMenuToEdit, $newUrl);
		}
		else
		{
			$mainMenu[$mainMenuToEdit][$subMenuToEdit] = $newUrl;
		}
	}
	
	// we now apply the menu rename
	foreach($menuRenameToApply as $rename)
	{
		$mainMenuOriginal = $rename[0];
		$subMenuOriginal = $rename[1];
		$mainMenuRenamed = $rename[2];
		$subMenuRenamed = $rename[3];
		if(isset($mainMenu[$mainMenuOriginal][$subMenuOriginal]))
		{
			$save = $mainMenu[$mainMenuOriginal][$subMenuOriginal];
			unset($mainMenu[$mainMenuOriginal][$subMenuOriginal]);
			$mainMenu[$mainMenuRenamed][$subMenuRenamed] = $save;
		}
	}	
	
	// we now do some cleaning on the menu
	foreach($mainMenu as $key => &$element)
	{
		if(is_null($element))
		{
			unset($mainMenu[$key]);
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
	return $mainMenu;
}


function Piwik_AddMenu( $mainMenuName, $subMenuName, $url )
{
	global $mainMenu;
	
	if(!isset($mainMenu[$mainMenuName]))
	{
		$mainMenu[$mainMenuName]['_url'] = $url;
	}
	if(!empty($subMenuName))
	{
		$mainMenu[$mainMenuName][$subMenuName] = $url;
	}
	
}

function Piwik_RenameMenuEntry($mainMenuOriginal, $subMenuOriginal, 
								$mainMenuRenamed, $subMenuRenamed)
{
	global $menuRenameToApply;
	$menuRenameToApply[] = array($mainMenuOriginal, $subMenuOriginal, 
								$mainMenuRenamed, $subMenuRenamed);
}

function Piwik_EditMenuUrl( $mainMenuToEdit, $subMenuToEdit, $newUrl )
{
	global $menuEditsToApply;
	$menuEditsToApply[] = array($mainMenuToEdit, $subMenuToEdit, $newUrl);
} 
