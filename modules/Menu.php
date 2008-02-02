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
			uksort($element, 'Piwik_sortSubMenu');
		}	
	}
	return $mainMenu;
}


function Piwik_AddMenu( $mainMenuName, $subMenuName, $url, $setUrlMainMenu = false )
{
	global $mainMenu;
	
	if(!empty($subMenuName))
	{
		$mainMenu[$mainMenuName][$subMenuName] = $url;
	}
	
	if($setUrlMainMenu 
		|| !isset($mainMenu[$mainMenuName]['_url']))
	{
		$mainMenu[$mainMenuName]['_url'] = $url;
	}
}

function Piwik_sortSubMenu( $sub1, $sub2 )
{
	if(in_array(strtolower($sub2), array('overview','evolution')))
	{
		return 1;
	}
	return -1;
}