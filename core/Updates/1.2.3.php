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
use Piwik\Config;
use Piwik\Updater;
use Piwik\Updates;

/**
 * @package Updates
 */
class Updates_1_2_3 extends Updates
{
    static function getSql($schema = 'Myisam')
    {
        return array(
            // LOAD DATA INFILE uses the database's charset
            'ALTER DATABASE `' . Config::getInstance()->database['dbname'] . '` DEFAULT CHARACTER SET utf8'                                        => false,

            // Various performance improvements schema updates
            'ALTER TABLE `' . Common::prefixTable('log_visit') . '`
				DROP INDEX index_idsite_datetime_config,
				DROP INDEX index_idsite_idvisit,
				ADD INDEX index_idsite_config_datetime (idsite, config_id, visit_last_action_time),
				ADD INDEX index_idsite_datetime (idsite, visit_last_action_time)' => false,
        );
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());
    }
}

