<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater;

use Exception;

/**
 * Base class for migrations. Any migration must extend this class.
 *
 * @api
 */
abstract class Migration
{
    /**
     * Executes the migration.
     * @return void
     */
    abstract public function exec();

    /**
     * Get a description of what the migration actually does. For example "Activate plugin $plugin" or
     * "SELECT * FROM table".
     *
     * @return string
     */
    abstract public function __toString();

    /**
     * Decides whether an error should be ignored or not.
     * @param Exception $exception
     * @return bool
     */
    public function shouldIgnoreError($exception)
    {
        return false;
    }
}
