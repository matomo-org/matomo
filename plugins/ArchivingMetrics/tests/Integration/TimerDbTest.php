<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ArchivingMetrics\tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\ArchivingMetrics\Clock\Clock;
use Piwik\Plugins\ArchivingMetrics\Context;
use Piwik\Plugins\ArchivingMetrics\Timer;
use Piwik\Plugins\ArchivingMetrics\Writer\DbWriter;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group ArchivingMetrics
 * @group ArchivingMetrics_TimerDb
 * @group Plugins
 */
class TimerDbTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        Db::query('DELETE FROM ' . Common::prefixTable('archiving_metrics'));
    }

    public function testItWritesAndReadsFromDatabase(): void
    {
        $context = new Context(1, 'day', '', '2024-01-01', '2024-01-01', '');

        $timer = new Timer(true, new Clock(), new DbWriter());
        $timer->start($context);
        $timer->complete($context, [999], false);

        $rows = Db::fetchAll('SELECT * FROM ' . Common::prefixTable('archiving_metrics'));

        $this->assertNotEmpty($rows, 'Expected archiving_metrics table to have at least one record.');
    }
}
