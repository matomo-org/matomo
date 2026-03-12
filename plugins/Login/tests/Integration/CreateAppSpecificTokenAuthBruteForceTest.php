<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\tests\Integration;

use Piwik\API\Request;
use Piwik\Container\StaticContainer;
use Piwik\NoAccessException;
use Piwik\Plugins\Login\Login as LoginPlugin;
use Piwik\Plugins\Login\Security\BruteForceDetection;
use Piwik\Plugins\Login\SystemSettings;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Login
 * @group Plugins
 */
class CreateAppSpecificTokenAuthBruteForceTest extends IntegrationTestCase
{
    /**
     * @var BruteForceDetection
     */
    private $bruteForceDetection;

    /**
     * @var string
     */
    private $userLogin;

    /**
     * @var string
     */
    private $userEmail;

    /**
     * @var string
     */
    private $userPassword = 'someStrongPassword123!';

    /**
     * @var SystemSettings
     */
    private $settings;

    /**
     * @var array<string, mixed>
     */
    private $post = [];

    /**
     * @var array<string, mixed>
     */
    private $request = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->post = $_POST;
        $this->request = $_REQUEST;

        $this->bruteForceDetection = StaticContainer::get(BruteForceDetection::class);
        $this->settings = new SystemSettings();
        $this->settings->maxFailedLoginsPerMinutes->setValue(1);
        $this->bruteForceDetection->deleteAll();

        $this->userLogin = 'blockeduser' . substr(md5(uniqid('', true)), 0, 8);
        $this->userEmail = $this->userLogin . '@matomo.org';

        UsersManagerAPI::getInstance()->addUser($this->userLogin, $this->userPassword, $this->userEmail);
    }

    public function tearDown(): void
    {
        $this->bruteForceDetection->deleteAll();
        $_POST = $this->post;
        $_REQUEST = $this->request;

        parent::tearDown();
    }

    public function testCreateAppSpecificTokenAuthThrowsWhenUserLoginIsBlockedUsingLogin(): void
    {
        $this->blockLogin($this->userLogin);
        $this->assertTrue($this->bruteForceDetection->isUserLoginBlocked($this->userLogin));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Login_LoginNotAllowedBecauseUserLoginBlocked');

        Request::processRequest('UsersManager.createAppSpecificTokenAuth', [
            'userLogin' => $this->userLogin,
            'passwordConfirmation' => $this->userPassword,
            'description' => 'blocked login test',
        ]);
    }

    public function testCreateAppSpecificTokenAuthThrowsWhenUserLoginIsBlockedUsingEmail(): void
    {
        $this->blockLogin($this->userLogin);
        $this->assertTrue($this->bruteForceDetection->isUserLoginBlocked($this->userLogin));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Login_LoginNotAllowedBecauseUserLoginBlocked');

        Request::processRequest('UsersManager.createAppSpecificTokenAuth', [
            'userLogin' => $this->userEmail,
            'passwordConfirmation' => $this->userPassword,
            'description' => 'blocked email test',
        ]);
    }

    public function testCreateAppSpecificTokenAuthCreatesTokenUsingEmailWhenUserNotBlocked(): void
    {
        $token = Request::processRequest('UsersManager.createAppSpecificTokenAuth', [
            'userLogin' => $this->userEmail,
            'passwordConfirmation' => $this->userPassword,
            'description' => 'allowed email test',
        ]);

        $this->assertIsString($token);
        $this->assertNotSame('', $token);
    }

    public function testBeforeLoginCheckBruteForceThrowsWhenUserLoginIsBlockedUsingEmailInFormLogin(): void
    {
        $this->blockLogin($this->userLogin);
        $this->assertTrue($this->bruteForceDetection->isUserLoginBlocked($this->userLogin));

        $_POST['form_login'] = $this->userEmail;
        $_REQUEST['form_login'] = $this->userEmail;
        StaticContainer::get(\Piwik\Auth::class)->setLogin('anonymous');

        $this->expectException(NoAccessException::class);
        $this->expectExceptionCode(403);

        $plugin = new LoginPlugin();
        $plugin->beforeLoginCheckBruteForce();
    }

    private function blockLogin(string $userLogin): void
    {
        $requiredAttempts = BruteForceDetection::OVERALL_LOGIN_LOCKOUT_THRESHOLD_MIN + 1;

        for ($i = 1; $i <= $requiredAttempts; $i++) {
            $this->bruteForceDetection->addFailedAttempt('10.0.0.' . $i, $userLogin);
        }
    }
}
