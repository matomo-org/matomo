<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\tests\Integration;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\Cache;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Period\Range;
use Piwik\Plugins\BotTracking\API;
use Piwik\Plugins\BotTracking\Archiver;
use Piwik\Plugins\BotTracking\tests\Fixtures\BotTraffic;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Verifies that the Favoured Pages buildForNonDayPeriod override honours partial archiving: when only
 * one favoured report is requested for a non-day period (e.g. a single-report invalidation /
 * --force-report), only that record is (re)built and the other is left untouched. This exercises the
 * guard that mirrors core's default non-day loop.
 *
 * @group BotTracking
 * @group AIChatbotFavouredPages
 * @group Plugins
 */
class AIChatbotFavouredPagesPartialArchiveTest extends IntegrationTestCase
{
    /**
     * @var BotTraffic
     */
    public static $fixture;

    public function testNonDayPartialArchivingRebuildsOnlyTheRequestedFavouredRecord(): void
    {
        // Full archive of the range first (range is the period that supports single-report partial
        // archiving): both favoured records are built.
        $_GET['trigger'] = 'archivephp';
        API::getInstance()->getAIChatbotHumanFavouredPages(1, 'range', '2025-02-03,2025-02-09');

        $fullNames = $this->favouredBlobNames();
        self::assertContains(Archiver::AI_CHATBOTS_HUMAN_FAVOURED_PAGES_RECORD, $fullNames);
        self::assertContains(Archiver::AI_CHATBOTS_AI_FAVOURED_PAGES_RECORD, $fullNames);

        $maxIdArchive = $this->maxBlobIdArchive();

        // Change a day inside the range, then re-archive that day, so the range must re-aggregate.
        $tracker = Fixture::getTracker(1, '2025-02-04 09:00:00');
        $tracker->setUrl('https://example.com/article/2');
        Fixture::checkResponse($tracker->doTrackPageView('Partial archive page'));

        Cache::flushAll();
        API::getInstance()->getAIChatbotHumanFavouredPages(1, 'day', '2025-02-04'); // re-archive the changed day (trigger still set)
        unset($_GET['trigger']);

        // Invalidate the range and re-query ONLY the human-favoured report, which triggers partial
        // archiving with that single record as the requested report.
        StaticContainer::get(ArchiveInvalidator::class)->markArchivesAsInvalidated([1], ['2025-02-03,2025-02-09'], 'range');
        API::getInstance()->getAIChatbotHumanFavouredPages(1, 'range', '2025-02-03,2025-02-09');

        // The new partial archive must (re)build only the requested human-favoured record; the guard
        // must skip the AI-favoured one.
        $partialNames = $this->favouredBlobNames($maxIdArchive);
        self::assertContains(Archiver::AI_CHATBOTS_HUMAN_FAVOURED_PAGES_RECORD, $partialNames);
        self::assertNotContains(Archiver::AI_CHATBOTS_AI_FAVOURED_PAGES_RECORD, $partialNames);
    }

    private function maxBlobIdArchive(): int
    {
        return (int) Db::fetchOne('SELECT MAX(idarchive) FROM ' . Common::prefixTable('archive_blob_2025_02'));
    }

    /**
     * Favoured-pages blob record names archived in Feb 2025, optionally only from archives created
     * after $idArchiveGreaterThan.
     *
     * @return string[]
     */
    private function favouredBlobNames(int $idArchiveGreaterThan = 0): array
    {
        $sql = 'SELECT DISTINCT name FROM ' . Common::prefixTable('archive_blob_2025_02')
            . ' WHERE idarchive > ? AND period = ? AND date1 = ? AND date2 = ? AND name LIKE ?';
        $rows = Db::fetchAll($sql, [
            $idArchiveGreaterThan,
            Range::PERIOD_ID,
            '2025-02-03',
            '2025-02-09',
            'BotTracking_AIChatbots%FavouredPages',
        ]);

        return array_column($rows, 'name');
    }
}

AIChatbotFavouredPagesPartialArchiveTest::$fixture = new BotTraffic();
