<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\tests\Integration;

use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Auth;
use Piwik\Common;
use Piwik\Config;
use Piwik\Exception\RedirectException;
use Piwik\Plugins\Login\Controller;
use Piwik\Plugins\Login\PasswordResetter;
use Piwik\Nonce;
use Piwik\Auth\PasswordStrength;
use Piwik\Date;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Session\SessionInitializer;

/**
 * @group Login
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    /**
     * @var Controller
     */
    private $controller;
    private $post;

    public function setUp(): void
    {
        parent::setUp();

        $this->controller = new Controller(
            $passwordResetter = null,
            $auth = null,
            $sessionInitializer = null,
            $passwordVerify = null,
            $bruteForceDetection = null,
            $systemSettings = null,
            $passwordStrength = new PasswordStrength(true)
        );
        $this->post = $_POST;
        $_POST = [];
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $_POST = $this->post;
    }

    private function setupPostStateWithPassword(string $password)
    {
        $_POST['form_nonce'] = Nonce::getNonce('Login.login');
        $_POST['form_login'] = 'test';
        $_POST['form_password'] = $password;
        $_POST['form_password_bis'] = $password;
    }

    public function testResetPasswordStrengthCheckWeakPassword()
    {
        $this->setupPostStateWithPassword('password');
        $response = $this->controller->resetPassword();
        $this->assertStringContainsString('General_PasswordStrengthValidationFailed', $response);
    }

    public function testResetPasswordStrengthCheckStrongPassword()
    {
        $this->setupPostStateWithPassword('Password111!');
        $response = $this->controller->resetPassword();
        $this->assertStringNotContainsString('General_PasswordStrengthValidationFailed', $response);
    }

    private function generateTestUser(): array
    {
        // generate new user
        $userLogin = 'test';
        $userEmail = 'test@test.com';
        $usersModel = new Model();
        $usersModel->addUser($userLogin, $passwordHash = '', $userEmail, Date::now()->getDatetime());
        $token = $usersModel->generateRandomInviteToken();
        $usersModel->attachInviteToken($userLogin, $token, $expiryInDays = 1);

        return [$userEmail, $token];
    }

    private function setupPostInvitationSubmitted(string $token, string $userEmail, string $password, ?string $passwordConfirmation = null)
    {
        // simulate completing accept invitation form
        $_POST['token'] = $token;
        $_POST['password'] = $password;
        $_POST['passwordConfirmation'] = $passwordConfirmation ?? $password;
        $_POST['email'] = $userEmail;
        $_POST['invitation_form'] = 'Confirm';
        $_POST['conditionCheck'] = true;
    }

    public function testAcceptInvitationPasswordStrengthCheckWeakPassword()
    {

        [$userEmail, $token] = $this->generateTestUser();
        $this->setupPostInvitationSubmitted($token, $userEmail, 'password');

        $response = $this->controller->acceptInvitation();
        $this->assertStringContainsString('General_PasswordStrengthValidationFailed', $response);
    }

    public function testAcceptInvitationPasswordStrengthCheckStrongPassword()
    {
        [$userEmail, $token] = $this->generateTestUser();
        $this->setupPostInvitationSubmitted($token, $userEmail, 'Password111!', 'NotSamePassword');

        $response = $this->controller->acceptInvitation();
        $this->assertStringNotContainsString('General_PasswordStrengthValidationFailed', $response);
    }

    public function testAcceptInvitationIsOnlyProcessedForPostRequest()
    {
        [$userEmail, $token] = $this->generateTestUser();

        // provide a complete, valid acceptance form, but through GET instead of POST
        $_GET['token'] = $token;
        $_GET['password'] = 'Password111!';
        $_GET['passwordConfirmation'] = 'Password111!';
        $_GET['email'] = $userEmail;
        $_GET['invitation_form'] = 'Confirm';
        $_GET['conditionCheck'] = true;

        try {
            $response = $this->controller->acceptInvitation();
        } finally {
            foreach (['token', 'password', 'passwordConfirmation', 'email', 'invitation_form', 'conditionCheck'] as $key) {
                unset($_GET[$key]);
            }
        }

        // the form is only processed for POST requests, so the user must still be pending
        $this->assertTrue((new Model())->isPendingUser('test'));
        // and the set password form is rendered again instead
        $this->assertStringContainsString('invitation_form', $response);
    }

    public function testAcceptInvitationRejectsInvitationWithoutExpiry()
    {
        [, $token] = $this->generateTestUser();

        (new Model())->updateUserFields('test', ['invite_expired_at' => null]);
        $_POST['token'] = $token;

        $this->expectException(RedirectException::class);

        $this->controller->acceptInvitation();
    }

    public function testAuthenticateAndRedirectRebuildsFormRedirectUrl()
    {
        $host = 'localhost';

        // remember the process-global state we are about to mutate, so it can be restored afterwards
        $previousHttpHost = $_SERVER['HTTP_HOST'] ?? null;
        $previousSession = $_SESSION ?? null;
        $previousTrustedHosts = Config::getInstance()->General['trusted_hosts'] ?? null;

        $_SERVER['HTTP_HOST'] = $host;
        $_SESSION = [];
        Config::getInstance()->General['trusted_hosts'] = [$host];

        // the redirect url is rebuilt from its parsed parts, so special characters in the user info part
        // are escaped instead of being passed through verbatim
        $_POST['form_redirect'] = 'http://foo\\@' . $host . '/?module=CoreHome';

        $controller = new Controller(
            $this->createMock(PasswordResetter::class),
            $this->createMock(Auth::class),
            $this->createMock(SessionInitializer::class),
            $passwordVerify = null,
            $bruteForceDetection = null,
            $systemSettings = null,
            new PasswordStrength(true)
        );

        $method = new \ReflectionMethod(Controller::class, 'authenticateAndRedirect');
        $method->setAccessible(true);

        try {
            $method->invoke($controller, 'test', 'Password111!');
            $this->fail('Expected a redirect to be triggered');
        } catch (\Exception $e) {
            $redirectUrl = Common::$headersSentInTests['Location'] ?? '';
            // the backslash in the user info part must be escaped rather than passed through verbatim
            $this->assertStringNotContainsString('foo\\@' . $host, $redirectUrl);
            $this->assertStringContainsString('foo%5C@' . $host, $redirectUrl);
        } finally {
            if (null === $previousHttpHost) {
                unset($_SERVER['HTTP_HOST']);
            } else {
                $_SERVER['HTTP_HOST'] = $previousHttpHost;
            }
            $_SESSION = $previousSession ?? [];
            Config::getInstance()->General['trusted_hosts'] = $previousTrustedHosts;
        }
    }
}
