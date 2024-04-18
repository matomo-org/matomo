<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updater\Migration;

use Piwik\Updater\Migration;

/**
 * Base class for a single database migration. Any database migration must extend this class.
 *
 * @api
 */
abstract class Db extends Migration
{
    /**
     * Table '%s' already exists
     */
    public const ERROR_CODE_TABLE_EXISTS = 1050;

    /**
     *  Unknown table '%s'
     */
    public const ERROR_CODE_UNKNOWN_TABLE = 1051;

    /**
     *  Unknown column '%s' in '%s'
     */
    public const ERROR_CODE_UNKNOWN_COLUMN = 1054;

    /**
     * Duplicate column name '%s'
     */
    public const ERROR_CODE_DUPLICATE_COLUMN = 1060;

    /**
     * Duplicate key name '%s'
     */
    public const ERROR_CODE_DUPLICATE_KEY = 1061;

    /**
     * Duplicate entry '%s' for key %d
     */
    public const ERROR_CODE_DUPLICATE_ENTRY = 1062;

    /**
     * Syntax error
     */
    public const ERROR_CODE_SYNTAX_ERROR = 1064;

    /**
     * Multiple primary key defined
     */
    public const ERROR_CODE_DUPLICATE_PRIMARY_KEY = 1068;

    /**
     * Key column '%s' doesn't exist in table
     */
    public const ERROR_CODE_KEY_COLUMN_NOT_EXISTS = 1072;

    /**
     * Can't DROP '%s'; check that column/key exists
     */
    public const ERROR_CODE_COLUMN_NOT_EXISTS = 1091;

    /**
     * Table '%s.%s' doesn't exist
     */
    public const ERROR_CODE_TABLE_NOT_EXISTS = 1146;

    /**
     * This table type requires a primary key SQL: CREATE TEMPORARY TABLE %s
     */
    public const ERROR_CODE_REQUIRES_PRIMARY_KEY = 1173;

    /**
     * General error: 3750 Unable to create or change a table without a primary key, when the system variable 'sql_require_primary_key' is set.
     */
    public const ERROR_CODE_UNABLE_CREATE_TABLE_WITHOUT_PRIMARY_KEY = 3750;

    /**
     * Query execution was interrupted, maximum statement execution time exceeded
     */
    public const ERROR_CODE_MAX_EXECUTION_TIME_EXCEEDED_QUERY_INTERRUPTED = 3024;

    /**
     * Sort aborted: Query execution was interrupted, maximum statement execution time exceeded
     */
    public const ERROR_CODE_MAX_EXECUTION_TIME_EXCEEDED_SORT_ABORTED = 1028;

    /**
     * MySQL server has gone away
     */
    public const ERROR_CODE_MYSQL_SERVER_HAS_GONE_AWAY = 2006;
}
