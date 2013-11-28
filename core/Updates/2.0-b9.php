<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Site;
use Piwik\Updater;
use Piwik\Updates;

/**
 * @package Updates
 */
class Updates_2_0_b9 extends Updates
{
    static function getSql($schema = 'Myisam')
    {
        return array(
            "ALTER TABLE `" . Common::prefixTable('site')
                . "` ADD `type` VARCHAR(255) NOT NULL DEFAULT '". Site::DEFAULT_SITE_TYPE ."' AFTER `group` " => 1060,
        );
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());
    }
}
