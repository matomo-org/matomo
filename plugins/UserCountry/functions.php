<?php

function Piwik_getFlagFromCode($code)
{
	$path = PIWIK_INCLUDE_PATH . '/plugins/UserCountry/flags/%s.png';
	$pathWithCode = sprintf($path, $code);
	if(file_exists($pathWithCode))
	{
		return $pathWithCode;
	}
	return sprintf($path, 'xx');			
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
