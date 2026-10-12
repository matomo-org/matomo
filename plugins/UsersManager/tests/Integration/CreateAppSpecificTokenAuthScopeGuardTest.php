<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use Exception;
use Piwik\Access;
use Piwik\Auth;
use Piwik\AuthResult;
use Piwik\Plugins\UsersManager\API as UsersAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * A scoped token must not be able to mint a token that is less restricted than itself, which would let it
 * escape its own scope while still only ever using the access the scope allows.
 *
 * @group UsersManager
 */
class CreateAppSpecificTokenAuthScopeGuardTest extends IntegrationTestCase
{
    private const LOGIN = 'scopedissuer';
    private const PASSWORD = '123abcDk3_l3';

    public function setUp(): void
    {
        parent::setUp();

        $idSite = Fixture::createWebsite('2010-01-02 00:00:00');

        UsersAPI::getInstance()->addUser(self::LOGIN, self::PASSWORD, 'scopedissuer@matomo.org');
        UsersAPI::getInstance()->setUserAccess(self::LOGIN, 'admin', [$idSite]);
    }

    public function testScopedTokenCannotCreateUnscopedToken()
    {
        $this->authenticateWithScopedToken('view');

        self::expectException(Exception::class);
        self::expectExceptionMessage('UsersManager_ExceptionCreateTokenAuthAboveRequestTokenScope');

        UsersAPI::getInstance()->createAppSpecificTokenAuth(self::LOGIN, self::PASSWORD, 'unscoped');
    }

    public function testScopedTokenCannotCreateTokenWithHigherAccessLevel()
    {
        $this->authenticateWithScopedToken('view');

        self::expectException(Exception::class);
        self::expectExceptionMessage('UsersManager_ExceptionCreateTokenAuthAboveRequestTokenScope');

        UsersAPI::getInstance()->createAppSpecificTokenAuth(self::LOGIN, self::PASSWORD, 'admin', null, 0, false, 'admin');
    }

    public function testScopedTokenCanCreateTokenWithSameAccessLevel()
    {
        $this->authenticateWithScopedToken('write');

        $token = UsersAPI::getInstance()->createAppSpecificTokenAuth(self::LOGIN, self::PASSWORD, 'same', null, 0, false, 'write');

        self::assertNotEmpty($token);
    }

    public function testScopedTokenCanCreateTokenWithLowerAccessLevel()
    {
        $this->authenticateWithScopedToken('write');

        $token = UsersAPI::getInstance()->createAppSpecificTokenAuth(self::LOGIN, self::PASSWORD, 'lower', null, 0, false, 'view');

        self::assertNotEmpty($token);
    }

    public function testUnscopedTokenCanCreateUnscopedToken()
    {
        $this->authenticateWithScopedToken(null);

        $token = UsersAPI::getInstance()->createAppSpecificTokenAuth(self::LOGIN, self::PASSWORD, 'unscoped');

        self::assertNotEmpty($token);
    }

    public function testSuperuserScopedTokenIsNotTreatedAsRestriction()
    {
        $this->authenticateWithScopedToken('superuser');

        $token = UsersAPI::getInstance()->createAppSpecificTokenAuth(self::LOGIN, self::PASSWORD, 'unscoped');

        self::assertNotEmpty($token);
    }

    private function authenticateWithScopedToken(?string $accessLevel): void
    {
        $authMock = $this->createMock(Auth::class);
        $authMock->method('authenticate')->willReturn(new AuthResult(
            AuthResult::SUCCESS,
            self::LOGIN,
            'sometoken',
            ['token_access_level' => $accessLevel]
        ));

        $access = Access::getInstance();
        $access->setSuperUserAccess(false);
        self::assertTrue($access->reloadAccess($authMock));
    }
}
