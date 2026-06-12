<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\tests\Integration;

use Piwik\DataTable;
use Piwik\Plugins\BotTracking\API;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\Plugins\BotTracking\tests\Fixtures\BotTraffic;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Verifies the Human/AI-Favoured Pages reports read back from their archived records correctly:
 * the score is materialised during archiving, the totals-row "skip" aggregation op survives the
 * blob round-trip, and higher periods reprocess the score (rather than summing daily scores).
 *
 * @group BotTracking
 * @group AIChatbotFavouredPages
 * @group Plugins
 */
class AIChatbotFavouredPagesArchivingTest extends IntegrationTestCase
{
    /**
     * @var BotTraffic
     */
    public static $fixture;

    public function testHumanFavouredRecordIsArchivedWithScoreAndSkipOp(): void
    {
        $table = API::getInstance()->getAIChatbotHumanFavouredPages(1, 'day', '2025-02-03');

        self::assertInstanceOf(DataTable::class, $table);
        self::assertGreaterThan(0, $table->getRowsCount(), 'expected the archived favoured-pages record to have rows');

        // Score materialised during archiving and read straight back.
        $row = $table->getFirstRow();
        self::assertNotFalse($row->getColumn(Metrics::COLUMN_DISCREPANCY_SCORE));

        // The per-page 0-100 index must not be summed into the report totals row: the 'skip' op set
        // by the scorer at archive time has to survive serialisation into and out of the blob.
        $ops = $table->getMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME);
        self::assertSame('skip', $ops[Metrics::COLUMN_DISCREPANCY_SCORE] ?? null);
    }

    public function testEventOnlyPageIsNotCountedAsHumanPageview(): void
    {
        $table = API::getInstance()->getAIChatbotHumanFavouredPages(1, 'day', '2025-02-03');

        // example.com/event-only was tracked with an event but never a pageview (and no bot request).
        // It must be absent: events are not human pageviews (mirrors the Actions Pages report).
        self::assertFalse(
            $table->getRowFromLabel('example.com/event-only'),
            'a page seen only as an event context must not appear as a human pageview'
        );
    }

    public function testWeekReprocessesScoreOverSummedTraffic(): void
    {
        $week = API::getInstance()->getAIChatbotHumanFavouredPages(1, 'week', '2025-02-03');
        $row  = $week->getRowFromLabel('example.com/article/2');

        self::assertNotFalse($row, 'expected example.com/article/2 in the weekly favoured-pages record');

        // Traffic columns are additive: the page got 2 + 2 + 1 human visits across Feb 3-5.
        self::assertSame(5, (int) $row->getColumn(Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS));

        // The score is reprocessed on the weekly union, not summed from the daily blobs: article/2
        // is the busiest human page of the week (anchor), so for human=5 / ai=4 the bounded index is
        // 100 * max(0,(5-4)/9) * 1 = 11.1 — never the sum of the per-day scores.
        self::assertSame(11.1, (float) $row->getColumn(Metrics::COLUMN_DISCREPANCY_SCORE));
    }

    public function testAiFavouredRecordIsArchivedSeparately(): void
    {
        $table = API::getInstance()->getAIChatbotAIFavouredPages(1, 'day', '2025-02-03');

        self::assertInstanceOf(DataTable::class, $table);
        self::assertGreaterThan(0, $table->getRowsCount());

        $ops = $table->getMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME);
        self::assertSame('skip', $ops[Metrics::COLUMN_DISCREPANCY_SCORE] ?? null);
    }
}

AIChatbotFavouredPagesArchivingTest::$fixture = new BotTraffic();
