<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking;

use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;
use Piwik\Updater;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates;

class Updates_5_12_0_alpha extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    /**
     * @return Migration\Db[]
     */
    public function getMigrations(Updater $updater): array
    {
        return [
            $this->migration->db->addIndex(
                BotRequestsDao::getTableName(),
                ['idsite', 'bot_type', 'server_time'],
                BotRequestsDao::INDEX_IDSITE_BOT_TYPE_SERVER_TIME
            ),
        ];
    }

    public static function isMajorUpdate(): bool
    {
        return true;
    }

    public function doUpdate(Updater $updater): void
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
