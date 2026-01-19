<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ArchivingMetrics\tests\Integration;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Period;
use Piwik\Plugins\ArchivingMetrics\Clock\Clock;
use Piwik\Plugins\ArchivingMetrics\Context;
use Piwik\Plugins\ArchivingMetrics\Timer;
use Piwik\Plugins\ArchivingMetrics\Writer\DbWriter;
use Piwik\Segment;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group ArchivingMetrics
 * @group ArchivingMetrics_TimerDb
 * @group Plugins
 */
class TimerDbTest extends IntegrationTestCase
{
    public function testItWritesAndReadsFromDatabase(): void
    {
        $period = new Period\Day(Date::factory('2025-11-01'));
        $segment = new Segment('', [1]);

        $context = new Context(1, $period, $segment, '');

        $timer = new Timer(true, new Clock(), new DbWriter());
        $timer->start($context);
        $timer->complete($context, [999], false);

        $rows = Db::fetchAll('SELECT * FROM ' . Common::prefixTable('archiving_metrics'));

        self::assertCount(1, $rows, 'Expected archiving_metrics table to have exactly one record.');
        self::assertSame(999, (int) $rows[0]['idarchive']);
        self::assertSame(1, (int) $rows[0]['idsite']);
        self::assertNotEmpty($rows[0]['archive_name']);
        self::assertSame('2025-11-01', $rows[0]['date1']);
        self::assertSame('2025-11-01', $rows[0]['date2']);
        self::assertIsNumeric($rows[0]['period']);
        self::assertNotEmpty($rows[0]['ts_started']);
        self::assertNotEmpty($rows[0]['ts_finished']);
        self::assertIsNumeric($rows[0]['total_time']);
        self::assertIsNumeric($rows[0]['total_time_exclusive']);
    }
}
