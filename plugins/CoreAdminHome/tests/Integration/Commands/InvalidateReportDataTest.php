<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;

/**
 * @group CoreAdminHome
 * @group CoreAdminHome_Integration
 */
class InvalidateReportDataTest extends ConsoleCommandTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        Fixture::createWebsite('2012-01-01 00:00:00');
        Fixture::createWebsite('2012-01-01 00:00:00');
        Fixture::createWebsite('2012-01-01 00:00:00');
    }

    /**
     * @dataProvider getInvalidDateRanges
     */
    public function test_Command_FailsWhenAnInvalidDateRangeIsUsed($invalidDateRange)
    {
        $code = $this->applicationTester->run(array(
            'command' => 'core:invalidate-report-data',
            '--dates' => array($invalidDateRange),
            '--periods' => 'day',
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ));

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        $this->assertContains("Invalid date or date range specifier", $this->applicationTester->getDisplay());
    }

    public function getInvalidDateRanges()
    {
        return array(
            array('garbage'),
            array('2012-01-03 2013-02-01'),
        );
    }

    /**
     * @dataProvider getInvalidPeriodTypes
     */
    public function test_Command_FailsWhenAnInvalidPeriodTypeIsUsed($invalidPeriodType)
    {
        $code = $this->applicationTester->run(array(
            'command' => 'core:invalidate-report-data',
            '--dates' => '2012-01-01',
            '--periods' => $invalidPeriodType,
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ));

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        $this->assertContains("Invalid period type", $this->applicationTester->getDisplay());
    }

    public function getInvalidPeriodTypes()
    {
        return array(
            array('cranberries'),
            array('range'),
        );
    }

    /**
     * @dataProvider getInvalidSiteLists
     */
    public function test_Command_FailsWhenAnInvalidSiteListIsUsed($invalidSites)
    {
        $code = $this->applicationTester->run(array(
            'command' => 'core:invalidate-report-data',
            '--dates' => '2012-01-01',
            '--periods' => 'day',
            '--sites' => $invalidSites,
            '--dry-run' => true,
            '-vvv' => true,
        ));

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        $this->assertContains("Invalid --sites value", $this->applicationTester->getDisplay());
    }

    public function getInvalidSiteLists()
    {
        return array(
            array('wolfalice'),
            array(','),
            array('1,500'),
        );
    }

    public function test_Command_FailsWhenAnInvalidSegmentIsUsed()
    {
        $code = $this->applicationTester->run(array(
            'command' => 'core:invalidate-report-data',
            '--dates' => '2012-01-01',
            '--periods' => 'day',
            '--sites' => '1',
            '--segment' => array('ablksdjfdslkjf'),
            '--dry-run' => true,
            '-vvv' => true,
        ));

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        $this->assertContains("The segment 'ablksdjfdslkjf' is not valid", $this->applicationTester->getDisplay());
    }

    /**
     * @dataProvider getTestDataForSuccessTests
     */
    public function test_Command_InvalidatesCorrectSitesAndDates($dates, $periods, $sites, $cascade, $segments, $expectedOutputs)
    {
        $code = $this->applicationTester->run(array(
            'command' => 'core:invalidate-report-data',
            '--dates' => $dates,
            '--periods' => $periods,
            '--sites' => $sites,
            '--cascade' => $cascade,
            '--segment' => $segments ?: array(),
            '--dry-run' => true,
            '-vvv' => true,
        ));

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());

        foreach ($expectedOutputs as $output) {
            $this->assertContains($output, $this->applicationTester->getDisplay());
        }
    }

    public function getTestDataForSuccessTests()
    {
        return array(

            array( // no cascade, single site + single day
                array('2012-01-01'),
                'day',
                '1',
                false,
                null,
                array(
                    '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2012-01-01 ], period = [ day ], segment = [  ]',
                ),
            ),

            array( // no cascade, single site + single day
                array('2012-01-01'),
                'day',
                '1',
                true,
                null,
                array(
                    '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2012-01-01 ], period = [ day ], segment = [  ]',
                ),
            ),

            array( // no cascade, single site, date, period
                array('2012-01-01'),
                'week',
                '1',
                false,
                null,
                array(
                    '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2011-12-26 ], period = [ week ], segment = [  ]',
                ),
            ),

            array( // no cascade, multiple site, date & period
                array('2012-01-01,2012-02-05', '2012-01-26,2012-01-27', '2013-03-19'),
                'month,week',
                '1,3',
                false,
                null,
                array(
                    '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2012-01-01, 2012-02-01 ], period = [ month ], segment = [  ], cascade = [ 0 ]',
                    '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2012-01-01 ], period = [ month ], segment = [  ], cascade = [ 0 ]',
                    '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2013-03-01 ], period = [ month ], segment = [  ], cascade = [ 0 ]',
                    '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2011-12-26, 2012-01-02, 2012-01-09, 2012-01-16, 2012-01-23, 2012-01-30 ], period = [ week ], segment = [  ], cascade = [ 0 ]',
                    '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2012-01-23 ], period = [ week ], segment = [  ], cascade = [ 0 ]',
                    '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2013-03-18 ], period = [ week ], segment = [  ], cascade = [ 0 ]',
                ),
            ),

            array( // cascade, single site, date, period
                array('2012-01-30,2012-02-10'),
                'week',
                '2',
                true,
                null,
                array(
                    '[Dry-run] invalidating archives for site = [ 2 ], dates = [ 2012-01-30, 2012-02-06 ], period = [ week ], segment = [  ], cascade = [ 1 ]',
                ),
            ),

            array( // cascade, multiple site, date & period
                array('2012-02-03,2012-02-04', '2012-03-15'),
                'month,week,day',
                'all',
                true,
                null,
                array(
                    '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-02-01 ], period = [ month ], segment = [  ], cascade = [ 1 ]',
                    '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-03-01 ], period = [ month ], segment = [  ], cascade = [ 1 ]',
                    '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-01-30 ], period = [ week ], segment = [  ], cascade = [ 1 ]',
                    '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-03-12 ], period = [ week ], segment = [  ], cascade = [ 1 ]',
                    '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-02-03, 2012-02-04 ], period = [ day ], segment = [  ], cascade = [ 1 ]',
                    '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-03-15 ], period = [ day ], segment = [  ], cascade = [ 1 ]',
                ),
            ),

            array( // cascade, one week, date & period + segment
                array('2012-01-01,2012-01-14'),
                'week',
                'all',
                true,
                array('browserCode==FF'),
                array(
                    '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2011-12-26, 2012-01-02, 2012-01-09 ], period = [ week ], segment = [ browserCode==FF ], cascade = [ 1 ]',
                ),
            ),
        );
    }
}
