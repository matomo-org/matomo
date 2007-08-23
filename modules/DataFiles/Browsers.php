<?php
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
