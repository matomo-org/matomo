<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables\Tracker\LogTable;

use Piwik\Plugins\ExampleLogTables\Dao\CustomAccountLog as Dao;
use Piwik\Plugins\ExampleLogTables\Dao\CustomUserLog as UserDao;
use Piwik\Tracker\LogTable;

/**
 * Declares the plugin's account table as log data -- one hop further out than the user table.
 *
 * See {@see CustomUserLog} for what a declaration buys and what it commits you to. The point of this
 * second class is that the join path may be indirect: this table shares no column with any core
 * table, so it names the plugin's *own* user table, which in turn knows how to reach `log_visit`.
 * Core resolves the chain recursively, and neither hop has an `idvisit` column anywhere in it.
 *
 * The cost of joining a table whose rows are shared between subjects is real: erasing one user
 * deletes the account row they belonged to, even when other users belong to it. Core deletes
 * everything reachable from the visits being erased, which is the right default for a compliance
 * feature. It does mean a table of genuinely shared reference data should not declare a join into
 * the subject chain -- here it is acceptable only because the tracker rewrites the row on the next
 * request from any remaining member of the account.
 */
class CustomAccountLog extends LogTable
{
    public function getName()
    {
        return Dao::TABLE_NAME;
    }

    public function getIdColumn()
    {
        return 'account_name';
    }

    /**
     * @return string[]
     */
    public function getPrimaryKey()
    {
        return ['account_name'];
    }

    /**
     * @return array<string, string>
     */
    public function getWaysToJoinToOtherLogTables()
    {
        return [UserDao::TABLE_NAME => 'account_name'];
    }
}
