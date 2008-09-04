<?php
function Piwik_getHostnameName($in)
{
	if(empty($in))
	{
		return Piwik_Translate('General_Unknown');
	}
	elseif(strtolower($in) === 'ip')
	{
		return "IP";
	}
	return ucfirst(substr($in, 0, strpos($in, '.')));
}

function Piwik_getHostnameUrl($in)
{
	if(empty($in)
		|| strtolower($in) === 'ip')
	{
		return "";
	}
	return "http://www.".$in."/";
}
