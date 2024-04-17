<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Monolog\Handler\AbstractProcessingHandler;
use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Piwik\Plugins\CustomDimensions\API as CustomDimensionsAPI;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorAPI;
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

        CustomDimensionsAPI::getInstance()->configureNewCustomDimension(
            $idSite,
            'test',
            CustomDimensions::SCOPE_VISIT,
            true
        );

        SegmentEditorAPI::getInstance()->add('test segment', 'browserCode==IE', $idSite);
        SegmentEditorAPI::getInstance()->add('custom dimension', 'dimension1==test', $idSite);
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
        $code = $this->applicationTester->run([
            'command' => 'core:invalidate-report-data',
            '--dates' => [$invalidDateRange],
            '--periods' => 'day',
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ]);

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("Invalid date or date range specifier", $this->getLogOutput());
    }

    public function getInvalidDateRanges()
    {
        return [
            ['garbage'],
            ['2012-01-03 2013-02-01'],
        ];
    }

    /**
     * @dataProvider getInvalidPeriodTypes
     */
    public function test_Command_FailsWhenAnInvalidPeriodTypeIsUsed($invalidPeriodType)
    {
        $code = $this->applicationTester->run([
            'command' => 'core:invalidate-report-data',
            '--dates' => '2012-01-01',
            '--periods' => $invalidPeriodType,
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ]);

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("Invalid period type", $this->getLogOutput());
    }

    public function getInvalidPeriodTypes()
    {
        return [
            ['cranberries'],
        ];
    }

    /**
     * @dataProvider getInvalidSiteLists
     */
    public function test_Command_FailsWhenAnInvalidSiteListIsUsed($invalidSites)
    {
        $code = $this->applicationTester->run([
            'command' => 'core:invalidate-report-data',
            '--dates' => '2012-01-01',
            '--periods' => 'day',
            '--sites' => $invalidSites,
            '--dry-run' => true,
            '-vvv' => true,
        ]);

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("Invalid --sites value", $this->getLogOutput());
    }

    public function getInvalidSiteLists()
    {
        return [
            ['wolfalice'],
            [','],
            ['1,500'],
        ];
    }

    public function test_Command_FailsWhenAnInvalidSegmentIsUsed()
    {
        $code = $this->applicationTester->run([
            'command' => 'core:invalidate-report-data',
            '--dates' => '2012-01-01',
            '--periods' => 'day',
            '--sites' => '1',
            '--segment' => ['ablksdjfdslkjf'],
            '--dry-run' => true,
            '-vvv' => true,
        ]);

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("The segment condition 'ablksdjfdslkjf' is not valid", $this->getLogOutput());
    }

    public function test_Command_FailsWhenACustomDimensionSegmentIsNotSupportedByAllSites()
    {
        $code = $this->applicationTester->run([
            'command' => 'core:invalidate-report-data',
            '--dates' => '2012-01-01',
            '--periods' => 'day',
            '--sites' => '1,2',
            '--segment' => ['custom dimension'],
            '--dry-run' => true,
            '-vvv' => true,
        ]);

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("Segment 'dimension1' is not a supported segment", $this->getLogOutput());
    }


    public function test_Command_FailsWhenACustomDimensionSegmentIsNotValidForAnySite()
    {
        $code = $this->applicationTester->run([
            'command' => 'core:invalidate-report-data',
            '--dates' => '2012-01-01',
            '--periods' => 'day',
            '--sites' => '2,3',
            '--segment' => ['custom dimension'],
            '--dry-run' => true,
            '-vvv' => true,
        ]);

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("'custom dimension' did not match any stored segment, but invalidating it anyway", $this->getLogOutput());
        self::assertStringContainsString("The segment condition 'custom dimension' is not valid", $this->getLogOutput());
    }

    /**
     * @dataProvider getTestDataForSuccessTests
     */
    public function test_Command_InvalidatesCorrectSitesAndDates($dates, $periods, $sites, $cascade, $segments, $plugin, $expectedOutputs)
    {
        $options = [
            'command' => 'core:invalidate-report-data',
            '--dates' => $dates,
            '--periods' => $periods,
            '--sites' => $sites,
            '--cascade' => $cascade,
            '--segment' => $segments ?: [],
            '--dry-run' => true,
            '-vvv' => true,
        ];

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
        $code = $this->applicationTester->run([
            'command' => 'core:invalidate-report-data',
            '--dates' => ['2019-01-01,2019-01-09'],
            '--periods' => 'range',
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ]);

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("Invalidating range periods overlapping 2019-01-01,2019-01-09 [segment = ]", $this->getLogOutput());
    }

    public function test_Command_InvalidateDateRange_invalidDate()
    {
        $code = $this->applicationTester->run([
            'command' => 'core:invalidate-report-data',
            '--dates' => ['2019-01-01,2019-01--09'],
            '--periods' => 'range',
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ]);

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("The date '2019-01-01,2019-01--09' is not a correct date range", $this->getLogOutput());
    }

    public function test_Command_InvalidateDateRange_onlyOneDate()
    {
        $code = $this->applicationTester->run([
            'command' => 'core:invalidate-report-data',
            '--dates' => ['2019-01-01'],
            '--periods' => 'range',
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ]);

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("The date '2019-01-01' is not a correct date range", $this->getLogOutput());
    }

    public function test_Command_InvalidateDateRange_tooManyDatesInRange()
    {
        $code = $this->applicationTester->run([
            'command' => 'core:invalidate-report-data',
            '--dates' => ['2019-01-01,2019-01-09,2019-01-12,2019-01-15'],
            '--periods' => 'range',
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ]);

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("The date '2019-01-01,2019-01-09,2019-01-12,2019-01-15' is not a correct date range", $this->getLogOutput());
    }

    public function test_Command_InvalidateDateRange_multipleDateRanges()
    {
        $code = $this->applicationTester->run([
            'command' => 'core:invalidate-report-data',
            '--dates' => ['2019-01-01,2019-01-09', '2019-01-12,2019-01-15'],
            '--periods' => 'range',
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ]);

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("Invalidating range periods overlapping 2019-01-01,2019-01-09;2019-01-12,2019-01-15", $this->getLogOutput());
    }

    public function test_Command_InvalidateDateRange_invalidateAllPeriodTypesSkipsRangeWhenNotRangeDAte()
    {
        $code = $this->applicationTester->run([
            'command' => 'core:invalidate-report-data',
            '--dates' => ['2019-01-01'],
            '--periods' => 'all',
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ]);

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringNotContainsString("range", $this->getLogOutput());
        self::assertStringNotContainsString("Range", $this->getLogOutput());
    }

    public function test_Command_InvalidateDateRange_invalidateAllPeriodTypes()
    {
        $code = $this->applicationTester->run([
            'command' => 'core:invalidate-report-data',
            '--dates' => ['2019-01-01,2019-01-09'],
            '--periods' => 'all',
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ]);

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("Invalidating day periods in 2019-01-01,2019-01-09 [segment = ]", $this->getLogOutput());
        self::assertStringContainsString("Invalidating week periods in 2019-01-01,2019-01-09 [segment = ]", $this->getLogOutput());
        self::assertStringContainsString("Invalidating month periods in 2019-01-01,2019-01-09 [segment = ]", $this->getLogOutput());
        self::assertStringContainsString("Invalidating year periods in 2019-01-01,2019-01-09 [segment = ]", $this->getLogOutput());
        self::assertStringContainsString("Invalidating range periods overlapping 2019-01-01,2019-01-09 [segment = ]", $this->getLogOutput());
    }

    public function test_Command_InvalidateAll_multipleDateRanges()
    {
        $code = $this->applicationTester->run([
            'command' => 'core:invalidate-report-data',
            '--dates' => ['2019-01-01,2019-01-09', '2019-01-12,2019-01-13'],
            '--periods' => 'all',
            '--sites' => '1',
            '--dry-run' => true,
            '-vvv' => true,
        ]);

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("Invalidating range periods overlapping 2019-01-01,2019-01-09;2019-01-12,2019-01-13 [segment = ]", $this->getLogOutput());
    }

    /**
     * @return iterable<string, array<mixed>>
     */
    public function getTestDataForSuccessTests(): iterable
    {
        yield 'no cascade, single site + single day' => [
            ['2012-01-01'],
            'day',
            '1',
            false,
            null,
            null,
            [
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2012-01-01 ], period = [ day ], segment = [  ]',
            ],
        ];

        yield 'cascade, single site + single day' => [
            ['2012-01-01'],
            'day',
            '1',
            true,
            null,
            null,
            [
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2012-01-01 ], period = [ day ], segment = [  ]',
            ],
        ];

        yield 'no cascade, single site, date, period' => [
            ['2012-01-01'],
            'week',
            '1',
            false,
            null,
            null,
            [
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2011-12-26 ], period = [ week ], segment = [  ]',
            ],
        ];

        yield 'no cascade, multiple site, date & period' => [
            ['2012-01-01,2012-02-05', '2012-01-26,2012-01-27', '2013-03-19'],
            'month,week',
            '1,3',
            false,
            null,
            null,
            [
                '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2012-01-01, 2012-02-01 ], period = [ month ], segment = [  ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2012-01-01 ], period = [ month ], segment = [  ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2013-03-01 ], period = [ month ], segment = [  ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2011-12-26, 2012-01-02, 2012-01-09, 2012-01-16, 2012-01-23, 2012-01-30 ], period = [ week ], segment = [  ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2012-01-23 ], period = [ week ], segment = [  ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2013-03-18 ], period = [ week ], segment = [  ], cascade = [ 0 ]',
            ],
        ];

        yield 'cascade, single site, date, period' => [
            ['2012-01-30,2012-02-10'],
            'week',
            '2',
            true,
            null,
            null,
            [
                '[Dry-run] invalidating archives for site = [ 2 ], dates = [ 2012-01-30, 2012-02-06 ], period = [ week ], segment = [  ], cascade = [ 1 ]',
            ],
        ];

        yield 'cascade, multiple site, date & period' => [
            ['2012-02-03,2012-02-04', '2012-03-15'],
            'month,week,day',
            'all',
            true,
            null,
            null,
            [
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-02-01 ], period = [ month ], segment = [  ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-03-01 ], period = [ month ], segment = [  ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-01-30 ], period = [ week ], segment = [  ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-03-12 ], period = [ week ], segment = [  ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-02-03, 2012-02-04 ], period = [ day ], segment = [  ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-03-15 ], period = [ day ], segment = [  ], cascade = [ 1 ]',
            ],
        ];

        yield 'cascade, one week, date & period + segment' => [
            ['2012-01-01,2012-01-14'],
            'week',
            'all',
            true,
            ['browserCode==FF'],
            null,
            [
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2011-12-26, 2012-01-02, 2012-01-09 ], period = [ week ], segment = [ browserCode==FF ], cascade = [ 1 ]',
            ],
        ];

        yield 'w/ plugin' => [
            ['2015-05-04'],
            'day',
            '1',
            false,
            null,
            'ExamplePlugin',
            [
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2015-05-04 ], period = [ day ], segment = [  ], cascade = [ 0 ], plugin = [ ExamplePlugin ]',
            ],
        ];

        yield 'match segment by id' => [
            ['2015-05-04'],
            'day',
            '1',
            false,
            [1],
            null,
            [
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2015-05-04 ], period = [ day ], segment = [ browserCode==IE ], cascade = [ 0 ]',
            ],
        ];

        yield 'match segment by name' => [
            ['2015-05-04'],
            'day',
            '1',
            false,
            ['test segment'],
            null,
            [
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2015-05-04 ], period = [ day ], segment = [ browserCode==IE ], cascade = [ 0 ]',
            ],
        ];

        yield 'match custom dimension segment by name' => [
            ['2015-05-04'],
            'day',
            '1',
            false,
            ['custom dimension'],
            null,
            [
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2015-05-04 ], period = [ day ], segment = [ dimension1==test ], cascade = [ 0 ]',
            ],
        ];

        yield 'match custom dimension segment by definition' => [
            ['2015-05-04'],
            'day',
            '1',
            false,
            ['dimension1==test'],
            null,
            [
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2015-05-04 ], period = [ day ], segment = [ dimension1==test ], cascade = [ 0 ]',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function provideContainerConfigBeforeClass(): array
    {
        if (null === self::$captureHandler) {
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

    private function getLogOutput(): string
    {
        return implode("\n", self::$captureHandler->messages);
    }
}
