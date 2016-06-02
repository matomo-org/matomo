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
class Updates_0_2_32 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        return array(
            // 0.2.32 [941]
            'ALTER TABLE `' . Common::prefixTable('access') . '`
				CHANGE `login` `login` VARCHAR( 100 ) NOT NULL'                                                                       => false,
            'ALTER TABLE `' . Common::prefixTable('user') . '`
				CHANGE `login` `login` VARCHAR( 100 ) NOT NULL'           => false,
            'ALTER TABLE `' . Common::prefixTable('user_dashboard') . '`
				CHANGE `login` `login` VARCHAR( 100 ) NOT NULL' => '1146',
            'ALTER TABLE `' . Common::prefixTable('user_language') . '`
				CHANGE `login` `login` VARCHAR( 100 ) NOT NULL'  => '1146',
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
