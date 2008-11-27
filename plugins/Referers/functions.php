<?php
function Piwik_getPathFromUrl($url)
{
	$path = Piwik_Common::getPathAndQueryFromUrl($url);
	if(empty($path))
	{
		return 'index';
	}
	return $path;
}

function Piwik_getSearchEngineUrlFromName($name)
{
	require_once "DataFiles/SearchEngines.php";
	if(isset($GLOBALS['Piwik_SearchEngines_NameToUrl'][$name]))
	{
		$url = 'http://'.$GLOBALS['Piwik_SearchEngines_NameToUrl'][$name];
	}
	else
	{
		$url = 'URL unknown!';
	}
	return $url;
}


function Piwik_getSearchEngineLogoFromName($url)
{
	require_once "DataFiles/SearchEngines.php";
	$path = 'plugins/Referers/images/searchEngines/%s.png';
	$beginningUrl = strpos($url,'//') + 2;
	$normalPath = sprintf($path, substr($url,$beginningUrl));

	// flags not in the package !
	if(!file_exists($normalPath))
	{
		return sprintf($path, 'xx');
	}
	return $normalPath;
}


function Piwik_getRefererTypeLabel($label)
{
	$indexTranslation = '';
	switch($label)
	{
		case Piwik_Common::REFERER_TYPE_DIRECT_ENTRY:
			$indexTranslation = 'Referers_DirectEntry';
			break;
		case Piwik_Common::REFERER_TYPE_SEARCH_ENGINE:
			$indexTranslation = 'Referers_SearchEngines';
			break;
		case Piwik_Common::REFERER_TYPE_WEBSITE:
			$indexTranslation = 'Referers_Websites';
			break;
		case Piwik_Common::REFERER_TYPE_NEWSLETTER:
			$indexTranslation = 'Referers_Newsletters';
			break;
		case Piwik_Common::REFERER_TYPE_CAMPAIGN:
			$indexTranslation = 'Referers_Campaigns';
			break;
	}
	return Piwik_Translate($indexTranslation);
}