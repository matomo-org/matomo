<?php
use Piwik\Common;
use Piwik\Config;
use Piwik\IP;
use Piwik\SettingsServer;

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class IPTest extends PHPUnit_Framework_TestCase
{
    /**
     * Dataprovider for testSanitizeIp
     */
    public function getIPData()
    {
        return array( // input, output
            // single IPv4 address
            array('127.0.0.1', '127.0.0.1'),

            // single IPv6 address (ambiguous)
            array('::1', '::1'),
            array('::ffff:127.0.0.1', '::ffff:127.0.0.1'),
            array('2001:5c0:1000:b::90f8', '2001:5c0:1000:b::90f8'),

            // single IPv6 address
            array('[::1]', '::1'),
            array('[2001:5c0:1000:b::90f8]', '2001:5c0:1000:b::90f8'),
            array('[::ffff:127.0.0.1]', '::ffff:127.0.0.1'),

            // single IPv4 address (CIDR notation)
            array('192.168.1.1/32', '192.168.1.1'),

            // single IPv6 address (CIDR notation)
            array('::1/128', '::1'),
            array('::ffff:127.0.0.1/128', '::ffff:127.0.0.1'),
            array('2001:5c0:1000:b::90f8/128', '2001:5c0:1000:b::90f8'),

            // IPv4 address with port
            array('192.168.1.2:80', '192.168.1.2'),

            // IPv6 address with port
            array('[::1]:80', '::1'),
            array('[::ffff:127.0.0.1]:80', '::ffff:127.0.0.1'),
            array('[2001:5c0:1000:b::90f8]:80', '2001:5c0:1000:b::90f8'),

            // hostnames with port?
            array('localhost', 'localhost'),
            array('localhost:80', 'localhost'),
            array('www.example.com', 'www.example.com'),
            array('example.com:80', 'example.com'),
        );
    }

    /**
     * @dataProvider getIPData
     * @group Core
     */
    public function testSanitizeIp($ip, $expected)
    {
        $this->assertEquals($expected, IP::sanitizeIp($ip));
    }

    /**
     * Dataprovider for testSanitizeIpRange
     */
    public function getIPRangeData()
    {
        return array(
            array('', false),
            array(' 127.0.0.1 ', '127.0.0.1/32'),
            array('192.168.1.0', '192.168.1.0/32'),
            array('192.168.1.1/24', '192.168.1.1/24'),
            array('192.168.1.2/16', '192.168.1.2/16'),
            array('192.168.1.3/8', '192.168.1.3/8'),
            array('192.168.2.*', '192.168.2.0/24'),
            array('192.169.*.*', '192.169.0.0/16'),
            array('193.*.*.*', '193.0.0.0/8'),
            array('*.*.*.*', '0.0.0.0/0'),
            array('*.*.*.1', false),
            array('*.*.1.1', false),
            array('*.1.1.1', false),
            array('1.*.1.1', false),
            array('1.1.*.1', false),
            array('1.*.*.1', false),
            array('::1', '::1/128'),
            array('::ffff:127.0.0.1', '::ffff:127.0.0.1/128'),
            array('2001:5c0:1000:b::90f8', '2001:5c0:1000:b::90f8/128'),
            array('::1/64', '::1/64'),
            array('::ffff:127.0.0.1/64', '::ffff:127.0.0.1/64'),
            array('2001:5c0:1000:b::90f8/64', '2001:5c0:1000:b::90f8/64'),
        );
    }

    /**
     * @dataProvider getIPRangeData
     * @group Core
     */
    public function testSanitizeIpRange($ip, $expected)
    {
        $this->assertEquals($expected, IP::sanitizeIpRange($ip));
    }

    /**
     * Dataprovider for testP2N
     */
    public function getP2NTestData()
    {
        return array(
            // IPv4
            array('0.0.0.0', "\x00\x00\x00\x00"),
            array('127.0.0.1', "\x7F\x00\x00\x01"),
            array('192.168.1.12', "\xc0\xa8\x01\x0c"),
            array('255.255.255.255', "\xff\xff\xff\xff"),

            // IPv6
            array('::', "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00"),
            array('::1', "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01"),
            array('::fffe:7f00:1', "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xfe\x7f\x00\x00\x01"),
            array('::ffff:127.0.0.1', "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x7f\x00\x00\x01"),
            array('2001:5c0:1000:b::90f8', "\x20\x01\x05\xc0\x10\x00\x00\x0b\x00\x00\x00\x00\x00\x00\x90\xf8"),
        );
    }

    /**
     * @dataProvider getP2NTestData
     * @group Core
     */
    public function testP2N($P, $N)
    {
        $this->assertEquals($N, IP::P2N($P));
    }

    /**
     * Dataprovider for testP2NInvalidInput
     */
    public function getP2NInvalidInputData()
    {
        return array(
            // not a series of dotted numbers
            array(null),
            array(''),
            array('alpha'),
            array('...'),

            // missing an octet
            array('.0.0.0'),
            array('0..0.0'),
            array('0.0..0'),
            array('0.0.0.'),

            // octets must be 0-255
            array('-1.0.0.0'),
            array('1.1.1.256'),

            // leading zeros not supported (i.e., can be ambiguous, e.g., octal)
            array('07.07.07.07'),
        );
    }

    /**
     * @group Core
     * 
     * @dataProvider getP2NInvalidInputData
     */
    public function testP2NInvalidInput($P)
    {
        $this->assertEquals("\x00\x00\x00\x00", IP::P2N($P));
    }

    /**
     * @group Core
     */
    public function getN2PTestData()
    {
        // a valid network address is either 4 or 16 bytes; those lines are intentionally left blank ;)
        return array(
            array(null),
            array(''),
            array("\x01"),
            array("\x01\x00"),
            array("\x01\x00\x00"),

            array("\x01\x00\x00\x00\x00"),
            array("\x01\x00\x00\x00\x00\x00"),
            array("\x01\x00\x00\x00\x00\x00\x00"),
            array("\x01\x00\x00\x00\x00\x00\x00\x00"),
            array("\x01\x00\x00\x00\x00\x00\x00\x00\x00"),
            array("\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00"),
            array("\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00"),
            array("\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00"),
            array("\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00"),
            array("\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00"),
            array("\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00"),

            array("\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00"),
        );
    }

    /**
     * @dataProvider getP2NTestData
     * @group Core
     */
    public function testN2P($P, $N)
    {
        $this->assertEquals($P, IP::N2P($N), "$P vs" . IP::N2P($N));
    }

    /**
     * @dataProvider getN2PTestData
     * @group Core
     */
    public function testN2PinvalidInput($N)
    {
        $this->assertEquals("0.0.0.0", IP::N2P($N), bin2hex($N));
    }

    /**
     * @dataProvider getP2NTestData
     * @group Core
     */
    public function testPrettyPrint($P, $N)
    {
        $this->assertEquals($P, IP::prettyPrint($N), "$P vs" . IP::N2P($N));
    }

    /**
     * @dataProvider getN2PTestData
     * @group Core
     */
    public function testPrettyPrintInvalidInput($N)
    {
        $this->assertEquals("0.0.0.0", IP::prettyPrint($N), bin2hex($N));
    }

    /**
     * Dataprovider for IP4 test data
     */
    public function getIPv4Data()
    {
        // a valid network address is either 4 or 16 bytes; those lines are intentionally left blank ;)
        return array(
            // invalid
            array(null, false),
            array("", false),

            // IPv4
            array("\x00\x00\x00\x00", true),
            array("\x7f\x00\x00\x01", true),

            // IPv4-compatible (this transitional format is deprecated in RFC 4291, section 2.5.5.1)
            array("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xc0\xa8\x01\x01", true),

            // IPv4-mapped (RFC 4291, 2.5.5.2)
            array("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\xc0\xa8\x01\x02", true),

            // other IPv6 address
            array("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\x00\xc0\xa8\x01\x03", false),
            array("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01\xc0\xa8\x01\x04", false),
            array("\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xc0\xa8\x01\x05", false),

            /*
             * We assume all stored IP addresses (pre-Piwik 1.4) were converted from UNSIGNED INT to VARBINARY.
             * The following is just for informational purposes.
             */

            // 192.168.1.0
            array('-1062731520', false),
            array('3232235776', false),

            // 10.10.10.10
            array('168430090', false),

            // 0.0.39.15 - this is the ambiguous case (i.e., 4 char string)
            array('9999', true),
            array("\x39\x39\x39\x39", true),

            // 0.0.3.231
            array('999', false),
            array("\x39\x39\x39", false),
        );

    }

    /**
     * @dataProvider getIPv4Data
     * @group Core
     */
    public function testIsIPv4($ip, $bool)
    {
        $this->assertEquals($bool, IP::isIPv4($ip), bin2hex($ip));
    }

    /**
     * Dataprovider for long2ip test
     */
    public function getLong2IPTestData()
    {
        // a valid network address is either 4 or 16 bytes; those lines are intentionally left blank ;)
        return array(
            // invalid
            array(null, '0.0.0.0'),
            array("", '0.0.0.0'),

            // IPv4
            array("\x7f\x00\x00\x01", '127.0.0.1'),

            // IPv4-compatible (this transitional format is deprecated in RFC 4291, section 2.5.5.1)
            array("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xc0\xa8\x01\x01", '192.168.1.1'),

            // IPv4-mapped (RFC 4291, 2.5.5.2)
            array("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\xc0\xa8\x01\x02", '192.168.1.2'),

            // other IPv6 address
            array("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\x00\xc0\xa8\x01\x03", '0.0.0.0'),
            array("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01\xc0\xa8\x01\x04", '0.0.0.0'),
            array("\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xc0\xa8\x01\x05", '0.0.0.0'),

            /*
             * We assume all stored IP addresses (pre-Piwik 1.4) were converted from UNSIGNED INT to VARBINARY.
             * The following is just for informational purposes.
             */

            // 192.168.1.0
            array('-1062731520', '0.0.0.0'),
            array('3232235776', '0.0.0.0'),

            // 10.10.10.10
            array('168430090', '0.0.0.0'),

            // 0.0.39.15 - this is the ambiguous case (i.e., 4 char string)
            array('9999', '57.57.57.57'),
            array("\x39\x39\x39\x39", '57.57.57.57'),

            // 0.0.3.231
            array('999', '0.0.0.0'),
            array("\x39\x39\x39", '0.0.0.0'),
        );
    }

    /**
     * @dataProvider getLong2IPTestData
     * @group Core
     */
    public function testLong2ip($N, $P)
    {
        $this->assertEquals($P, IP::long2ip($N), bin2hex($N));
        // this is our compatibility function
        $this->assertEquals($P, Common::long2ip($N), bin2hex($N));
    }

    /**
     * Dataprovider for ip range test
     */
    public function getIPsForRangeTest()
    {
        return array(

            // invalid ranges
            array(null, false),
            array('', false),
            array('0', false),

            // single IPv4
            array('127.0.0.1', array("\x7f\x00\x00\x01", "\x7f\x00\x00\x01")),

            // IPv4 with wildcards
            array('192.168.1.*', array("\xc0\xa8\x01\x00", "\xc0\xa8\x01\xff")),
            array('192.168.*.*', array("\xc0\xa8\x00\x00", "\xc0\xa8\xff\xff")),
            array('192.*.*.*', array("\xc0\x00\x00\x00", "\xc0\xff\xff\xff")),
            array('*.*.*.*', array("\x00\x00\x00\x00", "\xff\xff\xff\xff")),

            // single IPv4 in expected CIDR notation
            array('192.168.1.1/24', array("\xc0\xa8\x01\x00", "\xc0\xa8\x01\xff")),

            array('192.168.1.127/32', array("\xc0\xa8\x01\x7f", "\xc0\xa8\x01\x7f")),
            array('192.168.1.127/31', array("\xc0\xa8\x01\x7e", "\xc0\xa8\x01\x7f")),
            array('192.168.1.127/30', array("\xc0\xa8\x01\x7c", "\xc0\xa8\x01\x7f")),
            array('192.168.1.127/29', array("\xc0\xa8\x01\x78", "\xc0\xa8\x01\x7f")),
            array('192.168.1.127/28', array("\xc0\xa8\x01\x70", "\xc0\xa8\x01\x7f")),
            array('192.168.1.127/27', array("\xc0\xa8\x01\x60", "\xc0\xa8\x01\x7f")),
            array('192.168.1.127/26', array("\xc0\xa8\x01\x40", "\xc0\xa8\x01\x7f")),
            array('192.168.1.127/25', array("\xc0\xa8\x01\x00", "\xc0\xa8\x01\x7f")),

            array('192.168.1.255/32', array("\xc0\xa8\x01\xff", "\xc0\xa8\x01\xff")),
            array('192.168.1.255/31', array("\xc0\xa8\x01\xfe", "\xc0\xa8\x01\xff")),
            array('192.168.1.255/30', array("\xc0\xa8\x01\xfc", "\xc0\xa8\x01\xff")),
            array('192.168.1.255/29', array("\xc0\xa8\x01\xf8", "\xc0\xa8\x01\xff")),
            array('192.168.1.255/28', array("\xc0\xa8\x01\xf0", "\xc0\xa8\x01\xff")),
            array('192.168.1.255/27', array("\xc0\xa8\x01\xe0", "\xc0\xa8\x01\xff")),
            array('192.168.1.255/26', array("\xc0\xa8\x01\xc0", "\xc0\xa8\x01\xff")),
            array('192.168.1.255/25', array("\xc0\xa8\x01\x80", "\xc0\xa8\x01\xff")),

            array('192.168.255.255/24', array("\xc0\xa8\xff\x00", "\xc0\xa8\xff\xff")),
            array('192.168.255.255/23', array("\xc0\xa8\xfe\x00", "\xc0\xa8\xff\xff")),
            array('192.168.255.255/22', array("\xc0\xa8\xfc\x00", "\xc0\xa8\xff\xff")),
            array('192.168.255.255/21', array("\xc0\xa8\xf8\x00", "\xc0\xa8\xff\xff")),
            array('192.168.255.255/20', array("\xc0\xa8\xf0\x00", "\xc0\xa8\xff\xff")),
            array('192.168.255.255/19', array("\xc0\xa8\xe0\x00", "\xc0\xa8\xff\xff")),
            array('192.168.255.255/18', array("\xc0\xa8\xc0\x00", "\xc0\xa8\xff\xff")),
            array('192.168.255.255/17', array("\xc0\xa8\x80\x00", "\xc0\xa8\xff\xff")),

            // single IPv6
            array('::1', array("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01", "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01")),

            // single IPv6 in expected CIDR notation
            array('::1/128', array("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01", "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01")),
            array('::1/127', array("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00", "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01")),
            array('::fffe:7f00:1/120', array("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xfe\x7f\x00\x00\x00", "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xfe\x7f\x00\x00\xff")),
            array('::ffff:127.0.0.1/120', array("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x7f\x00\x00\x00", "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x7f\x00\x00\xff")),

            array('2001:ca11:911::b0b:15:dead/128', array("\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xad", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xad")),
            array('2001:ca11:911::b0b:15:dead/127', array("\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xac", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xad")),
            array('2001:ca11:911::b0b:15:dead/126', array("\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xac", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xaf")),
            array('2001:ca11:911::b0b:15:dead/125', array("\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xa8", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xaf")),
            array('2001:ca11:911::b0b:15:dead/124', array("\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xa0", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xaf")),
            array('2001:ca11:911::b0b:15:dead/123', array("\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xa0", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xbf")),
            array('2001:ca11:911::b0b:15:dead/122', array("\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\x80", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xbf")),
            array('2001:ca11:911::b0b:15:dead/121', array("\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\x80", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xff")),
            array('2001:ca11:911::b0b:15:dead/120', array("\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\x00", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\xff")),
            array('2001:ca11:911::b0b:15:dead/119', array("\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xde\x00", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xdf\xff")),
            array('2001:ca11:911::b0b:15:dead/118', array("\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xdc\x00", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xdf\xff")),
            array('2001:ca11:911::b0b:15:dead/117', array("\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xd8\x00", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xdf\xff")),
            array('2001:ca11:911::b0b:15:dead/116', array("\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xd0\x00", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xdf\xff")),
            array('2001:ca11:911::b0b:15:dead/115', array("\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xc0\x00", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xdf\xff")),
            array('2001:ca11:911::b0b:15:dead/114', array("\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xc0\x00", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xff\xff")),
            array('2001:ca11:911::b0b:15:dead/113', array("\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\x80\x00", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xff\xff")),
            array('2001:ca11:911::b0b:15:dead/112', array("\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\x00\x00", "\x20\x01\xca\x11\x09\x11\x00\x00\x00\x00\x0b\x0b\x00\x15\xff\xff")),
        );
    }

    /**
     * @dataProvider getIPsForRangeTest
     * @group Core
     */
    public function testGetIpsForRange($range, $expected)
    {
        $this->assertEquals($expected, IP::getIpsForRange($range));
    }

    /**
     * Dataprovider for testIsIpInRange
     */
    public function getIpsInRangeData()
    {
        return array(
            array('192.168.1.10', array(
                '192.168.1.9'         => false,
                '192.168.1.10'        => true,
                '192.168.1.11'        => false,

                // IPv6 addresses (including IPv4 mapped) have to be compared against IPv6 address ranges
                '::ffff:192.168.1.10' => false,
            )),

            array('::ffff:192.168.1.10', array(
                '::ffff:192.168.1.9'                      => false,
                '::ffff:192.168.1.10'                     => true,
                '::ffff:c0a8:010a'                        => true,
                '0000:0000:0000:0000:0000:ffff:c0a8:010a' => true,
                '::ffff:192.168.1.11'                     => false,

                // conversely, IPv4 addresses have to be compared against IPv4 address ranges
                '192.168.1.10'                            => false,
            )),

            array('192.168.1.10/32', array(
                '192.168.1.9'  => false,
                '192.168.1.10' => true,
                '192.168.1.11' => false,
            )),

            array('192.168.1.10/31', array(
                '192.168.1.9'  => false,
                '192.168.1.10' => true,
                '192.168.1.11' => true,
                '192.168.1.12' => false,
            )),

            array('192.168.1.128/25', array(
                '192.168.1.127' => false,
                '192.168.1.128' => true,
                '192.168.1.255' => true,
                '192.168.2.0'   => false,
            )),

            array('192.168.1.10/24', array(
                '192.168.0.255' => false,
                '192.168.1.0'   => true,
                '192.168.1.1'   => true,
                '192.168.1.2'   => true,
                '192.168.1.3'   => true,
                '192.168.1.4'   => true,
                '192.168.1.7'   => true,
                '192.168.1.8'   => true,
                '192.168.1.15'  => true,
                '192.168.1.16'  => true,
                '192.168.1.31'  => true,
                '192.168.1.32'  => true,
                '192.168.1.63'  => true,
                '192.168.1.64'  => true,
                '192.168.1.127' => true,
                '192.168.1.128' => true,
                '192.168.1.255' => true,
                '192.168.2.0'   => false,
            )),

            array('192.168.1.*', array(
                '192.168.0.255' => false,
                '192.168.1.0'   => true,
                '192.168.1.1'   => true,
                '192.168.1.2'   => true,
                '192.168.1.3'   => true,
                '192.168.1.4'   => true,
                '192.168.1.7'   => true,
                '192.168.1.8'   => true,
                '192.168.1.15'  => true,
                '192.168.1.16'  => true,
                '192.168.1.31'  => true,
                '192.168.1.32'  => true,
                '192.168.1.63'  => true,
                '192.168.1.64'  => true,
                '192.168.1.127' => true,
                '192.168.1.128' => true,
                '192.168.1.255' => true,
                '192.168.2.0'   => false,
            )),
        );
    }

    /**
     * @group Core
     * 
     * @dataProvider getIpsInRangeData
     */
    public function testIsIpInRange($range, $test)
    {
        foreach ($test as $ip => $expected) {
            // range as a string
            $this->assertEquals($expected, IP::isIpInRange(IP::P2N($ip), array($range)), "$ip in $range");

            // range as an array(low, high)
            $aRange = IP::getIpsForRange($range);
            $aRange[0] = IP::N2P($aRange[0]);
            $aRange[1] = IP::N2P($aRange[1]);
            $this->assertEquals($expected, IP::isIpInRange(IP::P2N($ip), array($aRange)), "$ip in $range");
        }
    }

    /**
     * Dataprovider for ip from header tests
     */
    public function getIpFromHeaderTestData()
    {
        return array(
            array('localhost inside LAN', array('127.0.0.1', '', null, null, '127.0.0.1')),
            array('outside LAN, no proxy', array('128.252.135.4', '', null, null, '128.252.135.4')),
            array('outside LAN, no (trusted) proxy', array('128.252.135.4', '137.18.2.13, 128.252.135.4', '', null, '128.252.135.4')),
            array('outside LAN, one trusted proxy', array('192.168.1.10', '137.18.2.13, 128.252.135.4, 192.168.1.10', 'HTTP_X_FORWARDED_FOR', null, '128.252.135.4')),
            array('outside LAN, proxy', array('192.168.1.10', '128.252.135.4, 192.168.1.10', 'HTTP_X_FORWARDED_FOR', null, '128.252.135.4')),
            array('outside LAN, misconfigured proxy', array('192.168.1.10', '128.252.135.4, 192.168.1.10, 192.168.1.10', 'HTTP_X_FORWARDED_FOR', null, '128.252.135.4')),
            array('outside LAN, multiple proxies', array('192.168.1.10', '128.252.135.4, 192.168.1.20, 192.168.1.10', 'HTTP_X_FORWARDED_FOR', '192.168.1.*', '128.252.135.4')),
            array('outside LAN, multiple proxies', array('[::ffff:7f00:10]', '128.252.135.4, [::ffff:7f00:20], [::ffff:7f00:10]', 'HTTP_X_FORWARDED_FOR', '::ffff:7f00:0/120', '128.252.135.4')),
        );
    }

    /**
     * @dataProvider getIpFromHeaderTestData
     * @group Core
     */
    public function testGetIpFromHeader($description, $test)
    {
        Config::getInstance()->setTestEnvironment();

        $_SERVER['REMOTE_ADDR'] = $test[0];
        $_SERVER['HTTP_X_FORWARDED_FOR'] = $test[1];
        Config::getInstance()->General['proxy_client_headers'] = array($test[2]);
        Config::getInstance()->General['proxy_ips'] = array($test[3]);
        $this->assertEquals($test[4], IP::getIpFromHeader(), $description);
    }

    /**
     * Dataprovider
     * @return array
     */
    public function getIpTestData()
    {
        return array(
            array('0.0.0.0'),
            array('72.14.204.99'),
            array('127.0.0.1'),
            array('169.254.0.1'),
            array('208.80.152.2'),
            array('224.0.0.1'),
        );
    }


    /**
     * @group Core
     * 
     * @dataProvider getIpTestData
     */
    public function testGetNonProxyIpFromHeader($ip)
    {
        $this->assertEquals($ip, IP::getNonProxyIpFromHeader($ip, array()));
    }

    /**
     * @group Core
     * 
     * @dataProvider getIpTestData
     */
    public function testGetNonProxyIpFromHeader2($ip)
    {
        // 1.1.1.1 is not a trusted proxy
        $_SERVER['REMOTE_ADDR'] = '1.1.1.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '';
        $this->assertEquals('1.1.1.1', IP::getNonProxyIpFromHeader('1.1.1.1', array('HTTP_X_FORWARDED_FOR')));
    }

    /**
     * @group Core
     * 
     * @dataProvider getIpTestData
     */
    public function testGetNonProxyIpFromHeader3($ip)
    {
        // 1.1.1.1 is a trusted proxy
        $_SERVER['REMOTE_ADDR'] = '1.1.1.1';

        $_SERVER['HTTP_X_FORWARDED_FOR'] = $ip;
        $this->assertEquals($ip, IP::getNonProxyIpFromHeader('1.1.1.1', array('HTTP_X_FORWARDED_FOR')));

        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4, ' . $ip;
        $this->assertEquals($ip, IP::getNonProxyIpFromHeader('1.1.1.1', array('HTTP_X_FORWARDED_FOR')));

        // misconfiguration
        $_SERVER['HTTP_X_FORWARDED_FOR'] = $ip . ', 1.1.1.1';
        $this->assertEquals($ip, IP::getNonProxyIpFromHeader('1.1.1.1', array('HTTP_X_FORWARDED_FOR')));
    }

    /**
     * Dataprovider for testGetLastIpFromList
     */
    public function getLastIpFromListTestData()
    {
        return array(
            array('', ''),
            array('127.0.0.1', '127.0.0.1'),
            array(' 127.0.0.1 ', '127.0.0.1'),
            array(' 192.168.1.1, 127.0.0.1', '127.0.0.1'),
            array('192.168.1.1 ,127.0.0.1 ', '127.0.0.1'),
            array('192.168.1.1,', ''),
        );
    }

    /**
     * @group Core
     * 
     * @dataProvider getLastIpFromListTestData
     */
    public function testGetLastIpFromList($csv, $expected)
    {
        // without excluded IPs
        $this->assertEquals($expected, IP::getLastIpFromList($csv));

        // with excluded Ips
        $this->assertEquals($expected, IP::getLastIpFromList($csv . ', 10.10.10.10', array('10.10.10.10')));
    }

    /**
     * @group Core
     */
    public function testGetHostByAddr()
    {
        $hosts = array('localhost', 'localhost.localdomain', strtolower(@php_uname('n')), '127.0.0.1');
        $host = IP::getHostByAddr('127.0.0.1');
        $this->assertTrue(in_array(strtolower($host), $hosts), $host . ' -> localhost');

        if (!SettingsServer::isWindows() || PHP_VERSION >= '5.3') {
            $hosts = array('ip6-localhost', 'localhost', 'localhost.localdomain', strtolower(@php_uname('n')), '::1');
            $this->assertTrue(in_array(strtolower(IP::getHostByAddr('::1')), $hosts), '::1 -> ip6-localhost');
        }
    }
}
