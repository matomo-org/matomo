<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use Piwik\Config;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\UsersManager\Controller;
use Piwik\Nonce;
use Piwik\Auth\PasswordStrength;
use Piwik\Date;
use Piwik\Plugin\ThemeStyles;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Plugins\Login\PasswordVerifier;
use Piwik\Plugins\UsersManager\UserPreferences;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Translation\Loader\DevelopmentLoader;
use Piwik\Translation\Loader\JsonFileLoader;
use Piwik\Translation\Translator;

/**
 * @group UsersManager
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
    private $get;
    private $request;
    private $enableUsersAdmin;

    public function setUp(): void
    {
        parent::setUp();

        $this->controller = new Controller(
            $translator = new Translator(new DevelopmentLoader(new JsonFileLoader())),
            $passwordVerify = new PasswordVerifier(),
            $userModel = new Model(),
            $passwordStrength = new PasswordStrength(true)
        );
        $this->post = $_POST;
        $this->get = $_GET;
        $this->request = $_REQUEST;
        $this->enableUsersAdmin = Config::getInstance()->General['enable_users_admin'];

        FakeAccess::$superUser = true;

        $identity = FakeAccess::$identity;
        UsersManagerAPI::getInstance()->addUser($identity, 'Password111!', 'controller-test@example.com');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $_POST = $this->post;
        $_GET = $this->get;
        $_REQUEST = $this->request;
        Config::getInstance()->General['enable_users_admin'] = $this->enableUsersAdmin;
    }

    public function testRecordPasswordChangePasswordStrengthCheckWeakPassword()
    {
        $this->setupPostStateWithPassword('password1');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_PasswordStrengthValidationFailed');
        $this->controller->recordPasswordChange();
    }

    public function testRecordPasswordChangePasswordStrengthCheckStrongPassword()
    {
        $this->setupPostStateWithPassword('Password111!');

        // create user to get test in a repeatable state
        $userLogin = 'super user was set';
        $userEmail = 'test@test.com';
        $usersModel = new Model();
        $usersModel->addUser($userLogin, $passwordHash = '', $userEmail, Date::now()->getDatetime());

        // expect test to get past strength check and fail when checking existing password
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ConfirmWithReAuthentication');
        $this->controller->recordPasswordChange();
    }

    public function testRecordUserSettingsShouldRejectInvalidThemeMode()
    {
        Config::getInstance()->General['enable_users_admin'] = 0;

        $_GET = [
            'format' => 'json',
            'themeMode' => 'invalid',
            'defaultReport' => '1',
            'defaultDate' => 'today',
            'language' => 'en',
            'timeformat' => '0',
        ];
        $_POST = [];
        $_REQUEST = $_GET;

        $response = $this->controller->recordUserSettings();

        $this->assertStringContainsString('Invalid theme mode', $response);
        $this->assertSame(ThemeStyles::LIGHT_MODE, (new UserPreferences())->getThemeMode());
    }

    private function setupPostStateWithPassword(string $password)
    {
        $_POST['nonce'] = Nonce::getNonce('changePasswordNonce');
        $_POST['password'] = $password;
        $_POST['passwordBis'] = $password;
        // original password (irrelevant for test)
        $_POST['passwordConfirmation'] = '';
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
