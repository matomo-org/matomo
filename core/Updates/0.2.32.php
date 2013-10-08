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
class Updates_0_2_32 extends Updates
{
    static function getSql($schema = 'Myisam')
    {
        return array(
            // 0.2.32 [941]
            'ALTER TABLE `' . Common::prefixTable('access') . '`
				CHANGE `login` `login` VARCHAR( 100 ) NOT NULL'                                                                       => false,
            'ALTER TABLE `' . Common::prefixTable('user') . '`
				CHANGE `login` `login` VARCHAR( 100 ) NOT NULL'           => false,
            'ALTER TABLE `' . Common::prefixTable('user_dashboard') . '`
				CHANGE `login` `login` VARCHAR( 100 ) NOT NULL' => '1146',
            'ALTER TABLE `' . Common::prefixTable('user_language') . '`
				CHANGE `login` `login` VARCHAR( 100 ) NOT NULL'  => '1146',
        );
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());
    }
}
