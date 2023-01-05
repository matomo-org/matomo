<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
    const ERROR_CODE_TABLE_EXISTS = 1050;

    /**
     *  Unknown table '%s'
     */
    const ERROR_CODE_UNKNOWN_TABLE = 1051;

    /**
     *  Unknown column '%s' in '%s'
     */
    const ERROR_CODE_UNKNOWN_COLUMN = 1054;

    /**
     * Duplicate column name '%s'
     */
    const ERROR_CODE_DUPLICATE_COLUMN = 1060;

    /**
     * Duplicate key name '%s'
     */
    const ERROR_CODE_DUPLICATE_KEY = 1061;

    /**
     * Duplicate entry '%s' for key %d
     */
    const ERROR_CODE_DUPLICATE_ENTRY = 1062;
    
    /**
     * Multiple primary key defined
     */
    const ERROR_CODE_DUPLICATE_PRIMARY_KEY = 1068;

    /**
     * Key column '%s' doesn't exist in table
     */
    const ERROR_CODE_KEY_COLUMN_NOT_EXISTS = 1072;

    /**
     * Can't DROP '%s'; check that column/key exists
     */
    const ERROR_CODE_COLUMN_NOT_EXISTS = 1091;

    /**
     * Table '%s.%s' doesn't exist
     */
    const ERROR_CODE_TABLE_NOT_EXISTS = 1146;

    /**
     * This table type requires a primary key SQL: CREATE TEMPORARY TABLE %s
     */
    const ERROR_CODE_REQUIRES_PRIMARY_KEY = 1173;

    /**
     * General error: 3750 Unable to create or change a table without a primary key, when the system variable 'sql_require_primary_key' is set.
     */
    const ERROR_CODE_UNABLE_CREATE_TABLE_WITHOUT_PRIMARY_KEY = 3750;

    /**
     * Query execution was interrupted, maximum statement execution time exceeded
     */
    const ERROR_CODE_MAX_EXECUTION_TIME_EXCEEDED_QUERY_INTERRUPTED = 3024;

    /**
     * Sort aborted: Query execution was interrupted, maximum statement execution time exceeded
     */
    const ERROR_CODE_MAX_EXECUTION_TIME_EXCEEDED_SORT_ABORTED = 1028;

    /**
     * MySQL server has gone away
     */
    const ERROR_CODE_MYSQL_SERVER_HAS_GONE_AWAY = 2006;

}
