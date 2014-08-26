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
use Piwik\Updates;

/**
 * Update for version 2.6.0-b1.
 */
class Updates_2_6_0_b1 extends Updates
{
    /**
     * Here you can define any action that should be performed during the update. For instance executing SQL statements,
     * renaming config entries, updating files, etc.
     */
    static function update()
    {
        $config = Config::getInstance();
        $config->Plugins_Tracker = array();
        $config->forceSave();
    }
}
