<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Fixtures;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\Model as UsersManagerModel;
use Piwik\Tests\Framework\Fixture;

/**
 * Generates auth tokens for token notification tests
 */
class Tokens extends Fixture
{
    public $tokens = [
        'user1' => [
            ['user1, not expired user token, secure only', '2024-01-01 00:00:00', null, '0', '1'],
            ['user1, not expired user token, not secure only', '2025-01-01 00:00:00', null, '0', '0'],
            ['user1, not expired system token, secure only', '2024-01-01 00:00:00', null, '1', '1'],
            ['user1, not expired system token, not secure only', '2025-01-01 00:00:00', null, '1', '0'],
            ['user1, expired user token, secure only', '2024-01-01 00:00:00', '2024-02-01 00:00:00', '0', '1'],
            ['user1, expired user token, not secure only', '2025-01-01 00:00:00', '2024-02-01 00:00:00', '0', '0'],
            ['user1, expired system token, secure only', '2024-01-01 00:00:00', '2024-02-01 00:00:00', '1', '1'],
            ['user1, expired system token, not secure only', '2025-01-01 00:00:00', '2024-02-01 00:00:00', '1', '0'],
        ],
        'user2' => [
            ['user2, not expired user token, secure only', '2024-01-01 00:00:00', null, '0', '1'],
            ['user2, not expired user token, not secure only', '2025-01-01 00:00:00', null, '0', '0'],
            ['user2, not expired system token, secure only', '2024-01-01 00:00:00', null, '1', '1'],
            ['user2, not expired system token, not secure only', '2025-01-01 00:00:00', null, '1', '0'],
            ['user2, expired user token, secure only', '2024-01-01 00:00:00', '2024-02-01 00:00:00', '0', '1'],
            ['user2, expired user token, not secure only', '2025-01-01 00:00:00', '2024-02-01 00:00:00', '0', '0'],
            ['user2, expired system token, secure only', '2024-01-01 00:00:00', '2024-02-01 00:00:00', '1', '1'],
            ['user2, expired system token, not secure only', '2025-01-01 00:00:00', '2024-02-01 00:00:00', '1', '0'],
        ],
    ];

    public function setUp(): void
    {
        Fixture::createWebsite('2020-01-11 00:00:00');

        $this->setUpUsersAndTokens();
    }

    private function setUpUsersAndTokens()
    {
        $api = API::getInstance();
        $api->addUser('user1', 'password1', 'user1@example.com');
        $api->addUser('user2', 'password2', 'user2@example.com');
        $api->setUserAccess('user2', 'view', [1]);

        $model = new UsersManagerModel();

        foreach ($this->tokens as $user => $tokens) {
            foreach ($tokens as $token) {
                $model->addTokenAuth($user, $model->generateRandomTokenAuth(), ...$token);
            }
        }
    }

    public function resetTsRotationNotification()
    {
        Db::get()->query("UPDATE " . Common::prefixTable('user_token_auth') . " SET ts_rotation_notified = NULL");
    }
}
