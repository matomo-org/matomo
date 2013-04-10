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
class Piwik_Updates_0_2_12 extends Piwik_Updates
{
    static function getSql($schema = 'Myisam')
    {
        return array(
            'ALTER TABLE `' . Piwik_Common::prefixTable('site') . '`
				CHANGE `ts_created` `ts_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL'                    => false,
            'ALTER TABLE `' . Piwik_Common::prefixTable('log_visit') . '`
				DROP `config_color_depth`' => false,

            // 0.2.12 [673]
            // Note: requires INDEX privilege
            'DROP INDEX index_idaction ON `' . Piwik_Common::prefixTable('log_action') . '`'                      => '1091',
        );
    }

    static function update()
    {
        Piwik_Updater::updateDatabase(__FILE__, self::getSql());
    }
}
