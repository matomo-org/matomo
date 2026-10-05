<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Db;

use Piwik\Db\QueryLog;

/**
 * @group Core
 * @group ClickHouse
 */
class QueryLogTest extends \PHPUnit\Framework\TestCase
{
    /** @var string */
    private $file = '';

    protected function tearDown(): void
    {
        putenv(QueryLog::ENV_FILE);
        putenv(QueryLog::ENV_CONTEXT);
        if ($this->file !== '' && file_exists($this->file)) {
            unlink($this->file);
        }

        parent::tearDown();
    }

    public function testInterpolatesPositionalValuesQuotedAsStrings()
    {
        $sql = QueryLog::interpolateMysql(
            $this->getQuotingAdapter(),
            'SELECT * FROM log_visit WHERE idsite = ? AND visit_last_action_time >= ? AND referer_name IS ?',
            [1, '2026-08-03 00:00:00', null]
        );

        self::assertSame(
            "SELECT * FROM log_visit WHERE idsite = '1' AND visit_last_action_time >= '2026-08-03 00:00:00' AND referer_name IS NULL",
            $sql
        );
    }

    public function testLeavesQuestionMarksInsideQuotesAlone()
    {
        $sql = QueryLog::interpolateMysql(
            $this->getQuotingAdapter(),
            "SELECT '?', \"it\\'s ?\", `a?` FROM t WHERE name = ?",
            ['x']
        );

        self::assertSame("SELECT '?', \"it\\'s ?\", `a?` FROM t WHERE name = 'x'", $sql);
    }

    public function testWritesBinaryValuesAsHexLiterals()
    {
        $sql = QueryLog::interpolateMysql($this->getQuotingAdapter(), 'SELECT 1 FROM log_visit WHERE idvisitor = ?', [hex2bin('00ff10a0b0c0d0e0')]);

        self::assertSame("SELECT 1 FROM log_visit WHERE idvisitor = X'00ff10a0b0c0d0e0'", $sql);
    }

    public function testAppendsValuesThatDoNotMatchThePlaceholders()
    {
        $sql = QueryLog::interpolateMysql($this->getQuotingAdapter(), 'SELECT ? FROM t', [1, 2]);

        self::assertSame('SELECT ? FROM t /* unbound values: [1,2] */', $sql);
    }

    public function testRecordsOneJsonLinePerStatementWithTheContext()
    {
        $this->enableLogFile();
        putenv(QueryLog::ENV_CONTEXT . '=' . json_encode(['case' => 'a1', 'step' => 'measured']));

        $adapter = $this->getQuotingAdapter();
        $result = QueryLog::runMysql($adapter, 'SELECT ?', [5], static fn() => 'result');
        QueryLog::record('clickhouse', $adapter, 'CREATE TEMPORARY TABLE t (idvisit UInt64) ENGINE = Memory', microtime(true), null);

        self::assertSame('result', $result);

        $lines = array_map('json_decode', file($this->file, FILE_IGNORE_NEW_LINES));
        self::assertCount(2, $lines);
        self::assertSame(['mysql', "SELECT '5'"], [$lines[0]->engine, $lines[0]->sql]);
        self::assertSame(['clickhouse', 'CREATE TEMPORARY TABLE t (idvisit UInt64) ENGINE = Memory'], [$lines[1]->engine, $lines[1]->sql]);
        self::assertSame(['case' => 'a1', 'step' => 'measured'], (array) $lines[0]->context);
        self::assertSame(getmypid(), $lines[0]->pid);
        self::assertNull($lines[0]->error);
    }

    public function testRecordsAFailedStatementAndRethrows()
    {
        $this->enableLogFile();

        try {
            QueryLog::runMysql($this->getQuotingAdapter(), 'SELECT nope', [], static function () {
                throw new \RuntimeException('Unknown column');
            });
            self::fail('The statement exception was swallowed');
        } catch (\RuntimeException $e) {
            self::assertSame('Unknown column', $e->getMessage());
        }

        $line = json_decode((string) file_get_contents($this->file));
        self::assertSame(['SELECT nope', 'Unknown column'], [$line->sql, $line->error]);
    }

    public function testWritesNothingWhenNoFileIsConfigured()
    {
        self::assertFalse(QueryLog::isEnabled());

        $this->file = (string) tempnam(sys_get_temp_dir(), 'querylog');
        QueryLog::record('mysql', $this->getQuotingAdapter(), 'SELECT 1', microtime(true), null);

        self::assertSame('', file_get_contents($this->file));
    }

    private function enableLogFile(): void
    {
        $this->file = (string) tempnam(sys_get_temp_dir(), 'querylog');
        putenv(QueryLog::ENV_FILE . '=' . $this->file);
    }

    private function getQuotingAdapter(): object
    {
        return new class {
            public function quote($value): string
            {
                return "'" . addslashes((string) $value) . "'";
            }
        };
    }
}
