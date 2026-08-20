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

/**
 * Creates and drops the plugin's two log tables, and wires up the three things about this plugin
 * that Matomo does *not* discover by location.
 *
 * Everything else in this plugin is found by where its file sits. These are not:
 *
 * - **`isTrackerPlugin()` must return `true`.** Putting a RequestProcessor in `Tracker/` is not
 *   enough. The tracker loads only the plugins it considers tracker plugins, and
 *   `Plugin\Manager::isTrackerPlugin()` auto-detects that from visit, action or conversion
 *   dimensions, from `Tracker.*` event subscriptions and from `Request.initAuthenticationObject`.
 *   This plugin has none of those -- its dimensions describe columns of its own tables rather than
 *   of a core log table -- so without the override the class in `Tracker/` is never reached and no
 *   row is ever written, with no error anywhere. The resulting list is cached, so adding the
 *   override to an install that has already tracked a request takes effect once that cache is
 *   cleared, not on the next request.
 * - **`Db.getTablesInstalled`** is a subscription. Without it the tables are missing from
 *   `DbHelper::getTablesInstalled()`, the list core uses to drop all tables, to convert the schema
 *   to utf8mb4, to detect an existing install and to report database usage. None of those failures
 *   announce themselves.
 * - **`Metrics.getDefaultMetricTranslations`** is likewise a subscription, and it takes translation
 *   *keys*. Without it the archived metric keeps its raw record name wherever core consults that
 *   map.
 *
 * One structural constraint shapes the code below: `Plugin\Manager` instantiates a plugin class
 * directly, never through the container, so its constructor cannot take dependencies. That is why
 * the DAOs are built with `new` here while the RequestProcessor has them injected, and it is the
 * same reason `VisitorDetails` builds its own -- Live constructs that one directly too.
 */
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
     * @param array<string, string> $translations
     */
    public function addMetricTranslations(array &$translations): void
    {
        // Pass the translation key, not a translated string: core maps `Piwik::translate()` over the
        // whole array after posting the event, so translating here would translate the value twice.
        $translations[AdminGroupVisits::NB_VISITS_ADMIN_GROUP_RECORD] = 'ExampleLogTables_NbVisitsAdminGroup';
    }

    public function isTrackerPlugin()
    {
        // See the class docblock: nothing about this plugin makes the tracker load it on its own.
        return true;
    }

    public function install()
    {
        // Both DAOs use `CREATE TABLE IF NOT EXISTS`, so running this more than once is harmless.
        // It runs on activation only, though: bumping the version in `plugin.json` does not re-run
        // it, so a schema change after release needs an `Updates/` migration as well -- and an
        // install that activated the plugin before it had a working `install()` cannot be repaired
        // from the user interface at all. The README says how to recover by hand.
        (new CustomUserLog())->install();
        (new CustomGroupLog())->install();
    }

    public function uninstall()
    {
        (new CustomGroupLog())->uninstall();
        (new CustomUserLog())->uninstall();
    }

    /**
     * @param string[] $allTablesInstalled
     */
    public function getTablesInstalled(array &$allTablesInstalled): void
    {
        $allTablesInstalled[] = Common::prefixTable(CustomUserLog::TABLE_NAME);
        $allTablesInstalled[] = Common::prefixTable(CustomGroupLog::TABLE_NAME);
    }
}
