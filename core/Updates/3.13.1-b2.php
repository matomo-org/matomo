<?php
/**
 * Matomo - free/libre analytics platform
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
    const GEO_LITE_COUNTRY_URL = 'https://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.tar.gz';

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
        $geoIpLiteUrl = 'https://geolite.maxmind.com/download/geoip/database/GeoLite2-City.tar.gz';
        $optionTable = Common::prefixTable('option');
        $migration1 = $this->migration->db->boundSql(
            "UPDATE `$optionTable` SET option_value = ? WHERE option_name = ? AND option_value = ?",
            [GeoIp2::getDbIpLiteUrl(), GeoIP2AutoUpdater::LOC_URL_OPTION_NAME, $geoIpLiteUrl]
        );
        $migration2 = $this->migration->db->boundSql(
            "UPDATE `$optionTable` SET option_value = ? WHERE option_name = ? AND option_value = ?",
            [GeoIp2::getDbIpLiteUrl('country'), GeoIP2AutoUpdater::LOC_URL_OPTION_NAME, self::GEO_LITE_COUNTRY_URL]
        );
        return [$migration1, $migration2];
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
