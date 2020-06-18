<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Plugin\Manager;
use Piwik\Plugins\Installation\ServerFilesGenerator;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 4.0.0-b2.
 */
class Updates_4_0_0_b2 extends PiwikUpdates
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
        $migrations = array();

        // invalidations table
        $migrations[] = $this->migration->db->createTable('archive_invalidations', [
            'idinvalidation' => 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
            'idarchive' => 'INTEGER UNSIGNED NULL',
            'name' => 'VARCHAR(255) NOT NULL',
            'idsite' => 'INTEGER NOT NULL',
            'date1' => 'DATE NOT NULL',
            'date2' => 'DATE NOT NULL',
            'period' => 'TINYINT UNSIGNED NOT NULL',
            'ts_invalidated' => 'DATETIME NOT NULL',
            'status' => 'TINYINT(1) UNSIGNED DEFAULT 0',
        ], ['idinvalidation']);

        $migrations[] = $this->migration->db->addIndex('archive_invalidations', ['idsite', 'date1', 'period'], 'index_idsite_dates_period_name');
        // keep piwik_ignore for existing  installs
        $migrations[] = $this->migration->config->set('Tracker', 'ignore_visits_cookie_name', 'piwik_ignore');

        if (!Manager::getInstance()->isPluginActivated('CustomDimensions')) {
            $migrations[] = $this->migration->plugin->activate('CustomDimensions');
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));

        if ($this->usesGeoIpLegacyLocationProvider()) {
            // switch to default provider if GeoIp Legacy was still in use
            LocationProvider::setCurrentProvider(LocationProvider\DefaultProvider::ID);
        }

        ServerFilesGenerator::createFilesForSecurity();
    }

    protected function usesGeoIpLegacyLocationProvider()
    {
        $currentProvider = LocationProvider::getCurrentProviderId();

        return in_array($currentProvider, [
            'geoip_pecl',
            'geoip_php',
            'geoip_serverbased',
        ]);
    }
}
