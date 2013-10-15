<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Date;
use Piwik\Period\Year;
use Piwik\Translate;

/**
 * Testing Period_Year
 */
class Period_YearTest extends PHPUnit_Framework_TestCase
{
    /**
     * test normal case
     * @group Core
     */
    public function testYearNormalcase()
    {
        $correct = array(
            '2024-01-01',
            '2024-02-01',
            '2024-03-01',
            '2024-04-01',
            '2024-05-01',
            '2024-06-01',
            '2024-07-01',
            '2024-08-01',
            '2024-09-01',
            '2024-10-01',
            '2024-11-01',
            '2024-12-01',);

        $year = new Year(Date::factory('2024-10-09'));
        $this->assertEquals(12, $year->getNumberOfSubperiods());
        $this->assertEquals($correct, $year->toString());
    }

    /**
     * test past
     * @group Core
     */
    public function testYearPastAndWrongdate()
    {
        $correct = array(
            '2000-01-01',
            '2000-02-01',
            '2000-03-01',
            '2000-04-01',
            '2000-05-01',
            '2000-06-01',
            '2000-07-01',
            '2000-08-01',
            '2000-09-01',
            '2000-10-01',
            '2000-11-01',
            '2000-12-01',
        );

        $year = new Year(Date::factory('2000-02-15'));
        $this->assertEquals(12, $year->getNumberOfSubperiods());
        $this->assertEquals($correct, $year->toString());
    }

    /**
     * @group Core
     */
    public function testGetLocalizedShortString()
    {
        Translate::loadEnglishTranslation();
        $year = new Year(Date::factory('2024-10-09'));
        $shouldBe = '2024';
        $this->assertEquals($shouldBe, $year->getLocalizedShortString());
    }

    /**
     * @group Core
     */
    public function testGetLocalizedLongString()
    {
        Translate::loadEnglishTranslation();
        $year = new Year(Date::factory('2024-10-09'));
        $shouldBe = '2024';
        $this->assertEquals($shouldBe, $year->getLocalizedLongString());
    }

    /**
     * @group Core
     */
    public function testGetPrettyString()
    {
        Translate::loadEnglishTranslation();
        $year = new Year(Date::factory('2024-10-09'));
        $shouldBe = '2024';
        $this->assertEquals($shouldBe, $year->getPrettyString());
    }
}