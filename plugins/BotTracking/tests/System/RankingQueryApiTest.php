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

        $this->runApiTests(['BotTracking.getAIAssistantRequests'], [
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

        $this->runApiTests(['BotTracking.getAIAssistantRequests'], [
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
