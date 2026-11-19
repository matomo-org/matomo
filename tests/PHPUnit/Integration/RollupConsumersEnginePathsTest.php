<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\API\Request;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db\Schema;
use Piwik\DbHelper;
use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Piwik\Plugins\CustomDimensions\Dao\Configuration;
use Piwik\Plugins\CustomDimensions\FeatureFlags\CustomDimensionReportWithRollUp;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\ForcedQueryCapabilities;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Archives the reports built from rollup ranking queries (AI assistants, AI chatbot requests,
 * action-scoped custom dimensions) through the window-function and emulated-rollup paths, and
 * compares them with the reports from the native paths.
 *
 * @group Core
 * @group RankingQuery
 */
class RollupConsumersEnginePathsTest extends IntegrationTestCase
{
    private const DATE = '2026-03-03';

    private const AI_SOURCES = [
        'https://chatgpt.com/c/1',
        'https://perplexity.ai/search/2',
        'https://claude.ai/chat/3',
        'https://gemini.google.com/app/4',
    ];

    // an assistant with a single visit to a single page: its subtotal ties with its only detail row
    private const SOLO_SOURCE = 'https://chat.mistral.ai/chat/5';

    private const AI_BOTS = ['ChatGPT-User/1.0', 'Perplexity-User/1.0', 'Claude-User/1.0'];

    private const REPORTS = [
        ['Referrers.getAIAssistants', ['expanded' => 1]],
        ['Referrers.getAIAssistants', ['flat' => 1]],
        ['BotTracking.getAIChatbotRequests', ['expanded' => 1]],
        ['CustomDimensions.getCustomDimension', ['idDimension' => 1, 'flat' => 1]],
    ];

    public function setUp(): void
    {
        parent::setUp();

        $general = &Config::getInstance()->General;
        // MySQL can't reference a temporary table twice in a statement, as the rollup emulation does,
        // so the segment is applied directly instead of from a cached temporary table
        $general['enable_segments_cache'] = 0;
        foreach (['referrers', 'bots', 'custom_dimensions'] as $report) {
            $general['datatable_archiving_maximum_rows_' . $report] = 0;
            $general['datatable_archiving_maximum_rows_subtable_' . $report] = 0;
        }
        Config::getInstance()->FeatureFlags = [(new CustomDimensionReportWithRollUp())->getName() . '_feature' => 'enabled'];

        Fixture::createSuperUser();
        Fixture::createWebsite(self::DATE . ' 00:00:00', 1, 'https://example.com');
        (new Configuration())->configureNewDimension(1, 'Topic', CustomDimensions::SCOPE_ACTION, 1, true, [], true);

        $this->trackVisits();
        $this->trackSoloVisit();
        $this->trackBotRequests();
    }

    /**
     * @dataProvider getPaths
     */
    public function testReportsMatchTheNativePaths(bool $windowFunctions, bool $groupByWithRollup, int $rankingLimit): void
    {
        Config::getInstance()->General['archiving_ranking_query_row_limit'] = $rankingLimit;

        $this->deleteArchives();
        $expected = $this->archiveReports();

        $this->deleteArchives();
        $actual = ForcedQueryCapabilities::run($windowFunctions, $groupByWithRollup, function () use ($windowFunctions, $groupByWithRollup): array {
            $reports = $this->archiveReports();

            // the forced capabilities were in effect for the whole archiving
            $this->assertSame($windowFunctions, Schema::getInstance()->supportsWindowFunctions());
            $this->assertSame($groupByWithRollup, Schema::getInstance()->supportsGroupByWithRollup());

            return $reports;
        });

        foreach ($expected as $report => $rows) {
            $this->assertNotEmpty($rows, $report);
            if ($rankingLimit > 0) {
                $this->assertStringContainsString('Others', json_encode($rows), "$report has no ranking-query Others row");
            }
        }
        $this->assertSame($expected, $actual);

        if ($rankingLimit === 0) {
            // the single page of the single-visit assistant is kept although it ties with its subtotal
            $this->assertStringContainsString('landing-solo', json_encode($expected['Referrers.getAIAssistants {"expanded":1} ']));
            $this->assertStringContainsString('landing-solo', json_encode($expected['Referrers.getAIAssistants {"flat":1} ']));
        }
    }

    public function getPaths(): iterable
    {
        foreach ([3 => 'ranked', 0 => 'unranked'] as $limit => $ranking) {
            if ($limit === 0) {
                // without ranking, window functions aren't used
                yield "$ranking, emulated rollup" => [false, false, $limit];
                continue;
            }
            yield "$ranking, window" => [true, true, $limit];
            yield "$ranking, emulated rollup" => [false, false, $limit];
            yield "$ranking, window, emulated rollup" => [true, false, $limit];
        }
    }

    private function deleteArchives(): void
    {
        DbHelper::deleteArchiveTables();
        Fixture::clearInMemoryCaches(false);
    }

    private function archiveReports(): array
    {
        $reports = [];
        foreach (self::REPORTS as [$method, $params]) {
            // AI chatbot requests are not segmented
            foreach ($method === 'BotTracking.getAIChatbotRequests' ? [''] : ['', 'browserCode==FF'] as $segment) {
                $rows = Request::processRequest($method, $params + [
                    'idSite' => 1,
                    'period' => 'day',
                    'date' => self::DATE,
                    'segment' => $segment,
                    'format' => 'original',
                ]);
                $reports[$method . ' ' . json_encode($params) . ' ' . $segment] = $this->toRows($rows);
            }
        }

        return $reports;
    }

    private function toRows($table): array
    {
        $rows = [];
        foreach ($table->getRows() as $row) {
            $columns = $row->getColumns();
            ksort($columns);
            $subtable = $row->getSubtable();
            if ($subtable) {
                $columns['subtable'] = $this->toRows($subtable);
            }
            $rows[] = json_encode($columns);
        }
        sort($rows);

        return $rows;
    }

    /**
     * Visits from AI assistants with a different count per (assistant, landing page), so a ranking
     * limit never has to break ties, each with a pageview carrying a custom dimension value.
     */
    private function trackVisits(): void
    {
        $hour = 0;
        foreach (self::AI_SOURCES as $source => $referrer) {
            for ($page = 0; $page < 3; $page++) {
                $visits = 3 * $source + $page + 1;
                for ($i = 0; $i < $visits; $i++) {
                    $tracker = Fixture::getTracker(1, Date::factory(self::DATE)->addHour(($hour++) / 10)->getDatetime(), true);
                    $tracker->setUserAgent($i % 2 ? 'Mozilla/5.0 (X11; Linux x86_64; rv:120.0) Gecko/20100101 Firefox/120.0' : 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36');
                    $tracker->setIp('10.1.' . intdiv($hour, 250) . '.' . ($hour % 250 + 1));
                    $tracker->setForceNewVisit();
                    $tracker->setUrlReferrer($referrer);
                    $tracker->setUrl("https://example.com/landing-$page");
                    $tracker->setCustomDimension(1, "topic-$source");
                    Fixture::checkResponse($tracker->doTrackPageView("Landing $page"));
                }
            }
        }
    }

    private function trackSoloVisit(): void
    {
        $tracker = Fixture::getTracker(1, Date::factory(self::DATE)->addHour(20)->getDatetime(), true);
        $tracker->setIp('10.2.0.1');
        $tracker->setForceNewVisit();
        $tracker->setUrlReferrer(self::SOLO_SOURCE);
        $tracker->setUrl('https://example.com/landing-solo');
        $tracker->setCustomDimension(1, 'topic-solo');
        Fixture::checkResponse($tracker->doTrackPageView('Landing solo'));
    }

    private function trackBotRequests(): void
    {
        $hour = 0;
        foreach (self::AI_BOTS as $bot => $userAgent) {
            for ($page = 0; $page < 4; $page++) {
                $requests = 4 * $bot + $page + 1;
                for ($i = 0; $i < $requests; $i++) {
                    $tracker = Fixture::getTracker(1, Date::factory(self::DATE)->addHour(($hour++) / 10)->getDatetime(), true);
                    $tracker->setUserAgent($userAgent);
                    $tracker->setCustomTrackingParameter('recMode', '1');
                    $tracker->setCustomTrackingParameter('http_status', '200');
                    $tracker->setUrl("https://example.com/bot-page-$page");
                    Fixture::checkResponse($tracker->doTrackPageView(''));
                }
            }
        }
    }
}
