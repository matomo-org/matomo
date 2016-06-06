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
class Updates_2_0_b3 extends Updates
{
    public static function isMajorUpdate()
    {
        return true;
    }

    public function getMigrationQueries(Updater $updater)
    {
        return array(
            'ALTER TABLE ' . Common::prefixTable('log_visit')
            . " ADD COLUMN  visit_total_events SMALLINT(5) UNSIGNED NOT NULL AFTER visit_total_searches" => 1060,

            'ALTER TABLE ' . Common::prefixTable('log_link_visit_action')
            . " ADD COLUMN  idaction_event_category INTEGER(10) UNSIGNED AFTER idaction_name_ref,
	            ADD COLUMN  idaction_event_action INTEGER(10) UNSIGNED AFTER idaction_event_category" => 1060,
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));

        try {
            \Piwik\Plugin\Manager::getInstance()->activatePlugin('Events');
        } catch (\Exception $e) {
        }
    }
}
