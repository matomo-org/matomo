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

/**
 */
class Updates_0_4_2 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        return array(
            'ALTER TABLE `' . Common::prefixTable('log_visit') . '`
				ADD `config_java` TINYINT(1) NOT NULL AFTER `config_flash`'         => 1060,
            'ALTER TABLE `' . Common::prefixTable('log_visit') . '`
				ADD `config_quicktime` TINYINT(1) NOT NULL AFTER `config_director`' => 1060,
            'ALTER TABLE `' . Common::prefixTable('log_visit') . '`
				ADD `config_gears` TINYINT(1) NOT NULL AFTER  `config_windowsmedia`,
				ADD `config_silverlight` TINYINT(1) NOT NULL AFTER `config_gears`'  => 1060,
        );
    }

    // when restoring (possibly) previousy dropped columns, ignore mysql code error 1060: duplicate column
    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
