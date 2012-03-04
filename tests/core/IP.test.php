<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once 'IP.php';
class Test_Piwik_IP extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
	}

	public function setUp()
	{
		parent::setUp();
		$_GET = $_POST = array();
	}

	public function tearDown()
	{
		parent::tearDown();
	}

	function test_sanitizeIp()
	{
		$tests = array(
			// single IPv4 address
			'127.0.0.1' => '127.0.0.1',

			// single IPv6 address (ambiguous)
			'::1' => '::1',
			'::ffff:127.0.0.1' => '::ffff:127.0.0.1',
			'2001:5c0:1000:b::90f8' => '2001:5c0:1000:b::90f8',

			// single IPv6 address
			'[::1]' => '::1',
			'[2001:5c0:1000:b::90f8]' => '2001:5c0:1000:b::90f8',
			'[::ffff:127.0.0.1]' => '::ffff:127.0.0.1',

			// single IPv4 address (CIDR notation)
			'192.168.1.1/32' => '192.168.1.1',

			// single IPv6 address (CIDR notation)
			'::1/128' => '::1',
			'::ffff:127.0.0.1/128' => '::ffff:127.0.0.1',
			'2001:5c0:1000:b::90f8/128' => '2001:5c0:1000:b::90f8',

			// IPv4 address with port
			'192.168.1.2:80' => '192.168.1.2',

			// IPv6 address with port
			'[::1]:80' => '::1',
			'[::ffff:127.0.0.1]:80' => '::ffff:127.0.0.1',
			'[2001:5c0:1000:b::90f8]:80' => '2001:5c0:1000:b::90f8',

			// hostnames with port?
			'localhost' => 'localhost',
			'localhost:80' => 'localhost',
			'www.example.com' => 'www.example.com',
			'example.com:80' => 'example.com',
		);

		foreach($tests as $ip => $expected)
		{
			$this->assertEqual( Piwik_IP::sanitizeIp($ip), $expected, "$ip" );
		}
	}

	function test_sanitizeIpRange()
	{
		$tests = array(
			'' => false,
			' 127.0.0.1 ' => '127.0.0.1/32',
			'192.168.1.0' => '192.168.1.0/32',
			'192.168.1.1/24' => '192.168.1.1/24',
			'192.168.1.2/16' => '192.168.1.2/16',
			'192.168.1.3/8' => '192.168.1.3/8',
			'192.168.2.*' => '192.168.2.0/24',
			'192.169.*.*' => '192.169.0.0/16',
			'193.*.*.*' => '193.0.0.0/8',
			'*.*.*.*' => '0.0.0.0/0',
			'*.*.*.1' => false,
			'*.*.1.1' => false,
			'*.1.1.1' => false,
			'1.*.1.1' => false,
			'1.1.*.1' => false,
			'1.*.*.1' => false,
			'::1' => '::1/128',
			'::ffff:127.0.0.1' => '::ffff:127.0.0.1/128',
			'2001:5c0:1000:b::90f8' => '2001:5c0:1000:b::90f8/128',
			'::1/64' => '::1/64',
			'::ffff:127.0.0.1/64' => '::ffff:127.0.0.1/64',
			'2001:5c0:1000:b::90f8/64' => '2001:5c0:1000:b::90f8/64',
		);

		foreach($tests as $ip => $expected)
		{
			$this->assertEqual( Piwik_IP::sanitizeIpRange($ip), $expected, "$ip" );
		}
	}

	private function getP2NTestData()
	{
		return array(
			// IPv4
			'0.0.0.0' => "\x00\x00\x00\x00",
			'127.0.0.1' => "\x7F\x00\x00\x01",
			'192.168.1.12' => "\xc0\xa8\x01\x0c",
			'255.255.255.255' => "\xff\xff\xff\xff",

			// IPv6
			'::' => "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			'::1' => "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01",
			'::fffe:7f00:1' => "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xfe\x7f\x00\x00\x01",
			'::ffff:127.0.0.1' => "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x7f\x00\x00\x01",
			'2001:5c0:1000:b::90f8' => "\x20\x01\x05\xc0\x10\x00\x00\x0b\x00\x00\x00\x00\x00\x00\x90\xf8",
		);
	}

	function test_P2N()
	{
		foreach($this->getP2NTestData() as $P => $N)
		{
			$this->assertEqual( Piwik_IP::P2N($P), $N, "$P" );
		}
	}

	function test_P2N_invalidInput()
	{
		$tests = array(
			// not a series of dotted numbers
			null,
			'',
			'alpha',
			'...',

			// missing an octet
			'.0.0.0',
			'0..0.0',
			'0.0..0',
			'0.0.0.',

			// octets must be 0-255
			'-1.0.0.0',
			'1.1.1.256',
		);

		if (!Piwik_Common::isMacOS())
		{
			// leading zeros not supported (i.e., can be ambiguous, e.g., octal)
			$tests[] = '07.07.07.07';
		}

		foreach($tests as $P)
		{
			$this->assertEqual( Piwik_IP::P2N($P), "\x00\x00\x00\x00", "$P" );
		}
	}

	private function getN2PTestData()
	{
		// a valid network address is either 4 or 16 bytes; those lines are intentionally left blank ;)
		return array(
			null,
			'',
			"\x01",
			"\x01\x00",
			"\x01\x00\x00",

			"\x01\x00\x00\x00\x00",
			"\x01\x00\x00\x00\x00\x00",
			"\x01\x00\x00\x00\x00\x00\x00",
			"\x01\x00\x00\x00\x00\x00\x00\x00",
			"\x01\x00\x00\x00\x00\x00\x00\x00\x00",
			"\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			"\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			"\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			"\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			"\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			"\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",

			"\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
		);
	}

	function test_N2P()
	{
		foreach($this->getP2NTestData() as $P => $N)
		{
			$this->assertEqual( Piwik_IP::N2P($N), $P, "$P vs" . Piwik_IP::N2P($N) );
		}
	}

	function test_N2P_invalidInput()
	{
		foreach($this->getN2PTestData() as $N)
		{
			$this->assertEqual( Piwik_IP::N2P($N), "0.0.0.0", bin2hex($N) );
		}
	}

	function test_prettyPrint()
	{
		foreach($this->getP2NTestData() as $P => $N)
		{
			$this->assertEqual( Piwik_IP::prettyPrint($N), $P, "$P vs" . Piwik_IP::N2P($N) );
		}
	}

	function test_prettyPrint_invalidInput()
	{
		foreach($this->getN2PTestData() as $N)
		{
			$this->assertEqual( Piwik_IP::prettyPrint($N), "0.0.0.0", bin2hex($N) );
		}
	}

	function test_isIPv4()
	{
		// a valid network address is either 4 or 16 bytes; those lines are intentionally left blank ;)
		$tests = array(
			// invalid
			null => false,
			"" => false,

			// IPv4
			"\x00\x00\x00\x00" => true,
			"\x7f\x00\x00\x01" => true,

			// IPv4-compatible (this transitional format is deprecated in RFC 4291, section 2.5.5.1)
			"\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xc0\xa8\x01\x01" => true,

			// IPv4-mapped (RFC 4291, 2.5.5.2)
			"\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\xc0\xa8\x01\x02" => true,

			// other IPv6 address
			"\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\x00\xc0\xa8\x01\x03" => false,
			"\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01\xc0\xa8\x01\x04" => false,
			"\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xc0\xa8\x01\x05" => false,

			/*
			 * We assume all stored IP addresses (pre-Piwik 1.4) were converted from UNSIGNED INT to VARBINARY.
			 * The following is just for informational purposes.
			 */

			// 192.168.1.0
			'-1062731520' => false,
			'3232235776' => false,

			// 10.10.10.10
			'168430090' => false,

			// 0.0.39.15 - this is the ambiguous case (i.e., 4 char string)
			'9999' => true,
			"\x39\x39\x39\x39" => true,

			// 0.0.3.231
			'999' => false,
			"\x39\x39\x39" => false,
		);

		foreach($tests as $N => $bool)
		{
			$this->assertEqual( Piwik_IP::isIPv4($N), $bool, bin2hex($N) );
		}
	}

	function test_long2ip()
	{
		// a valid network address is either 4 or 16 bytes; those lines are intentionally left blank ;)
		$tests = array(
			// invalid
			null => '0.0.0.0',
			"" => '0.0.0.0',

			// IPv4
			"\x7f\x00\x00\x01" => '127.0.0.1',

			// IPv4-compatible (this transitional format is deprecated in RFC 4291, section 2.5.5.1)
			"\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xc0\xa8\x01\x01" => '192.168.1.1',

			// IPv4-mapped (RFC 4291, 2.5.5.2)
			"\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\xc0\xa8\x01\x02" => '192.168.1.2',

			// other IPv6 address
			"\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\x00\xc0\xa8\x01\x03" => '0.0.0.0',
			"\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01\xc0\xa8\x01\x04" => '0.0.0.0',
			"\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xc0\xa8\x01\x05" => '0.0.0.0',

			/*
			 * We assume all stored IP addresses (pre-Piwik 1.4) were converted from UNSIGNED INT to VARBINARY.
			 * The following is just for informational purposes.
			 */

			// 192.168.1.0
			'-1062731520' => '0.0.0.0',
			'3232235776' => '0.0.0.0',

			// 10.10.10.10
			'168430090' => '0.0.0.0',

			// 0.0.39.15 - this is the ambiguous case (i.e., 4 char string)
			'9999' => '57.57.57.57',
			"\x39\x39\x39\x39" => '57.57.57.57',

			// 0.0.3.231
			'999' => '0.0.0.0',
			"\x39\x39\x39" => '0.0.0.0',
		);

		foreach($tests as $N => $P)
		{
			$this->assertEqual( Piwik_IP::long2ip($N), $P, bin2hex($N) );
			// this is our compatibility function
			$this->assertEqual( Piwik_Common::long2ip($N), $P, bin2hex($N) );
		}
	}

	function test_getIpsForRange()
	{
		$tests = array(

			// invalid ranges
			null => false,
			'' => false,
			'0' => false,

			// single IPv4
			'127.0.0.1' => array( "\x7f\x00\x00\x01", "\x7f\x00\x00\x01" ),

			// IPv4 with wildcards
			'192.168.1.*' => array( "\xc0\xa8\x01\x00", "\xc0\xa8\x01\xff" ),
			'192.168.*.*' => array( "\xc0\xa8\x00\x00", "\xc0\xa8\xff\xff" ),
			'192.*.*.*' => array( "\xc0\x00\x00\x00", "\xc0\xff\xff\xff" ),
			'*.*.*.*' => array( "\x00\x00\x00\x00", "\xff\xff\xff\xff" ),

			// single IPv4 in expected CIDR notation
			'192.168.1.1/24' => array( "\xc0\xa8\x01\x00", "\xc0\xa8\x01\xff" ),

			'192.168.1.127/32' => array( "\xc0\xa8\x01\x7f", "\xc0\xa8\x01\x7f" ),
			'192.168.1.127/31' => array( "\xc0\xa8\x01\x7e", "\xc0\xa8\x01\x7f" ),
			'192.168.1.127/30' => array( "\xc0\xa8\x01\x7c", "\xc0\xa8\x01\x7f" ),
			'192.168.1.127/29' => array( "\xc0\xa8\x01\x78", "\xc0\xa8\x01\x7f" ),
			'192.168.1.127/28' => array( "\xc0\xa8\x01\x70", "\xc0\xa8\x01\x7f" ),
			'192.168.1.127/27' => array( "\xc0\xa8\x01\x60", "\xc0\xa8\x01\x7f" ),
			'192.168.1.127/26' => array( "\xc0\xa8\x01\x40", "\xc0\xa8\x01\x7f" ),
			'192.168.1.127/25' => array( "\xc0\xa8\x01\x00", "\xc0\xa8\x01\x7f" ),

			'192.168.1.255/32' => array( "\xc0\xa8\x01\xff", "\xc0\xa8\x01\xff" ),
			'192.168.1.255/31' => array( "\xc0\xa8\x01\xfe", "\xc0\xa8\x01\xff" ),
			'192.168.1.255/30' => array( "\xc0\xa8\x01\xfc", "\xc0\xa8\x01\xff" ),
			'192.168.1.255/29' => array( "\xc0\xa8\x01\xf8", "\xc0\xa8\x01\xff" ),
			'192.168.1.255/28' => array( "\xc0\xa8\x01\xf0", "\xc0\xa8\x01\xff" ),
			'192.168.1.255/27' => array( "\xc0\xa8\x01\xe0", "\xc0\xa8\x01\xff" ),
			'192.168.1.255/26' => array( "\xc0\xa8\x01\xc0", "\xc0\xa8\x01\xff" ),
			'192.168.1.255/25' => array( "\xc0\xa8\x01\x80", "\xc0\xa8\x01\xff" ),

			'192.168.255.255/24' => array( "\xc0\xa8\xff\x00", "\xc0\xa8\xff\xff" ),
			'192.168.255.255/23' => array( "\xc0\xa8\xfe\x00", "\xc0\xa8\xff\xff" ),
			'192.168.255.255/22' => array( "\xc0\xa8\xfc\x00", "\xc0\xa8\xff\xff" ),
			'192.168.255.255/21' => array( "\xc0\xa8\xf8\x00", "\xc0\xa8\xff\xff" ),
			'192.168.255.255/20' => array( "\xc0\xa8\xf0\x00", "\xc0\xa8\xff\xff" ),
			'192.168.255.255/19' => array( "\xc0\xa8\xe0\x00", "\xc0\xa8\xff\xff" ),
			'192.168.255.255/18' => array( "\xc0\xa8\xc0\x00", "\xc0\xa8\xff\xff" ),
			'192.168.255.255/17' => array( "\xc0\xa8\x80\x00", "\xc0\xa8\xff\xff" ),

			// single IPv6
			'::1' => array( "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01", "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01" ),

			// single IPv6 in expected CIDR notation
			'::1/128' => array( "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01", "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01" ),
			'::1/127' => array( "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00", "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01" ),
			'::fffe:7f00:1/120' => array( "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xfe\x7f\x00\x00\x00", "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xfe\x7f\x00\x00\xff" ),
			'::ffff:127.0.0.1/120' => array( "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x7f\x00\x00\x00", "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x7f\x00\x00\xff" ),

			'2001:ca11:911::b0b:15:dead/128' => array( "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xad", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xad" ),
			'2001:ca11:911::b0b:15:dead/127' => array( "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xac", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xad" ),
			'2001:ca11:911::b0b:15:dead/126' => array( "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xac", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xaf" ),
			'2001:ca11:911::b0b:15:dead/125' => array( "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xa8", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xaf" ),
			'2001:ca11:911::b0b:15:dead/124' => array( "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xa0", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xaf" ),
			'2001:ca11:911::b0b:15:dead/123' => array( "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xa0", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xbf" ),
			'2001:ca11:911::b0b:15:dead/122' => array( "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\x80", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xbf" ),
			'2001:ca11:911::b0b:15:dead/121' => array( "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\x80", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xff" ),
			'2001:ca11:911::b0b:15:dead/120' => array( "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\x00", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xff" ),
			'2001:ca11:911::b0b:15:dead/119' => array( "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\x00", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xdf\xff" ),
			'2001:ca11:911::b0b:15:dead/118' => array( "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xdc\x00", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xdf\xff" ),
			'2001:ca11:911::b0b:15:dead/117' => array( "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xd8\x00", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xdf\xff" ),
			'2001:ca11:911::b0b:15:dead/116' => array( "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xd0\x00", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xdf\xff" ),
			'2001:ca11:911::b0b:15:dead/115' => array( "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xc0\x00", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xdf\xff" ),
			'2001:ca11:911::b0b:15:dead/114' => array( "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xc0\x00", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xff\xff" ),
			'2001:ca11:911::b0b:15:dead/113' => array( "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\x80\x00", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xff\xff" ),
			'2001:ca11:911::b0b:15:dead/112' => array( "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\x00\x00", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xff\xff" ),
		);

		foreach($tests as $range => $expected)
		{
			$this->assertEqual( Piwik_IP::getIpsForRange($range), $expected, $range );
		}
	}

	function test_isIpInRange()
	{
		$tests = array(
			'192.168.1.10' => array(
				'192.168.1.9' => false,
				'192.168.1.10' => true,
				'192.168.1.11' => false,

				// IPv6 addresses (including IPv4 mapped) have to be compared against IPv6 address ranges
				'::ffff:192.168.1.10' => false,
			),

			'::ffff:192.168.1.10' => array(
				'::ffff:192.168.1.9' => false,
				'::ffff:192.168.1.10' => true,
				'::ffff:c0a8:010a' => true,
				'0000:0000:0000:0000:0000:ffff:c0a8:010a' => true,
				'::ffff:192.168.1.11' => false,

				// conversely, IPv4 addresses have to be compared against IPv4 address ranges
				'192.168.1.10' => false,
			),

			'192.168.1.10/32' => array(
				'192.168.1.9' => false,
				'192.168.1.10' => true,
				'192.168.1.11' => false,
			),

			'192.168.1.10/31' => array(
				'192.168.1.9' => false,
				'192.168.1.10' => true,
				'192.168.1.11' => true,
				'192.168.1.12' => false,
			),

			'192.168.1.128/25' => array(
				'192.168.1.127' => false,
				'192.168.1.128' => true,
				'192.168.1.255' => true,
				'192.168.2.0' => false,
			),

			'192.168.1.10/24' => array(
				'192.168.0.255' => false,
				'192.168.1.0' => true,
				'192.168.1.1' => true,
				'192.168.1.2' => true,
				'192.168.1.3' => true,
				'192.168.1.4' => true,
				'192.168.1.7' => true,
				'192.168.1.8' => true,
				'192.168.1.15' => true,
				'192.168.1.16' => true,
				'192.168.1.31' => true,
				'192.168.1.32' => true,
				'192.168.1.63' => true,
				'192.168.1.64' => true,
				'192.168.1.127' => true,
				'192.168.1.128' => true,
				'192.168.1.255' => true,
				'192.168.2.0' => false,
			),

			'192.168.1.*' => array(
				'192.168.0.255' => false,
				'192.168.1.0' => true,
				'192.168.1.1' => true,
				'192.168.1.2' => true,
				'192.168.1.3' => true,
				'192.168.1.4' => true,
				'192.168.1.7' => true,
				'192.168.1.8' => true,
				'192.168.1.15' => true,
				'192.168.1.16' => true,
				'192.168.1.31' => true,
				'192.168.1.32' => true,
				'192.168.1.63' => true,
				'192.168.1.64' => true,
				'192.168.1.127' => true,
				'192.168.1.128' => true,
				'192.168.1.255' => true,
				'192.168.2.0' => false,
			),
		);

		// testing with a single range
		foreach($tests as $range => $test)
		{
			foreach($test as $ip => $expected)
			{
				// range as a string
				$this->assertEqual( Piwik_IP::isIpInRange(Piwik_IP::P2N($ip), array($range)), $expected, "$ip in $range" );

				// range as an array(low, high)
				$aRange = Piwik_IP::getIpsForRange($range);
				$aRange[0] = Piwik_IP::N2P($aRange[0]);
				$aRange[1] = Piwik_IP::N2P($aRange[1]);
				$this->assertEqual( Piwik_IP::isIpInRange(Piwik_IP::P2N($ip), array($aRange)), $expected, "$ip in $range" );
			}
		}
	}

	private function saveGlobals($names)
	{
		$saved = array();
		foreach($names as $name)
		{
			$saved[$name] = isset($_SERVER[$name]) ? $_SERVER[$name] : null;
		}
		return $saved;
	}

	private function restoreGlobals($saved)
	{
		foreach($saved as $name => $value)
		{
			if(is_null($value))
			{
				unset($_SERVER[$name]);
			}
			else
			{
				$_SERVER[$name] = $value;
			}
		}
	}

	function test_getIpFromHeader()
	{
		Piwik::createConfigObject();
		Piwik_Config::getInstance()->setTestEnvironment();
		$saved = $this->saveGlobals(array('REMOTE_ADDR', 'HTTP_X_FORWARDED_FOR'));

		$tests = array(
			'localhost inside LAN' => array('127.0.0.1', '', null, null, '127.0.0.1'),
			'outside LAN, no proxy' => array('128.252.135.4', '', null, null, '128.252.135.4'),
			'outside LAN, no (trusted) proxy' => array('128.252.135.4', '137.18.2.13, 128.252.135.4', '', null, '128.252.135.4'),
			'outside LAN, one trusted proxy' => array('192.168.1.10', '137.18.2.13, 128.252.135.4, 192.168.1.10', 'HTTP_X_FORWARDED_FOR', null, '128.252.135.4'),
			'outside LAN, proxy' => array('192.168.1.10', '128.252.135.4, 192.168.1.10', 'HTTP_X_FORWARDED_FOR', null, '128.252.135.4'),
			'outside LAN, misconfigured proxy' => array('192.168.1.10', '128.252.135.4, 192.168.1.10, 192.168.1.10', 'HTTP_X_FORWARDED_FOR', null, '128.252.135.4'),
			'outside LAN, multiple proxies' => array('192.168.1.10', '128.252.135.4, 192.168.1.20, 192.168.1.10', 'HTTP_X_FORWARDED_FOR', '192.168.1.*', '128.252.135.4'),
			'outside LAN, multiple proxies' => array('[::ffff:7f00:10]', '128.252.135.4, [::ffff:7f00:20], [::ffff:7f00:10]', 'HTTP_X_FORWARDED_FOR', '::ffff:7f00:0/120', '128.252.135.4'),
		);

		foreach($tests as $description => $test)
		{
			$_SERVER['REMOTE_ADDR'] = $test[0];
			$_SERVER['HTTP_X_FORWARDED_FOR'] = $test[1];
			Piwik_Config::getInstance()->General['proxy_client_headers'] = array($test[2]);
			Piwik_Config::getInstance()->General['proxy_ips'] = array($test[3]);
			$this->assertEqual( Piwik_IP::getIpFromHeader(), $test[4], $description );
		}

		$this->restoreGlobals($saved);
	}

	function test_getNonProxyIpFromHeader()
	{
		Piwik::createConfigObject();
		Piwik_Config::getInstance()->setTestEnvironment();
		$saved = $this->saveGlobals(array('REMOTE_ADDR', 'HTTP_X_FORWARDED_FOR'));

		$ips = array(
			'0.0.0.0',
			'72.14.204.99',
			'127.0.0.1',
			'169.254.0.1',
			'208.80.152.2',
			'224.0.0.1',
		);

		// no proxies
		foreach($ips as $ip)
		{
			$this->assertEqual( Piwik_IP::getNonProxyIpFromHeader($ip, array()), $ip, $ip );
		}

		// 1.1.1.1 is not a trusted proxy
		$_SERVER['REMOTE_ADDR'] = '1.1.1.1';
		foreach($ips as $ip)
		{
			$_SERVER['HTTP_X_FORWARDED_FOR'] = '';
			$this->assertEqual( Piwik_IP::getNonProxyIpFromHeader('1.1.1.1', array('HTTP_X_FORWARDED_FOR')), '1.1.1.1', $ip);
		}

		// 1.1.1.1 is a trusted proxy
		$_SERVER['REMOTE_ADDR'] = '1.1.1.1';
		foreach($ips as $ip)
		{
			$_SERVER['HTTP_X_FORWARDED_FOR'] = $ip;
			$this->assertEqual( Piwik_IP::getNonProxyIpFromHeader('1.1.1.1', array('HTTP_X_FORWARDED_FOR')), $ip, $ip);

			$_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4, ' . $ip;
			$this->assertEqual( Piwik_IP::getNonProxyIpFromHeader('1.1.1.1', array('HTTP_X_FORWARDED_FOR')), $ip, $ip);

			// misconfiguration
			$_SERVER['HTTP_X_FORWARDED_FOR'] = $ip . ', 1.1.1.1';
			$this->assertEqual( Piwik_IP::getNonProxyIpFromHeader('1.1.1.1', array('HTTP_X_FORWARDED_FOR')), $ip, $ip);
		}

		$this->restoreGlobals($saved);
	}

	function test_getLastIpFromList()
	{
		$tests = array(
			'' => '',
			'127.0.0.1' => '127.0.0.1',
			' 127.0.0.1 ' => '127.0.0.1',
			' 192.168.1.1, 127.0.0.1' => '127.0.0.1',
			'192.168.1.1 ,127.0.0.1 ' => '127.0.0.1',
			'192.168.1.1,' => '',
		);

		foreach($tests as $csv => $expected)
		{
			// without excluded IPs
			$this->assertEqual( Piwik_IP::getLastIpFromList($csv), $expected);

			// with excluded Ips
			$this->assertEqual( Piwik_IP::getLastIpFromList($csv . ', 10.10.10.10', array('10.10.10.10')), $expected);
		}
	}

	function test_getHostByAddr()
	{
		$hosts = array( 'localhost', strtolower(@php_uname('n')), '127.0.0.1' );
		$this->assertTrue( in_array(strtolower(Piwik_IP::getHostByAddr('127.0.0.1')), $hosts), '127.0.0.1 -> localhost' );

		if (!Piwik_Common::isWindows() || PHP_VERSION >= '5.3')
		{
			$hosts = array( 'ip6-localhost', 'localhost', strtolower(@php_uname('n')), '::1' );
			$this->assertTrue( in_array(strtolower(Piwik_IP::getHostByAddr('::1')), $hosts), '::1 -> ip6-localhost' );
		}
	}

	function test_php_compat_inet_ntop()
	{
		$adds = array(
			'127.0.0.1'                => '7f000001',
			'192.232.131.222'          => 'c0e883de',
			'255.0.0.0'                => 'ff000000',
			'255.255.255.255'          => 'ffffffff',
			'::1'                      => '00000000000000000000000000000001',
			'::101'                    => '00000000000000000000000000000101',
			'::0.1.1.1'                => '00000000000000000000000000010101',
			'2001:260:0:10::1'         => '20010260000000100000000000000001',
			'2001:0:0:260::1'          => '20010000000002600000000000000001',
			'2001::260:0:0:10:1'       => '20010000000002600000000000100001',
			'2001:5c0:1000:b::90f8'    => '200105c01000000b00000000000090f8',
			'fe80::200:4cff:fe43:172f' => 'fe8000000000000002004cfffe43172f',
			'::ffff:127.0.0.1'         => '00000000000000000000ffff7f000001',
			'::127.0.0.1'              => '0000000000000000000000007f000001',
			'::fff0:7f00:1'            => '00000000000000000000fff07f000001',
		);

		foreach ($adds as $k => $v) {
			$this->assertEqual( php_compat_inet_ntop(pack('H*', $v)), $k, $k );
			if(!Piwik_Common::isWindows() && !Piwik_Common::isMacOS())
			{
				$this->assertEqual( @inet_ntop(pack('H*', $v)), $k, $k );
			}
		}
	}

	function test_php_compat_inet_pton()
	{
		$adds = array(
			'127.0.0.1'                => '7f000001',
			'192.232.131.222'          => 'c0e883de',
			'255.0.0.0'                => 'ff000000',
			'255.255.255.255'          => 'ffffffff',
			'::'                       => '00000000000000000000000000000000',
			'::0'                      => '00000000000000000000000000000000',
			'0::'                      => '00000000000000000000000000000000',
			'0::0'                     => '00000000000000000000000000000000',
			'::1'                      => '00000000000000000000000000000001',
			'2001:260:0:10::1'         => '20010260000000100000000000000001',
			'2001:5c0:1000:b::90f8'    => '200105c01000000b00000000000090f8',
			'fe80::200:4cff:fe43:172f' => 'fe8000000000000002004cfffe43172f',
			'::ffff:127.0.0.1'         => '00000000000000000000ffff7f000001',
			'::127.0.0.1'              => '0000000000000000000000007f000001',

			// relaxed rules
			'00000::'                  => '00000000000000000000000000000000',
			'1:2:3:4:5:ffff:127.0.0.1' => '00010002000300040005ffff7f000001',

			// invalid input
			null => false,
			false => false,
			true => false,
			'' => false,
			'0' => false,
			'07.07.07.07' => false,
			'1.' => false,
			'.1' => false,
			'1.1' => false,
			'.1.1.' => false,
			'1.1.1.' => false,
			'.1.1.1' => false,
			'1.2.3.4.' => false,
			'.1.2.3.4' => false,
			'1.2.3.256' => false,
			'a.b.c.d' => false,
			'::1::' => false,
			'1:2:3:4:::5:6' => false,
			'1:2:3:4:5:6:' => false,
			':1:2:3:4:5:6' => false,
			'1:2:3:4:5:6:7:' => false,
			':1:2:3:4:5:6:7' => false,
			'::11111:0' => false,
			'::g' => false,
			'::ffff:127.00.0.1' => false,
			'::ffff:127.0.0.01' => false,
			'::ffff:256.0.0.1' => false,
			'::ffff:1.256.0.1' => false,
			'::ffff:65536.0.0.1' => false,
			'::ffff:256.65536.0.1' => false,
			'::ffff:65536.65536.0.1' => false,
			'::ffff:7f01:0.1' => false,
			'ffff:127.0.0.1:ffff::' => false,
		);

		foreach ($adds as $k => $v) {
			$this->assertEqual( bin2hex(php_compat_inet_pton($k)), $v, $k );
			if(!Piwik_Common::isWindows() && !Piwik_Common::isMacOS())
			{
				$this->assertEqual( bin2hex(@inet_pton($k)), $v, $k );
			}
		}
	}
}
