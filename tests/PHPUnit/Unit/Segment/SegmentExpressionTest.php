<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
        return array(
            // classic expressions
            array('A', "A"),
            array('A,B', "(A OR B )"),
            array('A;B', "A AND B"),
            array('A;B;C', "A AND B AND C"),
            array('A,B;C,D;E,F,G', "(A OR B) AND (C OR D) AND (E OR F OR G)"),

            // unescape the backslash
            array('A\,B\,C,D', "(A,B,C OR D)"),
            array('\,A', ' ,A '),
            // unescape only when it was escaping a known delimiter
            array('\\\A', ' \\\A '),
            // unescape at the end
            array('\,\;\A\B,\,C,D\;E\,', '(,;\A\B OR ,C OR D;E,)'),

            // only replace when a following expression is detected
            array('A,', 'A,'),
            array('A;', 'A;'),
            array('A;B;', 'A AND B;'),
            array('A,B,', '(A OR B,)'),
        );
    }

    /**
     * @dataProvider getSimpleSegmentExpressions
     * @group Core
     */
    public function testSegmentSqlSimpleNoOperation($expression, $expectedSql)
    {
        $segment = new SegmentExpression($expression);
        $expected = array('where' => $expectedSql, 'bind' => array());
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
        return array(
            array('A==B%', array('where' => "A = ?", 'bind' => array('B%'))),
            array('ABCDEF====B===', array('where' => "ABCDEF = ?", 'bind' => array('==B==='))),
            array('A===B;CDEF!=C!=', array('where' => "A = ? AND ( CDEF IS NULL OR CDEF <> ? )", 'bind' => array('=B', 'C!='))),
            array('A==B,C==D', array('where' => "(A = ? OR C = ?)", 'bind' => array('B', 'D'))),
            array('A!=B;C==D', array('where' => "(A IS NULL OR A <> ?) AND C = ?", 'bind' => array('B', 'D'))),
            array('A!=B;C==D,E!=Hello World!=', array('where' => "( A IS NULL OR A <> ? ) AND ( C = ? OR ( E IS NULL OR E <> ? ))", 'bind' => array('B', 'D', 'Hello World!='))),
            array('A=@B;C=$D', array('where' => "A LIKE ? AND C LIKE ?", 'bind' => array('%B%', '%D'))),

            array('A>B', array('where' => "A > ?", 'bind' => array('B'))),
            array('A<B', array('where' => "A < ?", 'bind' => array('B'))),
            array('A<=B', array('where' => "A <= ?", 'bind' => array('B'))),
            array('A>=B', array('where' => "A >= ?", 'bind' => array('B'))),
            array('ABCDEF>=>=>=B===', array('where' => "ABCDEF >= ?", 'bind' => array('>=>=B==='))),
            array('A>=<=B;CDEF>G;H>=I;J<K;L<=M', array('where' => "A >= ? AND CDEF > ? AND H >= ? AND J < ? AND L <= ?", 'bind' => array('<=B', 'G', 'I', 'K', 'M'))),
            array('A>=B;C>=D,E<w_ow great!', array('where' => "A >= ? AND ( C >= ? OR E < ?)", 'bind' => array('B', 'D', 'w_ow great!'))),

            array('A=@B_', array('where' => "A LIKE ?", 'bind' => array('%B\_%'))),
            array('A!@B%', array('where' => "( A IS NULL OR A NOT LIKE ? )", 'bind' => array('%B\%%'))),
            array('A=$B%', array('where' => "A LIKE ?", 'bind' => array('%B\%'))),
            array('A=^B%', array('where' => "A LIKE ?", 'bind' => array('B\%%'))),

            array('log_visit.A==3', ['where' => 'log_visit.A = ?', 'bind' => ['3']], [], ['log_visit']),
            array('log_visit.A==3;log_conversion.B>4', ['where' => 'log_visit.A = ? AND log_conversion.B > ?', 'bind' => ['3', '4']], [], ['log_visit', 'log_conversion']),
            array('(UNIX_TIMESTAMP(log_visit.A)-log_visit.B)==3', ['where' => '(UNIX_TIMESTAMP(log_visit.A)-log_visit.B) = ?', 'bind' => ['3']], ['log_conversion'], ['log_conversion', 'log_visit']),
            array('(UNIX_TIMESTAMP(`log_visit`.A)-log_visit.`B`)==3', ['where' => '(UNIX_TIMESTAMP(`log_visit`.A)-log_visit.`B`) = ?', 'bind' => ['3']], ['log_conversion'], ['log_conversion', 'log_visit']),
            array('(UNIX_TIMESTAMP(`log_visit.A`)-`log_visit`.`B`)==3', ['where' => '(UNIX_TIMESTAMP(`log_visit.A`)-`log_visit`.`B`) = ?', 'bind' => ['3']], ['log_conversion'], ['log_conversion', 'log_visit']),
        );
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
        return array(
            array('A=B'),
            array('C!D'),
            array(''),
            array('      '),
            array(',;,'),
            array(','),
            array(',,'),
            array('!='),
        );
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
    public function test_parseColumnsFromSqlExpr($field, $expected)
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
