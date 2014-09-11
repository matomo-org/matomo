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
    static function getSql()
    {
        return array(
            // ignore existing column name error (1060)
            'ALTER TABLE ' . Common::prefixTable('logger_message')
            . " ADD COLUMN tag VARCHAR(50) NULL AFTER idlogger_message" => 1060,

            'ALTER TABLE ' . Common::prefixTable('logger_message')
            . " ADD COLUMN level TINYINT AFTER timestamp"               => 1060,
        );
    }

    static function update()
    {
        // add tag & level columns to logger_message table
        Updater::updateDatabase(__FILE__, self::getSql());
    }
}
