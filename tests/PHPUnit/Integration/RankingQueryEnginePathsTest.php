<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Db\RollupQuery;
use Piwik\Db\Schema;
use Piwik\RankingQuery;
use Piwik\Tests\Framework\ForcedQueryCapabilities;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Runs ranking and rollup queries through the window-function and emulated-rollup paths on MySQL
 * and MariaDB, and compares the rows with the user-variable and `WITH ROLLUP` paths these engines
 * normally use.
 *
 * @group Core
 * @group RankingQuery
 */
class RankingQueryEnginePathsTest extends IntegrationTestCase
{
    private const PATHS = [
        'window'           => [true, true],
        'emulated rollup'  => [false, false],
        'window, emulated' => [true, false],
    ];

    /** @var string */
    private $table;

    public function setUp(): void
    {
        parent::setUp();

        $this->table = Common::prefixTable('ranking_paths');

        Db::exec("DROP TABLE IF EXISTS `{$this->table}`");
        Db::exec("CREATE TABLE `{$this->table}` (
            label VARCHAR(20) NOT NULL,
            sublabel VARCHAR(20) NOT NULL,
            visitor INT NOT NULL,
            score INT NOT NULL,
            flag INT NULL,
            part INT NULL
        )");

        $rows = [
            // label, sublabel, visitor, score, flag, part
            ['a', 'x', 1, 50, null, 1],
            ['a', 'y', 2, 20, 0, 1],
            ['a', 'z', 1, 5, 0, 2],
            ['b', 'x', 1, 30, 0, 1],
            ['b', 'y', 3, 30, 1, 2],
            ['c', 'x', 2, 25, 0, null],
            ['c', 'y', 4, 4, 2, 3],
            ['d', 'x', 3, 25, 0, 2],
            ['e', 'x', 5, 3, null, 1],
            ['e', 'y', 5, 2, 0, 2],
            ['f', 'x', 6, 7, 0, null],
            ['f', 'y', 6, 6, 0, 3],
        ];
        foreach ($rows as $row) {
            Db::query("INSERT INTO `{$this->table}` VALUES (?, ?, ?, ?, ?, ?)", $row);
        }
    }

    public function tearDown(): void
    {
        Db::exec("DROP TABLE IF EXISTS `{$this->table}`");

        parent::tearDown();
    }

    /**
     * @dataProvider getRankingCases
     */
    public function testWindowCountersMatchUserVariableCounters(callable $configure, string $innerQuery): void
    {
        $innerQuery = sprintf($innerQuery, $this->table);
        $run = function () use ($configure, $innerQuery): array {
            $query = new RankingQuery(2);
            $query->setOthersLabel('Others');
            $configure($query);

            return Db::fetchAll($query->generateRankingQuery($innerQuery));
        };

        $expected = $run();
        $actual = $this->withCapabilities(true, true, $run);

        $this->assertNotEmpty($expected);
        $this->assertSame($this->sorted($expected), $this->sorted($actual));
    }

    /**
     * The inner queries aggregate, like Matomo's: MariaDB ignores the order of a derived table
     * that doesn't, and the user-variable counters then count rows in storage order.
     */
    public function getRankingCases(): iterable
    {
        $grouped = 'SELECT label, SUM(score) AS score FROM `%s` GROUP BY label ORDER BY score DESC, label';

        yield 'plain' => [
            function (RankingQuery $query) {
                $query->addLabelColumn('label');
                $query->addColumn('score', 'sum');
            },
            $grouped,
        ];

        yield 'ties at the cutoff, broken by the inner order' => [
            function (RankingQuery $query) {
                $query->addLabelColumn('label');
                $query->addColumn('score', 'sum');
            },
            'SELECT CONCAT(label, sublabel) AS label, SUM(score) AS score FROM `%s` GROUP BY CONCAT(label, sublabel) ORDER BY score DESC, label',
        ];

        yield 'excluded rows, including a NULL mark' => [
            function (RankingQuery $query) {
                $query->addLabelColumn('label');
                $query->addColumn('score', 'sum');
                $query->setColumnToMarkExcludedRows('flag');
            },
            'SELECT CONCAT(label, sublabel) AS label, SUM(score) AS score, MAX(flag) AS flag FROM `%s` GROUP BY CONCAT(label, sublabel) ORDER BY score DESC, label',
        ];

        yield 'partitions, including NULL and values outside the set' => [
            function (RankingQuery $query) {
                $query->addLabelColumn('label');
                $query->addColumn('score', 'sum');
                $query->partitionResultIntoMultipleGroups('part', [1, 2]);
            },
            'SELECT CONCAT(label, sublabel) AS label, SUM(score) AS score, MAX(part) AS part FROM `%s` GROUP BY CONCAT(label, sublabel) ORDER BY score DESC, label',
        ];

        yield 'inner order by a qualified column selected under another name' => [
            function (RankingQuery $query) {
                $query->addLabelColumn('label');
                $query->addColumn('score', 'sum');
            },
            'SELECT t.sublabel AS label, SUM(t.score) AS score FROM `%s` t GROUP BY t.sublabel ORDER BY score DESC, t.sublabel',
        ];

        yield 'qualified inner order' => [
            function (RankingQuery $query) {
                $query->addLabelColumn('label');
                $query->addColumn('score', 'sum');
            },
            'SELECT t.label, SUM(t.score) AS score FROM `%s` t GROUP BY t.label ORDER BY score DESC, t.label',
        ];

        yield 'qualified inner order by a column whose name another result column has' => [
            function (RankingQuery $query) {
                $query->addLabelColumn('label');
                $query->addColumn('score', 'sum');
            },
            'SELECT t.sublabel AS label, label AS sublabel, SUM(t.score) AS score FROM `%s` t GROUP BY t.sublabel, t.label ORDER BY t.label DESC, t.sublabel DESC',
        ];

        yield 'bare inner order by a column selected under another name' => [
            function (RankingQuery $query) {
                $query->addLabelColumn('name');
                $query->addColumn('score', 'sum');
            },
            'SELECT label AS name, SUM(score) AS score FROM `%s` GROUP BY label ORDER BY label DESC',
        ];

        yield 'inner order by a column selected twice' => [
            function (RankingQuery $query) {
                $query->addLabelColumn('label');
                $query->addColumn('score', 'sum');
            },
            'SELECT t.label, t.label AS copy, SUM(t.score) AS score FROM `%s` t GROUP BY t.label ORDER BY score DESC, label',
        ];

        yield 'inner order by a qualified numeric column selected under another name' => [
            function (RankingQuery $query) {
                $query->addLabelColumn('label');
                $query->addColumn('total', 'sum');
            },
            'SELECT s.label, s.`1` AS total FROM (SELECT label, SUM(score) AS `1` FROM `%s` GROUP BY label) s ORDER BY s.`1` DESC, s.label',
        ];

        yield 'inner order by a column of a starred table' => [
            function (RankingQuery $query) {
                $query->addLabelColumn('label');
                $query->addColumn('score', 'sum');
            },
            'SELECT s.* FROM (SELECT label, SUM(score) AS score FROM `%s` GROUP BY label) s ORDER BY score DESC, label',
        ];

        yield 'inner order after a sorted subquery' => [
            function (RankingQuery $query) {
                $query->addLabelColumn('label');
                $query->addColumn('score', 'sum');
            },
            'SELECT s.label, SUM(s.score) AS score FROM (SELECT label, score FROM `%s` ORDER BY NULL) s GROUP BY s.label ORDER BY score DESC, s.label',
        ];
    }

    public function testWindowCountersSortByAResultNameNoOtherColumnHas(): void
    {
        $query = new RankingQuery(2);
        $query->addLabelColumn('copy');
        $query->addColumn('score', 'sum');
        $innerQuery = "SELECT score AS val, t.label AS val, t.label AS copy FROM `{$this->table}` t ORDER BY %s";

        $sql = $this->withCapabilities(true, true, function () use ($query, $innerQuery) {
            return $query->generateRankingQuery(sprintf($innerQuery, 't.label DESC'));
        });
        $this->assertStringContainsString('ORDER BY `copy` DESC', $sql);

        $this->expectException(\Exception::class);
        $this->withCapabilities(true, true, function () use ($query, $innerQuery) {
            return $query->generateRankingQuery(sprintf($innerQuery, 'val DESC'));
        });
    }

    public function testWindowCountersIgnoreTheSortOfASubquery(): void
    {
        $run = function (): array {
            // only the subquery is sorted, as in Matomo's wrapped segment queries
            $query = new RankingQuery(100);
            $query->setOthersLabel('Others');
            $query->addLabelColumn('label');
            $query->addColumn('score', 'sum');

            return Db::fetchAll($query->generateRankingQuery(
                "SELECT s.label, SUM(s.score) AS score FROM (SELECT label, score FROM `{$this->table}` ORDER BY NULL) s GROUP BY s.label"
            ));
        };

        $expected = $run();
        $actual = $this->withCapabilities(true, true, $run);

        $this->assertNotEmpty($expected);
        $this->assertSame($this->sorted($expected), $this->sorted($actual));
    }

    /**
     * @dataProvider getPathNames
     */
    public function testRollupRankingMatchesNativeRollupAndUserVariables(string $path): void
    {
        [$window, $rollup] = self::PATHS[$path];

        $run = function (): array {
            $select = 'label, sublabel, COUNT(DISTINCT visitor) AS visitors, SUM(score) AS score';
            $from = "`{$this->table}`";
            $where = 'score > ?';
            $groupBy = 'label, sublabel';
            $bind = [2];

            if (Schema::getInstance()->supportsGroupByWithRollup()) {
                $sql = "SELECT $select FROM $from WHERE $where GROUP BY $groupBy WITH ROLLUP";
            } else {
                $rollup = RollupQuery::build($select, $from, $where, $groupBy, $bind);
                $sql = $rollup['sql'];
                $bind = $rollup['bind'];
            }
            $sql = "SELECT * FROM ($sql) AS rollupQuery ORDER BY score DESC, label, sublabel";

            $query = new RankingQuery(2);
            $query->setOthersLabel('Others');
            $query->addLabelColumn(['label', 'sublabel']);
            $query->addColumn(['visitors', 'score'], 'sum');

            return Db::fetchAll($query->generateRankingQuery($sql, true), $bind);
        };

        $expected = $run();
        $actual = $this->withCapabilities($window, $rollup, $run);

        $this->assertNotEmpty($expected);
        $this->assertSame($this->sorted($expected), $this->sorted($actual));
    }

    public function testWindowRollupCounterIsSharedByPartitions(): void
    {
        $run = function (): array {
            $sql = "SELECT * FROM (
                SELECT label, sublabel, CASE WHEN label IN ('a', 'c', 'e') THEN 1 ELSE 2 END AS part, SUM(score) AS score
                FROM `{$this->table}` GROUP BY label, sublabel WITH ROLLUP
            ) AS rollupQuery ORDER BY score DESC, label, sublabel";

            $query = new RankingQuery(1);
            $query->setOthersLabel('Others');
            $query->addLabelColumn(['label', 'sublabel']);
            $query->addColumn('score', 'sum');
            $query->partitionResultIntoMultipleGroups('part', [1, 2]);

            return Db::fetchAll($query->generateRankingQuery($sql, true));
        };

        $expected = $run();
        $actual = $this->withCapabilities(true, true, $run);

        $this->assertNotEmpty($expected);
        $this->assertSame($this->sorted($expected), $this->sorted($actual));
    }

    /**
     * @dataProvider getUnsupportedWindowOrders
     */
    public function testWindowCountersRejectAnInnerOrderTheyCannotApply(string $orderBy): void
    {
        $query = new RankingQuery(1);
        $query->addLabelColumn('label');
        $query->addColumn('hits', 'sum');
        $innerQuery = "SELECT label, COUNT(*) AS hits FROM `{$this->table}` GROUP BY label ORDER BY $orderBy";

        $this->expectException(\Exception::class);
        $this->withCapabilities(true, true, function () use ($query, $innerQuery) {
            return $query->generateRankingQuery($innerQuery);
        });
    }

    public function getUnsupportedWindowOrders(): iterable
    {
        yield 'aggregate' => ['COUNT(*) DESC'];
        yield 'binding' => ['ABS(hits - ?)'];
        yield 'literal' => ["'a.b'"];
    }

    public function testEmulatedRollupMatchesNativeRollup(): void
    {
        $select = "label, COALESCE(NULLIF(sublabel, 'z'), '') AS sub, COUNT(DISTINCT visitor) AS visitors, COUNT(*) AS hits";
        $native = Db::fetchAll(
            "SELECT $select FROM `{$this->table}` WHERE score > ? GROUP BY label, sub WITH ROLLUP",
            [2]
        );
        $emulated = RollupQuery::build($select, "`{$this->table}`", 'score > ?', 'label, sub', [2]);

        $this->assertSame($this->sorted($native), $this->sorted(Db::fetchAll($emulated['sql'], $emulated['bind'])));
    }

    public function testEmulatedRollupReturnsNoGrandTotalWithoutRows(): void
    {
        $emulated = RollupQuery::build('label, COUNT(*) AS hits', "`{$this->table}`", 'score > ?', 'label', [1000]);

        $this->assertSame([], Db::fetchAll($emulated['sql'], $emulated['bind']));
        $this->assertSame([], Db::fetchAll("SELECT label, COUNT(*) AS hits FROM `{$this->table}` WHERE score > ? GROUP BY label WITH ROLLUP", [1000]));
    }

    public function getPathNames(): iterable
    {
        foreach (array_keys(self::PATHS) as $path) {
            yield $path => [$path];
        }
    }

    private function withCapabilities(bool $windowFunctions, bool $rollup, callable $callback)
    {
        return ForcedQueryCapabilities::run($windowFunctions, $rollup, $callback);
    }

    private function sorted(array $rows): array
    {
        $rows = array_map(function (array $row): string {
            return json_encode(array_map('strval', array_map(function ($value) {
                return $value ?? 'NULL';
            }, $row)));
        }, $rows);
        sort($rows);

        return $rows;
    }
}
