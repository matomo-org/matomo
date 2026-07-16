<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\tests\System;

use Piwik\Cache;
use Piwik\Config;
use Piwik\Plugins\BotTracking\tests\Fixtures\BotTraffic;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group BotTracking
 */
class RankingQueryApiTest extends SystemTestCase
{
    /**
     * @var BotTraffic
     */
    public static $fixture;

    public function testRankingQueryUsesOthersRowPages(): void
    {
        $generalConfig = &Config::getInstance()->General;
        $generalConfig['archiving_ranking_query_row_limit'] = 3;
        $generalConfig['datatable_archiving_maximum_rows_bots'] = 0; // no limit here, so we see that the ranking query creates the others row
        $generalConfig['datatable_archiving_maximum_rows_subtable_bots'] = 2;

        Cache::flushAll();
        self::deleteArchiveTables();

        $this->runApiTests(['BotTracking.getAIChatbotRequests'], [
            'idSite'                 => 1,
            'date'                   => '2025-02-03',
            'periods'                => ['day', 'week'],
            'otherRequestParameters' => [
                'expanded'           => 1,
                'secondaryDimension' => 'pages',
            ],
            'testSuffix'             => 'ranking_limit_pages',
        ]);
    }

    public function testRankingQueryUsesOthersRowDocuments(): void
    {
        $generalConfig = &Config::getInstance()->General;
        $generalConfig['archiving_ranking_query_row_limit'] = 3;
        $generalConfig['datatable_archiving_maximum_rows_bots'] = 4;
        $generalConfig['datatable_archiving_maximum_rows_subtable_bots'] = 2;

        Cache::flushAll();
        self::deleteArchiveTables();

        $this->runApiTests(['BotTracking.getAIChatbotRequests'], [
            'idSite'                 => 1,
            'date'                   => '2025-02-03',
            'periods'                => ['day', 'week'],
            'otherRequestParameters' => [
                'expanded'           => 1,
                'secondaryDimension' => 'documents',
            ],
            'testSuffix'             => 'ranking_limit_documents',
        ]);
    }

    public function testRankingQueryUsesOthersRowContentPages(): void
    {
        $generalConfig = &Config::getInstance()->General;
        $generalConfig['datatable_archiving_maximum_rows_ai_chatbot_content'] = 2;

        Cache::flushAll();
        self::deleteArchiveTables();

        $this->runApiTests(['BotTracking.getAIChatbotContentPages'], [
            'idSite'     => 1,
            'date'       => '2025-02-03',
            'periods'    => ['day', 'week'],
            'testSuffix' => 'ranking_limit_content_pages',
        ]);
    }

    public function testRankingQueryUsesOthersRowContentDocuments(): void
    {
        $generalConfig = &Config::getInstance()->General;
        $generalConfig['datatable_archiving_maximum_rows_ai_chatbot_content'] = 2;

        Cache::flushAll();
        self::deleteArchiveTables();

        $this->runApiTests(['BotTracking.getAIChatbotContentDocuments'], [
            'idSite'     => 1,
            'date'       => '2025-02-03',
            'periods'    => ['day', 'week'],
            'testSuffix' => 'ranking_limit_content_documents',
        ]);
    }

    public function testRankingQueryUsesOthersRowBrokenContent(): void
    {
        $generalConfig = &Config::getInstance()->General;
        $generalConfig['datatable_archiving_maximum_rows_ai_chatbot_content'] = 2;

        Cache::flushAll();
        self::deleteArchiveTables();

        $this->runApiTests(['BotTracking.getAIChatbotBrokenContent'], [
            'idSite'     => 1,
            'date'       => '2025-02-03',
            'periods'    => ['day', 'week'],
            'testSuffix' => 'ranking_limit_broken_content',
        ]);
    }

    public function testRankingQueryUsesOthersRowHumanFavouredPages(): void
    {
        $generalConfig = &Config::getInstance()->General;
        $generalConfig['datatable_archiving_maximum_rows_ai_chatbot_favoured_pages'] = 2;

        Cache::flushAll();
        self::deleteArchiveTables();

        // enable_filter_excludelowpop=0 shows every row (the default filter would drop the unscored
        // Others row), so the truncation tail is visible for both the day blob and the week re-aggregation.
        $this->runApiTests(['BotTracking.getAIChatbotHumanFavouredPages'], [
            'idSite'                 => 1,
            'date'                   => '2025-02-03',
            'periods'                => ['day', 'week'],
            'otherRequestParameters' => [
                'enable_filter_excludelowpop' => 0,
            ],
            'testSuffix'             => 'ranking_limit_human_favoured_pages',
        ]);
    }

    public function testRankingQueryUsesOthersRowAiFavouredPages(): void
    {
        $generalConfig = &Config::getInstance()->General;
        $generalConfig['datatable_archiving_maximum_rows_ai_chatbot_favoured_pages'] = 2;

        Cache::flushAll();
        self::deleteArchiveTables();

        $this->runApiTests(['BotTracking.getAIChatbotAIFavouredPages'], [
            'idSite'                 => 1,
            'date'                   => '2025-02-03',
            'periods'                => ['day', 'week'],
            'otherRequestParameters' => [
                'enable_filter_excludelowpop' => 0,
            ],
            'testSuffix'             => 'ranking_limit_ai_favoured_pages',
        ]);
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return __DIR__;
    }
}

RankingQueryApiTest::$fixture = new BotTraffic();
