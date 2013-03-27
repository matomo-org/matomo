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
class Piwik_Updates_1_7_2_rc5 extends Piwik_Updates
{
    static function getSql($schema = 'Myisam')
    {
        return array(
            'ALTER TABLE `' . Piwik_Common::prefixTable('pdf') . '`
		    	CHANGE `aggregate_reports_format` `display_format` TINYINT(1) NOT NULL' => false
        );
    }

    static function update()
    {
        try {
            Piwik_Updater::updateDatabase(__FILE__, self::getSql());
        } catch (Exception $e) {
        }
    }
}
