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
class Updates_1_7_2_rc5 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        return array(
            'ALTER TABLE `' . Common::prefixTable('pdf') . '`
		    	CHANGE `aggregate_reports_format` `display_format` TINYINT(1) NOT NULL' => false
        );
    }

    public function doUpdate(Updater $updater)
    {
        try {
            $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
        } catch (\Exception $e) {
        }
    }
}
