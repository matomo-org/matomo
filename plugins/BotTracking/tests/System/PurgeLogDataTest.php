<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\tests\System;

use Piwik\DataAccess\RawLogDao;
use Piwik\Date;
use Piwik\Db;
use Piwik\LogDeleter;
use Piwik\Plugin\Dimension\DimensionMetadataProvider;
use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;
use Piwik\Plugins\PrivacyManager\LogDataPurger;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\Plugin\LogTablesProvider;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group BotTracking
 * @group Plugins
 */
class PurgeLogDataTest extends SystemTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        Fixture::createWebsite('2014-02-04');
    }

    public function testLogDataPurgingRemovesBotRequests(): void
    {
        // track some bot requests
        $t = Fixture::getTracker(1, '2025-02-02 12:00:00');
        $t->setUserAgent('Mozilla/5.0 (compatible; ChatGPT-User/1.0)');
        $t->setUrl('https://matomo.org/faq/123');
        $t->setCustomTrackingParameter('recMode', '1');
        Fixture::checkResponse($t->doTrackPageView(''));

        $t = Fixture::getTracker(1, '2025-02-02 17:00:00');
        $t->setUserAgent('Perplexity-User/1.0');
        $t->setUrl('https://matomo.org/faq/987');
        $t->setCustomTrackingParameter('recMode', '1');
        Fixture::checkResponse($t->doTrackPageView(''));

        $t = Fixture::getTracker(1, '2025-02-03 01:00:00');
        $t->setUserAgent('MistralAI-User/2.0');
        $t->setUrl('https://matomo.org/faq/576');
        $t->setCustomTrackingParameter('recMode', '1');
        Fixture::checkResponse($t->doTrackPageView(''));

        // check that all requests were tracked
        $tableName = BotRequestsDao::getPrefixedTableName();
        $sql       = "SELECT COUNT(*) FROM `{$tableName}`";
        self::assertEquals(3, Db::fetchOne($sql));

        // run purging for dates before 2025-02-03
        $rawLogDao = new RawLogDao(new DimensionMetadataProvider());
        $purger    = new LogDataPurger(new LogDeleter($rawLogDao, new LogTablesProvider()), $rawLogDao);
        $days      = floor((Date::now()->getTimestamp() - Date::factory('2025-02-03 00:00:00')->getTimestamp()) / (3600 * 24));
        $purger->purgeData($days, true);

        // ensure that two bot requests were removed
        $tableName = BotRequestsDao::getPrefixedTableName();
        $sql       = "SELECT * FROM `{$tableName}`";
        $bots      = Db::fetchAll($sql);
        self::assertCount(1, $bots);
        self::assertEquals('MistralAI-User', $bots[0]['bot_name']);
    }
}
