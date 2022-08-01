<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\Emails\UserInviteEmail;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Site;
use Piwik\Updater;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates as PiwikUpdates;

/**
 * Update for version 4.11.0-rc2
 */
class Updates_4_11_0_rc2 extends PiwikUpdates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    private $pendingUsers;

    private $userTable;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
        $this->userTable = Common::prefixTable('user');
    }

    /**
     * @param Updater $updater
     *
     * @return Migration[]
     */
    public function getMigrations(Updater $updater)
    {
        try {
            $this->pendingUsers = Db::fetchAll(
                "SELECT * FROM $this->userTable WHERE invite_status = ? ",
                ['pending']
            );
        } catch (\Exception $e) {
            // ignore any errors. The column might not exist when updating from an older version,
            // so there wouldn't be anything to update anyway
        }
        return [
          $this->migration->db->dropColumn('user', 'invite_status'),
          $this->migration->db->addColumns('user', ['invite_token' => 'VARCHAR(191) DEFAULT null']),
          $this->migration->db->addColumns('user', ['invited_by' => 'VARCHAR(100) DEFAULT null']),
          $this->migration->db->addColumns('user', ['invite_expired_at' => 'TIMESTAMP null DEFAULT null']),
          $this->migration->db->addColumns('user', ['invite_accept_at' => 'TIMESTAMP null DEFAULT null']),
        ];
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));

        $model = new Model();
        if (!empty($this->pendingUsers)) {
            foreach ($this->pendingUsers as $user) {
                $model->deleteAllTokensForUser($user['login']);

                $site = $model->getSitesAccessFromUser($user['login']);
                if (isset($site[0])) {
                    $site = new Site($site[0]['site']);
                    $siteName = $site->getName();
                } else {
                    $siteName = "Default Site";
                }
                //generate Token
                $generatedToken = $model->generateRandomTokenAuth();

                //attach token to user
                $model->attachInviteToken($user['login'], $generatedToken, 7);

                // send email
                $email = StaticContainer::getContainer()->make(UserInviteEmail::class, [
                  'currentUser'  => Piwik::getCurrentUserLogin(),
                  'invitedUser'  => $user,
                  'siteName'     => $siteName,
                  'token'        => $generatedToken,
                  'expiryInDays' => 7
                ]);
                $email->safeSend();
            }
        }
    }
}
