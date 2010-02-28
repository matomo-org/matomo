<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

if(!class_exists('Piwik_AnonymizeIP', false))
{
	require_once dirname(__FILE__) . '/../AnonymizeIP.php';
}

class Test_Piwik_AnonymizeIP extends UnitTestCase
{
	// IP addresses and expected results
	protected $ipAddresses = array(
		// long => array( expected0, expected1, expected2, expected3, expected4 ),
		'0'   => array( 0, 0, 0, 0, 0 ),													// 00 00 00 00
		'1'   => array( 1, 0, 0, 0, 0 ),													// 00 00 00 01
		'255' => array( 255, 0, 0, 0, 0 ),													// 00 00 00 FF
		'256' => array( 256, 256, 0, 0, 0 ),												// 00 00 01 00
		'257' => array( 257, 256, 0, 0, 0 ),												// 00 00 01 01
		'65535' => array( 65535, 65280, 0, 0, 0),											// 00 00 FF FF
		'65536' => array( 65536, 65536, 65536, 0, 0),										// 00 01 00 00
		'65793' => array( 65793, 65792, 65536, 0, 0),										// 00 01 01 01
		'16777215' => array( 16777215, 16776960, 16711680, 0, 0),							// 00 FF FF FF
		'16777216' => array( 16777216, 16777216, 16777216, 16777216, 0),					// 01 00 00 00
		'2147483647' => array( 2147483647, 2147483392, 2147418112, 2130706432, 0),			// 7F FF FF FF
		'2147483648' => array( '2147483648', '2147483648', '2147483648', '2147483648', 0),	// 80 00 00 00
		'4294967295' => array( '4294967295', '4294967040', '4294901760', '4278190080', 0),	// FF FF FF FF
	);

	public function test_applyIPMask()
	{
		foreach($this->ipAddresses as $ip => $expected)
		{
			// each IP is tested with 0 to 4 octets masked
			for($maskLength = 0; $maskLength <= 4; $maskLength++)
			{
				$this->assertTrue( Piwik_AnonymizeIP::applyIPMask($ip, $maskLength) == $expected[$maskLength] );
			}
		}
	}
}
