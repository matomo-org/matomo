<?php
static $mainMenu = array();

// we setup the main categories in a specific order
$mainMenu['Dashboard'] = null;
$mainMenu['General'] = null;
$mainMenu['Visitors'] = null;
$mainMenu['Actions'] = null;
$mainMenu['Referers'] = null;
$mainMenu['Live!'] = null;


function Piwik_GetMenu()
{
	global $mainMenu;
	foreach($mainMenu as $key => &$element)
	{
		if(is_null($element))
		{
			unset($mainMenu[$key]);
		}
		else
		{			
			// we want to move some submenus in the first position
			$priority = array('Overview','Evolution');
			foreach($priority as $name)
			{
				if(isset($element[$name]))
				{
					$newElement = array($name => $element[$name]);
					unset($element[$name]);
					$element = $newElement + $element;
				}
			}
			$element['_url'] = current($element);
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
	global $mainMenu;
	if(isset($mainMenu[$mainMenuOriginal][$subMenuOriginal]))
	{
		$save = $mainMenu[$mainMenuOriginal][$subMenuOriginal];
		unset($mainMenu[$mainMenuOriginal][$subMenuOriginal]);
		$mainMenu[$mainMenuRenamed][$subMenuRenamed] = $save;
	}
}
