<?php
static $adminMenu = array();

function Piwik_GetAdminMenu()
{
	global $adminMenu;
	foreach($adminMenu as $key => &$element)
	{
		if(is_null($element))
		{
			unset($adminMenu[$key]);
		}
	}
	return $adminMenu;
}

function Piwik_AddAdminMenu( $adminMenuName, $url )
{
	global $adminMenu;

	if(!isset($adminMenu[$adminMenuName]))
	{
		$adminMenu[$adminMenuName] = $url;
	}
}

function Piwik_RenameAdminMenuEntry($adminMenuOriginal, $adminMenuRenamed)
{
	global $adminMenu;
	$save = $adminMenu[$adminMenuOriginal];
	unset($adminMenu[$adminMenuOriginal]);
	$adminMenu[$adminMenuRenamed] = $save;
}
