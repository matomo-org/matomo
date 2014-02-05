<?php
/**
 * Piwik - Open source web analytics
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
class Updates_1_5_b4 extends Updates
{
    static function getSql()
    {
        return array(
            'ALTER TABLE `' . Common::prefixTable('site') . '`
				 ADD ecommerce TINYINT DEFAULT 0' => false,
        );
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());
    }
}
