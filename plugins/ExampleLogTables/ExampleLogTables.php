<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables;

use Piwik\Common;
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
     * Gives the archived metric a readable name.
     *
     * Pass the translation key, not a translated string: core maps `Piwik::translate()` over the whole
     * array after posting the event, so translating here would translate the value twice.
     *
     * @param array<string, string> $translations
     */
    public function addMetricTranslations(array &$translations): void
    {
        $translations[AdminGroupVisits::NB_VISITS_ADMIN_GROUP_RECORD] = 'ExampleLogTables_NbVisitsAdminGroup';
    }

    /**
     * Tells Matomo to load this plugin in the tracker.
     *
     * Without this the tracker never loads the plugin, and the RequestProcessor in `Tracker/` is
     * never found: `Plugin\Manager::isTrackerPlugin()` only auto-detects plugins that declare a
     * visit, action or conversion dimension, subscribe to a `Tracker.*` event, or handle
     * `Request.initAuthenticationObject`. This plugin does none of those -- its dimensions describe
     * columns of its own tables rather than of a core log table -- so it has to say so itself.
     *
     * The resulting list of tracker plugins is cached, so adding this override to an install that has
     * already tracked a request takes effect once that cache is cleared, not on the next request.
     */
    public function isTrackerPlugin()
    {
        return true;
    }

    /**
     * Creates the two custom log tables when the plugin is activated.
     *
     * The DAOs are constructed with `new` here, unlike in the RequestProcessor, which has them
     * injected. The plugin class itself is instantiated directly by `Plugin\Manager`, never through
     * the container, so its constructor cannot take dependencies -- the same reason
     * `VisitorDetails` builds its own, since Live constructs that one directly too.
     *
     * Both DAOs use `CREATE TABLE IF NOT EXISTS`, so running this more than once is harmless.
     *
     * This runs on activation only. Bumping the version in `plugin.json` does not re-run it, so a
     * schema change after release needs an `Updates/` migration as well -- see the README.
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
     * Without this the tables are missing from `DbHelper::getTablesInstalled()`, which is the list
     * core uses to drop all tables, to convert the schema to utf8mb4, to detect an existing install
     * and to report database usage. None of those failures announce themselves.
     *
     * @param string[] $allTablesInstalled
     */
    public function getTablesInstalled(array &$allTablesInstalled): void
    {
        $allTablesInstalled[] = Common::prefixTable(CustomUserLog::TABLE_NAME);
        $allTablesInstalled[] = Common::prefixTable(CustomGroupLog::TABLE_NAME);
    }
}
