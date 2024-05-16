<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Monolog\Handler\AbstractProcessingHandler;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive;
use Piwik\Date;
use Piwik\Plugins\CoreAdminHome\API as CoreAdminHomeAPI;
use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Piwik\Plugins\CustomDimensions\API as CustomDimensionsAPI;
use Piwik\Plugins\PrivacyManager\Model\DataSubjects;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorAPI;
use Piwik\Plugins\VisitFrequency\API as VisitFrequencyAPI;
use Piwik\Plugins\VisitsSummary\API as VisitsSummaryAPI;
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
    public function testCommandFailsWhenAnInvalidDateRangeIsUsed($invalidDateRange)
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
    public function testCommandFailsWhenAnInvalidPeriodTypeIsUsed($invalidPeriodType)
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
    public function testCommandFailsWhenAnInvalidSiteListIsUsed($invalidSites)
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

    public function testCommandFailsWhenAnInvalidSegmentIsUsed()
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

    public function testCommandFailsWhenACustomDimensionSegmentIsNotSupportedByAllSites()
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


    public function testCommandFailsWhenACustomDimensionSegmentIsNotValidForAnySite()
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
    public function testCommandInvalidatesCorrectSitesAndDates($dates, $periods, $sites, $cascade, $segments, $plugin, $expectedOutputs)
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
        self::assertStringContainsString("Invalidating range periods overlapping 2019-01-01,2019-01-09 [segment = ]", $this->getLogOutput());
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

    public function testCommandInvalidateDateRangeTooManyDatesInRange()
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

    public function testCommandInvalidateDateRangeMultipleDateRanges()
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

    public function testCommandInvalidateDateRangeInvalidateAllPeriodTypes()
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

    public function testCommandInvalidateAllMultipleDateRanges()
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

    public function testInvalidationOfDependentSegments()
    {
        $testDate = Date::today()->subDay(10)->toString();

        // disable browser archiving
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
        Config::getInstance()->General['browser_archiving_disabled_enforce'] = 1;

        SegmentEditorAPI::getInstance()->add('fr segment', 'languageCode==fr', 1, true);

        // track a visitor
        $t = Fixture::getTracker(1, $testDate . ' 12:00:00', true);
        $t->setUserAgent('Mozilla/5.0 (compatible; MSIE 10.0; Windows Vista; Trident/5.0');
        $t->setIp('10.11.12.13');
        $t->setUrl('http://piwik.net/randomsite');
        $t->doTrackPageView('random site');

        // With a returning visit
        $t->setForceVisitDateTime($testDate . ' 17:00:00');
        $t->setForceNewVisit();
        $t->doTrackPageView('random site');

        // track a second visitor
        $t = Fixture::getTracker(1, $testDate . ' 12:30:00', true);
        $t->setIp('20.21.22.23');
        $t->setUserAgent('Mozilla/5.0 (compatible; MSIE 10.0; Windows 8; Trident/5.0)');
        $t->setUrl('http://piwik.net/randomsite');
        $t->doTrackPageView('random site');

        $archiver = new CronArchive();
        $archiver->main();

        $result = VisitsSummaryAPI::getInstance()->get(1, 'week', $testDate, 'languageCode==fr');
        self::assertEquals(3, $result->getFirstRow()->getColumn('nb_visits'));

        $result = VisitFrequencyAPI::getInstance()->get(1, 'week', $testDate, 'languageCode==fr');
        self::assertEquals(1, $result->getFirstRow()->getColumn('nb_visits_returning'));
        self::assertEquals(2, $result->getFirstRow()->getColumn('nb_visits_new'));

        // Remove one visit
        $datasubject = StaticContainer::get(DataSubjects::class);
        $datasubject->deleteDataSubjectsWithoutInvalidatingArchives([['idvisit' => '1']]);

        // Invalidate the segment
        CoreAdminHomeAPI::getInstance()->invalidateArchivedReports('1', $testDate, 'day', 'languageCode==fr');

        // re-run archiving
        $archiver = new CronArchive();
        $archiver->main();

        $result = VisitsSummaryAPI::getInstance()->get(1, 'week', $testDate, 'languageCode==fr');
        self::assertEquals(2, $result->getFirstRow()->getColumn('nb_visits'));

        // check that metrics built with dependent segment archives are updated as well
        $result = VisitFrequencyAPI::getInstance()->get(1, 'week', $testDate, 'languageCode==fr');
        self::assertEquals(1, $result->getFirstRow()->getColumn('nb_visits_returning'));
        self::assertEquals(1, $result->getFirstRow()->getColumn('nb_visits_new'));
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
