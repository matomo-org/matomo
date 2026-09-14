<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataAccess;

use Piwik\ArchiveProcessor\Parameters;
use Piwik\Config\DatabaseConfig;
use Piwik\DataAccess\ArchivingDbAdapter;
use Piwik\DataAccess\LogAggregator;
use Piwik\Date;
use Piwik\Period\Factory;
use Piwik\Segment;
use Piwik\Tests\Framework\Mock\Site;
use Piwik\Tracker\GoalManager;

/**
 * @group Core
 */
class LogAggregatorTest extends \PHPUnit\Framework\TestCase
{
    protected function tearDown(): void
    {
        \Piwik\Config::getInstance()->PerfCorpus = [];
        parent::tearDown();
    }

    private function dateStartFor(string $configured): string
    {
        if ('' !== $configured) {
            \Piwik\Config::getInstance()->PerfCorpus = ['archiving_window_days' => $configured];
        }

        $aggregator = new LogAggregator(new Parameters(
            new Site(1),
            Factory::build('day', Date::factory('2026-08-03')),
            $this->createMock(Segment::class)
        ));

        $property = new \ReflectionProperty(LogAggregator::class, 'dateStart');
        $property->setAccessible(true);

        return $property->getValue($aggregator)->toString('Y-m-d');
    }

    /**
     * The benchmark widening is off unless it is explicitly asked for. It makes an archive hold
     * reports for a week while claiming to be a day, so anything other than off-by-default would
     * be a way to silently corrupt an install.
     *
     * @dataProvider getArchivingWindowValuesThatChangeNothing
     */
    public function testTheArchivingWindowIsOffUnlessAskedFor(string $configured)
    {
        self::assertSame('2026-08-03', $this->dateStartFor($configured));
    }

    public function getArchivingWindowValuesThatChangeNothing(): array
    {
        return [
            'unset' => [''],
            'empty' => ['0'],
            'one day is already the period' => ['1'],
            'negative is nonsense' => ['-5'],
            'not a number' => ['banana'],
        ];
    }

    /**
     * @dataProvider getArchivingWindowValuesThatWiden
     */
    public function testTheArchivingWindowWidensTheStartAndNothingElse(string $configured, string $expectedStart)
    {
        self::assertSame($expectedStart, $this->dateStartFor($configured));
    }

    public function getArchivingWindowValuesThatWiden(): array
    {
        return [
            'a week ending on the archived day' => ['7', '2026-07-28'],
            'two days' => ['2', '2026-08-02'],
            'a month' => ['30', '2026-07-05'],
        ];
    }

    /**
     * The point of widening in LogAggregator rather than in the period is that the archive keeps
     * its identity: the done flag, date1, date2 and period must still say "the day asked for", or
     * the archive is written somewhere nobody will look for it and an A/B comparison stops
     * comparing like with like.
     */
    public function testTheArchivingWindowLeavesTheArchiveIdentityAlone()
    {
        \Piwik\Config::getInstance()->PerfCorpus = ['archiving_window_days' => '7'];

        $params = new Parameters(
            new Site(1),
            Factory::build('day', Date::factory('2026-08-03')),
            $this->createMock(Segment::class)
        );
        new LogAggregator($params);

        self::assertSame('2026-08-03', $params->getPeriod()->getDateStart()->toString('Y-m-d'));
        self::assertSame('2026-08-03', $params->getPeriod()->getDateEnd()->toString('Y-m-d'));
        self::assertSame('day', $params->getPeriod()->getLabel());
    }

    public function testQueryConversionsByDimensionForcingIndexFlagJoinPrefixHint()
    {
        $expectedSql = 'SELECT /*+ JOIN_PREFIX(log_conversion) */ /* segmenthash  */ /* sites 1 */ ';
        $dbMock = $this->createMock(ArchivingDbAdapter::class);
        $dbMock->expects($this->once())->method('query')->with($this->stringContains($expectedSql), $this->equalTo([]));

        DatabaseConfig::setConfigValue('enable_first_table_join_prefix', 1);

        $segmentMock = $this->createMock(Segment::class);
        $segmentMock->expects($this->once())->method('getSelectQuery')
            ->with($this->anything(), $this->equalTo([['table' => 'log_conversion', 'useIndex' => 'index_idsite_datetime']]))
            ->willReturn(['sql' => 'SELECT * FROM log_visit', 'bind' => []]);

        $aggregatorMock = $this->createPartialMock(LogAggregator::class, ['getDb']);
        $aggregatorMock->expects($this->once())->method('getDb')->willReturn($dbMock);
        $aggregatorMock->__construct(new Parameters(new Site(1), Factory::build('day', Date::now()), $segmentMock));
        $aggregatorMock->queryConversionsByDimension([], '', [], [], false, false, true);
    }

    /**
     * @dataProvider getTestQueryConversionsByDimensionForcingIndexFlagTestData
     *
     * @return void
     */
    public function testQueryConversionsByDimensionForcingIndexFlag(bool $forceIndex)
    {
        $dimensions = ['custom_var_k1', 'custom_var_v1'];
        $where = "%s.custom_var_k1 != ''";
        $extraFrom = [
            [
                'table' => 'log_visit',
                'joinOn' => 'log_visit.idvisit = log_conversion.idvisit',
            ],
        ];

        $maxRevenue = GoalManager::MAX_ALLOWED_REVENUE;
        $expectedSelect = "log_conversion.idgoal AS `idgoal`, 
			log_conversion.custom_var_k1 AS `custom_var_k1`, 
			log_conversion.custom_var_v1 AS `custom_var_v1`, 
			count(*) AS `1`, 
			count(distinct log_conversion.idvisit) AS `3`, 
			ROUND(SUM(CASE WHEN ABS(log_conversion.revenue) > {$maxRevenue} THEN 0 ELSE log_conversion.revenue END),2) AS `2`, 
			ROUND(SUM(CASE WHEN ABS(log_conversion.revenue_subtotal) > {$maxRevenue} THEN 0 ELSE log_conversion.revenue_subtotal END),2) AS `4`, 
			ROUND(SUM(CASE WHEN ABS(log_conversion.revenue_tax) > {$maxRevenue} THEN 0 ELSE log_conversion.revenue_tax END),2) AS `5`, 
			ROUND(SUM(CASE WHEN ABS(log_conversion.revenue_shipping) > {$maxRevenue} THEN 0 ELSE log_conversion.revenue_shipping END),2) AS `6`, 
			ROUND(SUM(CASE WHEN ABS(log_conversion.revenue_discount) > {$maxRevenue} THEN 0 ELSE log_conversion.revenue_discount END),2) AS `7`, 
			SUM(log_conversion.items) AS `8`";
        $expectedFrom = $forceIndex
            ? [['table' => LogAggregator::LOG_CONVERSION_TABLE, 'useIndex' => 'index_idsite_datetime']]
            : [LogAggregator::LOG_CONVERSION_TABLE];
        $expectedFrom = array_merge($expectedFrom, $extraFrom);
        $expectedWhere = "log_conversion.server_time >= ?
				AND log_conversion.server_time <= ?
				AND log_conversion.idsite IN (?) AND log_conversion.custom_var_k1 != ''";

        $dbMock = $this->createMock(ArchivingDbAdapter::class);
        $dbMock->expects($this->once())->method('query');

        $aggregatorMock = $this->createPartialMock(LogAggregator::class, ['generateQuery', 'getDb']);
        $aggregatorMock->expects($this->once())->method('generateQuery')->with($expectedSelect, $expectedFrom, $expectedWhere)->willReturn(['sql' => '', 'bind' => []]);
        $aggregatorMock->expects($this->once())->method('getDb')->willReturn($dbMock);
        $aggregatorMock->setSites([1]);
        $aggregatorMock->queryConversionsByDimension($dimensions, $where, [], $extraFrom, false, false, $forceIndex);
    }

    public function getTestQueryConversionsByDimensionForcingIndexFlagTestData(): array
    {
        return [
            [false],
            [true],
        ];
    }
}
