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
use Piwik\DbHelper;
use Piwik\Updater;
use Piwik\Updates;

/**
 * @package Updates
 */
class Updates_0_5_5 extends Updates
{
    static function getSql($schema = 'Myisam')
    {
        $sqlarray = array(
            'DROP INDEX index_idsite_date ON ' . Common::prefixTable('log_visit')                                                                => '1091',
            'CREATE INDEX index_idsite_date_config ON ' . Common::prefixTable('log_visit') . ' (idsite, visit_server_date, config_md5config(8))' => '1061',
        );

        $tables = DbHelper::getTablesInstalled();
        foreach ($tables as $tableName) {
            if (preg_match('/archive_/', $tableName) == 1) {
                $sqlarray['DROP INDEX index_all ON ' . $tableName] = '1091';
            }
            if (preg_match('/archive_numeric_/', $tableName) == 1) {
                $sqlarray['CREATE INDEX index_idsite_dates_period ON ' . $tableName . ' (idsite, date1, date2, period)'] = '1061';
            }
        }

        return $sqlarray;
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());

    }
}
