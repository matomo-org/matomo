<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Piwik\Access;
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
        $this->assertContains("Invalid date range specifier", $this->applicationTester->getDisplay());
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

    /**
     * @dataProvider getTestDataForSuccessTests
     */
    public function test_Command_InvalidatesCorrectSitesAndDates($dates, $periods, $sites, $cascade, $expectedOutputs)
    {
        $code = $this->applicationTester->run(array(
            'command' => 'core:invalidate-report-data',
            '--dates' => $dates,
            '--periods' => $periods,
            '--sites' => $sites,
            '--cascade' => $cascade,
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

            array( // no cascade, single site, date, period
                array('2012-01-01'),
                'week',
                '1',
                false,
                array(
                    '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2011-12-26 ], period = [ week ]',
                ),
            ),

            array( // no cascade, multiple site, date & period
                array('2012-01-01,2012-02-05', '2012-01-26,2012-01-27', '2013-03-19'),
                'month,week',
                '1,3',
                false,
                array(
                    '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2012-01-01, 2012-02-01, 2013-03-01 ], period = [ month ]',
                    '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2011-12-26, 2012-01-02, 2012-01-09, 2012-01-16, 2012-01-23, 2012-01-30, 2013-03-18 ], period = [ week ]',
                ),
            ),

            array( // cascade, single site, date, period
                array('2012-01-30,2012-02-10'),
                'week',
                '2',
                true,
                array(
                    '[Dry-run] invalidating archives for site = [ 2 ], dates = [ 2012-01-30, 2012-02-06 ], period = [ week ]',
                    '[Dry-run] invalidating archives for site = [ 2 ], dates = [ 2012-01-30, 2012-01-31, 2012-02-01, 2012-02-02, '
                    . '2012-02-03, 2012-02-04, 2012-02-05, 2012-02-06, 2012-02-07, 2012-02-08, 2012-02-09, 2012-02-10, '
                    . '2012-02-11, 2012-02-12 ], period = [ day ]',
                ),
            ),

            array( // cascade, multiple site, date & period
                array('2012-02-03,2012-02-04', '2012-03-15'),
                'month,week,day',
                'all',
                true,
                array(
                    '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-02-01, 2012-03-01 ], period = [ month ]',
                    '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-01-30, 2012-02-06, 2012-02-13, 2012-02-20, 2012-02-27, 2012-03-05, 2012-03-12, 2012-03-19, 2012-03-26 ], period = [ week ]',
                    '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-01-30, 2012-01-31, 2012-02-01,'
                    . ' 2012-02-02, 2012-02-03, 2012-02-04, 2012-02-05, 2012-02-06, 2012-02-07, 2012-02-08, 2012-02-09, '
                    . '2012-02-10, 2012-02-11, 2012-02-12, 2012-02-13, 2012-02-14, 2012-02-15, 2012-02-16, 2012-02-17, '
                    . '2012-02-18, 2012-02-19, 2012-02-20, 2012-02-21, 2012-02-22, 2012-02-23, 2012-02-24, 2012-02-25, '
                    . '2012-02-26, 2012-02-27, 2012-02-28, 2012-02-29, 2012-03-01, 2012-03-02, 2012-03-03, 2012-03-04, '
                    . '2012-03-05, 2012-03-06, 2012-03-07, 2012-03-08, 2012-03-09, 2012-03-10, 2012-03-11, 2012-03-12, '
                    . '2012-03-13, 2012-03-14, 2012-03-15, 2012-03-16, 2012-03-17, 2012-03-18, 2012-03-19, 2012-03-20, '
                    . '2012-03-21, 2012-03-22, 2012-03-23, 2012-03-24, 2012-03-25, 2012-03-26, 2012-03-27, 2012-03-28, '
                    . '2012-03-29, 2012-03-30, 2012-03-31, 2012-04-01 ], period = [ day ]'
                ),
            ),

        );
    }
}
