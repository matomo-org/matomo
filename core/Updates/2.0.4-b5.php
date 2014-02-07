<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\UsersManager\API as UsersManagerApi;
use Piwik\Updater;
use Piwik\UpdaterErrorException;
use Piwik\Updates;
use Piwik\Config;

/**
 */
class Updates_2_0_4_b5 extends Updates
{
    static function getSql()
    {
        return array(
            // ignore existing column name error (1060)
            'ALTER TABLE ' . Common::prefixTable('user')
            . " ADD COLUMN `superuser_access` tinyint(2) unsigned NOT NULL DEFAULT '0' AFTER token_auth" => 1060,
        );
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());

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

        $superUser = $config->superuser;

        if (!empty($superUser['bridge'])) {
            // there is a super user which is not from the config but from the bridge, that means we already have
            // a super user in the database
            return;
        } elseif (empty($superUser)) {

            throw new UpdaterErrorException('Unable to migrate superUser to database. Entry in config is missing.');
        }

        $userApi = UsersManagerApi::getInstance();

        Db::get()->insert(Common::prefixTable('user'), array(
                'login'      => $superUser['login'],
                'password'   => $superUser['password'],
                'alias'      => $superUser['login'],
                'email'      => $superUser['email'],
                'token_auth' => $userApi->getTokenAuth($superUser['login'], $superUser['password']),
                'date_registered'  => Date::now()->getDatetime(),
                'superuser_access' => 1
            )
        );

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
