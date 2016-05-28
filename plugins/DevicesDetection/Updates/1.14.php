<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\DevicesDetection;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;

class Updates_1_14 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        return array(
            'ALTER TABLE `' . Common::prefixTable('log_visit') . '`
				CHANGE `config_os_version` `config_os_version`  VARCHAR( 100 ) DEFAULT NULL,
				CHANGE `config_device_type` `config_device_type`  VARCHAR( 100 ) DEFAULT NULL' => false,
        );
    }

    static function isMajorUpdate()
    {
        return true;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }

}
