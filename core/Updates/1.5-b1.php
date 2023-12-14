<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_1_5_b1 extends Updates
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
        return array(
            $this->migration->db->createTable('log_conversion_item', array(
                'idsite' => 'int(10) UNSIGNED NOT NULL',
                'idvisitor' => 'BINARY(8) NOT NULL',
                'server_time' => 'DATETIME NOT NULL',
                'idvisit' => 'INTEGER(10) UNSIGNED NOT NULL',
                'idorder' => 'varchar(100) NOT NULL',
                'idaction_sku' => 'INTEGER(10) UNSIGNED NOT NULL',
                'idaction_name' => 'INTEGER(10) UNSIGNED NOT NULL',
                'idaction_category' => 'INTEGER(10) UNSIGNED NOT NULL',
                'price' => 'FLOAT NOT NULL',
                'quantity' => 'INTEGER(10) UNSIGNED NOT NULL',
                'deleted' => 'TINYINT(1) UNSIGNED NOT NULL',
            ), array('idvisit', 'idorder', 'idaction_sku')),
            $this->migration->db->addIndex('log_conversion_item', array('idsite', 'server_time')),

            $this->migration->db->addColumns('log_visit', array(
                'visitor_days_since_order' => 'SMALLINT(5) UNSIGNED NOT NULL',
                'visit_goal_buyer' => 'TINYINT(1) NOT NULL'
            )),
            
            $this->migration->db->addColumn('log_conversion', 'visitor_days_since_order', 'SMALLINT(5) UNSIGNED NOT NULL'),
            $this->migration->db->addColumns('log_conversion', array(
                'idorder' => 'varchar(100) default NULL',
                'items' => 'SMALLINT UNSIGNED DEFAULT NULL',
                'revenue_subtotal' => 'float default NULL',
                'revenue_tax' => 'float default NULL',
                'revenue_shipping' => 'float default NULL',
                'revenue_discount' => 'float default NULL',
            )),
            $this->migration->db->addUniqueKey('log_conversion', array('idsite', 'idorder'))
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
