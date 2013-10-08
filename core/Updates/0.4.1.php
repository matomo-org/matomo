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
class Updates_0_4_1 extends Updates
{
    static function getSql($schema = 'Myisam')
    {
        return array(
            'ALTER TABLE `' . Common::prefixTable('log_conversion') . '`
				CHANGE `idlink_va` `idlink_va` INT(11) DEFAULT NULL'                                                                     => false,
            'ALTER TABLE `' . Common::prefixTable('log_conversion') . '`
				CHANGE `idaction` `idaction` INT(11) DEFAULT NULL' => '1054',
        );
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());
    }
}
