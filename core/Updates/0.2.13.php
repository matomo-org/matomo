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
class Updates_0_2_13 extends Updates
{
    static function getSql()
    {
        return array(
            'DROP TABLE IF EXISTS `' . Common::prefixTable('option') . '`'    => false,

            'CREATE TABLE `' . Common::prefixTable('option') . "` (
				option_name VARCHAR( 64 ) NOT NULL ,
				option_value LONGTEXT NOT NULL ,
				autoload TINYINT NOT NULL DEFAULT '1',
				PRIMARY KEY ( option_name )
			)" => 1050,
        );
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());
    }
}
