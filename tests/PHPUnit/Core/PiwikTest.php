<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Access;
use Piwik\Filesystem;
use Piwik\MetricsFormatter;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API;
use Piwik\Translate;

class PiwikTest extends DatabaseTestCase
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
            '1e3', '0x123', "-1e-2",
        );
        foreach ($valid AS $key => $value) {
            $valid[$key] = array($value);
        }
        return $valid;
    }

    /**
     * @group Core
     * @group Piwik
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
        foreach ($notValid AS $key => $value) {
            $notValid[$key] = array($value);
        }
        return $notValid;
    }

    /**
     * @group Core
     * @group Piwik
     * @dataProvider getInvalidNumeric
     */
    public function testIsNumericNotValid($toTest)
    {
        $this->assertFalse(is_numeric($toTest), $toTest . " valid but shouldn't!");
    }

    /**
     * @group Core
     * @group Piwik
     */
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
     * Dataprovider for testGetPrettyTimeFromSeconds
     */
    public function getPrettyTimeFromSecondsData()
    {
        return array(
            array(30, array('30s', '00:00:30')),
            array(60, array('1 min 0s', '00:01:00')),
            array(100, array('1 min 40s', '00:01:40')),
            array(3600, array('1 hours 0 min', '01:00:00')),
            array(3700, array('1 hours 1 min', '01:01:40')),
            array(86400 + 3600 * 10, array('1 days 10 hours', '34:00:00')),
            array(86400 * 365, array('365 days 0 hours', '8760:00:00')),
            array((86400 * (365.25 + 10)), array('1 years 10 days', '9006:00:00')),
            array(1.342, array('1.34s', '00:00:01.34')),
            array(.342, array('0.34s', '00:00:00.34')),
			array(.02, array('0.02s', '00:00:00.02')),
            array(.002, array('0.002s', '00:00:00')),
			array(1.002, array('1s', '00:00:01')),
			array(1.02, array('1.02s', '00:00:01.02')),
			array(1.2, array('1.2s', '00:00:01.20')),
			array(122.1, array('2 min 2.1s', '00:02:02.10'))
        );
    }

    /**
     * @group Core
     * @group Piwik
     * @dataProvider getPrettyTimeFromSecondsData
     */
    public function testGetPrettyTimeFromSeconds($seconds, $expected)
    {
        if (($seconds * 100) > PHP_INT_MAX) {
            $this->markTestSkipped("Will not pass on 32-bit machine.");
        }
        
        Translate::loadEnglishTranslation();

        $sentenceExpected = str_replace(' ', '&nbsp;', $expected[0]);
        $numericExpected = $expected[1];
        $this->assertEquals($sentenceExpected, MetricsFormatter::getPrettyTimeFromSeconds($seconds, $sentence = true));
        $this->assertEquals($numericExpected, MetricsFormatter::getPrettyTimeFromSeconds($seconds, $sentence = false));

        Translate::unloadEnglishTranslation();
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
            'aa',
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'alpha/beta',
            'alpha:beta',
            'alpha;beta',
            'alpha<beta',
            'alpha=beta',
            'alpha>beta',
            'alpha?beta',
        );
        foreach ($notValid AS $key => $value) {
            $notValid[$key] = array($value);
        }
        return $notValid;
    }

    /**
     * @group Core
     * @group Piwik
     * @dataProvider getInvalidLoginStringData
     */
    public function testCheckInvalidLoginString($toTest)
    {
        try {
            Piwik::checkValidLoginString($toTest);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Dataprovider for testCheckValidLoginString
     */
    public function getValidLoginStringData()
    {
        $valid = array(
            'aaa',
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'shoot_puck@the-goal.com',
        );
        foreach ($valid AS $key => $value) {
            $valid[$key] = array($value);
        }
        return $valid;
    }

    /**
     * @group Core
     * @group Piwik
     * @dataProvider getValidLoginStringData
     */
    public function testCheckValidLoginString($toTest)
    {
        $this->assertNull(Piwik::checkValidLoginString($toTest));
    }

    /**
     * Dataprovider for testGetPrettyValue
     */
    public function getGetPrettyValueTestCases()
    {
        return array(
            array('revenue', 12, '$ 12'),
            array('revenue_evolution', '100 %', '100 %'),
            array('avg_time_generation', '3.333', '3.33s'),
            array('avg_time_generation', '333.333', '5&nbsp;min&nbsp;33.33s'),
            array('avg_time_on_page', '3', '00:00:03'),
            array('avg_time_on_page', '333', '00:05:33'),
        );
    }

    /**
     * @group Core
     * @group Piwik
     * @dataProvider getGetPrettyValueTestCases
     */
    public function testGetPrettyValue($columnName, $value, $expected)
    {
        Translate::loadEnglishTranslation();

        $access = Access::getInstance();
        $access->setSuperUser(true);

        $idsite = API::getInstance()->addSite("test", "http://test");

        $this->assertEquals(
            $expected,
            MetricsFormatter::getPrettyValue($idsite, $columnName, $value, false, false)
        );

        Translate::unloadEnglishTranslation();
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
     * @group Core
     * @group Piwik
     * @dataProvider getIsAssociativeArrayTestCases
     */
    public function testIsAssociativeArray($array, $expected)
    {
        $this->assertEquals($expected, Piwik::isAssociativeArray($array));
    }

    /**
     * @group Core
     * @group Piwik
     */
    public function testCheckIfFileSystemIsNFSOnNonNFS()
    {
        $this->assertFalse(Filesystem::checkIfFileSystemIsNFS());
    }
}
