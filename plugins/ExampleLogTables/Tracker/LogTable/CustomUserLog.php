<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables\Tracker\LogTable;

use Piwik\Plugins\ExampleLogTables\Dao\CustomUserLog as Dao;
use Piwik\Tracker\LogTable;

/**
 * Declares the plugin's user table as log data.
 *
 * This class is what turns a table the plugin happens to own into part of Matomo's log data, and it
 * is the highest-leverage file in the plugin. `LogTablesProvider` finds it because it lives under
 * `Tracker/` and extends `Piwik\Tracker\LogTable`; nothing registers it.
 *
 * What the declaration buys: segments over the table's columns, joins from the archiving queries,
 * and -- with no further code -- subject export, subject deletion, deleted-site cleanup and log
 * retention, because `PrivacyManager` drives all four off this one list.
 *
 * What it commits you to: a join path that resolves. A table core cannot find a path for is dropped
 * from the subject-access export *silently* and makes the deletion *throw*, so one wrong entry in
 * `getWaysToJoinToOtherLogTables()` produces a quietly incomplete GDPR export. That is why
 * `tests/Integration/DataSubjectLifecycleTest.php` exists.
 *
 * Two limits of joining on `user_id` rather than on `idvisit`, both worth knowing before copying
 * this shape: the rows are not scoped to a site, and the join does not survive PrivacyManager's
 * retroactive user id anonymisation. Both are written up under *Privacy* in the README.
 */
class CustomUserLog extends LogTable
{
    public function getName()
    {
        return Dao::TABLE_NAME;
    }

    public function getIdColumn()
    {
        return 'user_id';
    }

    /**
     * @return string[]
     */
    public function getPrimaryKey()
    {
        return ['user_id'];
    }

    /**
     * This table has no `idvisit` column, so it cannot override `getColumnToJoinOnIdVisit()`. The
     * fallback is to name a table it shares a column with, and let core work out the rest.
     *
     * @return array<string, string>
     */
    public function getWaysToJoinToOtherLogTables()
    {
        return ['log_visit' => 'user_id'];
    }
}
