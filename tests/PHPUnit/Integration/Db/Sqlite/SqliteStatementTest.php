<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Db\Sqlite;

use Piwik\Db;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Db
 * @group Sqlite
 */
class SqliteStatementTest extends IntegrationTestCase
{
    private const FAILS_ON_SECOND_ROW = "SELECT CASE WHEN x = 2 THEN json('{bad') ELSE x END AS v FROM (SELECT 1 AS x UNION ALL SELECT 2 UNION ALL SELECT 3)";

    public function setUp(): void
    {
        parent::setUp();

        if (!Fixture::isSqlite()) {
            $this->markTestSkipped('PDO SQLite fetchAll() behaviour');
        }
    }

    public function testFetchAllFailsInsteadOfReturningTheRowsBeforeAnError()
    {
        $this->expectException(\Zend_Db_Statement_Exception::class);
        $this->expectExceptionMessage('malformed JSON');

        Db::fetchAll(self::FAILS_ON_SECOND_ROW);
    }

    public function testFetchColFailsInsteadOfReturningTheRowsBeforeAnError()
    {
        $this->expectException(\Zend_Db_Statement_Exception::class);

        Db::get()->fetchCol(self::FAILS_ON_SECOND_ROW);
    }

    public function testFetchAllStillReturnsEveryRow()
    {
        $this->assertSame([['x' => 1], ['x' => 2]], Db::fetchAll('SELECT 1 AS x UNION ALL SELECT 2'));
    }
}
