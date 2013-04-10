<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Updates
 */

/**
 * @package Updates
 */
class Piwik_Updates_1_4_rc2 extends Piwik_Updates
{
    static function getSql($schema = 'Myisam')
    {
        return array(
            "SET sql_mode=''"                                                                                                                                  => false,
            // this converts the 32-bit UNSIGNED INT column to a 16 byte VARBINARY;
            // _but_ MySQL does string conversion! (e.g., integer 1 is converted to 49 -- the ASCII code for "1")
            'ALTER TABLE ' . Piwik_Common::prefixTable('log_visit') . '
				MODIFY location_ip VARBINARY(16) NOT NULL'                               => false,
            'ALTER TABLE ' . Piwik_Common::prefixTable('logger_api_call') . '
				MODIFY caller_ip VARBINARY(16) NOT NULL'                           => false,

            // fortunately, 2^32 is 10 digits long and fits in the VARBINARY(16) without truncation;
            // to fix this, we cast to an integer, convert to hex, pad out leading zeros, and unhex it
            'UPDATE ' . Piwik_Common::prefixTable('log_visit') . "
				SET location_ip = UNHEX(LPAD(HEX(CONVERT(location_ip, UNSIGNED)), 8, '0'))"   => false,
            'UPDATE ' . Piwik_Common::prefixTable('logger_api_call') . "
				SET caller_ip = UNHEX(LPAD(HEX(CONVERT(caller_ip, UNSIGNED)), 8, '0'))" => false,
        );
    }

    static function update()
    {
        Piwik_Updater::updateDatabase(__FILE__, self::getSql());
    }
}
