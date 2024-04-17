<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Db;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates;
use Piwik\Updater;

/**
 * Update for version 3.6.0-b4.
 */
class Updates_3_6_0_b4 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater)
    {
        return array(
            $this->migration->db->changeColumn('user', 'ts_password_modified', 'ts_password_modified', 'TIMESTAMP NULL'),
            $this->migration->db->sql('UPDATE `' . Common::prefixTable('user') . '` SET ts_password_modified = NULL'),
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));

        // the MySQL client experiences an odd race condition with this update. after running the migration script, when
        // UsersManager\Model::getUser is called, the fetch segfaults and simply exits. resetting the DB connection
        // after the update appears to circumvent this issue.
        Db::get()->closeConnection();
        Db::setDatabaseObject(null);
        Db::get();
    }
}
