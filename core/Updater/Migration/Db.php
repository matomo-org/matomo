<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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

}
