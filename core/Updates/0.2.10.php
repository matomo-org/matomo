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
use Piwik\Filesystem;
use Piwik\Updater;
use Piwik\Updates;

/**
 * @package Updates
 */
class Updates_0_2_10 extends Updates
{
    static function getSql($schema = 'Myisam')
    {
        return array(
            'CREATE TABLE `' . Common::prefixTable('option') . '` (
				idoption BIGINT NOT NULL AUTO_INCREMENT ,
				option_name VARCHAR( 64 ) NOT NULL ,
				option_value LONGTEXT NOT NULL ,
				PRIMARY KEY ( idoption , option_name )
			)'                                                                                                                                                       => false,

            // 0.1.7 [463]
            'ALTER IGNORE TABLE `' . Common::prefixTable('log_visit') . '`
				 CHANGE `location_provider` `location_provider` VARCHAR( 100 ) DEFAULT NULL' => '1054',

            // 0.1.7 [470]
            'ALTER TABLE `' . Common::prefixTable('logger_api_call') . '`
				CHANGE `parameter_names_default_values` `parameter_names_default_values` TEXT,
				CHANGE `parameter_values` `parameter_values` TEXT,
				CHANGE `returned_value` `returned_value` TEXT'                                => false,
            'ALTER TABLE `' . Common::prefixTable('logger_error') . '`
				CHANGE `message` `message` TEXT'                                                 => false,
            'ALTER TABLE `' . Common::prefixTable('logger_exception') . '`
				CHANGE `message` `message` TEXT'                                             => false,
            'ALTER TABLE `' . Common::prefixTable('logger_message') . '`
				CHANGE `message` `message` TEXT'                                               => false,

            // 0.2.2 [489]
            'ALTER IGNORE TABLE `' . Common::prefixTable('site') . '`
				 CHANGE `feedburnerName` `feedburnerName` VARCHAR( 100 ) DEFAULT NULL'            => '1054',
        );
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());

        $obsoleteFile = '/plugins/ExamplePlugin/API.php';
        if (file_exists(PIWIK_INCLUDE_PATH . $obsoleteFile)) {
            @unlink(PIWIK_INCLUDE_PATH . $obsoleteFile);
        }

        $obsoleteDirectories = array(
            '/plugins/AdminHome',
            '/plugins/Home',
            '/plugins/PluginsAdmin',
        );
        foreach ($obsoleteDirectories as $dir) {
            if (file_exists(PIWIK_INCLUDE_PATH . $dir)) {
                Filesystem::unlinkRecursive(PIWIK_INCLUDE_PATH . $dir, true);
            }
        }
    }
}
