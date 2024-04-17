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
use Piwik\Option;
use Piwik\Plugin;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

class Updates_3_8_0_b3 extends PiwikUpdates
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
        $userColumn = $this->migration->db->addColumn('user', 'twofactor_secret', "VARCHAR(40) NOT NULL DEFAULT ''");
        $backupCode = $this->migration->db->createTable('twofactor_recovery_code', array(
            'idrecoverycode' => 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
            'login' => 'VARCHAR(100) NOT NULL',
            'recovery_code' => 'VARCHAR(40) NOT NULL',
        ), array('idrecoverycode'));
        $twoFactorAuth = $this->migration->plugin->activate('TwoFactorAuth');
        $googleAuth = $this->migration->plugin->deactivate('GoogleAuthenticator');

        return array($userColumn, $backupCode, $twoFactorAuth, $googleAuth);
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));

        if (Plugin\Manager::getInstance()->isPluginActivated('GoogleAuthenticator')) {
            foreach (Option::getLike('GoogleAuthentication.%') as $name => $value) {
                $value = @Common::safe_unserialize($value);
                if (!empty($value['isActive']) && !empty($value['secret'])) {
                    $login = str_replace('GoogleAuthentication.', '', $name);

                    $table = Common::prefixTable('user');
                    Db::query("UPDATE $table SET twofactor_secret = ? where login = ?", array($value['secret'], $login));
                }
            }
        }
    }
}
