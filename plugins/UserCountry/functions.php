<?php

function Piwik_getFlagFromCode($code)
{
	$pathInPiwik = 'plugins/UserCountry/flags/%s.png';
	$pathWithCode = sprintf($pathInPiwik, $code);
	$absolutePath = PIWIK_INCLUDE_PATH . '/' . $pathWithCode;
	if(file_exists($absolutePath))
	{
		return $pathWithCode;
	}
	return sprintf($pathInPiwik, 'xx');			
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
