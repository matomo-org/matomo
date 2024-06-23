<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Unit;

use Matomo\Network\IP;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;

require_once PIWIK_INCLUDE_PATH . '/plugins/PrivacyManager/IPAnonymizer.php';

class AnonymizeIPTest extends \PHPUnit\Framework\TestCase
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

    public function getipv6Addresses()
    {
        return array(
            array('2001:db8:0:8d3:0:8a2e:70:7344', array(
                "\x20\x01\x0d\xb8\x00\x00\x08\xd3\x00\x00\x8a\x2e\x00\x70\x73\x44",
                "\x20\x01\x0d\xb8\x00\x00\x08\xd3\x00\x00\x00\x00\x00\x00\x00\x00", // mask 64 bits
                "\x20\x01\x0d\xb8\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00", // mask 80 bits
                "\x20\x01\x0d\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00", // mask 104 bits
                "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00" // mask all bits
            )),
            array('2001:6f8:900:724::2', array(
                "\x20\x01\x06\xf8\x09\x00\x07\x24\x00\x00\x00\x00\x00\x00\x00\x02",
                "\x20\x01\x06\xf8\x09\x00\x07\x24\x00\x00\x00\x00\x00\x00\x00\x00",
                "\x20\x01\x06\xf8\x09\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
                "\x20\x01\x06\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
                "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00"
            ))
        );
    }

    /**
     * @dataProvider getipv4Addresses
     * @group Plugins
     */
    public function testApplyIPMask($ipString, $expected)
    {
        $ip = IP::fromStringIP($ipString);

        // each IP is tested with 0 to 4 octets masked
        for ($maskLength = 0; $maskLength <= 4; $maskLength++) {
            $res = IPAnonymizer::applyIPMask($ip, $maskLength);
            $this->assertEquals($expected[$maskLength], $res->toBinary(), "Got " . $res . ", Expected " . bin2hex($expected[$maskLength]));
        }

        // edge case (bounds check)
        $this->assertEquals("\x00\x00\x00\x00", IPAnonymizer::applyIPMask($ip, 5)->toBinary());

        // mask IPv4 mapped addresses
        $mappedIp = IP::fromStringIP('::ffff:' . $ipString);
        for ($maskLength = 0; $maskLength <= 4; $maskLength++) {
            $res = IPAnonymizer::applyIPMask($mappedIp, $maskLength);
            $this->assertEquals("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff" . $expected[$maskLength], $res->toBinary(), "Got " . $res . ", Expected " . bin2hex($expected[$maskLength]));
        }
        $this->assertEquals("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\x00\x00\x00\x00\x00", IPAnonymizer::applyIPMask($mappedIp, 5)->toBinary());
    }

    /**
     * @dataProvider getipv6Addresses
     * @group Plugins
     */
    public function testApplyIPMask6($ip, $expected)
    {
        // each IP is tested with 0 to 4 octets masked
        for ($maskLength = 0; $maskLength <= 4; $maskLength++) {
            $res = IPAnonymizer::applyIPMask(IP::fromStringIP($ip), $maskLength);
            $this->assertEquals($expected[$maskLength], $res->toBinary(), "Got " . $res . ", Expected " . bin2hex($expected[$maskLength]) . ", Mask Level " . $maskLength);
        }
    }
}
