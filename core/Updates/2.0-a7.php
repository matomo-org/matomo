<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;

/**
 * @package Updates
 */
class Piwik_Updates_2_0_a7 extends Updates
{
    static function getSql($schema = 'Myisam')
    {
        return array(
            // ignore existing column name error (1060)
            'ALTER TABLE ' . Common::prefixTable('logger_message')
                . " ADD COLUMN plugin VARCHAR(50) NULL AFTER idlogger_message" => 1060,
        );
    }

    static function update()
    {
        // add plugin column to logger_message table
        Updater::updateDatabase(__FILE__, self::getSql());
    }
}
