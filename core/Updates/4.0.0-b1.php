<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\DataAccess\TableMetadata;
use Piwik\Updater\Migration\Custom as CustomMigration;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Plugin\Manager;
use Piwik\Plugins\CoreAdminHome\Commands\MigrateTokenAuths;
use Piwik\Plugins\CoreHome\Columns\Profilable;
use Piwik\Plugins\CoreHome\Columns\VisitorSecondsSinceFirst;
use Piwik\Plugins\CoreHome\Columns\VisitorSecondsSinceOrder;
use Piwik\Plugins\Installation\ServerFilesGenerator;
use Piwik\Plugins\PagePerformance\Columns\TimeDomCompletion;
use Piwik\Plugins\PagePerformance\Columns\TimeDomProcessing;
use Piwik\Plugins\PagePerformance\Columns\TimeNetwork;
use Piwik\Plugins\PagePerformance\Columns\TimeOnLoad;
use Piwik\Plugins\PagePerformance\Columns\TimeServer;
use Piwik\Plugins\PagePerformance\Columns\TimeTransfer;
use Piwik\Common;
use Piwik\Config;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\VisitorInterest\Columns\VisitorSecondsSinceLast;
use Piwik\SettingsPiwik;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 4.0.0-b1.
 */
class Updates_4_0_0_b1 extends PiwikUpdates
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
        $tableMetadata = new TableMetadata();

        $columnsToAdd = [];

        $migrations = [];

        $domain = Config::getLocalConfigPath() === Config::getDefaultLocalConfigPath() ? '' : Config::getHostname();
        $domainArg = !empty($domain) ? "--matomo-domain=". escapeshellarg($domain) . " " : '';
        $toString = sprintf('./console %score:matomo4-migrate-token-auths', $domainArg);
        $custom = new CustomMigration(array(MigrateTokenAuths::class, 'migrate'), $toString);

        $migrations[] = $custom;

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
            'report' => 'VARCHAR(255) NULL',
        ], ['idinvalidation']);

        $migrations[] = $this->migration->db->addIndex('archive_invalidations', ['idsite', 'date1', 'period'], 'index_idsite_dates_period_name');

        $migrations[] = $this->migration->db->dropColumn('user', 'alias');

        // prevent possible duplicates when shorting session id
        $migrations[] = $this->migration->db->sql('DELETE FROM `' . Common::prefixTable('session') . '` WHERE length(id) > 190');

        $migrations[] = $this->migration->db->changeColumnType('session', 'id', 'VARCHAR(191)');
        $migrations[] = $this->migration->db->changeColumnType('site_url', 'url', 'VARCHAR(190)');
        $migrations[] = $this->migration->db->changeColumnType('option', 'option_name', 'VARCHAR(191)');

        $migrations[] = $this->migration->db->changeColumnType('log_action', 'name', 'VARCHAR(4096)');
        $migrations[] = $this->migration->db->changeColumnType('log_conversion', 'url', 'VARCHAR(4096)');
        $migrations[] = $this->migration->db->changeColumn('log_link_visit_action', 'interaction_position', 'pageview_position', 'MEDIUMINT UNSIGNED DEFAULT NULL');

        $customTrackerPluginActive = false;
        if (in_array('CustomPiwikJs', Config::getInstance()->Plugins['Plugins'])) {
            $customTrackerPluginActive = true;
        }

        $migrations[] = $this->migration->plugin->activate('BulkTracking');
        $migrations[] = $this->migration->plugin->deactivate('CustomPiwikJs');
        $migrations[] = $this->migration->plugin->uninstall('CustomPiwikJs');

        if ($customTrackerPluginActive) {
            $migrations[] = $this->migration->plugin->activate('CustomJsTracker');
        }

        // Prepare all installed tables for utf8mb4 conversions. e.g. make some indexed fields smaller so they don't exceed the maximum key length
        $allTables = DbHelper::getTablesInstalled();

        foreach ($allTables as $table) {
            if (preg_match('/archive_/', $table) == 1) {
                $tableNameUnprefixed = Common::unprefixTable($table);
                $migrations[] = $this->migration->db->changeColumnType($tableNameUnprefixed, 'name', 'VARCHAR(190)');
            }
        }

        // Move the site search fields of log_visit out of custom variables into their own fields
        $columnsToAdd['log_link_visit_action']['search_cat'] = 'VARCHAR(200) NULL';
        $columnsToAdd['log_link_visit_action']['search_count'] = 'INTEGER(10) UNSIGNED NULL';

        // replace days to ... dimensions w/ seconds dimensions
        foreach (['log_visit', 'log_conversion'] as $table) {
            $columnsToAdd[$table]['visitor_seconds_since_first'] = VisitorSecondsSinceFirst::COLUMN_TYPE;
            $columnsToAdd[$table]['visitor_seconds_since_order'] = VisitorSecondsSinceOrder::COLUMN_TYPE;
        }
        $columnsToAdd['log_visit']['visitor_seconds_since_last'] = VisitorSecondsSinceLast::COLUMN_TYPE;
        $columnsToAdd['log_visit']['profilable'] = Profilable::COLUMN_TYPE;
        $columnsToAdd['log_link_visit_action'][TimeDomCompletion::COLUMN_NAME] = TimeDomCompletion::COLUMN_TYPE;
        $columnsToAdd['log_link_visit_action'][TimeDomProcessing::COLUMN_NAME] = TimeDomProcessing::COLUMN_TYPE;
        $columnsToAdd['log_link_visit_action'][TimeNetwork::COLUMN_NAME] = TimeNetwork::COLUMN_TYPE;
        $columnsToAdd['log_link_visit_action'][TimeOnLoad::COLUMN_NAME] = TimeOnLoad::COLUMN_TYPE;
        $columnsToAdd['log_link_visit_action'][TimeServer::COLUMN_NAME] = TimeServer::COLUMN_TYPE;
        $columnsToAdd['log_link_visit_action'][TimeTransfer::COLUMN_NAME] = TimeTransfer::COLUMN_TYPE;

        $columnsToMaybeAdd = ['revenue', 'revenue_discount', 'revenue_shipping', 'revenue_subtotal', 'revenue_tax'];
        $columnsLogConversion = $tableMetadata->getColumns(Common::prefixTable('log_conversion'));
        foreach ($columnsToMaybeAdd as $columnToMaybeAdd) {
            if (!in_array($columnToMaybeAdd, $columnsLogConversion, true)) {
                $columnsToAdd['log_conversion'][$columnToMaybeAdd] = 'DOUBLE NULL DEFAULT NULL';
            }
        }

        foreach ($columnsToAdd as $table => $columns) {
            $migrations[] = $this->migration->db->addColumns($table, $columns);

            foreach ($columns as $columnName => $columnType) {
                $optionKey = 'version_' . $table . '.' . $columnName;
                $optionValue = $columnType;

                if ($table == 'log_visit' && isset($columnsToAdd['log_conversion'][$columnName])) {
                    $optionValue .= '1'; // column is in log_conversion too
                }

                $migrations[] = $this->migration->db->sql("INSERT IGNORE INTO `" . Common::prefixTable('option')
                    . "` (option_name, option_value) VALUES ('$optionKey', '$optionValue')");
            }
        }

        if (Manager::getInstance()->isPluginInstalled('CustomVariables')) {
            $visitActionTable = Common::prefixTable('log_link_visit_action');
            $migrations[]     = $this->migration->db->sql("UPDATE $visitActionTable SET search_cat = if(custom_var_k4 = '_pk_scat', custom_var_v4, search_cat), search_count = if(custom_var_k5 = '_pk_scount', custom_var_v5, search_count) WHERE custom_var_k4 = '_pk_scat' or custom_var_k5 = '_pk_scount'");
        }

        if ($this->usesGeoIpLegacyLocationProvider()) {
            // activate GeoIp2 plugin for users still using GeoIp2 Legacy (others might have it disabled on purpose)
            $migrations[] = $this->migration->plugin->activate('GeoIp2');
        }

        // remove old options
        $migrations[] = $this->migration->db->sql('DELETE FROM `' . Common::prefixTable('option') . '` WHERE option_name IN ("geoip.updater_period", "geoip.loc_db_url", "geoip.isp_db_url", "geoip.org_db_url")');

        // init seconds_to_... columns
        $logVisitColumns = $tableMetadata->getColumns(Common::prefixTable('log_visit'));
        $hasDaysColumnInVisit = in_array('visitor_days_since_first', $logVisitColumns);

        if ($hasDaysColumnInVisit) {
            $migrations[] = $this->migration->db->sql("UPDATE " . Common::prefixTable('log_visit')
                . " SET visitor_seconds_since_first = visitor_days_since_first * 86400, 
                    visitor_seconds_since_order = visitor_days_since_order * 86400,
                    visitor_seconds_since_last = visitor_days_since_last * 86400");
        }

        $logConvColumns = $tableMetadata->getColumns(Common::prefixTable('log_conversion'));
        $hasDaysColumnInConv = in_array('visitor_days_since_first', $logConvColumns);

        if ($hasDaysColumnInConv) {
            $migrations[] = $this->migration->db->sql("UPDATE " . Common::prefixTable('log_conversion')
                . " SET visitor_seconds_since_first = visitor_days_since_first * 86400, 
                    visitor_seconds_since_order = visitor_days_since_order * 86400");
        }

        // remove old days_to_... columns
        $migrations[] = $this->migration->db->dropColumns('log_visit', [
            'config_gears',
            'config_director',
            'visitor_days_since_first',
            'visitor_days_since_order',
            'visitor_days_since_last',
        ]);
        $migrations[] = $this->migration->db->dropColumns('log_conversion', [
            'visitor_days_since_first',
            'visitor_days_since_order',
        ]);

        $config = Config::getInstance();

        if (!empty($config->mail['type']) && $config->mail['type'] === 'Crammd5') {
            $migrations[] = $this->migration->config->set('mail', 'type', 'Cram-md5');
        }

        // keep piwik_ignore for existing  installs
        $migrations[] = $this->migration->config->set('Tracker', 'ignore_visits_cookie_name', 'piwik_ignore');

        $migrations[] = $this->migration->plugin->activate('PagePerformance');
        if (!Manager::getInstance()->isPluginActivated('CustomDimensions')) {
            $migrations[] = $this->migration->plugin->activate('CustomDimensions');
        }

        $configTableLimit = Config::getInstance()->getFromLocalConfig('General')['datatable_archiving_maximum_rows_custom_variables'] ?? null;
        $configSubTableLimit = Config::getInstance()->getFromLocalConfig('General')['datatable_archiving_maximum_rows_subtable_custom_variables'] ?? null;

        if ($configTableLimit) {
            $migrations[] = $this->migration->config->set('General', 'datatable_archiving_maximum_rows_custom_dimensions', $configTableLimit);
        }
        if ($configSubTableLimit) {
            $migrations[] = $this->migration->config->set('General', 'datatable_archiving_maximum_rows_subtable_custom_dimensions', $configSubTableLimit);
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $salt = SettingsPiwik::getSalt();
        $sessions = Db::fetchAll('SELECT id from ' . Common::prefixTable('session'));

        foreach ($sessions as $session) {
            if (!empty($session['id']) && mb_strlen($session['id']) != 128) {
                $bind = [ hash('sha512', $session['id'] . $salt), $session['id'] ];
                try {
                    Db::query(sprintf('UPDATE %s SET id = ? WHERE id = ?', Common::prefixTable('session')), $bind);
                } catch (\Exception $e) {
                    // ignore possible duplicate key errors
                }
            }
        }

        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));

        if ($this->usesGeoIpLegacyLocationProvider()) {
            // switch to default provider if GeoIp Legacy was still in use
            LocationProvider::setCurrentProvider(LocationProvider\DefaultProvider::ID);
        }

        // eg the case when not updating from most recent Matomo 3.X and when not using the UI updater
        // afterwards the should receive a notification that the plugins are outdated
        self::ensureCorePluginsThatWereMovedToMarketplaceCanBeUpdated();
        ServerFilesGenerator::createFilesForSecurity();
    }

    public static function ensureCorePluginsThatWereMovedToMarketplaceCanBeUpdated()
    {
        $plugins = ['Provider', 'CustomVariables'];
        $pluginManager = Manager::getInstance();
        foreach ($plugins as $plugin) {
            if ($pluginManager->isPluginThirdPartyAndBogus($plugin)) {
                $pluginDir = Manager::getPluginDirectory($plugin);

                if (is_dir($pluginDir) &&
                    file_exists($pluginDir . '/' . $plugin . '.php')
                    && !file_exists($pluginDir . '/plugin.json')
                    && is_writable($pluginDir)) {
                    file_put_contents($pluginDir . '/plugin.json', '{
  "name": "'.$plugin.'",
  "description": "'.$plugin.'",
  "version": "3.14.1",
  "theme": false,
  "require": {
    "piwik": ">=3.0.0,<4.0.0-b1"
  },
  "authors": [
    {
      "name": "Matomo",
      "email": "hello@matomo.org",
      "homepage": "https:\/\/matomo.org"
    }
  ],
  "homepage": "https:\/\/matomo.org",
  "license": "GPL v3+",
  "keywords": ["'.$plugin.'"]
}');
                    // otherwise cached information might be used and it won't be loaded otherwise within same request
                    $pluginObj = $pluginManager->loadPlugin($plugin);
                    $pluginObj->reloadPluginInformation();
                    $pluginManager->unloadPlugin($pluginObj); // prevent any events being posted to it somehow
                }
            }
        }
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
