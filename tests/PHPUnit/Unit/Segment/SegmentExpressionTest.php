<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Segment\SegmentExpression;

/**
 * @group SegmentExpressionTest
 * @group Segment
 */
class SegmentExpressionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Dataprovider for testSegmentSqlSimpleNoOperation
     * @return array
     */
    public function getSimpleSegmentExpressions()
    {
        return [
            // classic expressions
            ['A', "A"],
            ['A,B', "( A OR B)"],
            ['A;B', "A AND B"],
            ['A;B;C', "A AND B AND C"],
            ['A,B;C,D;E,F,G', "( A OR B) AND ( C OR D) AND ( E OR F OR G)"],

            // unescape the backslash
            ['A\,B\,C,D', "( A,B,C OR D)"],
            ['\,A', ',A'],
            // unescape only when it was escaping a known delimiter
            ['\\\A', '\\\A'],
            // unescape at the end
            ['\,\;\A\B,\,C,D\;E\,', '( ,;\A\B OR ,C OR D;E,)'],

            // only replace when a following expression is detected
            ['A,', 'A,'],
            ['A;', 'A;'],
            ['A;B;', 'A AND B;'],
            ['A,B,', '( A OR B,)'],
        ];
    }

    /**
     * @dataProvider getSimpleSegmentExpressions
     * @group Core
     */
    public function testSegmentSqlSimpleNoOperation($expression, $expectedSql)
    {
        $segment = new SegmentExpression($expression);
        $expected = ['where' => $expectedSql, 'bind' => []];
        $processed = $segment->getSql();
        $this->assertEquals($expected, $processed);
    }

    /**
     * Dataprovider for testSegmentSqlWithOperations
     * @return array
     */
    public function getOperationSegmentExpressions()
    {
        // Filter expression => SQL string + Bind values
        return [
            ['A==B%', ['where' => "A = ?", 'bind' => ['B%']]],
            ['ABCDEF====B===', ['where' => "ABCDEF = ?", 'bind' => ['==B===']]],
            ['A===B;CDEF!=C!=', ['where' => "A = ? AND ( CDEF IS NULL OR CDEF <> ? )", 'bind' => ['=B', 'C!=']]],
            ['A==B,C==D', ['where' => "( A = ? OR C = ?)", 'bind' => ['B', 'D']]],
            ['A!=B;C==D', ['where' => "( A IS NULL OR A <> ? ) AND C = ?", 'bind' => ['B', 'D']]],
            ['A!=B;C==D,E!=Hello World!=', ['where' => "( A IS NULL OR A <> ? ) AND ( C = ? OR ( E IS NULL OR E <> ? ))", 'bind' => ['B', 'D', 'Hello World!=']]],
            ['A=@B;C=$D', ['where' => "A LIKE ? AND C LIKE ?", 'bind' => ['%B%', '%D']]],

            ['A>B', ['where' => "A > ?", 'bind' => ['B']]],
            ['A<B', ['where' => "A < ?", 'bind' => ['B']]],
            ['A<=B', ['where' => "A <= ?", 'bind' => ['B']]],
            ['A>=B', ['where' => "A >= ?", 'bind' => ['B']]],
            ['ABCDEF>=>=>=B===', ['where' => "ABCDEF >= ?", 'bind' => ['>=>=B===']]],
            ['A>=<=B;CDEF>G;H>=I;J<K;L<=M', ['where' => "A >= ? AND CDEF > ? AND H >= ? AND J < ? AND L <= ?", 'bind' => ['<=B', 'G', 'I', 'K', 'M']]],
            ['A>=B;C>=D,E<w_ow great!', ['where' => "A >= ? AND ( C >= ? OR E < ?)", 'bind' => ['B', 'D', 'w_ow great!']]],

            ['A=@B_', ['where' => "A LIKE ?", 'bind' => ['%B\_%']]],
            ['A!@B%', ['where' => "( A IS NULL OR A NOT LIKE ? )", 'bind' => ['%B\%%']]],
            ['A=$B%', ['where' => "A LIKE ?", 'bind' => ['%B\%']]],
            ['A=^B%', ['where' => "A LIKE ?", 'bind' => ['B\%%']]],

            ['log_visit.A==3', ['where' => 'log_visit.A = ?', 'bind' => ['3']], [], ['log_visit']],
            ['log_visit.A==3;log_conversion.B>4', ['where' => 'log_visit.A = ? AND log_conversion.B > ?', 'bind' => ['3', '4']], [], ['log_visit', 'log_conversion']],
            ['(UNIX_TIMESTAMP(log_visit.A)-log_visit.B)==3', ['where' => '(UNIX_TIMESTAMP(log_visit.A)-log_visit.B) = ?', 'bind' => ['3']], ['log_conversion'], ['log_conversion', 'log_visit']],
            ['(UNIX_TIMESTAMP(`log_visit`.A)-log_visit.`B`)==3', ['where' => '(UNIX_TIMESTAMP(`log_visit`.A)-log_visit.`B`) = ?', 'bind' => ['3']], ['log_conversion'], ['log_conversion', 'log_visit']],
            ['(UNIX_TIMESTAMP(`log_visit.A`)-`log_visit`.`B`)==3', ['where' => '(UNIX_TIMESTAMP(`log_visit.A`)-`log_visit`.`B`) = ?', 'bind' => ['3']], ['log_conversion'], ['log_conversion', 'log_visit']],
        ];
    }

    /**
     * @dataProvider getOperationSegmentExpressions
     * @group Core
     */
    public function testSegmentSqlWithOperations($expression, $expectedSql, $initialFrom = [], $expectedTables = [])
    {
        $segment = new SegmentExpression($expression);
        $segment->parseSubExpressions();
        $segment->parseSubExpressionsIntoSqlExpressions($initialFrom);
        $processed = $segment->getSql();
        $this->assertEquals($expectedSql, $processed);
        $this->assertEquals($expectedTables, $initialFrom);
    }

    /**
     * Dataprovider for testBogusFiltersExpectExceptionThrown
     * @return array
     */
    public function getBogusFilters()
    {
        return [
            ['A=B'],
            ['C!D'],
            [''],
            ['      '],
            [',;,'],
            [','],
            [',,'],
            ['!='],
        ];
    }

    /**
     * @dataProvider getBogusFilters
     * @group Core
     */
    public function testBogusFiltersExpectExceptionThrown($bogus)
    {
        $this->expectException(\Exception::class);

        $segment = new SegmentExpression($bogus);
        $segment->parseSubExpressions();
        $segment->getSql();
    }

    /**
     * @dataProvider getTestDataForParseColumnsFromSqlExpr
     */
    public function testParseColumnsFromSqlExpr($field, $expected)
    {
        $actual = SegmentExpression::parseColumnsFromSqlExpr($field);
        $this->assertEquals($expected, $actual);
    }

    public function getTestDataForParseColumnsFromSqlExpr()
    {
        return [
            [
                'log_visit.column',
                ['log_visit.column'],
            ],
            [
                'log_visitcolumn',
                [],
            ],
            [
                '`log_visit.column`',
                ['log_visit.column'],
            ],
            [
                '`log_visit`.column',
                ['log_visit.column'],
            ],
            [
                '`log_visit`.`column`',
                ['log_visit.column'],
            ],
            [
                'log_visit.`column`',
                ['log_visit.column'],
            ],
            [
                'log_visit.column == 5 OR log_link_visit_action.othercolumn <> 3',
                ['log_visit.column', 'log_link_visit_action.othercolumn'],
            ],
            [
                '(log_visit.column == 5)',
                ['log_visit.column'],
            ],
            [
                '(log_visit.column = 5) AND ((HOUR(log_visit.column) == 12)) AND FUNC(mytable.mycolumn) - OTHERFUNC(`myothertable`.`myothercolumn`) = LASTFUNC(mylasttable.mylastcolumn)',
                ['log_visit.column', 'mytable.mycolumn', 'myothertable.myothercolumn', 'mylasttable.mylastcolumn'],
            ],
            [
                'log_visit.column = 5 OR @@session.whatever == 5',
                ['log_visit.column'],
            ],
            [
                '@something.whatever = 5',
                [],
            ],
            [
                '@something.whatever <> 5.0',
                [],
            ],
            [
                'log_visit.thing = 3.45 AND @log_visit.what < 23.00',
                ['log_visit.thing'],
            ],
        ];
    }
}
