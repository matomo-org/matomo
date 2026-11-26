<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Db;

use Piwik\Db\RollupQuery;

/**
 * @group Core
 * @group Db
 */
class RollupQueryTest extends \PHPUnit\Framework\TestCase
{
    public function testBuildsOneGroupingQueryPerLevel(): void
    {
        $rollup = RollupQuery::build(
            "log_visit.referer_name, COALESCE(log_action.name, '') as action_name, COUNT(DISTINCT log_visit.idvisitor) AS visitors",
            'log_visit LEFT JOIN log_action ON log_action.idaction = log_visit.idaction',
            'log_visit.idsite = ? AND log_visit.visit_last_action_time >= ?',
            'log_visit.referer_name, action_name',
            [1, '2026-01-01']
        );

        $from = ' FROM log_visit LEFT JOIN log_action ON log_action.idaction = log_visit.idaction'
            . ' WHERE log_visit.idsite = ? AND log_visit.visit_last_action_time >= ?';

        $this->assertSame(
            "SELECT log_visit.referer_name, COALESCE(log_action.name, '') as action_name, COUNT(DISTINCT log_visit.idvisitor) AS visitors"
            . $from . ' GROUP BY log_visit.referer_name, action_name'
            . ' UNION ALL SELECT log_visit.referer_name, NULL AS `action_name`, COUNT(DISTINCT log_visit.idvisitor) AS visitors'
            . $from . ' GROUP BY log_visit.referer_name'
            . ' UNION ALL SELECT NULL AS `referer_name`, NULL AS `action_name`, COUNT(DISTINCT log_visit.idvisitor) AS visitors'
            . $from . ' HAVING COUNT(*) > 0',
            $rollup['sql']
        );
        $this->assertSame([1, '2026-01-01', 1, '2026-01-01', 1, '2026-01-01'], $rollup['bind']);
        $this->assertSame('`action_name` IS NULL DESC, `referer_name` IS NULL DESC', $rollup['order']);
    }

    public function testIgnoresCommasInsideParenthesesAndQuotes(): void
    {
        $rollup = RollupQuery::build("CONCAT(a, ',', b) AS ab, 'x,y' AS c, SUM(n) AS n", 't', '', 'ab', []);

        $this->assertSame(
            "SELECT CONCAT(a, ',', b) AS ab, 'x,y' AS c, SUM(n) AS n FROM t GROUP BY ab"
            . " UNION ALL SELECT NULL AS `ab`, 'x,y' AS c, SUM(n) AS n FROM t HAVING COUNT(*) > 0",
            $rollup['sql']
        );
        $this->assertSame([], $rollup['bind']);
    }

    public function testMatchesGroupingColumnsIgnoringQuotesAndCase(): void
    {
        $rollup = RollupQuery::build('`t`.`a`, label AS LabelName, SUM(n) AS n', 't', '', 'T.a, labelname', []);

        $this->assertStringEndsWith(
            ' UNION ALL SELECT NULL AS `a`, NULL AS `LabelName`, SUM(n) AS n FROM t HAVING COUNT(*) > 0',
            $rollup['sql']
        );
    }

    public function testRollsUpEveryProjectionOfAGroupingColumn(): void
    {
        $rollup = RollupQuery::build('label, label AS copy, SUM(n) AS n', 't', '', 'label', []);

        $this->assertStringEndsWith(
            ' UNION ALL SELECT NULL AS `label`, NULL AS `copy`, SUM(n) AS n FROM t HAVING COUNT(*) > 0',
            $rollup['sql']
        );
    }

    public function testRollsUpAGroupingColumnThatIsNotSelected(): void
    {
        $rollup = RollupQuery::build('SUM(n) AS n', 't', '', 'a', []);

        $this->assertSame('SELECT SUM(n) AS n FROM t GROUP BY a UNION ALL SELECT SUM(n) AS n FROM t HAVING COUNT(*) > 0', $rollup['sql']);
        $this->assertSame('', $rollup['order']);
    }

    public function testRejectsAGroupingColumnMatchingDifferentSelectItems(): void
    {
        $this->expectException(\Exception::class);
        RollupQuery::build('url, LOWER(url) AS url, SUM(n) AS n', 't', '', 'url', []);
    }

    public function testRejectsAnUnaliasedSelectedGroupingExpression(): void
    {
        $this->expectException(\Exception::class);
        RollupQuery::build('LOWER(a), SUM(n) AS n', 't', '', 'LOWER(a)', []);
    }

    public function testRejectsAGroupingExpressionWithBindings(): void
    {
        $this->expectException(\Exception::class);
        RollupQuery::build('COALESCE(a, ?) AS a, SUM(n) AS n', 't', '', 'a', ['-']);
    }

    public function testGivesTheParentFirstOrderOfANativeRollup(): void
    {
        $this->assertSame(
            '`action_name` IS NULL DESC, `referer_name` IS NULL DESC',
            RollupQuery::getParentFirstOrder(
                "log_visit.referer_name, COALESCE(log_action.name, '') as action_name, COUNT(*) AS `1`",
                'log_visit.referer_name, action_name'
            )
        );
        $this->assertSame('`a` IS NULL DESC', RollupQuery::getParentFirstOrder('a, LOWER(b), SUM(n) AS n', 'a, LOWER(b)'));
    }

    public function testRollsUpEveryProjectionOfAGroupingAlias(): void
    {
        $rollup = RollupQuery::build('label AS LabelName, label AS copy, COUNT(*) AS n', 't', '', 'LabelName', []);

        $this->assertStringEndsWith(
            ' UNION ALL SELECT NULL AS `LabelName`, NULL AS `copy`, COUNT(*) AS n FROM t HAVING COUNT(*) > 0',
            $rollup['sql']
        );
    }

    public function testKeepsAConstantThatDiffersFromTheGroupingOneByCase(): void
    {
        $rollup = RollupQuery::build("'X' AS kind, 'x' AS marker, COUNT(*) AS n", 't', '', "'X'", []);

        $this->assertStringEndsWith(
            " UNION ALL SELECT NULL AS `kind`, 'x' AS marker, COUNT(*) AS n FROM t HAVING COUNT(*) > 0",
            $rollup['sql']
        );
    }

    public function testRepeatsOnlyTheBindingsOfTheRepeatedClauses(): void
    {
        $rollup = RollupQuery::build("IF(a = ?, 1, 0) AS flag, b, COUNT(*) AS n", 't', 'c > ?', 'b', ['x', 2, 'after']);

        $this->assertSame(['x', 2, 'x', 2, 'after'], $rollup['bind']);
    }

    public function testRejectsMissingBindings(): void
    {
        $this->expectException(\Exception::class);
        RollupQuery::build('a, COUNT(*) AS n', 't', 'b = ? AND c = ?', 'a', [1]);
    }

    public function testRejectsGroupingBindings(): void
    {
        $this->expectException(\Exception::class);
        RollupQuery::build('COUNT(*) AS n', 't', '', 'COALESCE(a, ?)', ['-']);
    }

    public function testKeepsAConstantSelectedLikeTheGroupingAlias(): void
    {
        $rollup = RollupQuery::build('42 AS marker, 42 AS k, COUNT(*) AS n', 't', '', 'k', []);

        $this->assertStringEndsWith(' UNION ALL SELECT 42 AS marker, NULL AS `k`, COUNT(*) AS n FROM t HAVING COUNT(*) > 0', $rollup['sql']);
        // a constant grouping value can't tell subtotals apart
        $this->assertSame('', $rollup['order']);
    }

    public function testTellsANumericColumnNameFromANumber(): void
    {
        $rollup = RollupQuery::build('42 AS marker, `42` AS k, COUNT(*) AS n', 't', '', 'k', []);

        $this->assertStringEndsWith(' UNION ALL SELECT 42 AS marker, NULL AS `k`, COUNT(*) AS n FROM t HAVING COUNT(*) > 0', $rollup['sql']);
        $this->assertSame('`k` IS NULL DESC', $rollup['order']);
    }

    public function testRollsUpEveryProjectionOfAGroupingExpressionAlias(): void
    {
        $rollup = RollupQuery::build('LOWER(label) AS copy, LOWER(label) AS k, COUNT(*) AS n', 't', '', 'k', []);

        $this->assertStringEndsWith(' UNION ALL SELECT NULL AS `copy`, NULL AS `k`, COUNT(*) AS n FROM t HAVING COUNT(*) > 0', $rollup['sql']);
    }

    public function testGivesNoParentFirstOrderForBackslashes(): void
    {
        $this->assertSame('', RollupQuery::getParentFirstOrder("'O\\'Brien' AS marker, label, COUNT(*) AS n", 'label'));
    }

    public function testSkipsAGroupingColumnWhoseNameIsShared(): void
    {
        $this->assertSame('`a` IS NULL DESC', RollupQuery::getParentFirstOrder('a, b AS x, b AS x, COUNT(*) AS n', 'a, x'));
    }

    public function testOrdersParentsFirstByANamedProjection(): void
    {
        $this->assertSame('`k` IS NULL DESC', RollupQuery::getParentFirstOrder('LOWER(label), LOWER(label) AS k, COUNT(*) AS n', 'k'));
    }

    public function testRepeatsBindingsAroundAnEscapeClause(): void
    {
        $rollup = RollupQuery::build('a, COUNT(*) AS n', 't', "a LIKE ? ESCAPE '\\' AND n > ?", 'a', ['A%', 10]);

        $this->assertSame(['A%', 10, 'A%', 10], $rollup['bind']);
    }
}
