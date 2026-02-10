<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;
use Piwik\Updater;
use Piwik\Updater\Migration\Custom as CustomMigration;
use Piwik\Updates;

class Updates_5_8_0_b1 extends Updates
{
    public function getMigrations(Updater $updater): array
    {
        $migrations = [];

        $tableName = BotRequestsDao::getPrefixedTableName();
        if (DbHelper::tableExists($tableName)) {
            $migrations[] = new CustomMigration(function () use ($tableName) {
                Db::query(
                    sprintf('UPDATE `%s` SET bot_type = ? WHERE bot_type = ?', $tableName),
                    [BotDetector::BOT_TYPE_AI_CHATBOT, 'ai_assistant']
                );
            }, sprintf('UPDATE %s SET bot_type = "%s" WHERE bot_type = "ai_assistant"', $tableName, BotDetector::BOT_TYPE_AI_CHATBOT));
        }

        $migrations[] = new CustomMigration(function () {
            $invalidator = StaticContainer::get(ArchiveInvalidator::class);
            $invalidator->scheduleReArchiving('all', 'BotTracking', null, Date::factory('2000-01-01'));
        }, './console core:invalidate-report-data --plugin=BotTracking');

        return $migrations;
    }

    public function doUpdate(Updater $updater): void
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
