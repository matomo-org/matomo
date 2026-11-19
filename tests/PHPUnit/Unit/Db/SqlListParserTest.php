<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Db;

use Piwik\Db\SqlListParser;

/**
 * @group Core
 * @group Db
 */
class SqlListParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getOrderByCases
     */
    public function testReadsTheOrderByOfTheOutermostQuery(string $query, ?string $expected): void
    {
        $this->assertSame($expected, SqlListParser::getOrderBy($query));
    }

    public function getOrderByCases(): iterable
    {
        yield 'plain' => ['SELECT a FROM t ORDER BY a DESC, b', 'a DESC, b'];
        yield 'before LIMIT' => ['SELECT a FROM t ORDER BY `1` DESC LIMIT 5', '`1` DESC'];
        yield 'only in a subquery' => ['SELECT a FROM (SELECT a FROM t ORDER BY NULL) s GROUP BY a', null];
        yield 'after a subquery' => ['SELECT a FROM (SELECT a FROM t ORDER BY NULL) s ORDER BY a', 'a'];
        yield 'in a window' => ['SELECT SUM(n) OVER (ORDER BY b) AS s FROM t', null];
        yield 'in a literal' => ["SELECT a FROM t WHERE b = 'x ORDER BY y'", null];
        yield 'lower case' => ['select a from t order by a', 'a'];
        yield 'after an escape clause' => ["SELECT a FROM t WHERE a LIKE ? ESCAPE '\\' ORDER BY hits ASC", 'hits ASC'];
        yield 'in a comment' => ['SELECT a FROM t /* ORDER BY b */', null];
    }

    public function testNormalizesOutsideLiteralsOnly(): void
    {
        $this->assertSame("lower(t.a)='X b'", SqlListParser::normalize("LOWER( `T`.a ) = 'X b'"));
        $this->assertNotSame(SqlListParser::normalize("'x'"), SqlListParser::normalize("'X'"));
    }

    public function testSplitsListsAroundQuotesAndParentheses(): void
    {
        $this->assertSame(
            ["CONCAT(a, ',', b) AS ab", '`x,y` AS c', "'it''s, ok'", 'f(1, 2)'],
            SqlListParser::splitList("CONCAT(a, ',', b) AS ab, `x,y` AS c, 'it''s, ok', f(1, 2)")
        );
    }

    public function testTellsConstantsFromColumns(): void
    {
        $this->assertTrue(SqlListParser::isConstant("42"));
        $this->assertTrue(SqlListParser::isConstant("'label' "));
        $this->assertFalse(SqlListParser::isConstant('`42`'));
        $this->assertFalse(SqlListParser::isConstant('LOWER(label)'));
        $this->assertNotSame(SqlListParser::normalize('`42`'), SqlListParser::normalize('42'));
        $this->assertSame(SqlListParser::normalize('`T`.`a`'), SqlListParser::normalize('t.a'));
    }

    public function testCountsPlaceholdersOutsideQuotes(): void
    {
        $this->assertSame(2, SqlListParser::countPlaceholders("a = ? AND b = '?' AND c IN (?)"));
        $this->assertSame(2, SqlListParser::countPlaceholders("a LIKE ? ESCAPE '\\' AND b > ? /* ? */"));
        $this->assertSame(1, SqlListParser::countPlaceholders("a = 'it''s ?' AND b = ? -- ?"));
        $this->assertSame(1, SqlListParser::countPlaceholders("b = ? --comment ?"));
    }
}
