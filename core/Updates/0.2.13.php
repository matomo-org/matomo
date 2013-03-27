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
class Piwik_Updates_0_2_13 extends Piwik_Updates
{
    static function getSql($schema = 'Myisam')
    {
        return array(
            'DROP TABLE IF EXISTS `' . Piwik_Common::prefixTable('option') . '`'    => false,

            'CREATE TABLE `' . Piwik_Common::prefixTable('option') . "` (
				option_name VARCHAR( 64 ) NOT NULL ,
				option_value LONGTEXT NOT NULL ,
				autoload TINYINT NOT NULL DEFAULT '1',
				PRIMARY KEY ( option_name )
			)" => false,
        );
    }

    static function update()
    {
        Piwik_Updater::updateDatabase(__FILE__, self::getSql());
    }
}
