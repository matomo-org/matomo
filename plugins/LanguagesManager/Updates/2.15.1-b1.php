<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\LanguagesManager;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;


class Updates_2_15_1_b1 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        $updateSql = array(
            'ALTER TABLE `' . Common::prefixTable('user_language')
                . '` ADD COLUMN `use_12_hour_clock` TINYINT(1) NOT NULL DEFAULT 0 AFTER `language`' => array(1060)
        );
        return $updateSql;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
