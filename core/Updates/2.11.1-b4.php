<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Config;
use Piwik\Development;
use Piwik\Updates;

class Updates_2_11_1_b4 extends Updates
{
    /**
     * Here you can define any action that should be performed during the update. For instance executing SQL statements,
     * renaming config entries, updating files, etc.
     */
    public static function update()
    {
        if (!Development::isEnabled()) {
            return;
        }

        $config  = Config::getInstance();
        $dbTests = $config->database_tests;

        if ($dbTests['username'] === '@USERNAME@') {
            $dbTests['username'] = 'root';
        }

        $config->database_tests = $dbTests;

        $config->forceSave();
    }
}
