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
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;

/**
 * @group UsersManager
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    private const CURRENT_USER_LOGIN = 'usersManagerControllerTestLogin';
    private const CURRENT_USER_EMAIL = 'controller-test@example.com';

    /**
     * @var Controller
     */
    private $controller;
    private $post;
    private $get;
    private $request;
    private $enableUsersAdmin;
    private $superUser;
    private $identity;
    private $superUserLogin;

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
        $this->superUser = FakeAccess::$superUser;
        $this->identity = FakeAccess::$identity;
        $this->superUserLogin = FakeAccess::$superUserLogin;

        FakeAccess::$superUser = true;
        $userModel->deleteUser(self::CURRENT_USER_LOGIN);
        UsersManagerAPI::getInstance()->addUser(
            self::CURRENT_USER_LOGIN,
            'Password111!',
            self::CURRENT_USER_EMAIL
        );
        FakeAccess::$identity = self::CURRENT_USER_LOGIN;
        FakeAccess::$superUserLogin = self::CURRENT_USER_LOGIN;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $_POST = $this->post;
        $_GET = $this->get;
        $_REQUEST = $this->request;
        Config::getInstance()->General['enable_users_admin'] = $this->enableUsersAdmin;
        FakeAccess::$superUser = $this->superUser;
        FakeAccess::$identity = $this->identity;
        FakeAccess::$superUserLogin = $this->superUserLogin;
    }
    public function createSiteWithUser()
    {
        $idSite = SitesManagerAPI::getInstance()->addSite(
            'Test site',
            ['https://example.test']
        );
        UsersManagerAPI::getInstance()->setUserAccess(
            self::CURRENT_USER_LOGIN,
            'view',
            [$idSite]
        );
        UsersManagerAPI::getInstance()->setUserPreference(
            self::CURRENT_USER_LOGIN,
            UsersManagerAPI::PREFERENCE_DEFAULT_REPORT,
            $idSite
        );
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

    public function testUserSettingsShouldExposeMatchBrowserThemeModeOption()
    {
        $this->createSiteWithUser();
        $response = $this->controller->userSettings();

        $this->assertStringContainsString('theme-mode="&quot;light&quot;"', $response);
        $this->assertStringContainsString('UsersManager_ThemeModeMatchBrowser', $response);
        $this->assertStringContainsString('&quot;key&quot;:&quot;auto&quot;', $response);
    }

    public function testThemeModeShouldDefaultToLightForNewUsers()
    {
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
