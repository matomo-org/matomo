<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use Exception;
use Piwik\Plugins\UsersManager\API as UsersAPI;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group UsersManager
 */
class CreateAppSpecificTokenAuthSelfOnlyTest extends IntegrationTestCase
{
    private const PASSWORD = '123abcDk3_l3';

    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::clearAccess(true);
        UsersAPI::getInstance()->addUser('actor', self::PASSWORD, 'actor@matomo.org');
        UsersAPI::getInstance()->addUser('victim', self::PASSWORD, 'victim@matomo.org');
    }

    public function provideContainerConfig()
    {
        return ['Piwik\Access' => new FakeAccess()];
    }

    public function testLoggedInUserCannotCreateTokenForAnotherUser()
    {
        FakeAccess::clearAccess(false, [], [], 'actor');

        self::expectException(Exception::class);
        self::expectExceptionMessage('UsersManager_ExceptionCreateTokenAuthForOtherUser');

        UsersAPI::getInstance()->createAppSpecificTokenAuth('victim', self::PASSWORD, 'x');
    }

    public function testLoggedInUserCanCreateTokenForThemselves()
    {
        FakeAccess::clearAccess(false, [], [], 'actor');

        $token = UsersAPI::getInstance()->createAppSpecificTokenAuth('actor', self::PASSWORD, 'x');

        self::assertNotEmpty($token);
    }

    public function testSuperUserCannotCreateTokenForAnotherUserWhileLoggedIn()
    {
        FakeAccess::clearAccess(true, [], [], 'admin');

        self::expectException(Exception::class);
        self::expectExceptionMessage('UsersManager_ExceptionCreateTokenAuthForOtherUser');

        UsersAPI::getInstance()->createAppSpecificTokenAuth('victim', self::PASSWORD, 'x');
    }

    public function testAnonymousCanCreateTokenForTheCredentialOwner()
    {
        FakeAccess::clearAccess(false, [], [], 'anonymous');

        $token = UsersAPI::getInstance()->createAppSpecificTokenAuth('victim', self::PASSWORD, 'x');

        self::assertNotEmpty($token);
    }
}
