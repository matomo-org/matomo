<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\ExampleLogTables\Dao\CustomGroupLog;
use Piwik\Plugins\ExampleLogTables\Dao\CustomUserLog;
use Piwik\Plugins\ExampleLogTables\RecordBuilders\AdminGroupVisits;

class ExampleLogTables extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'Db.getTablesInstalled' => 'getTablesInstalled',
            'Metrics.getDefaultMetricTranslations' => 'addMetricTranslations',
        ];
    }

    /**
     * Gives the archived metric a readable name wherever Matomo renders it.
     *
     * @param array<string, string> $translations
     */
    public function addMetricTranslations(array &$translations): void
    {
        $name = Piwik::translate('ExampleLogTables_NbVisitsAdminGroup');

        $translations[AdminGroupVisits::NB_VISITS_ADMIN_GROUP_RECORD] = $name;
    }

    /**
     * Tells Matomo to load this plugin in the tracker.
     *
     * Without this the tracker never loads the plugin, and the RequestProcessor in `Tracker/` is
     * never found: `Plugin\Manager::isTrackerPlugin()` only auto-detects plugins that declare a
     * visit, action or conversion dimension, or subscribe to a `Tracker.*` event. This plugin does
     * neither -- its dimensions describe columns of its own tables rather than of a core log table --
     * so it has to say so itself.
     */
    public function isTrackerPlugin()
    {
        return true;
    }

    /**
     * Creates the two custom log tables when the plugin is activated.
     *
     * Both DAOs use `CREATE TABLE IF NOT EXISTS`, so running this more than once is harmless.
     */
    public function install()
    {
        (new CustomUserLog())->install();
        (new CustomGroupLog())->install();
    }

    /**
     * Drops the two custom log tables when the plugin is uninstalled.
     */
    public function uninstall()
    {
        (new CustomGroupLog())->uninstall();
        (new CustomUserLog())->uninstall();
    }

    /**
     * Register the new tables, so Matomo knows about them.
     *
     * @param string[] $allTablesInstalled
     */
    public function getTablesInstalled(array &$allTablesInstalled): void
    {
        $allTablesInstalled[] = Common::prefixTable(CustomUserLog::TABLE_NAME);
        $allTablesInstalled[] = Common::prefixTable(CustomGroupLog::TABLE_NAME);
    }
}
