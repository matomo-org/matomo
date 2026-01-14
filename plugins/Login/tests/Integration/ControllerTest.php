<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\tests\Integration;

use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\Login\Controller;
use Piwik\Nonce;
use Piwik\Auth\PasswordStrength;
use Piwik\Date;
use Piwik\Plugins\UsersManager\Model;

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
}
