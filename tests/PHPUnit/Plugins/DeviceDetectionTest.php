<?php

/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
require_once 'DevicesDetection/DevicesDetection.php';

class UserSettingsTest extends PHPUnit_Framework_TestCase
{

    public function getUserAgents()
    {
        return array(
            // array('User Agent String', array(
            //     array( browser_id, name, short_name, version, major_number, minor_number, family ),
            //     array( os_id, name, short_name ))),

            array('Mozilla/4.0+(compatible;+MSIE+8.0;+Windows+NT+6.1;+WOW64;+Trident/4.0;+GTB7.4;+SLCC2;+.NET+CLR+2.0.50727;+.NET+CLR+3.5.30729;+.NET+CLR+3.0.30729;+Media+Center+PC+6.0;+.NET4.0C;+.NET4.0E;+MS-RTC+LM+8;+InfoPath.2)', array(
                    array('IE', 'Internet Explorer', 'IE', '8.0', '8', '0', 'ie'),
                    array('WI7', 'Windows 7', 'Win 7'))),
        );
    }

    /**
     * Test getBrowser()
     *
     * @dataProvider getUserAgents
     * @group Plugins
     * @group UserSettings
     */
    public function testGetBrowser($userAgent, $expected)
    {

        $UAParser = new UserAgentParserEnhanced($userAgent);
        var_dump($UAParser);
//        $res = UserAgentParser::getBrowser($userAgent);
//        $family = false;
//
//        if ($res === false)
//            $this->assertFalse($expected[0]);
//        else {
//            $family = Piwik_getBrowserFamily($res['id']);
//            $this->assertEquals($expected[0][0], $res['id']);
//            $this->assertEquals($expected[0][1], $res['name']);
//            $this->assertEquals($expected[0][2], $res['short_name']);
//            $this->assertEquals($expected[0][3], $res['version']);
//            $this->assertEquals($expected[0][4], $res['major_number']);
//            $this->assertEquals($expected[0][5], $res['minor_number']);
//            $this->assertEquals($expected[0][6], $family);
//        }
    }

    /**
     * Test getOperatingSystem()
     *
     * @dataProvider getUserAgents
     * @group Plugins
     * @group UserSettings
     */
    public function testGetOperatingSystem($userAgent, $expected)
    {
//        $res = UserAgentParser::getOperatingSystem($userAgent);
//
//        $this->assertEquals($expected[1][0], $res['id']);
//        $this->assertEquals($expected[1][1], $res['name']);
//        $this->assertEquals($expected[1][2], $res['short_name']);
    }

}

?>
