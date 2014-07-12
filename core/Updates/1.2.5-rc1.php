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
class Updates_1_2_5_rc1 extends Updates
{
    static function getSql()
    {
        return array(
            'ALTER TABLE `' . Common::prefixTable('goal') . '`
		    	ADD `allow_multiple` tinyint(4) NOT NULL AFTER case_sensitive' => 1060,
            'ALTER TABLE `' . Common::prefixTable('log_conversion') . '`
				ADD buster int unsigned NOT NULL AFTER revenue,
				DROP PRIMARY KEY,
		    	ADD PRIMARY KEY (idvisit, idgoal, buster)' => 1060,
        );
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());
    }
}

