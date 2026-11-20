<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreHome\tests\Integration\Columns;

use Piwik\Db\Schema;
use Piwik\Plugins\CoreHome\Columns\VisitLastActionDayOfWeek;
use Piwik\Plugins\CoreHome\Columns\VisitLastActionTime;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The hour and day-of-week segments are spelled by the database in use, unless a plugin replaces them.
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

    public function testASubclassOrSetterStillReplacesTheSegmentSql(): void
    {
        $subclass = new class extends VisitLastActionTime {
            protected $sqlSegment = 'log_visit.visitor_returning';
        };
        $this->assertSame('log_visit.visitor_returning', $subclass->getSqlSegment());

        $dimension = new VisitLastActionDayOfWeek();
        $dimension->setSqlSegment('log_visit.visitor_count_visits');
        $this->assertSame('log_visit.visitor_count_visits', $dimension->getSqlSegment());
    }
}
