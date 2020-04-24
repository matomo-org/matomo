<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

/**
 * Base class for LogTables. You need to create a log table eg if you want to be able to create a segment for a custom
 * log table.
 */
abstract class LogTable {

    /**
     * Get the unprefixed database table name. For example 'log_visit' or 'log_action'.
     * @return string
     */
    abstract public function getName();

    /**
     * Get the name of the column that represents the primary key. For example "idvisit" or "idlink_va". If the table
     * does not have a unique ID for each row, you may choose a column that comes closest to it, for example "idvisit".
     * @return string
     */
    public function getIdColumn()
    {
        return '';
    }

    /**
     * Get the name of the column that can be used to join a visit with another table. This is the name of the column
     * that represents the "idvisit".
     * @return string
     */
    public function getColumnToJoinOnIdVisit()
    {
        return '';
    }

    /**
     * Get the name of the column that can be used to join an action with another table. This is the name of the column
     * that represents the "idaction".
     *
     * This could be more generic eg by specifying "$this->joinableOn = array('action' => 'idaction') and this
     * would allow to also add more complex structures in the future but not needed for now I'd say. Let's go with
     * simpler, more clean and expressive solution for now until needed.
     *
     * @return string
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
     * @return array
     */
    public function getWaysToJoinToOtherLogTables()
    {
        return array();
    }

    /**
     * Defines whether this table should be joined via a subselect. Return true if a complex join is needed. (eg when
     * having visits and needing actions, or when having visits and needing conversions, or vice versa).
     * @return bool
     */
    public function shouldJoinWithSubSelect()
    {
        return false;
    }

    /**
     * Defines a column that stores the date/time at which time an entry was written or updated. Setting this
     * can help improve the performance of some archive queries. For example the log_link_visit_action table would define
     * server_time while log_visit would define visit_last_action_time
     * @return string
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
     * In theory there could be case where it may be needed to join via two tables, so it could be needed at some
     * point to return an array of tables here. not sure if we should handle this case just yet. Alternatively,
     * once needed eg in LogQueryBuilder, we should maybe better call instead ->getLinkTableToBeAbleToJoinOnVisit()
     * again on the returned table until we have found a table that can be joined with visit.
     *
     * @return string
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
     * @return array
     */
    public function getPrimaryKey()
    {
        return array();
    }
}
