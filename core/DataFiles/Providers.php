<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package DataFiles
 */

/**
 * Providers names
 */
if(!isset($GLOBALS['Piwik_ProviderNames']))
{
	$GLOBALS['Piwik_ProviderNames'] = array(
			// France
			"wanadoo"		=> "Orange",
			"proxad"		=> "Free",
			"bbox"			=> "Bouygues Telecom",
			"bouyguestelecom"	=> "Bouygues Telecom",
			"coucou-networks"	=> "Free Mobile",
			"sfr"			=> "SFR",               //Acronym, keep in uppercase
			"univ-metz"		=> "Université de Lorraine",
			"unilim"		=> "Université de Limoges",
			"univ-paris5"		=> "Université Paris Descartes",
			
			// US
			"rr"			=> "Time Warner Cable Internet", // Not sure
		);
}
