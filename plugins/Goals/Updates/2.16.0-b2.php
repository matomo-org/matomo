<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Goals;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;


class Updates_2_16_0_b2 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        $updateSql = array(
            'ALTER TABLE `' . Common::prefixTable('goal')
                . '` ADD COLUMN `description` VARCHAR(255) NOT NULL DEFAULT \'\' AFTER `name`;' => array(1060)
        );
        return $updateSql;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
