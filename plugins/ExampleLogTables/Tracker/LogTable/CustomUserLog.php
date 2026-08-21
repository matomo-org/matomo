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
 * Three things about the declarations below are worth knowing before copying them:
 *
 * - **An id column is not free.** `getIdColumn()` names the column core treats as this table's
 *   identity, and declaring one enrols the table in two things beyond the obvious: the raw-log
 *   purge runs `SELECT MAX(<that column>)` on it, unquoted, and the unused-action purge read-locks
 *   every table that has one. Neither is a problem here, but both are reasons to pick a name that
 *   survives someone else's SQL, and a reason not to declare a column this table does not really
 *   identify rows by. Note also that core's own id columns are auto-increment integers, where that
 *   `MAX()` is a high-water mark; this one is a `VARCHAR`, so the maximum is lexicographic and means
 *   nothing. Harmless only because nothing consults the value for a table shaped like this one.
 * - **No `idvisit` means no `getColumnToJoinOnIdVisit()`.** This table has no such column, so the
 *   fallback is to name a table it shares a column with and let core work out the rest. A join
 *   declared on an unindexed column is a table scan at log-table scale, silently, so check both
 *   sides of every column you name -- and note that only one of them is yours. On this plugin's side
 *   both are covered: `user_id` is this table's primary key, and `account_name`, which the account
 *   table declares *its* join on, carries an index added for that reason and no other. The other
 *   side of this declaration is not covered and cannot be: `log_visit.user_id` is a column the
 *   `UserId` dimension adds, with no index behind it in core's schema. The three readers that matter
 *   -- archiving, the subject export and the subject deletion -- all narrow `log_visit` first, by
 *   site and date or by a list of `idvisit`, and reach this table by its key, so none of them pays
 *   for that. It is still worth checking rather than assuming, because it is a fact about how core
 *   happens to query and not a property of the declaration. Join on `idvisit` where you can; it is
 *   the column of `log_visit` that is indexed for this.
 * - **`getDateTimeColumn()` is left unset** for the same reason: there is no time on these rows to
 *   declare. A table that does have one should say so -- the archiving queries use it to narrow the
 *   scan to the period being archived, and without it every archive run reads the whole table.
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
     * @return array<string, string>
     */
    public function getWaysToJoinToOtherLogTables()
    {
        return ['log_visit' => 'user_id'];
    }
}
