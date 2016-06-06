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
class Updates_1_9_b16 extends Updates
{
    public static function isMajorUpdate()
    {
        return true;
    }

    public function getMigrationQueries(Updater $updater)
    {
        return array(
            'ALTER TABLE  `' . Common::prefixTable('log_link_visit_action') . '`
			CHANGE `idaction_url` `idaction_url` INT( 10 ) UNSIGNED NULL DEFAULT NULL'
            => false,

            'ALTER TABLE  `' . Common::prefixTable('log_visit') . '`
			ADD visit_total_searches SMALLINT(5) UNSIGNED NOT NULL AFTER `visit_total_actions`'
            => 1060,

            'ALTER TABLE  `' . Common::prefixTable('site') . '`
			ADD sitesearch TINYINT DEFAULT 1 AFTER `excluded_parameters`,
            ADD sitesearch_keyword_parameters TEXT NOT NULL AFTER `sitesearch`,
            ADD sitesearch_category_parameters TEXT NOT NULL AFTER `sitesearch_keyword_parameters`'
                                                                                             => 1060,

            // enable Site Search for all websites, users can manually disable the setting
            'UPDATE `' . Common::prefixTable('site') . '`
		    	SET `sitesearch` = 1' => false,
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
