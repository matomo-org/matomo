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

class Updates_2_12_0_b1 extends Updates
{

    static function getSql()
    {
        $logVisitTable = Common::prefixTable('log_visit');

        return array(
            'DROP INDEX index_idsite_config_datetime ON `' . $logVisitTable . '`' => 1091,
            'DROP INDEX index_idsite_datetime ON `' . $logVisitTable . '`' => 1091,
            'CREATE INDEX index_idsite_configid_datetime ON `' . $logVisitTable . '`(idsite, visit_last_action_time, config_id)' => 1061,
        );
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());
    }
}
