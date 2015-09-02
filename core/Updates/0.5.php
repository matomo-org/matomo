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
class Updates_0_5 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        return array(
            'ALTER TABLE ' . Common::prefixTable('log_action') . ' ADD COLUMN `hash` INTEGER(10) UNSIGNED NOT NULL AFTER `name`;'                        => 1060,
            'ALTER TABLE ' . Common::prefixTable('log_visit') . ' CHANGE visit_exit_idaction visit_exit_idaction_url INTEGER(11) NOT NULL;'              => 1054,
            'ALTER TABLE ' . Common::prefixTable('log_visit') . ' CHANGE visit_entry_idaction visit_entry_idaction_url INTEGER(11) NOT NULL;'            => 1054,
            'ALTER TABLE ' . Common::prefixTable('log_link_visit_action') . ' CHANGE `idaction_ref` `idaction_url_ref` INTEGER(10) UNSIGNED NOT NULL;'   => 1054,
            'ALTER TABLE ' . Common::prefixTable('log_link_visit_action') . ' CHANGE `idaction` `idaction_url` INTEGER(10) UNSIGNED NOT NULL;'           => 1054,
            'ALTER TABLE ' . Common::prefixTable('log_link_visit_action') . ' ADD COLUMN `idaction_name` INTEGER(10) UNSIGNED AFTER `idaction_url_ref`;' => 1060,
            'ALTER TABLE ' . Common::prefixTable('log_conversion') . ' CHANGE `idaction` `idaction_url` INTEGER(11) UNSIGNED NOT NULL;'                  => 1054,
            'UPDATE ' . Common::prefixTable('log_action') . ' SET `hash` = CRC32(name);'                                                                 => false,
            'CREATE INDEX index_type_hash ON ' . Common::prefixTable('log_action') . ' (type, hash);'                                                    => 1061,
            'DROP INDEX index_type_name ON ' . Common::prefixTable('log_action') . ';'                                                                   => 1091,
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
