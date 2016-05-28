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
use Piwik\Site;
use Piwik\Updater;
use Piwik\Updates;

/**
 */
class Updates_2_0_b9 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        return array(
            "ALTER TABLE `" . Common::prefixTable('site')
                . "` ADD `type` VARCHAR(255) NOT NULL DEFAULT '". Site::DEFAULT_SITE_TYPE ."' AFTER `group` " => 1060,
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
