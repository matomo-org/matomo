<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\DbHelper;
use Piwik\Config;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 5.0.0-b1.
 */
class Updates_5_0_0_b1 extends PiwikUpdates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater)
    {
        $migrations = [];

        // force utf8mb4 conversion in Matomo 5
        if (DbHelper::getUsedCharset() !== 'utf8mb4') {
            foreach (DbHelper::getUtf8mb4ConversionQueries() as $utf8mb4ConversionQuery) {
                $migrations[] = $this->migration->db->sql($utf8mb4ConversionQuery);
            }
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));

        $config = Config::getInstance();
        $config->database['charset'] = 'utf8mb4';
        $config->forceSave();
    }
}