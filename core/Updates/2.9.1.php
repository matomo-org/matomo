<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Updates;

/**
 * Update for version 2.9.1.
 */
class Updates_2_9_1 extends Updates
{

    /**
     * Here you can define any action that should be performed during the update. For instance executing SQL statements,
     * renaming config entries, updating files, etc.
     */
    static function update()
    {
        $pluginManager = \Piwik\Plugin\Manager::getInstance();

        try {
            $pluginManager->activatePlugin('BulkTracking');
        } catch(\Exception $e) {
        }
    }
}
