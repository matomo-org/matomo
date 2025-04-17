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
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates;

class Updates_5_4_0_b4 extends Updates
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
        return [
            // log visit
            $this->migration->db->addColumn('log_visit', 'idvisitor_new', 'CHAR(16) NOT NULL', 'idvisitor'),
            $this->migration->db->addColumn('log_visit', 'config_id_new', 'CHAR(16) NOT NULL', 'config_id'),
            $this->migration->db->addColumn('log_visit', 'location_ip_new', 'VARCHAR(32) NOT NULL', 'location_ip'),
            $this->migration->db->dropIndex('log_visit', 'index_idsite_idvisitor_time'),
            $this->migration->db->dropIndex('log_visit', 'index_idsite_config_datetime'),
            $this->migration->db->sql('UPDATE ' . Common::prefixTable('log_visit') . ' SET idvisitor_new = HEX(idvisitor), config_id_new = HEX(config_id), location_ip_new = HEX(location_ip)'),
            $this->migration->db->dropColumns('log_visit', ['idvisitor', 'config_id', 'location_ip']),
            $this->migration->db->changeColumn('log_visit', 'idvisitor_new', 'idvisitor', 'CHAR(16) NOT NULL'),
            $this->migration->db->changeColumn('log_visit', 'config_id_new', 'config_id', 'CHAR(16) NOT NULL'),
            $this->migration->db->changeColumn('log_visit', 'location_ip_new', 'location_ip', 'CHAR(32) NOT NULL'),
            $this->migration->db->sql('ALTER TABLE ' . Common::prefixTable('log_visit') . ' ADD INDEX index_idsite_config_datetime (idsite, config_id, visit_last_action_time)'),
            $this->migration->db->sql('ALTER TABLE ' . Common::prefixTable('log_visit') . ' ADD INDEX index_idsite_idvisitor_time (idsite, idvisitor, visit_last_action_time DESC)'),

            // log_conversion_item
            $this->migration->db->addColumn('log_conversion_item', 'idvisitor_new', 'CHAR(16) NOT NULL'),
            $this->migration->db->sql('UPDATE ' . Common::prefixTable('log_conversion_item') . ' SET idvisitor_new = HEX(idvisitor)'),
            $this->migration->db->dropColumn('log_conversion_item', 'idvisitor'),
            $this->migration->db->changeColumn('log_conversion_item', 'idvisitor_new', 'idvisitor', 'CHAR(16) NOT NULL'),

            // log_conversion
            $this->migration->db->addColumn('log_conversion', 'idvisitor_new', 'CHAR(16) NOT NULL'),
            $this->migration->db->sql('UPDATE ' . Common::prefixTable('log_conversion') . ' SET idvisitor_new = HEX(idvisitor)'),
            $this->migration->db->dropColumn('log_conversion', 'idvisitor'),
            $this->migration->db->changeColumn('log_conversion', 'idvisitor_new', 'idvisitor', 'CHAR(16) NOT NULL'),

            // log_link_visit_action
            $this->migration->db->addColumn('log_link_visit_action', 'idvisitor_new', 'CHAR(16) NOT NULL'),
            $this->migration->db->sql('UPDATE ' . Common::prefixTable('log_link_visit_action') . ' SET idvisitor_new = HEX(idvisitor)'),
            $this->migration->db->dropColumn('log_link_visit_action', 'idvisitor'),
            $this->migration->db->changeColumn('log_link_visit_action', 'idvisitor_new', 'idvisitor', 'CHAR(16) NOT NULL'),
        ];
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
