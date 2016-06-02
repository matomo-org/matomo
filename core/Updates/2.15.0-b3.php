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


class Updates_2_15_0_b3 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        $updateSql = array(
            'ALTER TABLE `' . Common::prefixTable('site')
                . '` ADD COLUMN `exclude_unknown_urls` TINYINT(1) DEFAULT 0 AFTER `currency`' => array(1060)
        );
        return $updateSql;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
