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
use Piwik\Plugins\CoreHome\Columns\VisitorDaysSinceFirst;
use Piwik\Plugins\CoreHome\Columns\VisitorDaysSinceOrder;
use Piwik\Plugins\VisitorInterest\Columns\VisitorDaysSinceLast;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The days-since segments evaluate to the same day count on every database.
 *
 * @group CoreHome
 * @group Plugins
 */
class DaysSinceSegmentSqlTest extends IntegrationTestCase
{
    public function testDaysSinceFirstVisitRoundsToTheNearestDay(): void
    {
        $sql = (new VisitorDaysSinceFirst())->getSqlSegment();

        // 1.6 days: SQLite would truncate an integer division to 1 before rounding
        foreach ([0 => 0, 43199 => 0, 43200 => 1, 138240 => 2, 172800 => 2] as $seconds => $days) {
            // an integer literal, as the column is: a bound string would be a double on MySQL
            $value = Db::fetchOne('SELECT ' . str_replace('log_visit.visitor_seconds_since_first', (string) $seconds, $sql));
            $this->assertEquals($days, $value, $seconds . ' seconds');
        }
    }

    public function testDaysSinceLastVisitAndOrderEqualTheSegmentValue(): void
    {
        foreach ([[new VisitorDaysSinceLast(), 'visitor_seconds_since_last'], [new VisitorDaysSinceOrder(), 'visitor_seconds_since_order']] as [$dimension, $column]) {
            $sql = str_replace('log_visit.' . $column, '?', $dimension->getSqlSegment());
            foreach ([0 => '0', 86399 => '0', 86400 => '1', 190000 => '2'] as $seconds => $days) {
                // the segment value is bound as a string
                $matches = Db::fetchOne('SELECT CASE WHEN ' . $sql . ' = ? THEN 1 ELSE 0 END', [$seconds, $days]);
                $this->assertEquals(1, $matches, $column . ': ' . $seconds . ' seconds');
                $excluded = Db::fetchOne("SELECT CASE WHEN $sql IS NOT NULL AND $sql <> '' AND $sql <> '0' THEN 1 ELSE 0 END", [$seconds, $seconds, $seconds]);
                $this->assertEquals($days === '0' ? 0 : 1, $excluded, $column . ': ' . $seconds . ' seconds, not empty');
            }
        }
    }
}
