<?php
require_once "DataFiles/Browsers.php";
require_once "DataFiles/OS.php";

function Piwik_getPluginsLogo( $oldLabel )
{
	return  "plugins/UserSettings/images/plugins/". $oldLabel . ".gif";
}

function Piwik_getOSLabel($oldLabel)
{
	if(isset($GLOBALS['Piwik_Oslist_IdToLabel'][$oldLabel]))
	{
		return $GLOBALS['Piwik_Oslist_IdToLabel'][$oldLabel];
	}
	return 'UNK';
}


function Piwik_getOSShortLabel($oldLabel)
{
	if(isset($GLOBALS['Piwik_Oslist_IdToShortLabel'][$oldLabel]))
	{
		return $GLOBALS['Piwik_Oslist_IdToShortLabel'][$oldLabel];
	}
	return 'UNK';
}

function Piwik_getBrowserTypeLabel($oldLabel)
{
	if(isset(Piwik_UserSettings::$browserType_display[$oldLabel]))
	{
		return Piwik_UserSettings::$browserType_display[$oldLabel];
	}
	return Piwik_Translate('General_Unknown');
}


function Piwik_getConfigurationLabel($str)
{
	$values = explode(";", $str);
	
	$os = Piwik_getOSLabel($values[0]);
	$name = $values[1];
	$browser = 'Unknown';
	if(isset($GLOBALS['Piwik_BrowserList_IdToLabel'][$name]))
	{
		$browser = $GLOBALS['Piwik_BrowserList_IdToLabel'][$name];
	}
	
	$resolution = $values[2];
	
	return $os . " / " . $browser . " / " . $resolution;
}

function Piwik_getBrowserLabel($oldLabel)
{
	$name = Piwik_getBrowserId($oldLabel);
	$version = Piwik_getBrowserVersion($oldLabel);
	if(isset($GLOBALS['Piwik_BrowserList_IdToLabel'][$name]))
	{
		return $GLOBALS['Piwik_BrowserList_IdToLabel'][$name] . " ". $version;
	}
	return 'UNK';
}

function Piwik_getBrowserShortLabel($oldLabel)
{
	$name = Piwik_getBrowserId($oldLabel);
	$version = Piwik_getBrowserVersion($oldLabel);
	if(isset($GLOBALS['Piwik_BrowserList_IdToShortLabel'][$name]))
	{
		return $GLOBALS['Piwik_BrowserList_IdToShortLabel'][$name] . " ". $version;
	}
	return 'UNK';
}

function Piwik_getBrowserId($str)
{
	return substr($str, 0, strpos($str, ';'));
}

function Piwik_getBrowserVersion($str)
{
	return substr($str, strpos($str, ';') + 1);
}

function Piwik_getBrowsersLogo($label)
{
	$id = Piwik_getBrowserId($label);
	return  "plugins/UserSettings/images/browsers/". $id . ".gif";
}

function Piwik_getOSLogo($label)
{
	$path = "plugins/UserSettings/images/os/". $label . ".gif";
	return $path;
}

function Piwik_getScreensLogo($label)
{
	return "plugins/UserSettings/images/screens/" . $label . ".gif";
}


function Piwik_UserSettings_keepStrlenGreater($value)
{
	return strlen($value) > 5;
}

function Piwik_getScreenTypeFromResolution($resolution)
{
	$width = intval(substr($resolution, 0, strpos($resolution, 'x')));
	$height= intval(substr($resolution, strpos($resolution, 'x') + 1));
	$ratio = Piwik::secureDiv($width, $height);
	
	if($ratio < 1.4)
	{
		$name = 'normal';
	}
	else if($ratio < 2)
	{
		$name = 'wide';
	}
	else
	{
		$name = 'dual';
	}
	return $name;
}

function Piwik_getBrowserFamily($browserLabel)
{
	$familyNameToUse = 'unknown';
		
	foreach(Piwik_UserSettings::$browserType as $familyName => $aBrowsers)
	{			
		if(in_array(substr($browserLabel, 0, 2), $aBrowsers))
		{
			$familyNameToUse = $familyName;
			break;				
		}
	}
	return $familyNameToUse;	
}			
