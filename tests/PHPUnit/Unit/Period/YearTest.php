<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Period;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Period\Year;

class YearTest extends BasePeriodTest
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

    public function getLocalizedShortStrings()
    {
        return array(
            array('en', '2024'),
            array('ko', '2024년'),
            array('zh-cn', '2024年'),
        );
    }

    /**
     * @group Core
     * @group Year
     * @dataProvider getLocalizedShortStrings
     */
    public function testGetLocalizedShortString($language, $shouldBe)
    {
        StaticContainer::get('Piwik\Translation\Translator')->setCurrentLanguage($language);

        $year = new Year(Date::factory('2024-10-09'));
        $this->assertEquals($shouldBe, $year->getLocalizedShortString());
    }

    public function getLocalizedLongStrings()
    {
        return array(
            array('en', '2024'),
            array('ko', '2024년'),
            array('zh-cn', '2024年'),
        );
    }

    /**
     * @group Core
     * @group Year
     * @dataProvider getLocalizedLongStrings
     */

    public function testGetLocalizedLongString($language, $shouldBe)
    {
        StaticContainer::get('Piwik\Translation\Translator')->setCurrentLanguage($language);

        $year = new Year(Date::factory('2024-10-09'));
        $this->assertEquals($shouldBe, $year->getLocalizedLongString());
    }

    /**
     * @group Core
     */
    public function testGetPrettyString()
    {
        $year = new Year(Date::factory('2024-10-09'));
        $shouldBe = '2024';
        $this->assertEquals($shouldBe, $year->getPrettyString());
    }
}
