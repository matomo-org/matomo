<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_1_2_rc1 extends Updates
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
        $customVarType = 'VARCHAR(100) DEFAULT NULL';
        $customVarColumns = array(
            'custom_var_k1' => $customVarType,
            'custom_var_v1' => $customVarType,
            'custom_var_k2' => $customVarType,
            'custom_var_v2' => $customVarType,
            'custom_var_k3' => $customVarType,
            'custom_var_v3' => $customVarType,
            'custom_var_k4' => $customVarType,
            'custom_var_v4' => $customVarType,
            'custom_var_k5' => $customVarType,
            'custom_var_v5' => $customVarType,
        );

        return array(
            // Various performance improvements schema updates
            $this->migration->db->sql('ALTER TABLE `' . Common::prefixTable('log_visit') . '`
                DROP INDEX `index_idsite_date_config`,
                DROP INDEX `index_idsite_datetime_config`
               ', array(Updater\Migration\Db::ERROR_CODE_UNKNOWN_COLUMN, Updater\Migration\Db::ERROR_CODE_COLUMN_NOT_EXISTS)),
            $this->migration->db->sql('ALTER TABLE `' . Common::prefixTable('log_visit') . '`
                DROP `visit_server_date`,
                ADD `idvisitor` BINARY(8) NOT NULL AFTER `idsite`,
                ADD `config_id` BINARY(8) NOT NULL AFTER `config_md5config`
               ', array(Updater\Migration\Db::ERROR_CODE_UNKNOWN_COLUMN, Updater\Migration\Db::ERROR_CODE_COLUMN_NOT_EXISTS)),
            $this->migration->db->sql('ALTER TABLE `' . Common::prefixTable('log_visit') . '`
                ADD `visit_entry_idaction_name` INT UNSIGNED NOT NULL AFTER `visit_entry_idaction_url`,
                ADD `visit_exit_idaction_name` INT UNSIGNED NOT NULL AFTER `visit_exit_idaction_url`,
                CHANGE `visit_exit_idaction_url` `visit_exit_idaction_url` INT UNSIGNED NOT NULL,
                CHANGE `visit_entry_idaction_url` `visit_entry_idaction_url` INT UNSIGNED NOT NULL,
                CHANGE `referer_type` `referer_type` TINYINT UNSIGNED NULL DEFAULT NULL,
                ADD visitor_count_visits SMALLINT(5) UNSIGNED NOT NULL AFTER `visitor_returning`,
                ADD visitor_days_since_last SMALLINT(5) UNSIGNED NOT NULL,
                ADD visitor_days_since_first SMALLINT(5) UNSIGNED NOT NULL
               ', Updater\Migration\Db::ERROR_CODE_DUPLICATE_COLUMN),

            $this->migration->db->addColumns('log_visit', $customVarColumns),

            $this->migration->db->addColumns('log_link_visit_action', array(
                'idsite' => 'INT( 10 ) UNSIGNED NOT NULL',
                'idvisitor' => 'BINARY(8) NOT NULL',
                'idaction_name_ref' => 'INT UNSIGNED NOT NULL'
            ), 'idlink_va'),

            $this->migration->db->addColumn('log_link_visit_action', 'server_time', 'DATETIME', 'idsite'),
            $this->migration->db->addIndex('log_link_visit_action', array('idsite', 'server_time'), 'index_idsite_servertime'),

            $this->migration->db->sql('ALTER TABLE `' . Common::prefixTable('log_conversion') . '`
                DROP `referer_idvisit`,
                ADD `idvisitor` BINARY(8) NOT NULL AFTER `idsite`
               ', array(Updater\Migration\Db::ERROR_CODE_DUPLICATE_COLUMN, Updater\Migration\Db::ERROR_CODE_COLUMN_NOT_EXISTS)),
            $this->migration->db->addColumns('log_conversion', array(
                'visitor_count_visits' => 'SMALLINT(5) UNSIGNED NOT NULL',
                'visitor_days_since_first' => 'SMALLINT(5) UNSIGNED NOT NULL',
            )),
            $this->migration->db->addColumns('log_conversion', $customVarColumns),

            // Migrate 128bits IDs inefficiently stored as 8bytes (256 bits) into 64bits
            $this->migration->db->sql('UPDATE ' . Common::prefixTable('log_visit') . '
                SET idvisitor = binary(unhex(substring(visitor_idcookie,1,16))),
                    config_id = binary(unhex(substring(config_md5config,1,16)))
                   ', Updater\Migration\Db::ERROR_CODE_UNKNOWN_COLUMN),
            $this->migration->db->sql('UPDATE ' . Common::prefixTable('log_conversion') . '
                SET idvisitor = binary(unhex(substring(visitor_idcookie,1,16)))
                   ', Updater\Migration\Db::ERROR_CODE_UNKNOWN_COLUMN),

            // Drop migrated fields
            $this->migration->db->sql('ALTER TABLE `' . Common::prefixTable('log_visit') . '`
                DROP visitor_idcookie,
                DROP config_md5config
                ', Updater\Migration\Db::ERROR_CODE_COLUMN_NOT_EXISTS),
            $this->migration->db->sql('ALTER TABLE `' . Common::prefixTable('log_conversion') . '`
                DROP visitor_idcookie
                ', Updater\Migration\Db::ERROR_CODE_COLUMN_NOT_EXISTS),

            // Recreate INDEX on new field
            $this->migration->db->addIndex('log_visit', array('idsite', 'visit_last_action_time', 'config_id'), 'index_idsite_datetime_config'),

            // Backfill action logs as best as we can
            $this->migration->db->sql('UPDATE ' . Common::prefixTable('log_link_visit_action') . ' as action,
                      ' . Common::prefixTable('log_visit') . '  as visit
                SET action.idsite = visit.idsite,
                    action.server_time = visit.visit_last_action_time,
                    action.idvisitor = visit.idvisitor
                WHERE action.idvisit=visit.idvisit
                '),

            $this->migration->db->changeColumnType('log_link_visit_action', 'server_time', 'DATETIME NOT NULL'),

            // New index used max once per request, in case this table grows significantly in the future
            $this->migration->db->addIndex('option', 'autoload', 'autoload'),

            // new field for websites
            $this->migration->db->addColumn('site', 'group', 'VARCHAR( 250 ) NOT NULL'),
        );
    }

    public function doUpdate(Updater $updater)
    {
        // first we disable the plugins and keep an array of warnings messages
        $pluginsToDisableMessage = array(
            'GeoIP'     => "GeoIP plugin was disabled, because it is not compatible with the new Piwik 1.2. \nYou can download the latest version of the plugin, compatible with Piwik 1.2.\n<a target='_blank' rel='noopener' href='https://github.com/matomo-org/matomo/issues/45'>Click here.</a>",
            'EntryPage' => "EntryPage plugin is not compatible with this version of Matomo, it was disabled.",
        );
        $disabledPlugins = array();
        foreach ($pluginsToDisableMessage as $pluginToDisable => $warningMessage) {
            if (\Piwik\Plugin\Manager::getInstance()->isPluginActivated($pluginToDisable)) {
                \Piwik\Plugin\Manager::getInstance()->deactivatePlugin($pluginToDisable);
                $disabledPlugins[] = $warningMessage;
            }
        }

        // Run the SQL
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));

        // Outputs warning message, pointing users to the plugin download page
        if (!empty($disabledPlugins)) {
            throw new \Exception("The following plugins were disabled during the upgrade:"
                . "<ul><li>" .
                implode('</li><li>', $disabledPlugins) .
                "</li></ul>");
        }
    }
}
