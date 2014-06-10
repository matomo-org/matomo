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
class Updates_1_5_b3 extends Updates
{
    static function getSql()
    {
        return array(
            'ALTER TABLE `' . Common::prefixTable('log_visit') . '`
				 CHANGE custom_var_k1 custom_var_k1 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_v1 custom_var_v1 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_k2 custom_var_k2 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_v2 custom_var_v2 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_k3 custom_var_k3 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_v3 custom_var_v3 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_k4 custom_var_k4 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_v4 custom_var_v4 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_k5 custom_var_k5 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_v5 custom_var_v5 VARCHAR(100) DEFAULT NULL'                                                                              => false,
            'ALTER TABLE `' . Common::prefixTable('log_conversion') . '`
				 CHANGE custom_var_k1 custom_var_k1 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_v1 custom_var_v1 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_k2 custom_var_k2 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_v2 custom_var_v2 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_k3 custom_var_k3 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_v3 custom_var_v3 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_k4 custom_var_k4 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_v4 custom_var_v4 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_k5 custom_var_k5 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_v5 custom_var_v5 VARCHAR(100) DEFAULT NULL'        => false,
            'ALTER TABLE `' . Common::prefixTable('log_link_visit_action') . '`
				 CHANGE custom_var_k1 custom_var_k1 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_v1 custom_var_v1 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_k2 custom_var_k2 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_v2 custom_var_v2 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_k3 custom_var_k3 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_v3 custom_var_v3 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_k4 custom_var_k4 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_v4 custom_var_v4 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_k5 custom_var_k5 VARCHAR(100) DEFAULT NULL,
				 CHANGE custom_var_v5 custom_var_v5 VARCHAR(100) DEFAULT NULL' => false,
        );
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());
    }
}
