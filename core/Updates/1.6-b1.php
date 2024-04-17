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
class Updates_1_6_b1 extends Updates
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
        $idActionType = 'INTEGER(10) UNSIGNED NOT NULL';
        $customVarType = 'VARCHAR(200) DEFAULT NULL';

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
            $this->migration->db->addColumns('log_conversion_item', array(
                'idaction_category2' => $idActionType,
                'idaction_category3' => $idActionType,
                'idaction_category4' => $idActionType,
                'idaction_category5' => $idActionType,
            ), 'idaction_category'),
            $this->migration->db->changeColumnTypes('log_visit', $customVarColumns),
            $this->migration->db->changeColumnTypes('log_conversion', $customVarColumns),
            $this->migration->db->changeColumnTypes('log_link_visit_action', $customVarColumns),
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
