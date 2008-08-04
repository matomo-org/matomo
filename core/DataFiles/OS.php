<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: OS.php 444 2008-04-11 13:38:22Z johmathe $
 * 
 * @package Piwik_UserSettings
 */

/**
 * Operating systems database.
 * If you want to add a new entry, please email us at hello at piwik.org
 * 
 */
if(!isset($GLOBALS['Piwik_Oslist']))
{
	$GLOBALS['Piwik_Oslist'] = array(
						'Nintendo Wii'	 => 'WII',
						'PlayStation Portable' => 'PSP',
						'PLAYSTATION 3'  => 'PS3',
						'Windows NT 6.0' => 'WVI',
						'Windows Vista'  => 'WVI',
						'Windows NT 5.2' => 'WS3',
						'Windows Server 2003' => 'WS3',
						'Windows NT 5.1' => 'WXP',
						'Windows XP'     => 'WXP',
						'Win98'          => 'W98',
						'Windows 98'     => 'W98',
						'Windows NT 5.0' => 'W2K',
						'Windows 2000'   => 'W2K',
						'Windows NT 4.0' => 'WNT',
						'WinNT'          => 'WNT',
						'Windows NT'     => 'WNT',
						'Win 9x 4.90'    => 'WME',
						'Win 9x 4.90'    => 'WME',
						'Windows Me'     => 'WME',
						'Win32'          => 'W95',
						'Win95'          => 'W95',		
						'Windows 95'     => 'W95',
						'Mac_PowerPC'    => 'MAC', 
						'Mac PPC'        => 'MAC',
						'PPC'            => 'MAC',
						'Mac PowerPC'    => 'MAC',
						'Mac OS'         => 'MAC',
						'Linux'          => 'LIN',
						'SunOS'          => 'SOS', 
						'FreeBSD'        => 'BSD', 
						'AIX'            => 'AIX', 
						'IRIX'           => 'IRI', 
						'HP-UX'          => 'HPX', 
						'OS/2'           => 'OS2', 
						'NetBSD'         => 'NBS',
						'Unknown'        => 'XXX' 
		);
		
		
	$GLOBALS['Piwik_Oslist_IdToLabel'] = array_flip($GLOBALS['Piwik_Oslist']);
	
	$GLOBALS['Piwik_Oslist_IdToShortLabel'] = array(
		'PS3' => 'PS3',
		'PSP' => 'PSP',
		'WII' => 'WII',
		'WVI' => 'Win Vista',
		'WS3' => 'Win S2003',
		'WXP' => 'Win XP',
		'W98' => 'Win 98',
		'W2K' => 'Win 2000', 
		'WNT' => 'Win NT',
		'WME' => 'Win Me',
		'W95' => 'Win 95',		
		'WCE' => 'Win CE',
		'MAC' => 'Mac OS',
		'LIN' => 'Linux', 
		'INC' => 'Inconnu', 
		'SOS' => 'SunOS', 
		'BSD' => 'FreeBSD', 
		'AIX' => 'AIX',
		'IRI' => 'IRIX', 
		'HPX' => 'HPX', 
		'OS2' => 'OS/2', 
		'NBS' => 'NetBSD',
		'XXX' => 'Unknown',
		);
}
