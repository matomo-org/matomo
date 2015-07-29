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

class Updates_2_15_0_b1 extends Updates
{
    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }

    public function getMigrationQueries(Updater $updater)
    {
        $optionTable = Common::prefixTable('option');
        $siteTable = Common::prefixTable('site');

        return array(
            // These settings are now separated by line returns instead of commas
            "UPDATE `$optionTable`
                SET `option_value` = REPLACE(`option_value`, ',', '\n')
                WHERE `option_name` = 'SitesManager_ExcludedIpsGlobal'
                   OR `option_name` = 'SitesManager_ExcludedQueryParameters'
                   OR `option_name` = 'SitesManager_ExcludedUserAgentsGlobal'",
            "UPDATE `$siteTable`
                SET `excluded_ips` = REPLACE(`excluded_ips`, ',', '\n'),
                    `excluded_parameters` = REPLACE(`excluded_parameters`, ',', '\n'),
                    `excluded_user_agents` = REPLACE(`excluded_user_agents`, ',', '\n')'",
        );
    }
}
