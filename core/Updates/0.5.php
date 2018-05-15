<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_0_5 extends Updates
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
        $logActionTable = Common::prefixTable('log_action');

        return array(
            $this->migration->db->addColumn('log_action', 'hash', 'INTEGER(10) UNSIGNED NOT NULL', 'name'),
            $this->migration->db->changeColumn('log_visit', 'visit_exit_idaction', 'visit_exit_idaction_url', 'INTEGER(11) NOT NULL'),
            $this->migration->db->changeColumn('log_visit', 'visit_entry_idaction', 'visit_entry_idaction_url', 'INTEGER(11) NOT NULL'),
            $this->migration->db->changeColumn('log_link_visit_action', 'idaction_ref', 'idaction_url_ref', 'INTEGER(10) UNSIGNED NOT NULL'),
            $this->migration->db->changeColumn('log_link_visit_action', 'idaction', 'idaction_url', 'INTEGER(10) UNSIGNED NOT NULL'),
            $this->migration->db->addColumn('log_link_visit_action', 'idaction_name', 'INTEGER(10) UNSIGNED', 'idaction_url_ref'),
            $this->migration->db->changeColumn('log_conversion', 'idaction', 'idaction_url', 'INTEGER(11) UNSIGNED NOT NULL'),
            $this->migration->db->sql('UPDATE ' . $logActionTable . ' SET `hash` = CRC32(name);'),
            $this->migration->db->addIndex('log_action', array('type', 'hash'), 'index_type_hash'),
            $this->migration->db->dropIndex('log_action', 'index_type_name'),
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
