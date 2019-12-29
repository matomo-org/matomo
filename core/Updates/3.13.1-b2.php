<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Plugins\GeoIp2\GeoIP2AutoUpdater;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 3.13.1-b2.
 */
class Updates_3_13_1_b2 extends PiwikUpdates
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
        $optionTable = Common::prefixTable('option');
        $migration = $this->migration->db->boundSql("UPDATE $optionTable SET option_value = ? WHERE option_name = ? AND option_value = ?",
            [GeoIp2::getDbIpLiteUrl(), GeoIP2AutoUpdater::LOC_URL_OPTION_NAME, GeoIp2::GEO_LITE_URL]);
        return [$migration];
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
