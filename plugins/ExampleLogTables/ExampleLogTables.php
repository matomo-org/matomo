<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExampleLogTables;

use Piwik\Plugins\ExampleLogTables\Dao\CustomUserLog;
use Piwik\Plugins\ExampleLogTables\Dao\CustomGroupLog;

class ExampleLogTables extends \Piwik\Plugin
{
    public function install()
    {
        // Install custom log table [disabled as example only]

        // $userLog = new CustomUserLog();
        // $userLog->install();

        // $userLog = new CustomGroupLog();
        // $userLog->install();
    }
}