<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../tests/config_test.php";
}

if(!class_exists('Piwik_AnonymizeIP', false))
{
	require_once dirname(__FILE__) . '/../AnonymizeIP.php';
}

class Test_Piwik_AnonymizeIP extends UnitTestCase
{
	// IPv4 addresses and expected results
	protected $ipv4Addresses = array(
		// ip => array( expected0, expected1, expected2, expected3, expected4 ),
		'0.0.0.0'         => array( "\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00" ),
		'0.0.0.1'         => array( "\x00\x00\x00\x01", "\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00" ),
		'0.0.0.255'       => array( "\x00\x00\x00\xff", "\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00" ),
		'0.0.1.0'         => array( "\x00\x00\x01\x00", "\x00\x00\x01\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00" ),
		'0.0.1.1'         => array( "\x00\x00\x01\x01", "\x00\x00\x01\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00" ),
		'0.0.255.255'     => array( "\x00\x00\xff\xff", "\x00\x00\xff\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00" ),
		'0.1.0.0'         => array( "\x00\x01\x00\x00", "\x00\x01\x00\x00", "\x00\x01\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00" ),
		'0.1.1.1'         => array( "\x00\x01\x01\x01", "\x00\x01\x01\x00", "\x00\x01\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00" ),
		'0.255.255.255'   => array( "\x00\xff\xff\xff", "\x00\xff\xff\x00", "\x00\xff\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00" ),
		'1.0.0.0'         => array( "\x01\x00\x00\x00", "\x01\x00\x00\x00", "\x01\x00\x00\x00", "\x01\x00\x00\x00", "\x00\x00\x00\x00" ),
		'127.255.255.255' => array( "\x7f\xff\xff\xff", "\x7f\xff\xff\x00", "\x7f\xff\x00\x00", "\x7f\x00\x00\x00", "\x00\x00\x00\x00" ),
		'128.0.0.0'       => array( "\x80\x00\x00\x00", "\x80\x00\x00\x00", "\x80\x00\x00\x00", "\x80\x00\x00\x00", "\x00\x00\x00\x00" ),
		'255.255.255.255' => array( "\xff\xff\xff\xff", "\xff\xff\xff\x00", "\xff\xff\x00\x00", "\xff\x00\x00\x00", "\x00\x00\x00\x00" ),
	);

	public function test_applyIPMask()
	{
		foreach($this->ipv4Addresses as $ip => $expected)
		{
			// each IP is tested with 0 to 4 octets masked
			for($maskLength = 0; $maskLength <= 4; $maskLength++)
			{
				$res = Piwik_AnonymizeIP::applyIPMask(Piwik_IP::P2N($ip), $maskLength);
				$this->assertEqual( $res, $expected[$maskLength], "Got ".bin2hex($res).", Expected " . bin2hex($expected[$maskLength]) );
			}

			// edge case (bounds check)
			$this->assertEqual( Piwik_AnonymizeIP::applyIPMask(Piwik_IP::P2N($ip), 5), "\x00\x00\x00\x00", $ip );

			// mask IPv4 mapped addresses
			for($maskLength = 0; $maskLength <= 4; $maskLength++)
			{
				$res = Piwik_AnonymizeIP::applyIPMask(Piwik_IP::P2N('::ffff:'.$ip), $maskLength);
				$this->assertEqual( $res, "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff".$expected[$maskLength], "Got ".bin2hex($res).", Expected " . bin2hex($expected[$maskLength]) );
			}
			$this->assertEqual( Piwik_AnonymizeIP::applyIPMask(Piwik_IP::P2N('::ffff:'.$ip), 5), "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\x00\x00\x00\x00\x00", $ip );

			// edge case (bounds check)
			$this->assertEqual( Piwik_AnonymizeIP::applyIPMask(Piwik_IP::P2N('2001::ffff:'.$ip), 17), "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00", $ip );
		}
	}
}
