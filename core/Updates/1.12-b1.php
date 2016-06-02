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
class Updates_1_12_b1 extends Updates
{
    public static function isMajorUpdate()
    {
        return true;
    }

    public function getMigrationQueries(Updater $updater)
    {
        return array(
            'ALTER TABLE `' . Common::prefixTable('log_link_visit_action') . '`
			 ADD `custom_float` FLOAT NULL DEFAULT NULL' => 1060
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
