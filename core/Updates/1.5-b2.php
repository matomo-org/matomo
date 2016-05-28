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

/**
 */
class Updates_1_5_b2 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        return array(
            'ALTER TABLE `' . Common::prefixTable('log_link_visit_action') . '`
				 ADD  custom_var_k1 VARCHAR(100) DEFAULT NULL AFTER time_spent_ref_action,
				 ADD  custom_var_v1 VARCHAR(100) DEFAULT NULL,
				 ADD  custom_var_k2 VARCHAR(100) DEFAULT NULL,
				 ADD  custom_var_v2 VARCHAR(100) DEFAULT NULL,
				 ADD  custom_var_k3 VARCHAR(100) DEFAULT NULL,
				 ADD  custom_var_v3 VARCHAR(100) DEFAULT NULL,
				 ADD  custom_var_k4 VARCHAR(100) DEFAULT NULL,
				 ADD  custom_var_v4 VARCHAR(100) DEFAULT NULL,
				 ADD  custom_var_k5 VARCHAR(100) DEFAULT NULL,
				 ADD  custom_var_v5 VARCHAR(100) DEFAULT NULL' => 1060,
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
