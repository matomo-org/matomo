<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables;

use Piwik\Common;

class ExampleLogTables extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'Db.getTablesInstalled' => 'getTablesInstalled'
        ];
    }

    public function install()
    {
        // Install custom log table [disabled as example only]

        // $userLog = new CustomUserLog();
        // $userLog->install();

        // $userLog = new CustomGroupLog();
        // $userLog->install();
    }

    /**
     * Register the new tables, so Matomo knows about them.
     *
     * @param array $allTablesInstalled
     */
    public function getTablesInstalled(&$allTablesInstalled)
    {
        $allTablesInstalled[] = Common::prefixTable('log_group');
        $allTablesInstalled[] = Common::prefixTable('log_custom');
    }
}
