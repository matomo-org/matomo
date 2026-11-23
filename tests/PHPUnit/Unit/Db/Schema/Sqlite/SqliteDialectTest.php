<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Db\Schema\Sqlite;

use PDO;
use PHPUnit\Framework\TestCase;
use Piwik\Db\Schema\Sqlite;
use Piwik\Tests\Unit\Db\Sqlite\RequiresSqlite;

/**
 * The SQLite dialect fragments, run against a real SQLite database where they have a behaviour to check.
 */
class SqliteDialectTest extends TestCase
{
    use RequiresSqlite;

    /**
     * @var Sqlite
     */
    private $schema;

    /**
     * @var PDO
     */
    private $pdo;

    public function setUp(): void
    {
        parent::setUp();
        $this->requireSqlite();
        $this->schema = new Sqlite();
        $this->pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->pdo->exec('CREATE TABLE t (k TEXT PRIMARY KEY, v INTEGER NOT NULL)');
        $this->pdo->exec("INSERT INTO t VALUES ('a', 1)");
    }

    public function testInsertIgnoreSkipsConflictingRows(): void
    {
        $this->pdo->exec($this->schema->getInsertIgnoreSql() . " INTO t VALUES ('a', 2), ('b', 3)");

        $this->assertSame([['a', 1], ['b', 3]], $this->fetchAll());
    }

    public function testUpsertUpdatesTheConflictingRowWithTheInsertedValue(): void
    {
        $this->pdo->exec(
            "INSERT INTO t VALUES ('a', 5), ('c', 7) " . $this->schema->getOnDuplicateKeyUpdateSql()
            . ' v = v + ' . $this->schema->getInsertedValueSql('v')
        );

        $this->assertSame([['a', 6], ['c', 7]], $this->fetchAll());
    }

    public function testGroupConcatOrdersAndSeparates(): void
    {
        $this->pdo->exec("INSERT INTO t VALUES ('b', 2), ('c', 3)");

        $sql = 'SELECT ' . $this->schema->getGroupConcatSql("k || '.' || v", 'v DESC', '|') . ' FROM t';
        $this->assertSame('c.3|b.2|a.1', $this->pdo->query($sql)->fetchColumn());
        $this->assertSame('a,b,c', $this->pdo->query('SELECT ' . $this->schema->getGroupConcatSql('k', 'k') . ' FROM t')->fetchColumn());
    }

    public function testLikeEscapeMakesBackslashEscapeWildcards(): void
    {
        $this->pdo->exec("INSERT INTO t VALUES ('a_b', 2), ('axb', 3), ('a%', 4), ('abc', 5)");

        $sql = 'SELECT k FROM t WHERE k LIKE ?' . $this->schema->getLikeEscapeSql() . ' ORDER BY k';
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['a\_b']);
        $this->assertSame(['a_b'], $statement->fetchAll(PDO::FETCH_COLUMN));
        $statement->execute(['a\%']);
        $this->assertSame(['a%'], $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    public function testDateTimeIntervalsKeepMysqlsDatetimeFormat(): void
    {
        $add = $this->schema->getDateTimeIntervalSql('?', '+', '?', 'SECOND');
        $subtract = $this->schema->getDateTimeIntervalSql('?', '-', '?', 'DAY');
        $statement = $this->pdo->prepare("SELECT $add, $subtract");
        $statement->execute(['2024-02-28 23:59:30', 45, '2024-03-01 10:00:00', 2]);

        $this->assertSame(['2024-02-29 00:00:15', '2024-02-28 10:00:00'], $statement->fetch(PDO::FETCH_NUM));
        // a column amount, compared as MySQL compares DATETIME values
        $this->assertSame(1, (int) $this->pdo->query("SELECT datetime('2024-01-01 00:00:10') < "
            . $this->schema->getDateTimeIntervalSql("'2024-01-01 00:00:00'", '+', '(5 + 6)', 'second'))->fetchColumn());
    }

    public function testScalarFunctionsReturnWhatMysqlReturns(): void
    {
        $sql = 'SELECT ' . implode(', ', [
            $this->schema->getHourSql('?'),
            $this->schema->getDayOfWeekSql('?'),
            $this->schema->getUnixTimestampSql('?'),
            $this->schema->getCharLengthSql('?'),
            $this->schema->getSecondsBetweenSql('?', '?'),
            $this->schema->getLeastSql('1800', $this->schema->getGreatestSql('5', 'CAST(? AS UNSIGNED)')),
        ]);
        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            '2024-03-03 07:05:00', // a Sunday: DAYOFWEEK() is 1
            '2024-03-03 07:05:00',
            '1970-01-02 00:00:00',
            'é€x',
            '2024-02-28 23:59:30',
            '2024-03-01 00:00:00',
            '30', // bound as text, as emulated prepares send it
        ]);

        $this->assertSame([7, 1, 86400, 3, 86430, 30], array_map('intval', $statement->fetch(PDO::FETCH_NUM)));
        $this->assertSame('integer', $this->pdo->query('SELECT typeof(' . $this->schema->getHourSql("'2024-03-03 07:05:00'") . ')')->fetchColumn());
        $this->assertSame(1, (int) $this->pdo->query('SELECT abs(' . $this->schema->getUnixTimestampSql()
            . ' - ' . time() . ') <= 2')->fetchColumn());
        $this->assertMatchesRegularExpression('/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', $this->pdo->query('SELECT '
            . $this->schema->getCurrentDateTimeSql())->fetchColumn());
    }

    public function testDayOfWeekAndHourCompareWithBoundStringsAsNumbers(): void
    {
        $this->pdo->exec("CREATE TABLE v (d TEXT)");
        $this->pdo->exec("INSERT INTO v VALUES ('2024-03-03 07:00:00'), ('2024-03-04 23:00:00')"); // Sunday, Monday
        $count = function (string $condition, string $value): int {
            $statement = $this->pdo->prepare('SELECT COUNT(*) FROM v WHERE ' . $condition);
            $statement->execute([$value]);
            return (int) $statement->fetchColumn();
        };

        $this->assertSame(1, $count($this->schema->getDayOfWeekSql('d') . ' = ?', '1'));
        $this->assertSame(1, $count($this->schema->getDayOfWeekSql('d') . ' < ?', '2'));
        $this->assertSame(1, $count($this->schema->getHourSql('d') . ' = ?', '7'));
        $this->assertSame(2, $count($this->schema->getHourSql('d') . ' >= ?', '7'));
    }

    public function testStraightJoinKeepsTheTableOrder(): void
    {
        $this->pdo->exec('CREATE TABLE a (id INTEGER PRIMARY KEY, k INTEGER)');
        $this->pdo->exec('CREATE TABLE b (id INTEGER PRIMARY KEY, a_id INTEGER)');
        $this->pdo->exec('INSERT INTO a VALUES (1, 10), (2, 20)');
        $this->pdo->exec('INSERT INTO b VALUES (1, 2), (2, 2), (3, 1)');
        $sql = 'SELECT a.k, COUNT(*) FROM a ' . $this->schema->getStraightJoinSql() . ' b ON a.id = b.a_id GROUP BY a.k ORDER BY a.k';
        $this->assertSame([[10, 1], [20, 2]], $this->pdo->query($sql)->fetchAll(\PDO::FETCH_NUM));

        $plan = $this->pdo->query('EXPLAIN QUERY PLAN ' . $sql)->fetchAll(\PDO::FETCH_COLUMN, 3);
        $this->assertMatchesRegularExpression('/^SCAN a\b/', $plan[0], 'the left table is read first');
    }

    public function testNullSafeEqualityAndSelectWithoutTable(): void
    {
        $sql = 'SELECT ' . $this->schema->getNullSafeEqualSql('?', '?') . $this->schema->getNoTableSql() . ' WHERE 1 = 1';
        $statement = $this->pdo->prepare($sql);
        foreach ([[null, null, 1], [null, 'a', 0], ['a', 'a', 1], ['a', 'b', 0]] as [$left, $right, $expected]) {
            $statement->execute([$left, $right]);
            $this->assertSame($expected, (int) $statement->fetchColumn());
        }

        // as MySQL's 2 <=> TRUE, a boolean literal is the value 1, not a truth test
        foreach ([['2', 'TRUE', 0], ['1', 'TRUE', 1], ['0', 'FALSE', 1], ['2', 'FALSE', 0], ['NULL', 'FALSE', 0], ['2', '(TRUE)', 0], ['2', ' ( ( true ) ) ', 0], ['1', '(TRUE)', 1], ['(0)', '(FALSE)', 1]] as [$left, $right, $expected]) {
            $value = $this->pdo->query('SELECT ' . $this->schema->getNullSafeEqualSql($left, $right))->fetchColumn();
            $this->assertSame($expected, (int) $value, $left . ' <=> ' . $right);
        }

        // a column's affinity applies whichever side it is on, as for any comparison
        $this->pdo->exec("CREATE TABLE affinity (s TEXT, n INTEGER)");
        $this->pdo->exec("INSERT INTO affinity VALUES ('01', 1)");
        foreach ([['s', 'n'], ['n', 's'], ['n', '?'], ['?', 'n']] as [$left, $right]) {
            $statement = $this->pdo->prepare('SELECT ' . $this->schema->getNullSafeEqualSql($left, $right) . ' FROM affinity');
            $statement->execute(strpos($left . $right, '?') !== false ? ['1'] : []);
            $this->assertSame(1, (int) $statement->fetchColumn(), $left . ' <=> ' . $right);
        }
    }

    public function testRowLocksAndTruncate(): void
    {
        $this->assertSame('', $this->schema->getRowLockSql());
        $this->assertSame('', $this->schema->getRowLockSql(false));

        $this->pdo->exec("INSERT INTO t VALUES ('b', 2)");
        $this->pdo->exec($this->schema->getTruncateTableSql('t'));
        $this->assertSame(0, (int) $this->pdo->query('SELECT COUNT(*) FROM t')->fetchColumn());

        $this->expectException(\InvalidArgumentException::class);
        $this->schema->getLeastSql('a');
    }

    public function testLongerIntervalsAreRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->schema->getDateTimeIntervalSql('?', '+', '1', 'MONTH');
    }

    public function testNoIndexHint(): void
    {
        $this->assertSame('', $this->schema->getIndexHintSql('index_idsite_idvisitor_time'));
        $this->assertSame('', $this->schema->getIndexHintSql('index_idsite_datetime', false));
    }

    /**
     * @dataProvider getLikePatterns
     */
    public function testLikePatternRegexMatchesAsShowTablesLike(string $pattern, array $expected): void
    {
        $tables = ['matomo_archive_numeric_2026_01', 'matomo_archive_blob_2026_01', 'matomo_archiveXnumeric', 'Matomo_archive_numeric_2026_01', 'matomo_log_visit'];
        $regex = Sqlite::getLikePatternRegex($pattern);

        $this->assertSame($expected, array_values(array_filter($tables, function ($table) use ($regex) {
            return preg_match($regex, $table) === 1;
        })));
    }

    public function getLikePatterns(): iterable
    {
        yield 'wildcards' => ['matomo_archive_%', ['matomo_archive_numeric_2026_01', 'matomo_archive_blob_2026_01', 'matomo_archiveXnumeric']];
        yield 'escaped underscores' => ['matomo\_archive\_%', ['matomo_archive_numeric_2026_01', 'matomo_archive_blob_2026_01']];
        yield 'exact name, case-sensitive' => ['matomo\_log\_visit', ['matomo_log_visit']];
        yield 'regex characters are literal' => ['matomo.log.visit', []];
    }

    public function testCapabilities(): void
    {
        $this->assertTrue($this->schema->supportsWindowFunctions());
        $this->assertFalse($this->schema->supportsGroupByWithRollup());
        $this->assertSame('SERIALIZABLE', $this->schema->getSupportedReadIsolationTransactionLevel());
    }

    private function fetchAll(): array
    {
        return array_map(function ($row) {
            return [$row[0], (int) $row[1]];
        }, $this->pdo->query('SELECT k, v FROM t ORDER BY k')->fetchAll(PDO::FETCH_NUM));
    }
}
