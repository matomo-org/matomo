<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Monolog\Handler\AbstractProcessingHandler;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Db;
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

        $idSite = Fixture::createWebsite('2011-01-01 00:00:00');
        Fixture::createWebsite('2012-01-02 00:00:00');
        Fixture::createWebsite('2012-01-01 00:00:00');

        CustomDimensionsAPI::getInstance()->configureNewCustomDimension(
            $idSite,
            'test',
            CustomDimensions::SCOPE_VISIT,
            true
        );

        Rules::setBrowserTriggerArchiving(false);
        SegmentEditorAPI::getInstance()->add('test segment', 'browserCode==IE', false, true);
        SegmentEditorAPI::getInstance()->add('custom dimension', 'dimension1==test', $idSite, true);
        SegmentEditorAPI::getInstance()->add('browser segment', 'browserCode==FF', false, false);
    }

    public function setUp(): void
    {
        parent::setUp();
        self::$captureHandler->messages = [];
        Db::query('TRUNCATE table ' . Common::prefixTable('archive_invalidations'));
    }

    protected function tearDown(): void
    {
        self::$captureHandler->messages = [];
        parent::tearDown();
    }

    /**
     * @dataProvider getInvalidCommandOptions
     */
    public function testCommandFailsWithInvalidOptions(array $commandOptions, array $expectedOutputs)
    {
        $commandOptions = array_merge(['command' => 'core:invalidate-report-data'], $commandOptions);

        $code = $this->applicationTester->run($commandOptions);
        self::assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());

        foreach ($expectedOutputs as $expectedOutput) {
            self::assertStringContainsString($expectedOutput, $this->getLogOutput());
        }
    }

    public function getInvalidCommandOptions(): iterable
    {
        yield 'invalid dates parameter' => [
            [
                '--dates' => ['garbage'],
                '--periods' => 'day',
                '--sites' => '1',
                '--dry-run' => true,
                '-vvv' => true,
            ],
            [
                'Invalid date or date range specifier'
            ]
        ];

        yield 'invalid dates parameter format' => [
            [
                '--dates' => ['2012-01-03 2013-02-01'],
                '--periods' => 'day',
                '--sites' => '1',
                '--dry-run' => true,
                '-vvv' => true,
            ],
            [
                'Invalid date or date range specifier'
            ]
        ];

        yield 'dates parameter with too many dates in range' => [
            [
                '--dates' => ['2019-01-01,2019-01-09,2019-01-12,2019-01-15'],
                '--periods' => 'range',
                '--sites' => '1',
                '--dry-run' => true,
                '-vvv' => true,
            ],
            [
                "The date '2019-01-01,2019-01-09,2019-01-12,2019-01-15' is not a correct date range"
            ]
        ];

        yield 'invalid period parameter' => [
            [
                '--dates' => ['2012-01-03'],
                '--periods' => 'cranberries',
                '--sites' => '1',
                '--dry-run' => true,
                '-vvv' => true,
            ],
            [
                'Invalid period type'
            ]
        ];

        yield 'non numeric sites parameter' => [
            [
                '--dates' => ['2012-01-03'],
                '--periods' => 'day',
                '--sites' => 'wolfalice',
                '--dry-run' => true,
                '-vvv' => true,
            ],
            [
                'Invalid --sites value'
            ]
        ];

        yield 'idSite list without values' => [
            [
                '--dates' => ['2012-01-03'],
                '--periods' => 'day',
                '--sites' => ',',
                '--dry-run' => true,
                '-vvv' => true,
            ],
            [
                'Invalid --sites value'
            ]
        ];

        yield 'not existing idSite included in list' => [
            [
                '--dates' => ['2012-01-03'],
                '--periods' => 'day',
                '--sites' => '1,500',
                '--dry-run' => true,
                '-vvv' => true,
            ],
            [
                'Invalid --sites value'
            ]
        ];

        yield 'invalid segment provided' => [
            [
                '--dates' => ['2012-01-03'],
                '--periods' => 'day',
                '--sites' => '1',
                '--segment' => ['ablksdjfdslkjf'],
                '--dry-run' => true,
                '-vvv' => true,
            ],
            [
                "The segment condition 'ablksdjfdslkjf' is not valid"
            ]
        ];

        yield 'segment name provided that does not exist for given site' => [
            [
                '--dates' => ['2012-01-03'],
                '--periods' => 'day',
                '--sites' => '2,3',
                '--segment' => ['custom dimension'],
                '--dry-run' => true,
                '-vvv' => true,
            ],
            [
                "'custom dimension' did not match any stored segment, but invalidating it anyway.",
                "The segment condition 'custom dimension' is not valid.",
            ]
        ];

        yield 'segment provided that does not exist for given site' => [
            [
                '--dates' => ['2012-01-03'],
                '--periods' => 'day',
                '--sites' => '2',
                '--segment' => ['dimension1==test'],
                '--dry-run' => true,
                '-vvv' => true,
            ],
            [
                "'dimension1==test' did not match any stored segment, but invalidating it anyway.",
                "Segment 'dimension1' is not a supported segment."
            ]
        ];
    }

     /**
     * @dataProvider getTestDataForSuccessTests
     */
    public function testCommandOutputSuccess($dates, $periods, $sites, $cascade, $segments, $plugin, $expectedOutputs)
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
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2012-01-01 ], period = [ day ], segment = [ browserCode==IE ]',
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2012-01-01 ], period = [ day ], segment = [ dimension1==test ]',
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
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2012-01-01 ], period = [ day ], segment = [ browserCode==IE ]',
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2012-01-01 ], period = [ day ], segment = [ dimension1==test ]',
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
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2011-12-26 ], period = [ week ], segment = [ browserCode==IE ]',
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2011-12-26 ], period = [ week ], segment = [ dimension1==test ]',
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
                'Segment [dimension1==test] not available for all sites, skipping this segment for sites [ 3 ].',
                '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2012-01-01, 2012-02-01 ], period = [ month ], segment = [  ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2012-01-01, 2012-02-01 ], period = [ month ], segment = [ browserCode==IE ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2012-01-01, 2012-02-01 ], period = [ month ], segment = [ dimension1==test ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2012-01-01 ], period = [ month ], segment = [  ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2012-01-01 ], period = [ month ], segment = [ browserCode==IE ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2012-01-01 ], period = [ month ], segment = [ dimension1==test ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2013-03-01 ], period = [ month ], segment = [  ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2013-03-01 ], period = [ month ], segment = [ browserCode==IE ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2013-03-01 ], period = [ month ], segment = [ dimension1==test ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2011-12-26, 2012-01-02, 2012-01-09, 2012-01-16, 2012-01-23, 2012-01-30 ], period = [ week ], segment = [  ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2011-12-26, 2012-01-02, 2012-01-09, 2012-01-16, 2012-01-23, 2012-01-30 ], period = [ week ], segment = [ browserCode==IE ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2011-12-26, 2012-01-02, 2012-01-09, 2012-01-16, 2012-01-23, 2012-01-30 ], period = [ week ], segment = [ dimension1==test ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2012-01-23 ], period = [ week ], segment = [  ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2012-01-23 ], period = [ week ], segment = [ browserCode==IE ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2012-01-23 ], period = [ week ], segment = [ dimension1==test ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2013-03-18 ], period = [ week ], segment = [  ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1, 3 ], dates = [ 2013-03-18 ], period = [ week ], segment = [ browserCode==IE ], cascade = [ 0 ]',
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2013-03-18 ], period = [ week ], segment = [ dimension1==test ], cascade = [ 0 ]',
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
                '[Dry-run] invalidating archives for site = [ 2 ], dates = [ 2012-01-30, 2012-02-06 ], period = [ week ], segment = [ browserCode==IE ], cascade = [ 1 ]',
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
                'Segment [dimension1==test] not available for all sites, skipping this segment for sites [ 2, 3 ].',
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-02-01 ], period = [ month ], segment = [  ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-02-01 ], period = [ month ], segment = [ browserCode==IE ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-02-01 ], period = [ month ], segment = [  ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-03-01 ], period = [ month ], segment = [  ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-03-01 ], period = [ month ], segment = [ browserCode==IE ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2012-03-01 ], period = [ month ], segment = [ dimension1==test ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-01-30 ], period = [ week ], segment = [  ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-01-30 ], period = [ week ], segment = [ browserCode==IE ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2012-01-30 ], period = [ week ], segment = [ dimension1==test ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-03-12 ], period = [ week ], segment = [  ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-03-12 ], period = [ week ], segment = [ browserCode==IE ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2012-03-12 ], period = [ week ], segment = [ dimension1==test ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-02-03, 2012-02-04 ], period = [ day ], segment = [  ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-02-03, 2012-02-04 ], period = [ day ], segment = [ browserCode==IE ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2012-02-03, 2012-02-04 ], period = [ day ], segment = [ dimension1==test ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-03-15 ], period = [ day ], segment = [  ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1, 2, 3 ], dates = [ 2012-03-15 ], period = [ day ], segment = [ browserCode==IE ], cascade = [ 1 ]',
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2012-03-15 ], period = [ day ], segment = [ dimension1==test ], cascade = [ 1 ]',
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
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2015-05-04 ], period = [ day ], segment = [ browserCode==IE ], cascade = [ 0 ], plugin = [ ExamplePlugin ]',
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2015-05-04 ], period = [ day ], segment = [ dimension1==test ], cascade = [ 0 ], plugin = [ ExamplePlugin ]',
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

        yield 'all visits segment only' => [
            ['2015-05-04'],
            'day',
            '1',
            false,
            [''],
            null,
            [
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2015-05-04 ], period = [ day ], segment = [  ], cascade = [ 0 ]',
            ],
        ];

        yield 'segment only invalidated for supported sites, whileignoring others' => [
            ['2012-01-01'],
            'day',
            '1,2',
            false,
            ['custom dimension'],
            null,
            [
                'Segment [dimension1==test] not available for all sites, skipping this segment for sites [ 2 ]',
                '[Dry-run] invalidating archives for site = [ 1 ], dates = [ 2012-01-01 ], period = [ day ], segment = [ dimension1==test ]',
            ],
        ];

        yield 'invalidating multiple date ranges' => [
            ['2019-01-01,2019-01-09', '2019-01-12,2019-01-15'],
            'range',
            '1',
            false,
            null,
            null,
            [
                "Invalidating range periods overlapping 2019-01-01,2019-01-09;2019-01-12,2019-01-15 for site = [ 1 ], segment = [  ]",
                "Invalidating range periods overlapping 2019-01-01,2019-01-09;2019-01-12,2019-01-15 for site = [ 1 ], segment = [ browserCode==IE ]",
                "Invalidating range periods overlapping 2019-01-01,2019-01-09;2019-01-12,2019-01-15 for site = [ 1 ], segment = [ dimension1==test ]",
            ],
        ];

        yield 'invalidating period type all for range invalidates all period types' => [
            ['2019-01-01,2019-01-09'],
            'all',
            '1',
            false,
            null,
            null,
            [
                "Invalidating day periods in 2019-01-01,2019-01-09 for site = [ 1 ], segment = [  ]",
                "Invalidating day periods in 2019-01-01,2019-01-09 for site = [ 1 ], segment = [ browserCode==IE ]",
                "Invalidating day periods in 2019-01-01,2019-01-09 for site = [ 1 ], segment = [ dimension1==test ]",
                "Invalidating week periods in 2019-01-01,2019-01-09 for site = [ 1 ], segment = [  ]",
                "Invalidating week periods in 2019-01-01,2019-01-09 for site = [ 1 ], segment = [ browserCode==IE ]",
                "Invalidating week periods in 2019-01-01,2019-01-09 for site = [ 1 ], segment = [ dimension1==test ]",
                "Invalidating month periods in 2019-01-01,2019-01-09 for site = [ 1 ], segment = [  ]",
                "Invalidating month periods in 2019-01-01,2019-01-09 for site = [ 1 ], segment = [ browserCode==IE ]",
                "Invalidating month periods in 2019-01-01,2019-01-09 for site = [ 1 ], segment = [ dimension1==test ]",
                "Invalidating year periods in 2019-01-01,2019-01-09 for site = [ 1 ], segment = [  ]",
                "Invalidating year periods in 2019-01-01,2019-01-09 for site = [ 1 ], segment = [ browserCode==IE ]",
                "Invalidating year periods in 2019-01-01,2019-01-09 for site = [ 1 ], segment = [ dimension1==test ]",
                "Invalidating range periods overlapping 2019-01-01,2019-01-09 for site = [ 1 ], segment = [  ]",
                "Invalidating range periods overlapping 2019-01-01,2019-01-09 for site = [ 1 ], segment = [ browserCode==IE ]",
                "Invalidating range periods overlapping 2019-01-01,2019-01-09 for site = [ 1 ], segment = [ dimension1==test ]",
            ],
        ];
    }


    /**
     * @dataProvider getInvalidationTestData
     */
    public function testCommandCreatesExpectedInvalidations(array $commandOptions, array $expectedInvalidations)
    {
        Db::query('TRUNCATE table ' . Common::prefixTable('archive_invalidations'));

        $commandOptions = array_merge(['command' => 'core:invalidate-report-data'], $commandOptions);

        $code = $this->applicationTester->run($commandOptions);

        self::assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());

        self::assertInvalidationsPresent($expectedInvalidations);
    }

    public function getInvalidationTestData(): iterable
    {
        yield "Invalidating a single day works for all segments, one website" => [
            [
                '--dates' => ['2012-01-01'],
                '--sites' => '1',
            ],
            [
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
            ]
        ];

        yield "Invalidating a single day works for all visits segment only, one website" => [
            [
                '--dates' => ['2012-01-01'],
                '--sites' => '1',
                '--segment' => [''],
            ],
            [
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
            ]
        ];

        yield "Invalidating a single day works for specific segment only, one website" => [
            [
                '--dates' => ['2012-01-01'],
                '--sites' => '1',
                '--segment' => ['browserCode==IE'],
            ],
            [
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
            ]
        ];

        yield "Invalidating a single day works for multiple segments, one website" => [
            [
                '--dates' => ['2012-01-01'],
                '--sites' => '1',
                '--segment' => ['browserCode==IE', 'dimension1==test'],
            ],
            [
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
            ]
        ];

        yield "Invalidating plugin for a single day works for all visits segment only, one website" => [
            [
                '--dates' => ['2012-01-01'],
                '--sites' => '1',
                '--segment' => [''],
                '--plugin' => 'Actions'
            ],
            [
                ['name' => 'done.Actions', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done.Actions', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done.Actions', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done.Actions', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
            ]
        ];

        yield "Invalidating plugin for a single day works for all segments, one website" => [
            [
                '--dates' => ['2012-01-01'],
                '--sites' => '1',
                '--plugin' => 'Actions'
            ],
            [
                ['name' => 'done.Actions', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done.Actions', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done.Actions', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done.Actions', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc.Actions', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc.Actions', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc.Actions', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc.Actions', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279.Actions', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279.Actions', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279.Actions', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279.Actions', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
            ]
        ];

        yield "Invalidating a single day as period works for segment, one website" => [
            [
                '--dates' => ['2012-01-01,2012-01-01'],
                '--sites' => '1',
                '--segment' => ['browserCode==IE'],
            ],
            [
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
            ]
        ];

        yield "Invalidating a period works for all segments, one website" => [
            [
                '--dates' => ['2012-01-01,2012-01-12'],
                '--sites' => '1',
            ],
            [
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-02', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-03', 'date2' => '2012-01-03', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-04', 'date2' => '2012-01-04', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-05', 'date2' => '2012-01-05', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-06', 'date2' => '2012-01-06', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-07', 'date2' => '2012-01-07', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-08', 'date2' => '2012-01-08', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-09', 'date2' => '2012-01-09', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-10', 'date2' => '2012-01-10', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-11', 'date2' => '2012-01-11', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-12', 'date2' => '2012-01-12', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-08', 'period' => 2, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-09', 'date2' => '2012-01-15', 'period' => 2, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-02', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-03', 'date2' => '2012-01-03', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-04', 'date2' => '2012-01-04', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-05', 'date2' => '2012-01-05', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-06', 'date2' => '2012-01-06', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-07', 'date2' => '2012-01-07', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-08', 'date2' => '2012-01-08', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-09', 'date2' => '2012-01-09', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-10', 'date2' => '2012-01-10', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-11', 'date2' => '2012-01-11', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-12', 'date2' => '2012-01-12', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-08', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-09', 'date2' => '2012-01-15', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-02', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-03', 'date2' => '2012-01-03', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-04', 'date2' => '2012-01-04', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-05', 'date2' => '2012-01-05', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-06', 'date2' => '2012-01-06', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-07', 'date2' => '2012-01-07', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-08', 'date2' => '2012-01-08', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-09', 'date2' => '2012-01-09', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-10', 'date2' => '2012-01-10', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-11', 'date2' => '2012-01-11', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-12', 'date2' => '2012-01-12', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-08', 'period' => 2, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-09', 'date2' => '2012-01-15', 'period' => 2, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
            ]
        ];

        yield "Invalidating multiple dates works for all segments, one website" => [
            [
                '--dates' => ['2012-01-01', '2012-01-06', '2012-01-12'],
                '--sites' => '1',
            ],
            [
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-06', 'date2' => '2012-01-06', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-12', 'date2' => '2012-01-12', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-08', 'period' => 2, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-09', 'date2' => '2012-01-15', 'period' => 2, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-06', 'date2' => '2012-01-06', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-12', 'date2' => '2012-01-12', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-08', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-09', 'date2' => '2012-01-15', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-06', 'date2' => '2012-01-06', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-12', 'date2' => '2012-01-12', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-08', 'period' => 2, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-09', 'date2' => '2012-01-15', 'period' => 2, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
            ]
        ];

        yield "Invalidating multiple periods works for all segments, one website" => [
            [
                '--dates' => ['2012-01-01,2012-01-03', '2012-01-06,2012-01-07', '2012-01-10,2012-01-12'],
                '--sites' => '1',
            ],
            [
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-02', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-03', 'date2' => '2012-01-03', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-06', 'date2' => '2012-01-06', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-07', 'date2' => '2012-01-07', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-10', 'date2' => '2012-01-10', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-11', 'date2' => '2012-01-11', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-12', 'date2' => '2012-01-12', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-08', 'period' => 2, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-09', 'date2' => '2012-01-15', 'period' => 2, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-02', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-03', 'date2' => '2012-01-03', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-06', 'date2' => '2012-01-06', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-07', 'date2' => '2012-01-07', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-10', 'date2' => '2012-01-10', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-11', 'date2' => '2012-01-11', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-12', 'date2' => '2012-01-12', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-08', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-09', 'date2' => '2012-01-15', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-02', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-03', 'date2' => '2012-01-03', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-06', 'date2' => '2012-01-06', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-07', 'date2' => '2012-01-07', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-10', 'date2' => '2012-01-10', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-11', 'date2' => '2012-01-11', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-12', 'date2' => '2012-01-12', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-08', 'period' => 2, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-09', 'date2' => '2012-01-15', 'period' => 2, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
            ]
        ];

        yield "Invalidating a single day works for all segments, multiple websites" => [
            [
                '--dates' => ['2012-01-01'],
                '--sites' => '1,2',
            ],
            [
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
                ['name' => 'done', 'idsite' => 2, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done', 'idsite' => 2, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done', 'idsite' => 2, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done', 'idsite' => 2, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 2, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 2, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 2, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 2, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
                // second segment is only valid for idsite 1, so missing here
            ]
        ];

        yield "Invalidating a week period works for specific segment only, one website" => [
            [
                '--dates' => ['2012-01-08'],
                '--sites' => '1',
                '--periods' => 'week',
                '--segment' => ['browserCode==IE'],
            ],
            [
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-08', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
            ]
        ];

        yield "Invalidating a week period accross years should not cascade up, one website" => [
            [
                '--dates' => ['2012-01-01'],
                '--sites' => '1',
                '--periods' => 'week',
                '--segment' => ['browserCode==IE'],
            ],
            [
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
            ]
        ];

        yield "Invalidating a week period accross months should not cascade up, one website" => [
            [
                '--dates' => ['2012-01-31'],
                '--sites' => '1',
                '--periods' => 'week',
                '--segment' => ['browserCode==IE'],
            ],
            [
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-30', 'date2' => '2012-02-05', 'period' => 2, 'report' => null],
            ]
        ];

        yield "Invalidating a month period works for specific segment only, one website" => [
            [
                '--dates' => ['2012-01-01'],
                '--sites' => '1',
                '--periods' => 'month',
                '--segment' => ['browserCode==IE'],
            ],
            [
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
            ]
        ];

        yield "Invalidating a year period works for specific segment only, one website" => [
            [
                '--dates' => ['2012-01-01'],
                '--sites' => '1',
                '--periods' => 'year',
                '--segment' => ['browserCode==IE'],
            ],
            [
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
            ]
        ];

        yield "Invalidating a month period works for specific segment only, one website, cascade down" => [
            [
                '--dates' => ['2012-01-01'],
                '--sites' => '1',
                '--periods' => 'month',
                '--cascade' => true,
                '--segment' => ['browserCode==IE'],
            ],
            [
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-02', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-03', 'date2' => '2012-01-03', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-04', 'date2' => '2012-01-04', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-05', 'date2' => '2012-01-05', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-06', 'date2' => '2012-01-06', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-07', 'date2' => '2012-01-07', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-08', 'date2' => '2012-01-08', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-09', 'date2' => '2012-01-09', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-10', 'date2' => '2012-01-10', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-11', 'date2' => '2012-01-11', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-12', 'date2' => '2012-01-12', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-13', 'date2' => '2012-01-13', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-14', 'date2' => '2012-01-14', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-15', 'date2' => '2012-01-15', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-16', 'date2' => '2012-01-16', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-17', 'date2' => '2012-01-17', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-18', 'date2' => '2012-01-18', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-19', 'date2' => '2012-01-19', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-20', 'date2' => '2012-01-20', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-21', 'date2' => '2012-01-21', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-22', 'date2' => '2012-01-22', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-23', 'date2' => '2012-01-23', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-24', 'date2' => '2012-01-24', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-25', 'date2' => '2012-01-25', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-26', 'date2' => '2012-01-26', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-27', 'date2' => '2012-01-27', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-28', 'date2' => '2012-01-28', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-29', 'date2' => '2012-01-29', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-30', 'date2' => '2012-01-30', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-31', 'date2' => '2012-01-31', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-08', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-09', 'date2' => '2012-01-15', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-16', 'date2' => '2012-01-22', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-23', 'date2' => '2012-01-29', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-30', 'date2' => '2012-02-05', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
            ]
        ];

        yield "Invalidating a week period accross years should not cascade up, one website, cascade down" => [
            [
                '--dates' => ['2012-01-01'],
                '--sites' => '1',
                '--periods' => 'week',
                '--cascade' => true,
                '--segment' => ['browserCode==IE'],
            ],
            [
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2011-12-26', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-27', 'date2' => '2011-12-27', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-28', 'date2' => '2011-12-28', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-29', 'date2' => '2011-12-29', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-30', 'date2' => '2011-12-30', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-31', 'date2' => '2011-12-31', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-01', 'date2' => '2011-12-31', 'period' => 3, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-01-01', 'date2' => '2011-12-31', 'period' => 4, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
            ]
        ];

        yield "Invalidating a week period should not invalidate dates before website creation, one website, cascade down" => [
            [
                '--dates' => ['2012-01-01'],
                '--sites' => '2',
                '--periods' => 'week',
                '--cascade' => true,
                '--segment' => ['browserCode==IE'],
            ],
            [
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 2, 'date1' => '2012-01-01', 'date2' => '2012-01-01', 'period' => 1, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 2, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 2, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 2, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
            ]
        ];

        yield "Invalidating multiple periods works for specific segment only, one website, no cascade" => [
            [
                '--dates' => ['2012-01-01'],
                '--sites' => '1',
                '--periods' => 'week,year',
                '--segment' => ['browserCode==IE'],
            ],
            [
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
            ]
        ];

        yield "Invalidating plugin for multiple periods works for specific multiple segments, multiple websites, no cascade" => [
            [
                '--dates' => ['2012-01-01', '2012-01-06,2012-01-07'],
                '--sites' => '1,2',
                '--periods' => 'week,year',
                '--segment' => ['browserCode==IE', 'dimension1==test'],
            ],
            [
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-08', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-02', 'date2' => '2012-01-08', 'period' => 2, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done9aedf9b6022140586347897209404279', 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 2, 'date1' => '2011-12-26', 'date2' => '2012-01-01', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 2, 'date1' => '2012-01-02', 'date2' => '2012-01-08', 'period' => 2, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 2, 'date1' => '2012-01-01', 'date2' => '2012-01-31', 'period' => 3, 'report' => null],
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 2, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'report' => null],
            ]
        ];
    }

    public function testCommandInvalidateDateRange()
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
        self::assertStringContainsString("Invalidating range periods overlapping 2019-01-01,2019-01-09 for site = [ 1 ], segment = [  ]", $this->getLogOutput());
        self::assertStringContainsString("Invalidating range periods overlapping 2019-01-01,2019-01-09 for site = [ 1 ], segment = [ browserCode==IE ]", $this->getLogOutput());
        self::assertStringContainsString("Invalidating range periods overlapping 2019-01-01,2019-01-09 for site = [ 1 ], segment = [ dimension1==test ]", $this->getLogOutput());
    }

    public function testCommandInvalidateDateRangeInvalidDate()
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

    public function testCommandInvalidateDateRangeOnlyOneDate()
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

    public function testCommandInvalidateDateRangeInvalidateAllPeriodTypesSkipsRangeWhenNotRangeDAte()
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

    private static function assertInvalidationsPresent(array $expectedInvalidations): void
    {
        $existingInvalidations = Db::fetchAll('SELECT name, idsite, date1, date2, period, report from ' . Common::prefixTable('archive_invalidations') . ' ORDER BY idsite, name, period, date1');

        self::assertEquals($expectedInvalidations, $existingInvalidations);
    }
}
