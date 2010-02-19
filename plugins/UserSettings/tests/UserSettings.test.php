<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

if(!class_exists('Piwik_UserSettings', false))
{
	require_once 'UserSettings/UserSettings.php';
}
require 'UserSettings/functions.php';

class Test_Piwik_UserSettings extends UnitTestCase
{
	protected $userAgents = array(
		// Internet Explorer
		'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET CLR 3.0.04506; .NET CLR 3.5.21022; InfoPath.2; SLCC1; Zune 3.0)',
		'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)',
		'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/4.0)',
		'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; .NET CLR 3.0.04506.648)',

		// Acoo Browser (treat as IE)
		'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; GTB6; Acoo Browser; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
		'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Acoo Browser; .NET CLR 2.0.50727; .NET CLR 1.1.4322)',
		'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; Acoo Browser; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',

		// AOL / America Online Browser (treat as IE)
		'Mozilla/4.0 (compatible; MSIE 7.0; AOL 9.5; AOLBuild 4337.43; Windows NT 5.1; .NET CLR 1.1.4322)',
		'Mozilla/4.0 (compatible; MSIE 7.0; AOL 9.1; AOLBuild 4334.5009; Windows NT 5.1; GTB5; .NET CLR 1.1.4322)',
		'Mozilla/4.0 (compatible; MSIE 7.0; AOL 9.0; Windows NT 5.1; InfoPath.1)',
		'Mozilla/4.0 (compatible; MSIE 6.0; AOL 8.0; Windows NT 5.1; SV1)',
		'Mozilla/4.0 (compatible; MSIE 7.0; AOL 7.0; Windows NT 5.1)',
		'Mozilla/4.0 (compatible; MSIE 5.5; AOL 6.0; Windows 98; Win 9x 4.90)',
		'Mozilla/4.0 (compatible; MSIE 5.5; AOL 5.0; Windows NT 5.0)',
		'Mozilla/4.0 (compatible; MSIE 4.01; AOL 4.0; Windows 95)',
		'Mozilla/4.0 (compatible; MSIE 7.0; America Online Browser 1.1; Windows NT 5.1; (R1 1.5); .NET CLR 2.0.50727; InfoPath.1)',
		'Mozilla/4.0 (compatible; MSIE 6.0; America Online Browser 1.1; Windows 98)',

		// Avant (treat as IE)
		'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Avant Browser; .NET CLR 2.0.50727; MAXTHON 2.0)',
		'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; Avant Browser; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0)',

		// Firefox
		'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:2.0a1pre) Gecko/2008060602 Minefield/4.0a1pre',
		'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2a2pre) Gecko/20090826 Namoroka/3.6a2pre',
		'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1b4pre) Gecko/20090420 Shiretoko/3.5b4pre (.NET CLR 3.5.30729)',
		'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.0.6) Gecko/2009011913 Firefox/3.0.6',
		'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1b2) Gecko/20060821 BonEcho/2.0b2',

		// Mozilla
		// - unsupported: Beonex

		// Opera
		'Opera/9.63 (Windows NT 5.1; U; en) Presto/2.1.1',
		'Opera/9.30 (Nintendo Wii; U; ; 2047-7; en)',
		'Opera/9.64 (Windows ME; U; en) Presto/2.1.1',
		'Opera/9.80 (Windows NT 5.1; U; en) Presto/2.2.15 Version/10.00',

		// BlackBerry
		'BlackBerry8700/4.1.0 Profile/MIDP-2.0 Configuration/CLDC-1.1',

		// Safari
		'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Version/3.1.2 Safari/525.21',
		'Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_2 like Mac OS X; en-us) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5G77 Safari/525.20',
		'Mozilla/5.0 (iPod; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/3A100a Safari/419.3',
		'Mozilla/5.0 (iPod; U; CPU iPhone OS 2_2_1 like Mac OS X; en-us) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5H11a Safari/525.20',
		'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_5; en-us) AppleWebKit/527.3+ (KHTML, like Gecko) Version/3.1.2 Safari/525.20.1',

		// Chrome
		'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Chrome/1.0.154.48 Safari/525.19',

		// Android
		'Mozilla/5.0 (Linux; U; Android 1.1; en-us; dream) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2',

		// Iron
		'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/531.0 (KHTML, like Gecko) Iron/3.0.189.0 Safari/531.0',

		// Palm webOS
		'Mozilla/5.0 (webOS/1.0; U; en-us) AppleWebKit/525.27.1 (KHTML, like Gecko) Version/1.0 Safari/525.27.1 Pre/1.0',
		'Mozilla/5.0 (webOS/Palm webOS 1.2.9; U; en-US) AppleWebKit/525.27.1 (KHTML, like Gecko) Version/1.0 Safari/525.27.1 Pixi/1.0',
		'Mozilla/5.0 [en] (PalmOS; U; WebPro/3.5; Palm-Zi72)',

		// ABrowse
		'Mozilla/5.0 (compatible; U; ABrowse 0.6; Syllable) AppleWebKit/420+ (KHTML, like Gecko)',
		'Mozilla/5.0 (compatible; ABrowse 0.4; Syllable)',

		// Arora
		'Mozilla/5.0 (X11; U; Linux; de-DE) AppleWebKit/527+ (KHTML, like Gecko, Safari/419.3) Arora/0.8.0',
		'Mozilla/5.0 (X11; U; Linux; en-US) AppleWebKit/527+ (KHTML, like Gecko, Safari/419.3) Arora/0.6',
		'Mozilla/5.0 (Windows; U; Windows NT 5.2; pt-BR) AppleWebKit/527+ (KHTML, like Gecko, Safari/419.3) Arora/0.4 (Change: )',
		'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN) AppleWebKit/523.15 (KHTML, like Gecko, Safari/419.3) Arora/0.3 (Change: 287 c9dfb30)',
		'Mozilla/5.0 (X11; U; Linux; en-US) AppleWebKit/523.15 (KHTML, like Gecko, Safari/419.3) Arora/0.2 (Change: 189 35c14e0)',

		// AmigaVoyager
		'AmigaVoyager/3.2 (AmigaOS/MC680x0)',
		'AmigaVoyager/2.95 (compatible; MC680x0; AmigaOS; SV1)',
		'AmigaVoyager/2.95 (compatible; MC680x0; AmigaOS)',
	);

	protected $unsupported = array(
		// PSP
		'PSP (PlayStation Portable); 2.00',
		'Mozilla/4.0 (PSP (PlayStation Portable); 2.00)',
	);

	public function test_getBrowser()
	{
		$expected = array(
			// id, name, short_name, version, major_number, minor_number, family
			array('IE', 'Internet Explorer', 'IE', '8.0', '8', '0', 'ie'),
			array('IE', 'Internet Explorer', 'IE', '8.0', '8', '0', 'ie'),
			array('IE', 'Internet Explorer', 'IE', '7.0', '7', '0', 'ie'),
			array('IE', 'Internet Explorer', 'IE', '6.0', '6', '0', 'ie'),
			array('IE', 'Internet Explorer', 'IE', '8.0', '8', '0', 'ie'),
			array('IE', 'Internet Explorer', 'IE', '7.0', '7', '0', 'ie'),
			array('IE', 'Internet Explorer', 'IE', '6.0', '6', '0', 'ie'),
			array('IE', 'Internet Explorer', 'IE', '7.0', '7', '0', 'ie'),
			array('IE', 'Internet Explorer', 'IE', '7.0', '7', '0', 'ie'),
			array('IE', 'Internet Explorer', 'IE', '7.0', '7', '0', 'ie'),
			array('IE', 'Internet Explorer', 'IE', '6.0', '6', '0', 'ie'),
			array('IE', 'Internet Explorer', 'IE', '7.0', '7', '0', 'ie'),
			array('IE', 'Internet Explorer', 'IE', '5.5', '5', '5', 'ie'),
			array('IE', 'Internet Explorer', 'IE', '5.5', '5', '5', 'ie'),
			array('IE', 'Internet Explorer', 'IE', '4.01', '4', '01', 'ie'),
			array('IE', 'Internet Explorer', 'IE', '7.0', '7', '0', 'ie'),
			array('IE', 'Internet Explorer', 'IE', '6.0', '6', '0', 'ie'),
			array('IE', 'Internet Explorer', 'IE', '7.0', '7', '0', 'ie'),
			array('IE', 'Internet Explorer', 'IE', '8.0', '8', '0', 'ie'),
			array('FF', 'Firefox', 'Firefox', '4.0', '4', '0', 'gecko'),
			array('FF', 'Firefox', 'Firefox', '3.6', '3', '6', 'gecko'),
			array('FF', 'Firefox', 'Firefox', '3.5', '3', '5', 'gecko'),
			array('FF', 'Firefox', 'Firefox', '3.0', '3', '0', 'gecko'),
			array('FF', 'Firefox', 'Firefox', '2.0', '2', '0', 'gecko'),
			array('OP', 'Opera', 'Opera', '9.63', '9', '63', 'opera'),
			array('OP', 'Opera', 'Opera', '9.30', '9', '30', 'opera'),
			array('OP', 'Opera', 'Opera', '9.64', '9', '64', 'opera'),
			array('OP', 'Opera', 'Opera', '10.00', '10', '0', 'opera'),
			array('BB', 'BlackBerry', 'BlackBerry', '8700.0', '8700', '0', 'unknown'),
			array('SF', 'Safari', 'Safari', '3.1', '3', '1', 'webkit'),
			array('SF', 'Safari', 'Safari', '3.1', '3', '1', 'webkit'),
			array('SF', 'Safari', 'Safari', '3.0', '3', '0', 'webkit'),
			array('SF', 'Safari', 'Safari', '3.1', '3', '1', 'webkit'),
			array('SF', 'Safari', 'Safari', '3.1', '3', '1', 'webkit'),
			array('CH', 'Google Chrome', 'Chrome', '1.0', '1', '0', 'webkit'),
			array('AD', 'Android', 'Android', '3.0', '3', '0', 'webkit'),
			array('IR', 'Iron', 'Iron', '3.0', '3', '0', 'webkit'),
			array('WO', 'Palm webOS', 'webOS', '1.0', '1', '0', 'webkit'),
			array('WO', 'Palm webOS', 'webOS', '1.0', '1', '0', 'webkit'),
			array('WP', 'WebPro', 'WebPro', '3.5', '3', '5', 'unknown'),
			array('AB', 'ABrowse', 'ABrowse', '0.6', '0', '6', 'webkit'),
			array('AB', 'ABrowse', 'ABrowse', '0.4', '0', '4', 'webkit'),
			array('AR', 'Arora', 'Arora', '0.8', '0', '8', 'webkit'),
			array('AR', 'Arora', 'Arora', '0.6', '0', '6', 'webkit'),
			array('AR', 'Arora', 'Arora', '0.4', '0', '4', 'webkit'),
			array('AR', 'Arora', 'Arora', '0.3', '0', '3', 'webkit'),
			array('AR', 'Arora', 'Arora', '0.2', '0', '2', 'webkit'),
			array('AV', 'AmigaVoyager', 'AmigaVoyager', '3.2', '3', '2', 'unknown'),
			array('AV', 'AmigaVoyager', 'AmigaVoyager', '2.95', '2', '95', 'unknown'),
			array('AV', 'AmigaVoyager', 'AmigaVoyager', '2.95', '2', '95', 'unknown'),
		);

		$this->assertEqual(count($expected), count($this->userAgents));

		for($i = 0; $i < count($this->userAgents); $i++)
		{
			$userAgent = $this->userAgents[$i];
			$res = UserAgentParser::getBrowser($userAgent);

			if($res === false)
				$ok = $res === $expected[$i];
			else
			{
				$family = Piwik_getBrowserFamily($res['id']);
				$ok = $expected[$i][0] == $res['id'] &&
				      $expected[$i][1] == $res['name'] &&
				      $expected[$i][2] == $res['short_name'] &&
				      $expected[$i][3] == $res['version'] &&
				      $expected[$i][4] == $res['major_number'] &&
				      $expected[$i][5] == $res['minor_number'] &&
				      $expected[$i][6] == $family;
			}
		
			$this->assertTrue($ok);
			if(!$ok)
			{
				var_dump(array($userAgent, $res, $family));
			}
		}
	}

	public function test_getOperatingSystem()
	{
		$expected = array(
			// id, name, short_name
			array('WI7', 'Windows 7', 'Win 7'),
			array('WVI', 'Windows Vista', 'Win Vista'),
			array('WVI', 'Windows Vista', 'Win Vista'),
			array('WXP', 'Windows XP', 'Win XP'),
			array('WXP', 'Windows XP', 'Win XP'),
			array('WXP', 'Windows XP', 'Win XP'),
			array('WXP', 'Windows XP', 'Win XP'),
			array('WXP', 'Windows XP', 'Win XP'),
			array('WXP', 'Windows XP', 'Win XP'),
			array('WXP', 'Windows XP', 'Win XP'),
			array('WXP', 'Windows XP', 'Win XP'),
			array('WXP', 'Windows XP', 'Win XP'),
			array('W98', 'Windows 98', 'Win 98'),
			array('W2K', 'Windows 2000', 'Win 2000'),
			array('W95', 'Windows 95', 'Win 95'),
			array('WXP', 'Windows XP', 'Win XP'),
			array('W98', 'Windows 98', 'Win 98'),
			array('WXP', 'Windows XP', 'Win XP'),
			array('WI7', 'Windows 7', 'Win 7'),
			array('LIN', 'Linux', 'Linux'),
			array('WVI', 'Windows Vista', 'Win Vista'),
			array('WI7', 'Windows 7', 'Win 7'),
			array('WXP', 'Windows XP', 'Win XP'),
			array('LIN', 'Linux', 'Linux'),
			array('WXP', 'Windows XP', 'Win XP'),
			array('WII', 'Nintendo Wii', 'Wii'),
			array('WME', 'Windows ME', 'Win Me'),
			array('WXP', 'Windows XP', 'Win XP'),
			array('BLB', 'BlackBerry', 'BlackBerry'),
			array('WXP', 'Windows XP', 'Win XP'),
			array('IPH', 'iPhone', 'iPhone'),
			array('IPD', 'iPhone', 'iPhone'),
			array('IPD', 'iPhone', 'iPhone'),
			array('MAC', 'Mac OS', 'Mac OS'),
			array('WXP', 'Windows XP', 'Win XP'),
			array('AND', 'Android', 'Android'),
			array('WI7', 'Windows 7', 'Win 7'),
			array('WOS', 'Palm webOS', 'webOS'),
			array('WOS', 'Palm webOS', 'webOS'),
			array('POS', 'Palm OS', 'Palm OS'),
			array('SYL', 'Syllable', 'Syllable'),
			array('SYL', 'Syllable', 'Syllable'),
			array('LIN', 'Linux', 'Linux'),
			array('LIN', 'Linux', 'Linux'),
			array('WS3', 'Windows Server 2003', 'Win S2003'),
			array('WXP', 'Windows XP', 'Win XP'),
			array('LIN', 'Linux', 'Linux'),
			array('AMI', 'AmigaOS', 'AmigaOS'),
			array('AMI', 'AmigaOS', 'AmigaOS'),
			array('AMI', 'AmigaOS', 'AmigaOS'),
		);

		$this->assertEqual(count($expected), count($this->userAgents));

		for($i = 0; $i < count($this->userAgents); $i++)
		{
			$userAgent = $this->userAgents[$i];
			$res = UserAgentParser::getOperatingSystem($userAgent);

			$ok = $expected[$i][0] == $res['id'] &&
			      $expected[$i][1] == $res['name'] &&
			      $expected[$i][2] == $res['short_name'];
			
			$this->assertTrue($ok);
			if(!$ok)
			{
				var_dump(array($userAgent, $res));
			}
		}
	}

	public function test_getBrowser_unsupported()
	{
		foreach($this->unsupported as $userAgent)
		{
			$res = UserAgentParser::getBrowser($userAgent);
			$ok = $res === false;

			$this->assertTrue($ok);
			if(!$ok)
			{
				var_dump(array($userAgent, $res));
			}
		}
	}

	public function test_getOperatingSystem_unsupported()
	{
		$expected = array(
			array('PSP', 'PlayStation Portable', 'PSP'),
			array('PSP', 'PlayStation Portable', 'PSP'),
		);

		$this->assertEqual(count($expected), count($this->unsupported));

		for($i = 0; $i < count($this->unsupported); $i++)
		{
			$userAgent = $this->unsupported[$i];
			$res = UserAgentParser::getOperatingSystem($userAgent);

			$ok = $expected[$i][0] == $res['id'] &&
			      $expected[$i][1] == $res['name'] &&
			      $expected[$i][2] == $res['short_name'];
			
			$this->assertTrue($ok);
			if(!$ok)
			{
				var_dump(array($userAgent, $res));
			}
		}
	}
}
