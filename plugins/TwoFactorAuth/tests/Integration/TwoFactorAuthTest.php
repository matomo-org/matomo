<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TwoFactorAuth\tests\Integration;

use Piwik\Access;
use Piwik\API\Request;
use Piwik\Auth;
use Piwik\AuthResult;
use Piwik\Nonce;
use Piwik\Session\SessionFingerprint;
use Piwik\Session\SessionNamespace;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\Login\Security\BruteForceDetection;
use Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeDao;
use Piwik\Plugins\TwoFactorAuth\Dao\TwoFaSecretRandomGenerator;
use Piwik\Plugins\TwoFactorAuth\SystemSettings;
use Piwik\Plugins\TwoFactorAuth\TwoFactorAuthentication;
use Piwik\Plugins\TwoFactorAuth\Controller;
use Piwik\Plugins\TwoFactorAuth\TwoFactorAuth;
use Piwik\Plugins\TwoFactorAuth\Validator;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Plugins\UsersManager\UserUpdater;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group TwoFactorAuth
 * @group Plugins
 */
class TwoFactorAuthTest extends IntegrationTestCase
{
    /**
     * @var RecoveryCodeDao
     */
    private $dao;

    /**
     * @var SystemSettings
     */
    private $settings;

    /**
     * @var TwoFactorAuthentication
     */
    private $twoFa;

    /**
     * @var BruteForceDetection
     */
    private $bruteForceDetection;

    private $userWith2Fa = 'myloginWith';
    private $userWithout2Fa = 'myloginWithout';
    private $userPassword = '123abcDk3_l3';
    private $user2faSecret = '123456';

    private $otherUserWith2Fa = 'myotherloginwith';
    private $otherUser2faSecret = '654321';

    public function setUp(): void
    {
        parent::setUp();

        foreach ([$this->userWith2Fa, $this->userWithout2Fa, $this->otherUserWith2Fa] as $user) {
            API::getInstance()->addUser($user, $this->userPassword, $user . '@matomo.org');
            $userUpdater = new UserUpdater();
            $userUpdater->setSuperUserAccessWithoutCurrentPassword($user, 1);
        }

        $this->dao = StaticContainer::get(RecoveryCodeDao::class);
        $this->settings = new SystemSettings();
        $secretGenerator = new TwoFaSecretRandomGenerator();
        $this->twoFa = new TwoFactorAuthentication($this->settings, $this->dao, $secretGenerator);

        $this->dao->createRecoveryCodesForLogin($this->userWith2Fa);
        $this->dao->createRecoveryCodesForLogin($this->otherUserWith2Fa);
        $this->twoFa->saveSecret($this->userWith2Fa, $this->user2faSecret);
        $this->twoFa->saveSecret($this->otherUserWith2Fa, $this->otherUser2faSecret);

        $this->bruteForceDetection = StaticContainer::get(BruteForceDetection::class);
        $this->bruteForceDetection->deleteAll();

        unset($_GET['authCode']);

        // default to an unauthenticated request; tests that need a specific user set it explicitly
        $this->setCurrentUser('anonymous');
    }

    public function tearDown(): void
    {
        $this->bruteForceDetection->deleteAll();
        unset($_GET['authCode'], $_GET['authCodeNonce'], $_GET['module'], $_GET['action']);
        // the session is not reset between tests, so leftover fingerprints or setup secrets would leak
        $_SESSION = [];
    }

    public function testLoginTwoFactorAuthRequiresFreshLoginWhenCurrentUserDoesNotMatchPendingSessionUser()
    {
        $this->setCurrentUser($this->otherUserWith2Fa);

        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($this->userWith2Fa, 'pending-session-token');

        $result = StaticContainer::get(Controller::class)->loginTwoFactorAuth();

        $this->assertStringContainsString('form_login', $result);
        $this->assertNull($sessionFingerprint->getUser());
        $this->assertTrue(Access::getInstance()->wasSessionExpired());
    }

    public function testLoginTwoFactorAuthRendersForPendingSessionUser()
    {
        $this->setCurrentUser($this->userWith2Fa);

        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($this->userWith2Fa, 'pending-session-token');

        $result = StaticContainer::get(Controller::class)->loginTwoFactorAuth();
        $this->assertStringContainsString('form_authcode', $result);
    }

    public function testOnLoginSetupTwoFactorAuthRendersWhenEnforcedAndUserNotEnrolled()
    {
        $this->setTwoFaRequired(true);
        $this->setCurrentUser($this->userWithout2Fa);

        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($this->userWithout2Fa, 'pending-session-token');

        $result = StaticContainer::get(Controller::class)->onLoginSetupTwoFactorAuth();

        // standalone="true" is only rendered by the login-only template
        $this->assertStringContainsString('standalone="true"', $result);
        $this->assertStringContainsString('auth-code-nonce', $result);
    }

    public function testOnLoginSetupTwoFactorAuthRendersWhenTwoFactorAuthWasResetDuringAVerifiedSession()
    {
        $this->setTwoFaRequired(true);
        $this->setCurrentUser($this->userWithout2Fa);

        // a superuser may reset a user's 2fa while that user has an already verified session, which leaves
        // them unenrolled but still verified - they have to be able to enroll again
        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($this->userWithout2Fa, 'pending-session-token');
        $sessionFingerprint->setTwoFactorAuthenticationVerified($this->userWithout2Fa);

        $result = StaticContainer::get(Controller::class)->onLoginSetupTwoFactorAuth();

        $this->assertStringContainsString('standalone="true"', $result);
    }

    public function testOnLoginSetupTwoFactorAuthEnrollsUserWhenEnforcedAndCodeIsValid()
    {
        $this->setTwoFaRequired(true);
        $this->setCurrentUser($this->userWithout2Fa);

        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($this->userWithout2Fa, 'pending-session-token');

        $this->dao->createRecoveryCodesForLogin($this->userWithout2Fa);

        $newSecret = $this->twoFa->generateSecret();
        $session = new SessionNamespace('TwoFactorAuthenticator');
        $session->secret = $newSecret;
        $_GET['authCode'] = $this->generateValidAuthCode($newSecret);
        $_GET['authCodeNonce'] = Nonce::getNonce(Controller::AUTH_CODE_NONCE);

        try {
            StaticContainer::get(Controller::class)->onLoginSetupTwoFactorAuth();
        } catch (\Exception $e) {
            // the setup redirects once finished, which cannot complete outside of a web request
        }

        $user = (new Model())->getUser($this->userWithout2Fa);
        $this->assertSame($newSecret, $user['twofactor_secret']);
        $this->assertTrue($sessionFingerprint->hasVerifiedTwoFactor());
    }

    public function testOnLoginSetupTwoFactorAuthNotAvailableWhenUserAlreadyUsesTwoFactorAuth()
    {
        $this->setTwoFaRequired(true);
        $this->setCurrentUser($this->userWith2Fa);

        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($this->userWith2Fa, 'pending-session-token');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('not available');

        StaticContainer::get(Controller::class)->onLoginSetupTwoFactorAuth();
    }

    public function testOnLoginSetupTwoFactorAuthNotAvailableWhenTwoFactorAuthIsNotEnforced()
    {
        $this->setCurrentUser($this->userWithout2Fa);

        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($this->userWithout2Fa, 'pending-session-token');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('not available');

        StaticContainer::get(Controller::class)->onLoginSetupTwoFactorAuth();
    }

    public function testOnLoginSetupTwoFactorAuthNotAvailableWhenSessionUserDoesNotMatchCurrentUser()
    {
        $this->setTwoFaRequired(true);
        $this->setCurrentUser($this->userWithout2Fa);

        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($this->otherUserWith2Fa, 'pending-session-token');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('not available');

        StaticContainer::get(Controller::class)->onLoginSetupTwoFactorAuth();
    }

    public function testOnLoginSetupTwoFactorAuthKeepsExistingSecretWhenNotAvailable()
    {
        $this->setTwoFaRequired(true);
        $this->setCurrentUser($this->userWith2Fa);

        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($this->userWith2Fa, 'pending-session-token');

        // simulate a submitted setup form holding a valid code for a freshly generated secret
        $replacementSecret = $this->twoFa->generateSecret();
        $session = new SessionNamespace('TwoFactorAuthenticator');
        $session->secret = $replacementSecret;
        $_GET['authCode'] = $this->generateValidAuthCode($replacementSecret);
        $_GET['authCodeNonce'] = Nonce::getNonce(Controller::AUTH_CODE_NONCE);

        $exception = null;

        try {
            StaticContainer::get(Controller::class)->onLoginSetupTwoFactorAuth();
        } catch (\Exception $e) {
            $exception = $e;
        }

        $user = (new Model())->getUser($this->userWith2Fa);
        $this->assertSame($this->user2faSecret, $user['twofactor_secret']);
        $this->assertNotNull($exception, 'An exception should have been thrown');
        $this->assertSame('not available', $exception->getMessage());
    }

    public function testOnRequestDispatchRequiresFreshLoginWhenDifferentUserIsAuthenticatedDuringPendingSession()
    {
        $this->setCurrentUser($this->otherUserWith2Fa);
        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($this->userWith2Fa, 'pending-session-token');

        $module = 'UsersManager';
        $action = 'index';
        $parameters = [];

        $plugin = new TwoFactorAuth();
        $plugin->onRequestDispatch($module, $action, $parameters);

        $this->assertSame(\Piwik\Piwik::getLoginPluginName(), $module);
        $this->assertSame('login', $action);
        $this->assertNull($sessionFingerprint->getUser());
        $this->assertTrue(Access::getInstance()->wasSessionExpired());
    }

    public function testValidatorDetectsPendingSessionUserMismatch()
    {
        $this->setCurrentUser($this->otherUserWith2Fa);
        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($this->userWith2Fa, 'pending-session-token');

        $validator = StaticContainer::get(Validator::class);

        $this->assertTrue($validator->hasPendingSessionTwoFactorAuthentication());
        $this->assertFalse($validator->isCurrentUserMatchingSessionUser());
    }

    public function testValidatorIgnoresUnverifiedSessionForUserWithoutTwoFactorAuthentication()
    {
        $this->setCurrentUser($this->otherUserWith2Fa);
        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($this->userWithout2Fa, 'plain-session-token');

        $validator = StaticContainer::get(Validator::class);

        $this->assertFalse($validator->hasPendingSessionTwoFactorAuthentication());
        $this->assertFalse($validator->isCurrentUserMatchingSessionUser());
    }

    public function testOnCreateAppSpecificTokenAuthCanAuthenticateWhenUserNotUsesTwoFA()
    {
        $token = Request::processRequest('UsersManager.createAppSpecificTokenAuth', array(
            'userLogin' => $this->userWithout2Fa,
            'passwordConfirmation' => $this->userPassword,
            'description' => 'twofa test',
        ));
        $this->assertEquals(32, strlen($token));
    }

    public function testOnCreateAppSpecificTokenAuthFailsWhenNotAuthenticatedEvenWhen2FAenabled()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_CurrentPasswordNotCorrect');

        Request::processRequest('UsersManager.createAppSpecificTokenAuth', array(
            'userLogin' => $this->userWith2Fa,
            'passwordConfirmation' => 'invalidPAssword',
            'description' => 'twofa test',
        ));
    }

    public function testOnCreateAppSpecificTokenAuthThrowsErrorWhenMissingTokenWhenUsing2FaAndAuthenticatedCorrectly()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('TwoFactorAuth_MissingAuthCodeAPI');

        Request::processRequest('UsersManager.createAppSpecificTokenAuth', array(

            'userLogin' => $this->userWith2Fa,
            'passwordConfirmation' => $this->userPassword,
            'description' => 'twofa test',
        ));
    }

    public function testOnCreateAppSpecificTokenAuthThrowsErrorWhenInvalidTokenWhenUsing2FaAndAuthenticatedCorrectly()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('TwoFactorAuth_InvalidAuthCode');

        $_GET['authCode'] = '111222';
        Request::processRequest('UsersManager.createAppSpecificTokenAuth', array(
            'userLogin' => $this->userWith2Fa,
            'passwordConfirmation' => $this->userPassword,
            'description' => 'twofa test',
        ));
    }

    public function testOnCreateAppSpecificTokenAuthThrowsErrorWhenMissingTokenWhenUsing2FaAndAuthenticatedCorrectlyUsingEmail()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('TwoFactorAuth_MissingAuthCodeAPI');

        Request::processRequest('UsersManager.createAppSpecificTokenAuth', array(
            'userLogin' => $this->userWith2Fa . '@matomo.org',
            'passwordConfirmation' => $this->userPassword,
            'description' => 'twofa test',
        ));
    }

    public function testOnCreateAppSpecificTokenAuthThrowsErrorWhenInvalidTokenWhenUsing2FaAndAuthenticatedCorrectlyUsingEmail()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('TwoFactorAuth_InvalidAuthCode');

        $_GET['authCode'] = '111222';
        Request::processRequest('UsersManager.createAppSpecificTokenAuth', array(
            'userLogin' => $this->userWith2Fa . '@matomo.org',
            'passwordConfirmation' => $this->userPassword,
            'description' => 'twofa test',
        ));
    }

    public function testOnCreateAppSpecificTokenAuthRecordsFailedAttemptWhenInvalidAuthCode()
    {
        $this->assertCount(0, $this->bruteForceDetection->getAll());

        $_GET['authCode'] = '111222';
        try {
            Request::processRequest('UsersManager.createAppSpecificTokenAuth', array(
                'userLogin' => $this->userWith2Fa,
                'passwordConfirmation' => $this->userPassword,
                'description' => 'twofa test',
            ));
            $this->fail('Expected an exception to be thrown for the invalid auth code');
        } catch (\Exception $e) {
            $this->assertStringContainsString('TwoFactorAuth_InvalidAuthCode', $e->getMessage());
        }

        $attempts = $this->bruteForceDetection->getAll();
        $this->assertCount(1, $attempts);
        $this->assertSame($this->userWith2Fa, $attempts[0]['login']);
    }

    public function testOnCreateAppSpecificTokenAuthRecordsFailedAttemptWhenMissingAuthCode()
    {
        $this->assertCount(0, $this->bruteForceDetection->getAll());

        try {
            Request::processRequest('UsersManager.createAppSpecificTokenAuth', array(
                'userLogin' => $this->userWith2Fa,
                'passwordConfirmation' => $this->userPassword,
                'description' => 'twofa test',
            ));
            $this->fail('Expected an exception to be thrown for the missing auth code');
        } catch (\Exception $e) {
            $this->assertStringContainsString('TwoFactorAuth_MissingAuthCodeAPI', $e->getMessage());
        }

        $attempts = $this->bruteForceDetection->getAll();
        $this->assertCount(1, $attempts);
        $this->assertSame($this->userWith2Fa, $attempts[0]['login']);
    }

    public function testOnCreateAppSpecificTokenAuthDoesNotRecordFailedAttemptWhenAuthCodeIsValid()
    {
        $_GET['authCode'] = $this->generateValidAuthCode($this->user2faSecret);
        $token = Request::processRequest('UsersManager.createAppSpecificTokenAuth', array(
            'userLogin' => $this->userWith2Fa,
            'passwordConfirmation' => $this->userPassword,
            'description' => 'twofa test',
        ));

        $this->assertEquals(32, strlen($token));
        $this->assertCount(0, $this->bruteForceDetection->getAll());
    }

    public function testOnCreateAppSpecificTokenAuthReturnsCorrectTokenWhenProvidingCorrectAuthTokenOnAuthenticationUsingEmail()
    {
        $_GET['authCode'] = $this->generateValidAuthCode($this->user2faSecret);
        $token = Request::processRequest('UsersManager.createAppSpecificTokenAuth', array(
            'userLogin' => $this->userWith2Fa . '@matomo.org',
            'passwordConfirmation' => $this->userPassword,
            'description' => 'twofa test',
        ));
        $this->assertEquals(32, strlen($token));
    }

    public function testOnCreateAppSpecificTokenAuthReturnsCorrectTokenWhenProvidingCorrectAuthTokenOnAuthentication()
    {
        $_GET['authCode'] = $this->generateValidAuthCode($this->user2faSecret);
        $token = Request::processRequest('UsersManager.createAppSpecificTokenAuth', array(
            'userLogin' => $this->userWith2Fa,
            'passwordConfirmation' => $this->userPassword,
            'description' => 'twofa test',
        ));
        $this->assertEquals(32, strlen($token));
    }

    public function testOnSuccessfulSessionRecordsFailedAttemptWhenInvalidAuthCodeDuringLogme()
    {
        $_GET['module'] = 'Login';
        $_GET['action'] = 'logme';
        $_GET['authCode'] = '111222';

        $this->assertCount(0, $this->bruteForceDetection->getAll());

        $plugin = new TwoFactorAuth();
        $plugin->onSuccessfulSession($this->userWith2Fa);

        $attempts = $this->bruteForceDetection->getAll();
        $this->assertCount(1, $attempts);
        $this->assertSame($this->userWith2Fa, $attempts[0]['login']);
    }

    public function testOnSuccessfulSessionDoesNotRecordFailedAttemptWhenNoAuthCodeDuringLogme()
    {
        $_GET['module'] = 'Login';
        $_GET['action'] = 'logme';

        $plugin = new TwoFactorAuth();
        $plugin->onSuccessfulSession($this->userWith2Fa);

        $this->assertCount(0, $this->bruteForceDetection->getAll());
    }

    public function testOnDeleteUserRemovesAllRecoveryCodesWhenUsingTwoFa()
    {
        Access::getInstance()->setSuperUserAccess(true);
        $this->assertNotEmpty($this->dao->getAllRecoveryCodesForLogin($this->userWith2Fa));
        Request::processRequest('UsersManager.deleteUser', array(
            'userLogin' => $this->userWith2Fa,
        ));
        $this->assertEmpty($this->dao->getAllRecoveryCodesForLogin($this->userWith2Fa));
    }

    public function testOnDeleteUserDoesNotFailToDeleteUserNotUsingTwoFa()
    {
        Access::getInstance()->setSuperUserAccess(true);
        $this->expectNotToPerformAssertions();
        Request::processRequest('UsersManager.deleteUser', array(
            'userLogin' => $this->userWithout2Fa,
        ));
    }

    private function setTwoFaRequired(bool $isRequired): void
    {
        // must go through the container: resolving it applies the test config decoration, which would
        // otherwise reset the value afterwards
        Access::doAsSuperUser(function () use ($isRequired) {
            StaticContainer::get(SystemSettings::class)->twoFactorAuthRequired->setValue($isRequired ? 1 : 0);
        });
    }

    private function generateValidAuthCode($secret)
    {
        $code = new \TwoFactorAuthenticator();
        return $code->getCode($secret);
    }

    private function setCurrentUser(string $login): void
    {
        $auth = $this->getMockBuilder(Auth::class)
                    ->onlyMethods(['getName', 'setTokenAuth', 'getTokenAuthSecret', 'getLogin', 'setLogin', 'setPassword', 'setPasswordHash', 'authenticate'])
                    ->getMock();
        $auth->method('authenticate')
            ->willReturn(new AuthResult(AuthResult::SUCCESS, $login, $login . '-token'));

        Access::getInstance()->setSuperUserAccess(false);
        Access::getInstance()->reloadAccess($auth);
    }
}
