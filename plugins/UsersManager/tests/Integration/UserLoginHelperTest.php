<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\UserLoginHelper;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group UsersManager
 * @group Plugins
 */
class UserLoginHelperTest extends IntegrationTestCase
{
    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $email;

    public function setUp(): void
    {
        parent::setUp();

        $this->login = 'helperuser' . substr(md5(uniqid('', true)), 0, 8);
        $this->email = $this->login . '@matomo.org';

        API::getInstance()->addUser($this->login, 'someStrongPassword123!', $this->email);
    }

    public function testFindUserByLoginOrEmailFindsByLoginAndEmail(): void
    {
        $byLogin = UserLoginHelper::findUserByLoginOrEmail($this->login);
        $byEmail = UserLoginHelper::findUserByLoginOrEmail($this->email);

        $this->assertNotEmpty($byLogin);
        $this->assertNotEmpty($byEmail);
        $this->assertSame($this->login, $byLogin['login']);
        $this->assertSame($this->email, $byLogin['email']);
        $this->assertSame($this->login, $byEmail['login']);
        $this->assertSame($this->email, $byEmail['email']);
    }

    public function testFindCanonicalLoginByLoginOrEmailResolvesAndReturnsNullWhenNotFound(): void
    {
        $this->assertSame($this->login, UserLoginHelper::findCanonicalLoginByLoginOrEmail($this->login));
        $this->assertSame($this->login, UserLoginHelper::findCanonicalLoginByLoginOrEmail($this->email));
        $this->assertNull(UserLoginHelper::findCanonicalLoginByLoginOrEmail('does-not-exist@example.org'));
    }

    public function testNormalizeLoginOrEmailToLoginKeepsUnknownInputUnchanged(): void
    {
        $this->assertSame($this->login, UserLoginHelper::normalizeLoginOrEmailToLogin($this->login));
        $this->assertSame($this->login, UserLoginHelper::normalizeLoginOrEmailToLogin($this->email));
        $this->assertSame('does-not-exist@example.org', UserLoginHelper::normalizeLoginOrEmailToLogin('does-not-exist@example.org'));
    }
}
