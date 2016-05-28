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
class Updates_2_0_a7 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        return array(
            // ignore existing column name error (1060)
            'ALTER TABLE ' . Common::prefixTable('logger_message')
            . " ADD COLUMN tag VARCHAR(50) NULL AFTER idlogger_message" => 1060,

            'ALTER TABLE ' . Common::prefixTable('logger_message')
            . " ADD COLUMN level TINYINT AFTER timestamp"               => 1060,
        );
    }

    public function doUpdate(Updater $updater)
    {
        // add tag & level columns to logger_message table
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
