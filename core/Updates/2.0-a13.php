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
use Piwik\Db;
use Piwik\Option;
use Piwik\Updater;
use Piwik\Updates;

/**
 */
class Updates_2_0_a13 extends Updates
{
    public static function getSql()
    {
        // Renaming old archived records now that the plugin is called Referrers
        $sql = array();
        $tables = \Piwik\DbHelper::getTablesInstalled();
        foreach ($tables as $tableName) {
            if (strpos($tableName, 'archive_') !== false) {
                $sql['UPDATE `' . $tableName . '` SET `name`=REPLACE(`name`, \'Referers_\', \'Referrers_\') WHERE `name` LIKE \'Referers_%\''] = false;
            }
        }
        $errorCodeTableNotFound = '1146';

        // Rename custom segments containing Referers segments
        $sql['UPDATE `' . Common::prefixTable('segment') . '` SET `definition`=REPLACE(`definition`, \'referer\', \'referrer\') WHERE `definition` LIKE \'%referer%\''] = $errorCodeTableNotFound;

        // Rename Referrers reports within scheduled reports
        $sql['UPDATE `' . Common::prefixTable('report') . '` SET `reports`=REPLACE(`reports`, \'Referer\', \'Referrer\') WHERE `reports` LIKE \'%Referer%\''] = $errorCodeTableNotFound;

        // Rename Referrers widgets in custom dashboards
        $sql['UPDATE `' . Common::prefixTable('user_dashboard') . '` SET `layout`=REPLACE(`layout`, \'Referer\', \'Referrer\') WHERE `layout` LIKE \'%Referer%\''] = $errorCodeTableNotFound;

        $sql['UPDATE `' . Common::prefixTable('option') . '` SET `option_name` = \'version_ScheduledReports\' WHERE `option_name` = \'version_PDFReports\' '] = '1062'; // http://forum.piwik.org/read.php?2,106895

        return $sql;
    }

    public static function update()
    {
        // delete schema version_
        Option::delete('version_Referers');

        Updater::updateDatabase(__FILE__, self::getSql());

        // old plugins deleted in 2.0-a17 update file

        try {
            \Piwik\Plugin\Manager::getInstance()->activatePlugin('Referrers');
        } catch (\Exception $e) {
        }
        try {
            \Piwik\Plugin\Manager::getInstance()->activatePlugin('ScheduledReports');
        } catch (\Exception $e) {
        }

    }
}
