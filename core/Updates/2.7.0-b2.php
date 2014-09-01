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
class Updates_2_7_0_b2 extends Updates
{
    static function getSql()
    {
        return array(
            'ALTER TABLE `' . Common::prefixTable('log_visit') . '`
			    ADD `user_id` varchar(200) NULL AFTER `config_id`
			   ' => array(1060),
        );
    }

    static function update()
    {
        // Run the SQL
        Updater::updateDatabase(__FILE__, self::getSql());
    }
}

