<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
require_once 'AnonymizeIP/AnonymizeIP.php';

class AnonymizeIPTest extends PHPUnit_Framework_TestCase
{
    // IPv4 addresses and expected results
    public function getipv4Addresses()
    {
        return array(
            // ip, array( expected0, expected1, expected2, expected3, expected4 ),
            array('0.0.0.0', array("\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00")),
            array('0.0.0.1', array("\x00\x00\x00\x01", "\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00")),
            array('0.0.0.255', array("\x00\x00\x00\xff", "\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00")),
            array('0.0.1.0', array("\x00\x00\x01\x00", "\x00\x00\x01\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00")),
            array('0.0.1.1', array("\x00\x00\x01\x01", "\x00\x00\x01\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00")),
            array('0.0.255.255', array("\x00\x00\xff\xff", "\x00\x00\xff\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00")),
            array('0.1.0.0', array("\x00\x01\x00\x00", "\x00\x01\x00\x00", "\x00\x01\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00")),
            array('0.1.1.1', array("\x00\x01\x01\x01", "\x00\x01\x01\x00", "\x00\x01\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00")),
            array('0.255.255.255', array("\x00\xff\xff\xff", "\x00\xff\xff\x00", "\x00\xff\x00\x00", "\x00\x00\x00\x00", "\x00\x00\x00\x00")),
            array('1.0.0.0', array("\x01\x00\x00\x00", "\x01\x00\x00\x00", "\x01\x00\x00\x00", "\x01\x00\x00\x00", "\x00\x00\x00\x00")),
            array('127.255.255.255', array("\x7f\xff\xff\xff", "\x7f\xff\xff\x00", "\x7f\xff\x00\x00", "\x7f\x00\x00\x00", "\x00\x00\x00\x00")),
            array('128.0.0.0', array("\x80\x00\x00\x00", "\x80\x00\x00\x00", "\x80\x00\x00\x00", "\x80\x00\x00\x00", "\x00\x00\x00\x00")),
            array('255.255.255.255', array("\xff\xff\xff\xff", "\xff\xff\xff\x00", "\xff\xff\x00\x00", "\xff\x00\x00\x00", "\x00\x00\x00\x00")),
        );
    }

    /**
     * @dataProvider getipv4Addresses
     * @group Plugins
     * @group AnonymizeIP
     */
    public function testApplyIPMask($ip, $expected)
    {
        // each IP is tested with 0 to 4 octets masked
        for ($maskLength = 0; $maskLength <= 4; $maskLength++) {
            $res = Piwik_AnonymizeIP::applyIPMask(Piwik_IP::P2N($ip), $maskLength);
            $this->assertEquals($expected[$maskLength], $res, "Got " . bin2hex($res) . ", Expected " . bin2hex($expected[$maskLength]));
        }

        // edge case (bounds check)
        $this->assertEquals("\x00\x00\x00\x00", Piwik_AnonymizeIP::applyIPMask(Piwik_IP::P2N($ip), 5));

        // mask IPv4 mapped addresses
        for ($maskLength = 0; $maskLength <= 4; $maskLength++) {
            $res = Piwik_AnonymizeIP::applyIPMask(Piwik_IP::P2N('::ffff:' . $ip), $maskLength);
            $this->assertEquals($res, "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff" . $expected[$maskLength], "Got " . bin2hex($res) . ", Expected " . bin2hex($expected[$maskLength]));
        }
        $this->assertEquals("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\x00\x00\x00\x00\x00", Piwik_AnonymizeIP::applyIPMask(Piwik_IP::P2N('::ffff:' . $ip), 5));

        // edge case (bounds check)
        $this->assertEquals("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00", Piwik_AnonymizeIP::applyIPMask(Piwik_IP::P2N('2001::ffff:' . $ip), 17));
    }
}
