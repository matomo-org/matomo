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
use Piwik\Db;

class Updates_2_14_0_b2 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        $dbSettings = new Db\Settings();
        $engine = $dbSettings->getEngine();

        $table = Common::prefixTable('site_setting');

        $sqlarray = array(
            "DROP TABLE IF EXISTS `$table`" => false,
            "CREATE TABLE `$table` (
                  idsite INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `setting_name` VARCHAR(255) NOT NULL,
                  `setting_value` LONGTEXT NOT NULL,
                      PRIMARY KEY(idsite, setting_name)
                    ) ENGINE=$engine DEFAULT CHARSET=utf8" => 1050,
        );

        return $sqlarray;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
