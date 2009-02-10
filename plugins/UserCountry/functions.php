<?php

function Piwik_getFlagFromCode($code)
{
	$path = 'plugins/UserCountry/flags/%s.png';
	$normalPath = sprintf($path, $code);
	$absolutePath = PIWIK_INCLUDE_PATH . "/" . $normalPath;
	if(!file_exists($absolutePath))
	{
		return sprintf($path, 'xx');			
	}
	return $normalPath;
}

function Piwik_ContinentTranslate($label)
{
	if($label == 'unk')
	{
		return Piwik_Translate('General_Unknown');
	}
	
	return Piwik_Translate('UserCountry_continent_'. $label);
}

function Piwik_CountryTranslate($label)
{
	if($label == 'xx')
	{
		return Piwik_Translate('General_Unknown');
	}
	return Piwik_Translate('UserCountry_country_'. $label);
}
