<?php
/**
 * Example usage
 * 
 * Browser info:
 * var_dump(UserAgentParser::getBrowser($_SERVER['HTTP_USER_AGENT']));
 * 
 * Outputs:
 * array
 * 	'id' => 'FF' 
 *  'name' => 'Firefox'
 *  'short_name' => 'Firefox'
 *  'version' => '3.0'
 *  'major_number' => '3'
 *  'minor_number' => '0'
 * 
 * Operating System info:
 * var_dump(UserAgentParser::getOperatingSystem($_SERVER['HTTP_USER_AGENT']));
 *
 * Outputs:
 * array
 *  'id' => 'WXP'
 * 	'name' => 'Windows XP'
 * 	'short_name' => 'Win XP'
 * 
 */
class UserAgentParser 
{
	// browser regex => browser ID
	static protected $browsers = array(
					'opera'							=> 'OP',
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
					'camino'						=> 'CA',
					'safari'						=> 'SF',
					'chrome'						=> 'CH',
					'k-meleon'						=> 'KM',
					'mozilla'						=> 'MO',
					'konqueror'						=> 'KO',
					'blackberry'					=> 'BB',
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
					'arora'							=> 'AR',
			);

	// OS regex => OS ID
	static protected $operatingSystems = array(
						'iPod'  		 => 'IPD',
						'iPhone'         => 'IPH',
						'Nintendo Wii'   => 'WII',
						'PlayStation Portable' => 'PSP',
						'PlayStation 3'  => 'PS3',
						'Android'  		 => 'AND',
						'PalmOS'  		 => 'POS',
						'Palm OS'  		 => 'POS',
						'BlackBerry' 	 => 'BLB',
						'Windows NT 6.1' => 'WI7',
						'Windows 7' 	 => 'WI7',
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
						'Windows ME'     => 'WME',
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
		);
			
	static protected $browserIdToName;
	static protected $browserIdToShortName;
	static protected $operatingSystemsIdToName;
	static protected $operatingSystemsIdToShortName;
	static private $init = false;
	
	/**
	 * Returns a 3 letters ID for the operating system part, given a user agent string.
	 * 
	 * @param string $userAgent
	 * @return string false if OS couldn't be identified, or 3 letters ID (eg. WXP)
	 * @see UserAgentParser/OperatingSystems.php for the list of OS (also available in self::$operatingSystems)
	 */
	static public function getOperatingSystem($userAgent)
	{
		self::init();
		$info = array(
			'id' => '',
			'name' => '',
			'short_name' => '',
		);
		foreach(self::$operatingSystems as $key => $value) {
			if (strstr($userAgent, $key) !== false) {
				$info['id'] = $value;
				break;
			}
		}
		if(empty($info['id'])) {
			return false;
		}
		$info['name'] = self::getOperatingSystemNameFromId($info['id']);
		$info['short_name'] = self::getOperatingSystemShortNameFromId($info['id']);
		return $info;
	}
	
	/**
	 * Returns the browser information array, given a user agent string.
	 * 
	 * @param string $userAgent
	 * @return array false if the browser is "unknown", or 
	 * 				array(		'name' 			=> '', // 2 letters ID, eg. FF 
	 * 							'major_number' 	=> '', // 2 in firefox 2.0.12
	 * 							'minor_number' 	=> '', // 0 in firefox 2.0.12
	 * 							'version' 		=> ''  // major_number.minor_number
	 * 				);
	 * @see self::$browsers for the list of OS 
	 */
	static public function getBrowser($userAgent)
	{
		self::init();

		$info = array(
			'id' 			=> '',
			'name'			=> '',
			'short_name'	=> '',
			'major_number' 	=> '',
			'minor_number' 	=> '',
			'version' 		=> '',
			);

		$browser = '';
		foreach(self::$browsers as $key => $value) {
			if(!empty($browser)) {
				$browser .= "|";
			}
			$browser .= $key;
		}

		$results = array();

		// added fix for Mozilla Suite detection
		if (preg_match_all("/(mozilla)[\/\sa-z;.0-9-(]+rv:([0-9]+)([.0-9a-z]+)\) gecko\/[0-9]{8}$/i", $userAgent, $results)
			||	preg_match_all("/($browser)[\/\sa-z(]*([0-9]+)([\.0-9a-z]+)?/i", $userAgent, $results)
			)
		 {
		 	$count = count($results[0])-1;
		 	
		 	// because google chrome is Mozilla/Chrome/Safari at the same time, we force Chrome
		 	if(($chrome = array_search('Chrome', $results[1])) !== false) {
		 		$count = $chrome;
		 	}
		 	
		 	// browser code
		 	$info['id'] = self::$browsers[strtolower($results[1][$count])];
		 		
		 	// major version number (1 in mozilla 1.7)
		 	$info['major_number'] = $results[2][$count];
		 		
		 	// is an minor version number ? If not, 0
		 	$match = array();
		 		
		 	preg_match('/([.\0-9]+)?([\.a-z0-9]+)?/i', $results[3][$count], $match);
		 		
		 	if(isset($match[1])) {
		 		// find minor version number (7 in mozilla 1.7, 9 in firefox 0.9.3)
		 		$dot = strpos(substr($match[1], 1), '.');
		 		if($dot !== false) {
		 			$info['minor_number'] = substr($match[1], 1, $dot);
		 		} 
		 		else {
		 			$info['minor_number'] = substr($match[1], 1);
		 		}
		 	}
		 	else {
		 		$info['minor_number'] = '0';
		 	}
		 	$info['version'] = $info['major_number'] . "." . $info['minor_number'];
		 	$info['name'] = self::getBrowserNameFromId($info['id']);
		 	$info['short_name'] = self::getBrowserShortNameFromId($info['id']);
		 	return $info;
		 }
		 return false;
	}
	
	static protected function init() {
		if(self::$init) {
			return;
		}
		self::$init = true;
		
		// init browser names and short names
		self::$browserIdToName = array_map('ucwords',array_flip(self::$browsers));
		self::$browserIdToName['CH'] = "Google Chrome";
		
		self::$browserIdToShortName = self::$browserIdToName;
		self::$browserIdToShortName['IE'] = "IE";
		self::$browserIdToShortName['FB'] = "Firebird";
		
		// init OS names and short names
		self::$operatingSystemsIdToName = array_flip(self::$operatingSystems);
		self::$operatingSystemsIdToShortName = array_merge(self::$operatingSystemsIdToName, array(
			'PS3' => 'PS3',
			'PSP' => 'PSP',
			'IPH' => 'iPhone',
			'WII' => 'WII',
			'WI7' => 'Win 7',
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
			'UNK' => 'Unknown',
			));
	}
	
	static public function getBrowserNameFromId($browserId)
	{
		self::init();
		if(isset(self::$browserIdToName[$browserId])) {
			return self::$browserIdToName[$browserId];
		}
		return false;
	}
	
	static public function getBrowserShortNameFromId($browserId)
	{
		self::init();
		if(isset(self::$browserIdToShortName[$browserId])) {
			return self::$browserIdToShortName[$browserId];
		}
		return false;
	}
	
	static public function getOperatingSystemNameFromId($osId)
	{
		self::init();
		if(isset(self::$operatingSystemsIdToName[$osId])) {
			return self::$operatingSystemsIdToName[$osId];
		}
		return false;
	}
	
	static public function getOperatingSystemShortNameFromId($osId)
	{
		self::init();
		if(isset(self::$operatingSystemsIdToShortName[$osId])) {
			return self::$operatingSystemsIdToShortName[$osId];
		}
		return false;
		
	}
	
}
