<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_1_5_b2 extends Updates
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
            $this->migration->db->addColumns('log_link_visit_action', $customVarColumns, 'time_spent_ref_action')
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
