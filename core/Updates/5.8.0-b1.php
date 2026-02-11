<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Plugins\BotTracking\BotDetector;
use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;
use Piwik\Updater;
use Piwik\Updater\Migration\Custom as CustomMigration;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

class Updates_5_8_0_b1 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater): array
    {
        $migrations = [];

        $tableName = BotRequestsDao::getPrefixedTableName();
        $earliestDatesBySite = [];
        if (DbHelper::tableExists($tableName)) {
            $migrations[] = $this->migration->db->boundSql(sprintf('UPDATE `%s` SET bot_type = ? WHERE bot_type = ?', $tableName), [BotDetector::BOT_TYPE_AI_CHATBOT, 'ai_assistant']);
            $earliestDatesBySite = Db::fetchAll(
                sprintf('SELECT idsite, DATE(MIN(server_time)) as earliest_date FROM `%s` GROUP BY idsite', $tableName)
            );
        }

        $migrations[] = new CustomMigration(function () use ($earliestDatesBySite) {
            $invalidator = StaticContainer::get(ArchiveInvalidator::class);
            foreach ($earliestDatesBySite as $siteData) {
                if (empty($siteData['idsite']) || empty($siteData['earliest_date'])) {
                    continue;
                }

                $startDate = Date::factory($siteData['earliest_date'])->subDay(1);
                $invalidator->scheduleReArchiving([(int)$siteData['idsite']], 'BotTracking', null, $startDate);
            }
        }, './console core:invalidate-report-data --plugin=BotTracking --sites=<site-id> --dates=<site-earliest-bot-date-minus-1-day>,today');

        return $migrations;
    }

    public function doUpdate(Updater $updater): void
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
