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
use Piwik\Config;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\UsersManager\API as UsersManagerApi;
use Piwik\Updater;
use Piwik\UpdaterErrorException;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_2_0_4_b5 extends Updates
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
            $this->migration->db->addColumn('user', 'superuser_access', "TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'")
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));

        try {
            self::migrateConfigSuperUserToDb();
        } catch (\Exception $e) {
            throw new UpdaterErrorException($e->getMessage());
        }
    }

    private static function migrateConfigSuperUserToDb()
    {
        $config = Config::getInstance();

        if (!$config->existsLocalConfig()) {
            return;
        }

        try {
            $superUser = $config->superuser;
        } catch (\Exception $e) {
            $superUser = null;
        }

        if (
            !empty($superUser['bridge'])
            || empty($superUser)
            || empty($superUser['login'])
        ) {
            // there is a super user which is not from the config but from the bridge, that means we already have
            // a super user in the database
            return;
        }

        $userApi = UsersManagerApi::getInstance();

        try {
            Db::get()->insert(Common::prefixTable('user'), array(
                    'login'      => $superUser['login'],
                    'password'   => $superUser['password'],
                    'alias'      => $superUser['login'],
                    'email'      => $superUser['email'],
                    'token_auth' => md5(Common::getRandomString(32)),
                    'date_registered'  => Date::now()->getDatetime(),
                    'superuser_access' => 1
                ));
        } catch (\Exception $e) {
            echo "There was an issue, but we proceed: " . $e->getMessage();
        }

        if (array_key_exists('salt', $superUser)) {
            $salt = $superUser['salt'];
        } else {
            $salt = Common::generateUniqId();
        }

        $config->General['salt'] = $salt;
        $config->superuser       = array();
        $config->forceSave();
    }
}
