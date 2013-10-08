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

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;

/**
 * @package Updates
 */
class Updates_0_2_13 extends Updates
{
    static function getSql($schema = 'Myisam')
    {
        return array(
            'DROP TABLE IF EXISTS `' . Common::prefixTable('option') . '`'    => false,

            'CREATE TABLE `' . Common::prefixTable('option') . "` (
				option_name VARCHAR( 64 ) NOT NULL ,
				option_value LONGTEXT NOT NULL ,
				autoload TINYINT NOT NULL DEFAULT '1',
				PRIMARY KEY ( option_name )
			)" => false,
        );
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());
    }
}
