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
class Updates_0_2_24 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        return array(
            'CREATE INDEX index_type_name
                ON ' . Common::prefixTable('log_action') . ' (type, name(15))'                       => 1072,
            'CREATE INDEX index_idsite_date
                ON ' . Common::prefixTable('log_visit') . ' (idsite, visit_server_date)'             => 1072,
            'DROP INDEX index_idsite ON ' . Common::prefixTable('log_visit')                         => 1091,
            'DROP INDEX index_visit_server_date ON ' . Common::prefixTable('log_visit')              => 1091,
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
