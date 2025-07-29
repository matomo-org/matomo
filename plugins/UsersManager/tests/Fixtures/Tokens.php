<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Fixtures;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\Model as UsersManagerModel;
use Piwik\Tests\Framework\Fixture;

/**
 * Generates auth tokens for token notification tests with an optional last_used date
 */
class Tokens extends Fixture
{
    public $tokens = [
        'user1' => [
            [['user1, not expired user token, secure only', '2024-01-01 00:00:00', null, '0', '1'], '2024-09-01 00:00:00'],
            [['user1, not expired user token, not secure only', '2025-01-01 00:00:00', null, '0', '0'], '2025-02-01 00:00:00'],
            [['user1, not expired system token, secure only', '2024-01-01 00:00:00', null, '1', '1'], null],
            [['user1, not expired system token, not secure only', '2025-01-01 00:00:00', null, '1', '0'], null],
            [['user1, expired user token, secure only', '2024-01-01 00:00:00', '2024-02-01 00:00:00', '0', '1'], null],
            [['user1, expired user token, not secure only', '2025-01-01 00:00:00', '2024-02-01 00:00:00', '0', '0'], null],
            [['user1, expired system token, secure only', '2024-01-01 00:00:00', '2024-02-01 00:00:00', '1', '1'], null],
            [['user1, expired system token, not secure only', '2025-01-01 00:00:00', '2024-02-01 00:00:00', '1', '0'], null],
        ],
        'user2' => [
            [['user2, not expired user token, secure only', '2024-01-01 00:00:00', null, '0', '1'], null],
            [['user2, not expired user token, not secure only', '2025-01-01 00:00:00', null, '0', '0'], null],
            [['user2, not expired system token, secure only', '2024-01-01 00:00:00', null, '1', '1'], null],
            [['user2, not expired system token, not secure only', '2025-01-01 00:00:00', null, '1', '0'], null],
            [['user2, expired user token, secure only', '2024-01-01 00:00:00', '2024-02-01 00:00:00', '0', '1'], '2024-01-10 00:00:00'],
            [['user2, expired user token, not secure only', '2025-01-01 00:00:00', '2024-02-01 00:00:00', '0', '0'], null],
            [['user2, expired system token, secure only', '2024-01-01 00:00:00', '2024-02-01 00:00:00', '1', '1'], null],
            [['user2, expired system token, not secure only', '2025-01-01 00:00:00', '2024-02-01 00:00:00', '1', '0'], null],
        ],
        'user3' => [
            [['user3, not expired user token, secure only, not used', '2024-01-01 00:00:00', null, '0', '1'], null],
            [['user3, not expired user token, secure only, used', '2024-01-01 00:00:00', null, '0', '1'], '2024-09-01 00:00:00'],
            [['user3, expired user token, secure only, not used', '2024-01-01 00:00:00', '2024-02-01 00:00:00', '0', '1'], null],
            [['user3, expired user token, secure only, used', '2024-01-01 00:00:00', '2024-02-01 00:00:00', '0', '1'], '2024-09-01 00:00:00'],
            [['user3, not expired system token, secure only, not used', '2024-01-01 00:00:00', null, '1', '1'], null],
            [['user3, not expired system token, secure only, used', '2024-01-01 00:00:00', null, '1', '1'], '2024-09-01 00:00:00'],
        ]
    ];

    public function setUp(): void
    {
        Fixture::createWebsite('2020-01-11 00:00:00');

        $this->setUpUsersAndTokens();
    }

    private function setUpUsersAndTokens()
    {
        Date::$now = Date::factory('2024-01-01')->getTimestamp();

        $api = API::getInstance();
        $model = new UsersManagerModel();

        for ($i = 1; $i <= 5; $i++) {
            $api->addUser("user$i", "password$i", "user$i@example.com");
            $api->setUserAccess("user$i", 'view', [1]);
        }
        $model->setSuperUserAccess('user1', true);
        $model->setLastSeenDatetime('user2', '2024-09-29 00:00:00');
        $model->setLastSeenDatetime('user4', '2024-09-29 00:00:00');

        foreach ($this->tokens as $user => $tokens) {
            foreach ($tokens as $tokenArray) {
                $tokenAuth = $model->generateRandomTokenAuth();
                $model->addTokenAuth($user, $tokenAuth, ...$tokenArray[0]);
                if ($lastUsed = $tokenArray[1]) {
                    $model->setTokenAuthWasUsed($tokenAuth, $lastUsed);
                }
            }
        }
    }

    public function resetTsRotationNotification()
    {
        Db::get()->query("UPDATE " . Common::prefixTable('user_token_auth') . " SET ts_rotation_notified = NULL");
    }

    public function resetTsInactivityNotification()
    {
        Db::get()->query("UPDATE " . Common::prefixTable('user') . " SET ts_inactivity_notified = NULL");
    }
}
