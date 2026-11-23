<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreHome\tests\Integration\Columns;

use Piwik\Db;
use Piwik\Db\Schema;
use Piwik\Plugins\CoreHome\Columns\VisitLastActionMinute;
use Piwik\Plugins\CoreHome\Columns\VisitLastActionWeekOfYear;
use Piwik\Plugins\CoreHome\Columns\VisitLastActionDayOfWeek;
use Piwik\Plugins\CoreHome\Columns\VisitLastActionTime;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The hour, day-of-week and other date-part segments are spelled by the database in use, unless a plugin replaces them.
 *
 * @group CoreHome
 * @group Plugins
 */
class TimeSegmentSqlTest extends IntegrationTestCase
{
    public function testTheSegmentSqlIsTheDatabasesSpelling(): void
    {
        $this->assertSame(
            Schema::getInstance()->getHourSql('log_visit.visit_last_action_time'),
            (new VisitLastActionTime())->getSqlSegment()
        );
        $this->assertSame(
            Schema::getInstance()->getDayOfWeekSql('log_visit.visit_last_action_time'),
            (new VisitLastActionDayOfWeek())->getSqlSegment()
        );
    }

    public function testTheDatePartSegmentsAreTheDatabasesSpelling(): void
    {
        $this->assertSame(
            Schema::getInstance()->getDatePartSql('MINUTE', 'log_visit.visit_last_action_time'),
            (new VisitLastActionMinute())->getSqlSegment()
        );
        $this->assertSame(
            Schema::getInstance()->getDatePartSql('WEEKOFYEAR', 'log_visit.visit_last_action_time'),
            (new VisitLastActionWeekOfYear())->getSqlSegment()
        );
    }

    /**
     * @dataProvider getDateParts
     */
    public function testADatePartHasMysqlsValue(string $part, string $datetime, int $expected): void
    {
        $sql = Schema::getInstance()->getDatePartSql($part, '?');
        $this->assertSame($expected, (int) Db::fetchOne('SELECT ' . $sql, [$datetime]));
        // a segment value is bound as a string
        $this->assertSame(1, (int) Db::fetchOne('SELECT CASE WHEN ' . $sql . ' = ? THEN 1 ELSE 0 END', [$datetime, (string) $expected]));
    }

    public function getDateParts(): iterable
    {
        yield ['MINUTE', '2021-01-03 10:07:30', 7];
        yield ['MINUTE', '10:59:30', 59];
        yield ['SECOND', '2021-01-03 10:07:09', 9];
        yield ['DAYOFMONTH', '2021-01-03 10:07:30', 3];
        yield ['DAYOFYEAR', '2020-12-31 23:59:59', 366];
        yield ['MONTH', '2021-11-03 00:00:00', 11];
        yield ['YEAR', '2021-01-03 10:07:30', 2021];
        foreach ([1 => 1, 3 => 1, 4 => 2, 6 => 2, 7 => 3, 9 => 3, 10 => 4, 12 => 4] as $month => $quarter) {
            yield ['QUARTER', sprintf('2021-%02d-15 12:00:00', $month), $quarter];
        }
        // ISO 8601 weeks around the turn of the year
        yield ['WEEKOFYEAR', '2020-12-31 12:00:00', 53];
        yield ['WEEKOFYEAR', '2021-01-03 23:59:59', 53];
        yield ['WEEKOFYEAR', '2021-01-04 00:00:00', 1];
        yield ['WEEKOFYEAR', '2024-12-30 00:00:00', 1];
        yield ['WEEKOFYEAR', '2025-12-28 12:00:00', 52];
        yield ['WEEKOFYEAR', '2026-01-01 12:00:00', 1];
        yield ['WEEKOFYEAR', '2021-06-16 12:00:00', 24];
    }

    public function testAnotherDatePartIsRefused(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Schema::getInstance()->getDatePartSql('MICROSECOND', 'log_visit.visit_last_action_time');
    }

    public function testASubclassOrSetterStillReplacesTheSegmentSql(): void
    {
        $subclass = new class extends VisitLastActionTime {
            protected $sqlSegment = 'log_visit.visitor_returning';
        };
        $this->assertSame('log_visit.visitor_returning', $subclass->getSqlSegment());

        $dimension = new VisitLastActionDayOfWeek();
        $dimension->setSqlSegment('log_visit.visitor_count_visits');
        $this->assertSame('log_visit.visitor_count_visits', $dimension->getSqlSegment());

        // an emptied segment falls back to the column, as for any dimension
        $dimension->setSqlSegment('');
        $this->assertSame('log_visit.visit_last_action_time', $dimension->getSqlSegment());
    }
}
