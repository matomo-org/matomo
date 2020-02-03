<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Date;
use Piwik\DbHelper;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Plugins\UserCountry\LocationProvider;
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
        $migrations = array();
        $migrations[] = $this->migration->db->changeColumnType('log_action', 'name', 'VARCHAR(4096)');
        $migrations[] = $this->migration->db->changeColumnType('log_conversion', 'url', 'VARCHAR(4096)');
        $migrations[] = $this->migration->db->changeColumn('log_link_visit_action', 'interaction_position', 'pageview_position', 'MEDIUMINT UNSIGNED DEFAULT NULL');

        /** APP SPECIFIC TOKEN START */
        $migrations[] = $this->migration->db->createTable('user_token_auth', array(
            'idusertokenauth' => 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
            'login' => 'VARCHAR(100) NOT NULL',
            'description' => 'VARCHAR('.Model::MAX_LENGTH_TOKEN_DESCRIPTION.') NOT NULL',
            'password' => 'VARCHAR(255) NOT NULL',
            'system_token' => 'TINYINT(1) NOT NULL DEFAULT 0',
            'hash_algo' => 'VARCHAR(30) NOT NULL',
            'last_used' => 'DATETIME NULL',
            'date_created' => ' DATETIME NOT NULL',
            'date_expired' => ' DATETIME NULL',
        ), 'idusertokenauth');
        $migrations[] = $this->migration->db->addUniqueKey('user_token_auth', 'password', 'uniq_password');

        $migrations[] = $this->migration->db->dropIndex('user', 'uniq_keytoken');

        $userModel = new Model();
        foreach ($userModel->getUsers(array()) as $user) {
            if (!empty($user['token_auth'])) {
                $migrations[] = $this->migration->db->insert('user_token_auth', array(
                    'login' => $user['login'],
                    'description' => 'Created by Matomo 4 migration',
                    'password' => $userModel->hashTokenAuth($user['token_auth']),
                    'date_created' => Date::now()->getDatetime()
                ));
            }
        }

        $migrations[] = $this->migration->db->dropColumn('user', 'alias');

        // we don't delete the token_auth column so users can still downgrade to 3.X if they want to. However, the original
        // token_auth will be regenerated for security reasons to no longer have it in plain text. So this column will be no longer used
        // unless someone downgrades to 3.x
        $columns = DbHelper::getTableColumns(Common::prefixTable('user'));
        if (isset($columns['token_auth'])) {
            $sql = sprintf('UPDATE %s set token_auth = MD5(CONCAT(NOW(), UUID()))', Common::prefixTable('user'));
            $migrations[] = $this->migration->db->sql($sql, Updater\Migration\Db::ERROR_CODE_UNKNOWN_COLUMN);
        }

        /** APP SPECIFIC TOKEN END */

        $customTrackerPluginActive = false;
        if (in_array('CustomPiwikJs', Config::getInstance()->Plugins['Plugins'])) {
            $customTrackerPluginActive = true;
        }

        $migrations[] = $this->migration->plugin->activate('BulkTracking');
        $migrations[] = $this->migration->plugin->deactivate('CustomPiwikJs');
        $migrations[] = $this->migration->plugin->uninstall('CustomPiwikJs');

        if ('utf8mb4' === DbHelper::getDefaultCharset()) {
            $allTables = DbHelper::getTablesInstalled();
            $database = Config::getInstance()->database['dbname'];

            $migrations[] = $this->migration->db->sql("ALTER DATABASE $database CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;;");

            foreach ($allTables as $table) {
                $migrations[] = $this->migration->db->sql("ALTER TABLE $table CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            }
        }

        if ($customTrackerPluginActive) {
            $migrations[] = $this->migration->plugin->activate('CustomJsTracker');
        }

        // Move the site search fields of log_visit out of custom variables into their own fields
        $migrations[] = $this->migration->db->addColumn('log_link_visit_action', 'search_cat', 'VARCHAR(200) NULL');
        $migrations[] = $this->migration->db->addColumn('log_link_visit_action', 'search_count', 'INTEGER(10) UNSIGNED NULL');
        $visitActionTable = Common::prefixTable('log_link_visit_action');
        $migrations[] = $this->migration->db->sql("UPDATE $visitActionTable SET search_cat = custom_var_v4 WHERE custom_var_k4 = '_pk_scat'");
        $migrations[] = $this->migration->db->sql("UPDATE $visitActionTable SET search_count = custom_var_v5 WHERE custom_var_k5 = '_pk_scount'");

        if ($this->usesGeoIpLegacyLocationProvider()) {
            // activate GeoIp2 plugin for users still using GeoIp2 Legacy (others might have it disabled on purpose)
            $migrations[] = $this->migration->plugin->activate('GeoIp2');
        }

        // remove old options
        $migrations[] = $this->migration->db->sql('DELETE FROM `' . Common::prefixTable('option') . '` WHERE option_name IN ("geoip.updater_period", "geoip.loc_db_url", "geoip.isp_db_url", "geoip.org_db_url")');

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));

        if ($this->usesGeoIpLegacyLocationProvider()) {
            // switch to default provider if GeoIp Legacy was still in use
            LocationProvider::setCurrentProvider(LocationProvider\DefaultProvider::ID);
        }

        // switch default charset to utf8mb4 in config if available
        $config = Config::getInstance();
        if ('utf8mb4' === DbHelper::getDefaultCharset()) {
            $config->database['charset'] = 'utf8mb4';
        } else {
            $config->database['charset'] = 'utf8';
        }
        $config->forceSave();
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
