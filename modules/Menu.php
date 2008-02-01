<?php
static $mainMenu = array();

function Piwik_GetMenu()
{
	global $mainMenu;
	return $mainMenu;
}

function Piwik_AddMenu( $mainMenuName, $subMenuName, $url )
{
	global $mainMenu;
	$mainMenu[$mainMenuName][$subMenuName] = $url;
}