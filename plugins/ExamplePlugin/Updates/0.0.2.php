<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ExamplePlugin\Updates;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

/**
 * Update for version 0.0.2.
 */
class Updates_0_0_2 extends PiwikUpdates
{

    /**
     * Return SQL to be executed in this update.
     *
     * SQL queries should be defined here, instead of in `doUpdate()`, since this method is used
     * in the `core:update` command when displaying the queries an update will run. If you execute
     * queries directly in `doUpdate()`, they won't be displayed to the user.
     *
     * @param Updater $updater
     * @return array ```
     *               array(
     *                   'ALTER .... ' => '1234', // if the query fails, it will be ignored if the error code is 1234
     *                   'ALTER .... ' => false,  // if an error occurs, the update will stop and fail
     *                                            // and user will have to manually run the query
     *               )
     *               ```
     */
    public function getMigrationQueries(Updater $updater)
    {
        $errorCodesToIgnore = array(1060);
        $tableName = Common::prefixTable('log_visit');
        $updateSql = "ALTER TABLE `" . $tableName . "` CHANGE `example` `example` BOOLEAN NOT NULL";

        return array(
            // $updateSql => $errorCodesToIgnore
        );
    }

    /**
     * Perform the incremental version update.
     *
     * This method should preform all updating logic. If you define queries in an overridden `getMigrationQueries()`
     * method, you must call {@link Updater::executeMigrationQueries()} here.
     *
     * See {@link Updates} for an example.
     *
     * @param Updater $updater
     */
    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
