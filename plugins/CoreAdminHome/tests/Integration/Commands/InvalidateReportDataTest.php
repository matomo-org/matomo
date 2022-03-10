<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Monolog\Handler\AbstractProcessingHandler;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;

/**
 * @group CoreAdminHome
 * @group CoreAdminHome_Integration
 */
class InvalidateReportDataTest extends ConsoleCommandTestCase
{
    private static $captureHandler;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $idSite = Fixture::createWebsite('2012-01-01 00:00:00');
        Fixture::createWebsite('2012-01-01 00:00:00');
        Fixture::createWebsite('2012-01-01 00:00:00');

        API::getInstance()->add('test segment', 'browserCode==IE', $idSite);
    }

    public function setUp(): void
    {
        parent::setUp();
        self::$captureHandler->messages = [];
    }

    protected function tearDown(): void
    {
        self::$captureHandler->messages = [];
        parent::tearDown();
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
        self::assertStringContainsString("Invalid date or date range specifier", $this->getLogOutput());
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
        self::assertStringContainsString("Invalid period type", $this->getLogOutput());
    }

    public function getInvalidPeriodTypes()
    {
        return array(
            array('cranberries'),
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
        self::assertStringContainsString("Invalid --sites value", $this->getLogOutput());
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
        self::assertStringContainsString("The segment condition 'ablksdjfdslkjf' is not valid", $this->getLogOutput());
    }

    /**
     * @dataProvider getTestDataForSuccessTests
     */
    public function test_Command_InvalidatesCorrectSitesAndDates($dates, $periods, $sites, $cascade, $segments, $plugin, $expectedOutputs)
    {
        $options = array(
            'command' => 'core:invalidate-report-data',
            '--dates' => $dates,
            '--periods' => $periods,
            '--sites' => $sites,
            '--cascade' => $cascade,
            '--segment' => $segments ?: array(),
            '--dry-run' => true,
            '-vvv' => true,
        );

        if (!empty($plugin)) {
            $options['--plugin'] = $plugin;
        }

        $code = $this->applicationTester->run($options);

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());

        foreach ($expectedOutputs as $output) {
            self::assertStringContainsString($output, $this->getLogOutput());
        }
    }

    public function test_Command_InvalidateDateRange()
    {
        $code = $this->applicationTester->run(array(
            'command' => 'core:invalidate-report-data',
            '--dates' => array('2019-01-01,2019-01-09'),
            '--periods' => 'range',
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ));

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("Invalidating range periods overlapping 2019-01-01,2019-01-09 [segment = ]", $this->getLogOutput());
    }

    public function test_Command_InvalidateDateRange_invalidDate()
    {
        $code = $this->applicationTester->run(array(
            'command' => 'core:invalidate-report-data',
            '--dates' => array('2019-01-01,2019-01--09'),
            '--periods' => 'range',
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ));

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("The date '2019-01-01,2019-01--09' is not a correct date range", $this->getLogOutput());
    }

    public function test_Command_InvalidateDateRange_onlyOneDate()
    {
        $code = $this->applicationTester->run(array(
            'command' => 'core:invalidate-report-data',
            '--dates' => array('2019-01-01'),
            '--periods' => 'range',
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ));

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("The date '2019-01-01' is not a correct date range", $this->getLogOutput());
    }

    public function test_Command_InvalidateDateRange_tooManyDatesInRange()
    {
        $code = $this->applicationTester->run(array(
            'command' => 'core:invalidate-report-data',
            '--dates' => array('2019-01-01,2019-01-09,2019-01-12,2019-01-15'),
            '--periods' => 'range',
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ));

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("The date '2019-01-01,2019-01-09,2019-01-12,2019-01-15' is not a correct date range", $this->getLogOutput());
    }

    public function test_Command_InvalidateDateRange_multipleDateRanges()
    {
        $code = $this->applicationTester->run(array(
            'command' => 'core:invalidate-report-data',
            '--dates' => array('2019-01-01,2019-01-09', '2019-01-12,2019-01-15'),
            '--periods' => 'range',
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ));

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("Invalidating range periods overlapping 2019-01-01,2019-01-09;2019-01-12,2019-01-15", $this->getLogOutput());
    }

    public function test_Command_InvalidateDateRange_invalidateAllPeriodTypesSkipsRangeWhenNotRangeDAte()
    {
        $code = $this->applicationTester->run(array(
            'command' => 'core:invalidate-report-data',
            '--dates' => array('2019-01-01'),
            '--periods' => 'all',
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ));

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringNotContainsString("range", $this->getLogOutput());
        self::assertStringNotContainsString("Range", $this->getLogOutput());
    }

    public function test_Command_InvalidateDateRange_invalidateAllPeriodTypes()
    {
        $code = $this->applicationTester->run(array(
            'command' => 'core:invalidate-report-data',
            '--dates' => array('2019-01-01,2019-01-09'),
            '--periods' => 'all',
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ));

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("Invalidating day periods in 2019-01-01,2019-01-09 [segment = ]", $this->getLogOutput());
        self::assertStringContainsString("Invalidating week periods in 2019-01-01,2019-01-09 [segment = ]", $this->getLogOutput());
        self::assertStringContainsString("Invalidating month periods in 2019-01-01,2019-01-09 [segment = ]", $this->getLogOutput());
        self::assertStringContainsString("Invalidating year periods in 2019-01-01,2019-01-09 [segment = ]", $this->getLogOutput());
        self::assertStringContainsString("Invalidating range periods overlapping 2019-01-01,2019-01-09 [segment = ]", $this->getLogOutput());
    }

    public function test_Command_InvalidateAll_multipleDateRanges()
    {
        $code = $this->applicationTester->run(array(
            'command' => 'core:invalidate-report-data',
            '--dates' => array('2019-01-01,2019-01-09', '2019-01-12,2019-01-13'),
            '--periods' => 'all',
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ));

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("Invalidating range periods overlapping 2019-01-01,2019-01-09;2019-01-12,2019-01-13 [segment = ]", $this->getLogOutput());
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
                null,
                array(
                    '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2011-12-26, 2012-01-02, 2012-01-09 ], period = [ week ], segment = [ browserCode==FF ], cascade = [ 1 ]',
                ),
            ),

            // w/ plugin
            [
                ['2015-05-04'],
                'day',
                '1',
                false,
                null,
                'ExamplePlugin',
                [
                    '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2015-05-04 ], period = [ day ], segment = [  ], cascade = [ 0 ], plugin = [ ExamplePlugin ]',
                ],
            ],

            // match segment by id
            [
                ['2015-05-04'],
                'day',
                '1',
                false,
                [1],
                null,
                [
                    '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2015-05-04 ], period = [ day ], segment = [ browserCode==IE ], cascade = [ 0 ]',
                ],
            ],

            // match segment by name
            [
                ['2015-05-04'],
                'day',
                '1',
                false,
                ['test segment'],
                null,
                [
                    '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2015-05-04 ], period = [ day ], segment = [ browserCode==IE ], cascade = [ 0 ]',
                ],
            ],
        );
    }

    public static function provideContainerConfigBeforeClass()
    {
        if (empty(self::$captureHandler)) {
            self::$captureHandler = new class extends AbstractProcessingHandler {
                public $messages = [];

                protected function write(array $record)
                {
                    $this->messages[] = (string)$record['formatted'];
                }
            };
        }

        return [
            'ini.tests.enable_logging' => 1,
            'Tests.log.allowAllHandlers' => true,
            'log.handlers' => [self::$captureHandler],
        ];
    }

    private function getLogOutput()
    {
        return implode("\n", self::$captureHandler->messages);
    }
}
