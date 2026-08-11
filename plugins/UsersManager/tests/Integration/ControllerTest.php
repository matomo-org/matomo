<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use Piwik\Common;
use Piwik\Config;
use Piwik\Exception\NoWebsiteFoundException;
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
use Piwik\Settings\Storage\UserScopedSettingsAccessManager;
use Piwik\Container\StaticContainer;

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
    /**
     * @var PasswordVerifier
     */
    private $passwordVerify;
    /**
     * @var Model
     */
    private $userModel;
    private $post;
    private $get;
    private $request;
    private $session;
    private $enableUsersAdmin;
    private $superUser;
    private $identity;
    private $superUserLogin;

    public function setUp(): void
    {
        parent::setUp();

        $this->passwordVerify = new PasswordVerifier();
        $this->userModel = new Model();
        $this->controller = new Controller(
            $translator = new Translator(new DevelopmentLoader(new JsonFileLoader())),
            $this->passwordVerify,
            $this->userModel,
            $passwordStrength = new PasswordStrength(true)
        );
        $this->post = $_POST;
        $this->get = $_GET;
        $this->request = $_REQUEST;
        $this->session = $_SESSION ?? null;
        $this->enableUsersAdmin = Config::getInstance()->General['enable_users_admin'];
        $this->superUser = FakeAccess::$superUser;
        $this->identity = FakeAccess::$identity;
        $this->superUserLogin = FakeAccess::$superUserLogin;

        FakeAccess::$superUser = true;
        $this->userModel->deleteUser(self::CURRENT_USER_LOGIN);
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

        // leave $_SESSION undefined for later tests if it was not defined before
        if (isset($this->session)) {
            $_SESSION = $this->session;
        } else {
            unset($_SESSION);
        }

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

        return $idSite;
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
        ];
        $_POST = [
            'themeMode' => 'invalid',
            'defaultReport' => '1',
            'defaultDate' => 'today',
            'language' => 'en',
            'timeformat' => '0',
        ];

        $response = $this->controller->recordUserSettings();

        $this->assertStringContainsString('Invalid theme mode', $response);
        $this->assertSame(ThemeStyles::LIGHT_MODE, (new UserPreferences())->getThemeMode());
    }

    public function testRecordUserSettingsReadsSettingsFromPost()
    {
        Config::getInstance()->General['enable_users_admin'] = 0;

        // settings are read from the post body, not the query string
        $_GET = [
            'format' => 'json',
            'themeMode' => 'invalid',
        ];
        $_POST = [
            'themeMode' => ThemeStyles::DARK_MODE,
            'defaultReport' => '1',
            'defaultDate' => 'today',
            'language' => 'en',
            'timeformat' => '0',
        ];

        $this->controller->recordUserSettings();

        $this->assertSame(ThemeStyles::DARK_MODE, (new UserPreferences())->getThemeMode());
    }

    public function testRecordUserSettingsReadsEmailFromPost()
    {
        // the email is only processed while the users admin is enabled
        Config::getInstance()->General['enable_users_admin'] = 1;
        $idSite = $this->createSiteWithUser();

        // the email is read from the post body, not the query string
        $_GET = [
            'format' => 'json',
            'email' => 'from-the-query-string@example.com',
        ];
        $_POST = [
            'email' => self::CURRENT_USER_EMAIL,
            'themeMode' => ThemeStyles::LIGHT_MODE,
            'defaultReport' => (string) $idSite,
            'defaultDate' => 'today',
            'language' => 'en',
            'timeformat' => '0',
        ];

        $response = $this->controller->recordUserSettings();

        $this->assertStringContainsString('"result":"success"', $response);
        $this->assertSame(
            self::CURRENT_USER_EMAIL,
            $this->userModel->getUser(self::CURRENT_USER_LOGIN)['email']
        );
    }

    public function testRecordUserSettingsRejectsEmptyDefaultReport()
    {
        Config::getInstance()->General['enable_users_admin'] = 0;

        $_GET = ['format' => 'json'];
        $_POST = [
            'themeMode' => ThemeStyles::LIGHT_MODE,
            'defaultReport' => '',
            'defaultDate' => 'today',
            'language' => 'en',
            'timeformat' => '0',
        ];

        $response = $this->controller->recordUserSettings();

        // an empty default report would leave the user settings page unable to render
        $this->assertStringContainsString('Invalid default report', $response);
        $this->assertFalse($this->getStoredPreference(UsersManagerAPI::PREFERENCE_DEFAULT_REPORT));
    }

    public function testRecordUserSettingsRejectsDefaultReportForSiteWithoutViewAccess()
    {
        FakeAccess::$superUser = false;
        FakeAccess::$idSitesView = [];

        $_GET = ['format' => 'json'];
        $_POST = [
            'themeMode' => ThemeStyles::LIGHT_MODE,
            'defaultReport' => '1',
            'defaultDate' => 'today',
            'language' => 'en',
            'timeformat' => '0',
        ];

        $response = $this->controller->recordUserSettings();

        $this->assertStringContainsString('Invalid default report', $response);
        $this->assertFalse($this->getStoredPreference(UsersManagerAPI::PREFERENCE_DEFAULT_REPORT));
    }

    public function testRecordUserSettingsRejectsInvalidDefaultDate()
    {
        $idSite = $this->createSiteWithUser();
        Config::getInstance()->General['enable_users_admin'] = 0;

        $_GET = ['format' => 'json'];
        $_POST = [
            'themeMode' => ThemeStyles::LIGHT_MODE,
            'defaultReport' => (string) $idSite,
            // survives getDefaultDateWithoutValidation() verbatim and would end up in menu urls
            'defaultDate' => 'last7&module=CoreAdminHome',
            'language' => 'en',
            'timeformat' => '0',
        ];

        $response = $this->controller->recordUserSettings();

        $this->assertStringContainsString('Invalid default date', $response);
        $this->assertFalse($this->getStoredPreference(UsersManagerAPI::PREFERENCE_DEFAULT_REPORT_DATE));
    }

    public function testRecordAnonymousUserSettingsReadsSettingsFromPost()
    {
        $idSite = $this->addSiteAnonymousCanView();

        // settings are read from the post body, not the query string
        $_GET = [
            'format' => 'json',
            'anonymousDefaultReport' => 'MultiSites',
            'anonymousDefaultDate' => 'week',
        ];
        $_POST = [
            'anonymousDefaultReport' => (string) $idSite,
            'anonymousDefaultDate' => 'today',
        ];

        $this->controller->recordAnonymousUserSettings();

        $storedReport = UsersManagerAPI::getInstance()->getUserPreference(
            UsersManagerAPI::PREFERENCE_DEFAULT_REPORT,
            'anonymous'
        );
        $storedDate = UsersManagerAPI::getInstance()->getUserPreference(
            UsersManagerAPI::PREFERENCE_DEFAULT_REPORT_DATE,
            'anonymous'
        );

        $this->assertSame((string) $idSite, (string) $storedReport);
        $this->assertSame('today', (string) $storedDate);
    }

    public function testRecordAnonymousUserSettingsRejectsSiteOnlyTheCurrentUserCanView()
    {
        $this->userModel->addUser('anonymous', '', 'anonymous@example.com', Date::now()->getDatetime());

        // the site is viewable by the super user recording the settings, but not by anonymous
        $idSite = $this->createSiteWithUser();

        $_GET = ['format' => 'json'];
        $_POST = [
            'anonymousDefaultReport' => (string) $idSite,
            'anonymousDefaultDate' => 'today',
        ];

        $response = $this->controller->recordAnonymousUserSettings();

        $this->assertStringContainsString('Invalid default report', $response);
        $this->assertFalse($this->getStoredPreference(UsersManagerAPI::PREFERENCE_DEFAULT_REPORT, 'anonymous'));
    }

    public function testRecordAnonymousUserSettingsRejectsInvalidDefaultDate()
    {
        $idSite = $this->addSiteAnonymousCanView();

        $_GET = ['format' => 'json'];
        $_POST = [
            'anonymousDefaultReport' => (string) $idSite,
            'anonymousDefaultDate' => 'last7&module=CoreAdminHome',
        ];

        $response = $this->controller->recordAnonymousUserSettings();

        $this->assertStringContainsString('Invalid default date', $response);
        $this->assertFalse(
            $this->getStoredPreference(UsersManagerAPI::PREFERENCE_DEFAULT_REPORT_DATE, 'anonymous')
        );
    }

    public function testRecordPasswordChangeReadsPasswordFromPost()
    {
        // password is read from the post body, not the query string
        $this->setupPostStateWithPassword('Password111!');
        $_GET['password'] = 'weak';
        $_GET['passwordBis'] = 'weak';

        // expect test to get past strength check and fail when checking existing password
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ConfirmWithReAuthentication');
        $this->controller->recordPasswordChange();
    }

    public function testDeleteTokenRequiresRecentPasswordVerification()
    {
        // don't redirect to the password confirmation, so the failed verification throws
        $this->passwordVerify->setDisableRedirect();

        $_POST = [
            'idtokenauth' => '1',
            'nonce' => Nonce::getNonce(Controller::NONCE_DELETE_AUTH_TOKEN),
        ];
        $_GET = [];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Not allowed');
        $this->controller->deleteToken();
    }

    public function testDeleteTokenDeletesTokenAfterPasswordConfirmationRedirect()
    {
        $idTokenAuth = $this->addTokenForCurrentUser('token submitted for deletion');

        $_POST = [
            'idtokenauth' => (string) $idTokenAuth,
            'nonce' => Nonce::getNonce(Controller::NONCE_DELETE_AUTH_TOKEN),
        ];
        $_GET = ['module' => 'UsersManager', 'action' => 'deleteToken'];

        // the password has not been confirmed yet, so the action redirects to the confirmation form
        $redirectUrl = $this->captureRedirect(function () {
            $this->controller->deleteToken();
        });
        $this->assertStringContainsString('action=confirmPassword', $redirectUrl);

        // confirming the password redirects back to the action with the stored parameters
        $redirectUrl = $this->captureRedirect(function () {
            $this->passwordVerify->setPasswordVerifiedCorrectly(self::CURRENT_USER_LOGIN);
        });
        $this->assertStringContainsString('action=deleteToken', $redirectUrl);
        $this->assertStringContainsString('idtokenauth=' . $idTokenAuth, $redirectUrl);

        // that redirect is a GET request, so the parameters arrive without a post body
        parse_str((string) parse_url($redirectUrl, PHP_URL_QUERY), $_GET);
        $_POST = [];

        $this->callDeleteTokenIgnoringFinalRedirect();

        $this->assertSame([], $this->getTokenIdsForCurrentUser());
    }

    public function testDeleteTokenPrefersPostedIdTokenAuth()
    {
        $idTokenAuthInPost = $this->addTokenForCurrentUser('token named in the post body');
        $idTokenAuthInQuery = $this->addTokenForCurrentUser('token named in the query string');

        $this->markPasswordAsVerified();

        $_POST = [
            'idtokenauth' => (string) $idTokenAuthInPost,
            'nonce' => Nonce::getNonce(Controller::NONCE_DELETE_AUTH_TOKEN),
        ];
        $_GET = ['idtokenauth' => (string) $idTokenAuthInQuery];

        $this->callDeleteTokenIgnoringFinalRedirect();

        $this->assertSame([(string) $idTokenAuthInQuery], $this->getTokenIdsForCurrentUser());
    }

    public function testDeleteTokenDeletesAllTokens()
    {
        $this->addTokenForCurrentUser('first token');
        $this->addTokenForCurrentUser('second token');

        $this->markPasswordAsVerified();

        // what the "delete all tokens" form submits
        $_POST = [
            'idtokenauth' => 'all',
            'nonce' => Nonce::getNonce(Controller::NONCE_DELETE_AUTH_TOKEN),
        ];
        $_GET = [];

        $this->callDeleteTokenIgnoringFinalRedirect();

        $this->assertSame([], $this->getTokenIdsForCurrentUser());
    }

    public function testDeleteTokenDeletesNothingWhenPostedIdTokenAuthIsEmpty()
    {
        $idTokenAuthInQuery = $this->addTokenForCurrentUser('token named in the query string');

        // an empty posted value is a value, so the query string is not consulted for it
        $_POST = ['idtokenauth' => ''];
        $_GET = ['idtokenauth' => (string) $idTokenAuthInQuery];

        $this->callDeleteTokenIgnoringFinalRedirect();

        $this->assertSame([(string) $idTokenAuthInQuery], $this->getTokenIdsForCurrentUser());
    }

    public function testDeleteTokenRejectsInvalidIdTokenAuth()
    {
        $_POST = ['idtokenauth' => '1&2'];
        $_GET = [];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid idtokenauth');
        $this->controller->deleteToken();
    }

    public function testUserSettingsShouldExposeMatchBrowserThemeModeOption()
    {
        $this->createSiteWithUser();
        $response = $this->controller->userSettings();

        $this->assertStringContainsString('theme-mode="&quot;light&quot;"', $response);
        $this->assertStringContainsString('UsersManager_ThemeModeMatchBrowser', $response);
        $this->assertStringContainsString('&quot;key&quot;:&quot;auto&quot;', $response);
    }

    public function testUserSettingsShouldRepairDeletedStoredDefaultReport()
    {
        $deletedSiteId = $this->createSiteWithUser();
        $fallbackSiteId = SitesManagerAPI::getInstance()->addSite(
            'Fallback site',
            ['https://fallback.example.test']
        );
        UsersManagerAPI::getInstance()->setUserAccess(
            self::CURRENT_USER_LOGIN,
            'view',
            [$deletedSiteId, $fallbackSiteId]
        );

        SitesManagerAPI::getInstance()->deleteSite($deletedSiteId);

        $response = $this->controller->userSettings();

        $storedDefaultReport = StaticContainer::get(UserScopedSettingsAccessManager::class)->get(
            'UsersManager',
            self::CURRENT_USER_LOGIN,
            UsersManagerAPI::PREFERENCE_DEFAULT_REPORT,
            false
        );

        $this->assertSame((string) $fallbackSiteId, (string) $storedDefaultReport);
        $this->assertStringContainsString('Fallback site', $response);
    }

    public function testThemeModeShouldDefaultToLightForNewUsers()
    {
        $this->assertSame(ThemeStyles::LIGHT_MODE, (new UserPreferences())->getThemeMode());
    }

    private function addSiteAnonymousCanView(): int
    {
        $this->userModel->addUser('anonymous', '', 'anonymous@example.com', Date::now()->getDatetime());

        $idSite = SitesManagerAPI::getInstance()->addSite(
            'Anonymous site',
            ['https://anonymous.example.test']
        );
        UsersManagerAPI::getInstance()->setUserAccess('anonymous', 'view', [$idSite]);

        return $idSite;
    }

    /**
     * @return mixed the stored value, or false when nothing was stored
     */
    private function getStoredPreference(string $preferenceName, string $userLogin = self::CURRENT_USER_LOGIN)
    {
        return StaticContainer::get(UserScopedSettingsAccessManager::class)->get(
            'UsersManager',
            $userLogin,
            $preferenceName,
            false
        );
    }

    private function addTokenForCurrentUser(string $description): string
    {
        return (string) $this->userModel->addTokenAuth(
            self::CURRENT_USER_LOGIN,
            'token' . Common::generateUniqId(),
            $description,
            Date::now()->getDatetime()
        );
    }

    private function getTokenIdsForCurrentUser(): array
    {
        $tokens = $this->userModel->getAllNonSystemTokensForLogin(self::CURRENT_USER_LOGIN);

        return array_map('strval', array_column($tokens, 'idusertokenauth'));
    }

    private function markPasswordAsVerified(): void
    {
        // the password only counts as verified while a verification is pending, so the redirect has
        // to be initiated first
        $this->passwordVerify->setDisableRedirect();
        $this->passwordVerify->requirePasswordVerifiedRecently([
            'module' => 'UsersManager',
            'action' => 'deleteToken',
        ]);
        $this->passwordVerify->setPasswordVerifiedCorrectly(self::CURRENT_USER_LOGIN);
    }

    /**
     * Runs a callable that is expected to redirect and returns the url it redirected to. Redirects
     * throw instead of sending a header when running on the command line.
     */
    private function captureRedirect(callable $callable): string
    {
        unset(Common::$headersSentInTests['Location']);

        try {
            $callable();
        } catch (\Exception $e) {
            $this->assertStringContainsString('would redirect you to this URL', $e->getMessage());

            return trim(Common::$headersSentInTests['Location'] ?? '');
        }

        $this->fail('the call was expected to redirect');
    }

    private function callDeleteTokenIgnoringFinalRedirect(): void
    {
        // the action ends by redirecting to the user security page, which cannot be followed here as
        // no site exists to build the url from
        try {
            $this->controller->deleteToken();
            $this->fail('deleteToken() was expected to end in a redirect');
        } catch (NoWebsiteFoundException $e) {
            // expected
        }
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
