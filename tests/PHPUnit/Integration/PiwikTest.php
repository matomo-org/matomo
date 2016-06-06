<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Access;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API;
use Piwik\Translate;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class PiwikTest extends IntegrationTestCase
{
    /**
     * Dataprovider for testIsNumericValid
     */
    public function getValidNumeric()
    {
        $valid = array(
            -1, 0, 1, 1.5, -1.5, 21111, 89898, 99999999999, -4565656,
            (float)-1, (float)0, (float)1, (float)1.5, (float)-1.5, (float)21111, (float)89898, (float)99999999999, (float)-4565656,
            (int)-1, (int)0, (int)1, (int)1.5, (int)-1.5, (int)21111, (int)89898, (int)99999999999, (int)-4565656,
            '-1', '0', '1', '1.5', '-1.5', '21111', '89898', '99999999999', '-4565656',
            '1e3', 0x123, "-1e-2",
        );

        if (!self::isPhp7orLater()) {
            // this seems to be no longer considered valid in PHP 7+
            $value[] = '0x123';
        }

        foreach ($valid as $key => $value) {
            $valid[$key] = array($value);
        }
        return $valid;
    }

    /**
     * @dataProvider getValidNumeric
     */
    public function testIsNumericValid($toTest)
    {
        $this->assertTrue(is_numeric($toTest), $toTest . " not valid but should!");
    }

    /**
     * Dataprovider for testIsNumericNotValid
     */
    public function getInvalidNumeric()
    {
        $notValid = array(
            '-1.0.0', '1,2', '--1', '-.', '- 1', '1-',
        );
        foreach ($notValid as $key => $value) {
            $notValid[$key] = array($value);
        }
        return $notValid;
    }

    /**
     * @dataProvider getInvalidNumeric
     */
    public function testIsNumericNotValid($toTest)
    {
        $this->assertFalse(is_numeric($toTest), $toTest . " valid but shouldn't!");
    }

    public function testSecureDiv()
    {
        $this->assertSame(3, Piwik::secureDiv(9, 3));
        $this->assertSame(0, Piwik::secureDiv(9, 0));
        $this->assertSame(10, Piwik::secureDiv(10, 1));
        $this->assertSame(10.0, Piwik::secureDiv(10.0, 1.0));
        $this->assertSame(5.5, Piwik::secureDiv(11.0, 2));
        $this->assertSame(0, Piwik::secureDiv(11.0, 'a'));
    }

    /**
     * Dataprovider for testCheckValidLoginString
     */
    public function getInvalidLoginStringData()
    {
        $notValid = array(
            '',
            '   ',
            'a',
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'alpha/beta',
            'alpha:beta',
            'alpha;beta',
            'alpha<beta',
            'alpha=beta',
            'alpha>beta',
            'alpha?beta',
        );
        foreach ($notValid as $key => $value) {
            $notValid[$key] = array($value);
        }
        return $notValid;
    }

    /**
     * @dataProvider getInvalidLoginStringData
     * @expectedException \Exception
     */
    public function testCheckInvalidLoginString($toTest)
    {
        Piwik::checkValidLoginString($toTest);
    }

    /**
     * Dataprovider for testCheckValidLoginString
     */
    public function getValidLoginStringData()
    {
        $valid = array(
            'aa',
            'aaa',
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'äÄüÜöÖß',
            'shoot_puck@the-goal.com',
        );
        foreach ($valid as $key => $value) {
            $valid[$key] = array($value);
        }
        return $valid;
    }

    /**
     * @dataProvider getValidLoginStringData
     */
    public function testCheckValidLoginString($toTest)
    {
        $this->assertNull(Piwik::checkValidLoginString($toTest));
    }

    /**
     * Data provider for testIsAssociativeArray.
     */
    public function getIsAssociativeArrayTestCases()
    {
        return array(
            array(array(0 => 'a', 1 => 'b', 2 => 'c', 3 => 'd', 4 => 'e', 5 => 'f'), false),
            array(array(-1 => 'a', 0 => 'a', 1 => 'a', 2 => 'a', 3 => 'a'), true),
            array(array(4 => 'a', 5 => 'a', 6 => 'a', 7 => 'a', 8 => 'a'), true),
            array(array(0 => 'a', 2 => 'a', 3 => 'a', 4 => 'a', 5 => 'a'), true),
            array(array('abc' => 'a', 0 => 'b', 'sdfds' => 'd'), true),
            array(array('abc' => 'def'), true)
        );
    }

    /**
     * @dataProvider getIsAssociativeArrayTestCases
     */
    public function testIsAssociativeArray($array, $expected)
    {
        $this->assertEquals($expected, Piwik::isAssociativeArray($array));
    }
}
