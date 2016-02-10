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
class Updates_1_9_3_b8 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        return array(
            // ignore existing column name error (1060)
            'ALTER TABLE ' . Common::prefixTable('site')
            . " ADD COLUMN excluded_user_agents TEXT NOT NULL AFTER excluded_parameters" => 1060,
        );
    }

    public function doUpdate(Updater $updater)
    {
        // add excluded_user_agents column to site table
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
