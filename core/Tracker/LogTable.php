<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tracker;

/**
 * Base class for LogTables. You need to create a log table eg if you want to be able to create a segment for a custom
 * log table.
 *
 * Put the class in the `Tracker` directory of your plugin so it is found automatically.
 *
 * @api
 */
abstract class LogTable
{
    /**
     * Get the unprefixed database table name. For example 'log_visit' or 'log_action'.
     *
     * @return string The unprefixed table name.
     */
    abstract public function getName();

    /**
     * Get the name of the column that represents the primary key. For example "idvisit" or "idlink_va". If the table
     * does not have a unique ID for each row, you may choose a column that comes closest to it, for example "idvisit".
     *
     * @return string The column name, or an empty string if there is none.
     */
    public function getIdColumn()
    {
        return '';
    }

    /**
     * Get the name of the column that can be used to join a visit with another table. This is the name of the column
     * that represents the "idvisit".
     *
     * @return string The column name, or an empty string if the table cannot be joined on idvisit.
     */
    public function getColumnToJoinOnIdVisit()
    {
        return '';
    }

    /**
     * Get the name of the column that can be used to join an action with another table. This is the name of the column
     * that represents the "idaction".
     *
     * @return string The column name, or an empty string if the table cannot be joined on idaction.
     */
    public function getColumnToJoinOnIdAction()
    {
        return '';
    }

    /**
     * If a table can neither be joined via idVisit nor idAction, it should be given a way to join with other tables
     * so the log table can be joined via idvisit through a different table joins.
     *
     * For this to work it requires the same column to be present in two tables. If for example you have a table
     * `log_foo_bar (idlogfoobar, idlogfoo)` and a table `log_foo(idlogfoo, idsite, idvisit)`, then you can in the
     * log table instance for `log_foo_bar` return `array('log_foo' => 'idlogfoo')`. This tells the core that a join
     * with that other log table is possible using the specified column.
     *
     * A join declared here also wins over the generic idvisit/idaction join to the same partner table.
     * Partners are tried in join order starting with the FROM table, and the first partner that yields any
     * join is used, so a declared join does not replace a generic join to an earlier table: with `log_visit`
     * as the FROM table, a table declaring a join to `log_link_visit_action` is still joined to `log_visit`
     * on idvisit. Declare one when the generic join to that partner would be wrong, e.g. two tables that each
     * hold many rows per visit are joined 1:1 on the declared column instead of being multiplied on idvisit.
     * A table declaring joins to several available partners is joined to the first of them; before 6.0.0
     * the last one won.
     *
     * Winning also means the partner's {@see shouldJoinWithSubSelect()} is not consulted for that pair,
     * since that wrapping belongs to the generic join. Declaring a join to a partner that asks for it,
     * `log_visit` being the only one in core, therefore drops the sub-select that partner would have
     * requested, which can change what a segmented query aggregates.
     *
     * @return array<string, string> Maps the name of another log table to the column used to join with it.
     */
    public function getWaysToJoinToOtherLogTables()
    {
        return array();
    }

    /**
     * Defines whether this table should be joined via a subselect. Return true if a complex join is needed. (eg when
     * having visits and needing actions, or when having visits and needing conversions, or vice versa).
     *
     * Only consulted for the generic idvisit join: a partner reached through a join declared in
     * {@see getWaysToJoinToOtherLogTables()} is joined directly, whatever this returns.
     *
     * @return bool True if the table has to be joined via a sub-select.
     */
    public function shouldJoinWithSubSelect()
    {
        return false;
    }

    /**
     * Defines a column that stores the date/time at which time an entry was written or updated. Setting this
     * can help improve the performance of some archive queries. For example the log_link_visit_action table would define
     * server_time while log_visit would define visit_last_action_time.
     *
     * @return string The column name, or an empty string if there is none.
     */
    public function getDateTimeColumn()
    {
        return '';
    }

    /**
     * Returns the name of a log table that allows to join on a visit. Eg if there is a table "action", and it is not
     * joinable with "visit" table, it can return "log_link_visit_action" to be able to join the action table on visit
     * via this link table.
     *
     * @return string|null The unprefixed name of the link table, or null if none is needed.
     */
    public function getLinkTableToBeAbleToJoinOnVisit()
    {
        return;
    }

    /**
     * Get the names of the columns that represents the primary key. For example "idvisit" or "idlink_va". If the table
     * defines the primary key based on multiple columns, you must specify them all
     * (eg array('idvisit', 'idgoal', 'buster')).
     *
     * @return string[] The column names.
     */
    public function getPrimaryKey()
    {
        return array();
    }

    /**
     * Defines if the log table holds an `idvisitor` column.
     * If set to yes, existing records are automatically updated when the visitor id changes during a visit.
     */
    public function hasIdVisitorColumn(): bool
    {
        return false;
    }
}
