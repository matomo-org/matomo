<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Plugins\UsersManager\UserUpdater;
use Piwik\Tests\Framework\Fixture;

/**
 * Generates tracker testing data for our APITest
 *
 * This Simple fixture adds one website and tracks one visit with couple pageviews and an ecommerce conversion
 */
class ManyUsers extends Fixture
{
    public const SITE_COUNT = 100;
    public const USER_COUNT = 100;

    public $dateTime = '2013-01-23 01:23:45';
    public $idSite = 1;
    public $siteCopyCount;
    public $userCopyCount;
    public $addTextSuffixes;
    public $users = [];

    public $baseUsers = [
        'login1' => ['superuser' => 1],
        'login2' => ['view' => [3, 5], 'admin' => [1, 2, 6]],
        'login3' => ['view' => [], 'admin' => []], // no access to any site
        'login4' => ['view' => [6], 'admin' => []], // only access to one with view
        'login5' => ['view' => [], 'admin' => [3]], // only access to one with admin
        'login6' => ['view' => [], 'admin' => [6, 3]], // access to a couple of sites with admin
        'login7' => ['view' => [2, 1, 6, 3], 'admin' => []], // access to a couple of sites with view
        'login8' => ['view' => [4, 7], 'admin' => [2, 5]], // access to a couple of sites with admin and view
        'login9' => ['view' => [5, 6], 'admin' => [8, 9]],
        'login10' => ['superuser' => 1]
    ];

    public $pendingUser = [
      'login' => '000pendingUser1',
      'email' => 'pendinguser1light@example.com'
    ];

    public $pendingUser2 = [
      'login' => 'zzzpendingUser2',
      'email' => 'zpendinguser2light@example.com'
    ];

    public $baseSites = [
      'sleep',
      'escapesequence',
      'hunter',
      'transistor',
      'wicket',
      'relentless',
      'scarecrow',
      'nova',
      'resilience',
      'tricks',
    ];

    public $textAdditions = [
      'life',
      'light',
      'flight',
      'conchords',
    ];

    public function __construct(
        $userCopyCount = self::USER_COUNT,
        $siteCopyCount = self::SITE_COUNT,
        $addTextSuffixes = true
    ) {
        $this->userCopyCount = $userCopyCount;
        $this->siteCopyCount = $siteCopyCount;
        $this->addTextSuffixes = $addTextSuffixes;
    }

    public function setUp(): void
    {
        $this->setUpWebsite();
        $this->setUpUsers();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsite()
    {
        for ($i = 0; $i < self::SITE_COUNT; $i++) {
            $siteName = $this->baseSites[$i % count($this->baseSites)];
            if ($i != 0) {
                $siteName .= $i;
            }
            Fixture::createWebsite('2010-01-01 00:00:00', $ecommerce = 0, $siteName);
        }
    }

    protected function setUpUsers()
    {
        $totalUserCount = 0;

        $model = new Model();
        $api = API::getInstance();

        // add a pending invite user
        $api->inviteUser($this->pendingUser['login'], $this->pendingUser['email'], 1);

        for ($i = 0; $i != $this->userCopyCount; ++$i) {
            $addToEmail = $i % 2 == 0;

            foreach ($this->baseUsers as $baseLogin => $permissions) {
                ++$totalUserCount;

                $textAddition = $this->textAdditions[$totalUserCount % count($this->textAdditions)];

                $login = $this->addTextSuffixes ? ($i . '_' . $baseLogin) : $baseLogin;
                if ($this->addTextSuffixes && !$addToEmail) {
                    $login .= $textAddition;
                }

                $email = $login . '@example.com';
                if ($this->addTextSuffixes && $addToEmail) {
                    $email = $login . $textAddition . '@example.com';
                }

                $api->addUser($login, 'password', $email);

                foreach ($permissions as $access => $idSites) {
                    if (empty($idSites)) {
                        continue;
                    }

                    if ($access == 'superuser') {
                        $userUpdater = new UserUpdater();
                        $userUpdater->setSuperUserAccessWithoutCurrentPassword($login, true);
                    } else {
                        $api->setUserAccess($login, $access, $idSites);
                    }
                }

                $tokenAuth = $model->generateRandomTokenAuth();
                $model->addTokenAuth($login, $tokenAuth, 'many users test', Date::now()->getDatetime());

                $user = $model->getUser($login);
                $this->users[$login]['token'] = $tokenAuth;
            }
        }

        //add admin view pending user
        $api->inviteUser($this->pendingUser2['login'], $this->pendingUser2['email'], 1);
        $model->updateUserFields($this->pendingUser2['login'], ['invited_by' => 'login2']);
    }
}
