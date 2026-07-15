<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\tests\Integration;

use Piwik\Access;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\Login\PasswordVerifier;
use Piwik\Plugins\UsersManager\API as UsersAPI;
use Piwik\Plugins\UsersManager\UserUpdater;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Verifies that checking a password does not change the current authentication state.
 *
 * @group Login
 */
class PasswordVerifierAuthStateTest extends IntegrationTestCase
{
    private const OTHER_USER = 'anothersuperuser';
    private const OTHER_USER_PASSWORD = '123abcDk3_l3';

    public function setUp(): void
    {
        parent::setUp();

        UsersAPI::getInstance()->addUser(self::OTHER_USER, self::OTHER_USER_PASSWORD, self::OTHER_USER . '@matomo.org');
        (new UserUpdater())->setSuperUserAccessWithoutCurrentPassword(self::OTHER_USER, true);
    }

    public function testCheckingAPasswordDoesNotChangeTheAuthenticatedUser()
    {
        $this->authenticateAsAnonymous();
        $this->assertFalse(Access::getInstance()->hasSuperUserAccess());
        $this->assertNull(Access::getInstance()->getLogin());

        // Check another user's password.
        $isCorrect = StaticContainer::get(PasswordVerifier::class)
            ->isPasswordCorrect(self::OTHER_USER, self::OTHER_USER_PASSWORD);
        $this->assertTrue($isCorrect);

        // The current user must be unchanged afterwards.
        Access::getInstance()->reloadAccess();

        $this->assertFalse(Access::getInstance()->hasSuperUserAccess());
        $this->assertNull(Access::getInstance()->getLogin());
    }

    private function authenticateAsAnonymous(): void
    {
        Access::getInstance()->setSuperUserAccess(false);

        /** @var \Piwik\Auth $auth */
        $auth = StaticContainer::get('Piwik\Auth');
        $auth->setLogin('anonymous');
        $auth->setTokenAuth('anonymous');
        $auth->setPasswordHash(null);

        Access::getInstance()->reloadAccess($auth);
    }
}
