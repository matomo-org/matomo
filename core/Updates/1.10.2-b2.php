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
class Updates_1_10_2_b2 extends Updates
{
    static function getSql($schema = 'Myisam')
    {
        return array(
            // ignore existing column name error (1060)
            'ALTER TABLE ' . Common::prefixTable('site')
            . " ADD COLUMN `keep_url_fragment` TINYINT NOT NULL DEFAULT 0 AFTER `group`" => 1060,
        );
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());
    }
}
