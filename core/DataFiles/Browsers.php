<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Browsers.php 444 2008-04-11 13:38:22Z johmathe $
 * 
 * @package Piwik_UserSettings
 */

/**
 * Browser list.
 * If you want to add a new entry, please email us at hello at piwik.org
 * 
 */
if(!isset($GLOBALS['Piwik_BrowserList'] ))
{
	$GLOBALS['Piwik_BrowserList'] = array(
					'msie'							=> 'IE',
					'microsoft internet explorer'	=> 'IE',
					'internet explorer'				=> 'IE',
					'netscape6'						=> 'NS',
					'netscape'						=> 'NS',
					'galeon'						=> 'GA',
					'phoenix'						=> 'PX',
					'firefox'						=> 'FF',
					'mozilla firebird'				=> 'FB',
					'firebird'						=> 'FB',
					'seamonkey'						=> 'SM',
					'chimera'						=> 'CH',
					'camino'						=> 'CA',
					'safari'						=> 'SF',
					'k-meleon'						=> 'KM',
					'mozilla'						=> 'MO',
					'opera'							=> 'OP',
					'konqueror'						=> 'KO',
					'icab'							=> 'IC',
					'lynx'							=> 'LX',
					'links'							=> 'LI',
					'ncsa mosaic'					=> 'MC',
					'amaya'							=> 'AM',
					'omniweb'						=> 'OW',
					'hotjava'						=> 'HJ',
					'browsex'						=> 'BX',
					'amigavoyager'					=> 'AV',
					'amiga-aweb'					=> 'AW',
					'ibrowse'						=> 'IB',
					'unknown'						=> 'UNK'
			);	
			
			
	$GLOBALS['Piwik_BrowserList_IdToLabel'] 
		= array_map('ucwords',array_flip($GLOBALS['Piwik_BrowserList']));
	
	$GLOBALS['Piwik_BrowserList_IdToShortLabel'] = $GLOBALS['Piwik_BrowserList_IdToLabel'];
	$GLOBALS['Piwik_BrowserList_IdToShortLabel']['IE'] = "IE";
	$GLOBALS['Piwik_BrowserList_IdToShortLabel']['FB'] = "Firebird";
}
