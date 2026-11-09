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
use Piwik\Http\BadRequestException;
use Piwik\Exception\NoWebsiteFoundException;
use Piwik\Nonce;
use Piwik\Session\SessionFingerprint;
use Piwik\Session\SessionNamespace;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\Login\Auth as LoginAuth;
use Piwik\Plugins\Login\PasswordVerifier;
use Piwik\Plugins\Login\SecondFactorVerifier;
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
use Piwik\Request\AuthenticationToken;
use Piwik\Tests\Framework\Fixture;
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
        unset(
            $_GET['authCode'],
            $_GET['authCodeNonce'],
            $_GET['module'],
            $_GET['action'],
            $_GET['token_auth'],
            $_GET['force_api_session'],
            $_GET['disableNonce'],
            $_GET['idSite'],
            $_GET[SecondFactorVerifier::CONFIRMATION_TOKEN_PARAMETER],
            $_POST['regenerateNonce']
        );
        // the session is not reset between tests, so leftover fingerprints or setup secrets would leak
        $_SESSION = [];
    }

    public function testUserRequiresSecondFactorConfirmationOnlyWhenUsingTwoFactorAuth()
    {
        $verifier = StaticContainer::get(SecondFactorVerifier::class);

        $this->assertTrue($verifier->isSecondFactorRequired($this->userWith2Fa));
        $this->assertFalse($verifier->isSecondFactorRequired($this->userWithout2Fa));
    }

    public function testVerifySecondFactorAcceptsAuthAppAndNormalisedRecoveryCodes()
    {
        $this->setCurrentUser($this->userWith2Fa);

        $this->assertTrue($this->confirmSecondFactor(
            ['module' => 'TwoFactorAuth', 'action' => 'disableTwoFactorAuth'],
            $this->generateValidAuthCode($this->user2faSecret)
        ));

        // recovery codes are accepted the way users may type them
        $recoveryCode = $this->dao->getAllRecoveryCodesForLogin($this->userWith2Fa)[0];
        $typedRecoveryCode = ' ' . strtolower(substr($recoveryCode, 0, 3) . '-' . substr($recoveryCode, 3)) . ' ';
        $this->assertTrue($this->confirmSecondFactor(
            ['module' => 'TwoFactorAuth', 'action' => 'showRecoveryCodes'],
            $typedRecoveryCode
        ));
        $this->assertNotContains($recoveryCode, $this->dao->getAllRecoveryCodesForLogin($this->userWith2Fa));
    }

    public function testVerifySecondFactorRejectsInvalidCodeAndRecordsFailedAttempt()
    {
        $this->setCurrentUser($this->userWith2Fa);

        $this->assertFalse($this->confirmSecondFactor(['module' => 'TwoFactorAuth', 'action' => 'disableTwoFactorAuth'], '000000'));

        $attempts = $this->bruteForceDetection->getAll();
        $this->assertCount(1, $attempts);
        $this->assertSame($this->userWith2Fa, $attempts[0]['login']);
    }

    public function testDisableTwoFactorAuthRequiresSecondFactorAfterThePassword()
    {
        $_GET['disableNonce'] = $this->setUpVerifiedDisableFlow($this->userWith2Fa);

        $this->expectRedirectToSecondFactorConfirmation(function () {
            StaticContainer::get(Controller::class)->disableTwoFactorAuth();
        });

        $this->assertTrue(TwoFactorAuthentication::isUserUsingTwoFactorAuthentication($this->userWith2Fa));
    }

    public function testDisableTwoFactorAuthDisables2FaOnceSecondFactorIsConfirmed()
    {
        $_GET['disableNonce'] = $this->setUpVerifiedDisableFlow($this->userWith2Fa);
        $this->assertTrue($this->confirmSecondFactor(
            ['module' => 'TwoFactorAuth', 'action' => 'disableTwoFactorAuth', 'disableNonce' => $_GET['disableNonce']],
            $this->generateValidAuthCode($this->user2faSecret)
        ));

        $this->disableTwoFactorAuthExpectingRedirect();

        $this->assertFalse(TwoFactorAuthentication::isUserUsingTwoFactorAuthentication($this->userWith2Fa));
    }

    public function testDisableTwoFactorAuthIgnoresSecondFactorConfirmedForAnotherAction()
    {
        $_GET['disableNonce'] = $this->setUpVerifiedDisableFlow($this->userWith2Fa);
        $this->assertTrue($this->confirmSecondFactor(
            ['module' => 'TwoFactorAuth', 'action' => 'showRecoveryCodes'],
            $this->generateValidAuthCode($this->user2faSecret)
        ));

        $this->expectRedirectToSecondFactorConfirmation(function () {
            StaticContainer::get(Controller::class)->disableTwoFactorAuth();
        });

        $this->assertTrue(TwoFactorAuthentication::isUserUsingTwoFactorAuthentication($this->userWith2Fa));
    }

    public function testDisableTwoFactorAuthThrowsWhenNonceIsInvalidEvenWhenPasswordVerified()
    {
        $this->setUpVerifiedDisableFlow($this->userWith2Fa);
        $_GET['disableNonce'] = 'not-a-valid-nonce';

        try {
            StaticContainer::get(Controller::class)->disableTwoFactorAuth();
            $this->fail('Expected a security check exception to be thrown for the invalid nonce');
        } catch (\Exception $e) {
            $this->assertStringContainsString('General_ExceptionSecurityCheckFailed', $e->getMessage());
        }

        // 2fa must remain enabled when the nonce is invalid
        $this->assertTrue(TwoFactorAuthentication::isUserUsingTwoFactorAuthentication($this->userWith2Fa));
    }

    public function testDisableTwoFactorAuthRejectsInvalidNonceBeforeAskingForThePassword()
    {
        $this->setCurrentUser($this->userWith2Fa);
        $this->setTwoFactorAuthVerified($this->userWith2Fa);

        $_GET['disableNonce'] = 'not-a-valid-nonce';

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('General_ExceptionSecurityCheckFailed');

        StaticContainer::get(Controller::class)->disableTwoFactorAuth();
    }

    public function testShowRecoveryCodesRequiresSecondFactorAfterThePassword()
    {
        $this->setUpVerifiedPassword($this->userWith2Fa, ['module' => 'TwoFactorAuth', 'action' => 'showRecoveryCodes']);

        $this->expectRedirectToSecondFactorConfirmation(function () {
            StaticContainer::get(Controller::class)->showRecoveryCodes();
        });
    }

    public function testShowRecoveryCodesReportsAnExpiredRegenerateNonceOnceConfirmedAgain()
    {
        $this->setUpVerifiedPassword($this->userWith2Fa, ['module' => 'TwoFactorAuth', 'action' => 'showRecoveryCodes']);
        $_GET['idSite'] = $this->createWebsiteAsSuperUser();
        $_POST['regenerateNonce'] = 'expired-nonce';

        $message = '';
        try {
            StaticContainer::get(Controller::class)->showRecoveryCodes();
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $this->assertMatchesRegularExpression('/secondFactorRequest=([0-9a-f]{32})/', $message);
        preg_match('/secondFactorRequest=([0-9a-f]{32})/', $message, $matches);

        $url = StaticContainer::get(SecondFactorVerifier::class)->confirmSecondFactor(
            $matches[1],
            $this->generateValidAuthCode($this->user2faSecret)
        );
        $this->assertStringNotContainsString('regenerateError', (string) $url);

        // the confirmed view, as the redirect back opens it
        unset($_POST['regenerateNonce']);
        parse_str((string) parse_url((string) $url, PHP_URL_QUERY), $query);
        $_GET[SecondFactorVerifier::CONFIRMATION_TOKEN_PARAMETER] = $query[SecondFactorVerifier::CONFIRMATION_TOKEN_PARAMETER];

        $html = StaticContainer::get(Controller::class)->showRecoveryCodes();

        $this->assertStringContainsString('regenerate-error="true"', $html);
    }

    public function testSetupTwoFactorAuthRequiresSecondFactorToReplaceTheConfiguredDevice()
    {
        $this->setUpVerifiedPassword($this->userWith2Fa, ['module' => 'TwoFactorAuth', 'action' => 'setupTwoFactorAuth']);

        // the setup page needs a website for its layout; the test users are super users
        $_GET['idSite'] = $this->createWebsiteAsSuperUser();

        $this->expectRedirectToSecondFactorConfirmation(function () {
            StaticContainer::get(Controller::class)->setupTwoFactorAuth();
        });

        // no new secret was generated for the setup
        $this->assertEmpty((new SessionNamespace('TwoFactorAuthenticator'))->secret);
    }

    public function testSetupTwoFactorAuthRequiresSecondFactorForEveryViewWhenReplacingTheConfiguredDevice()
    {
        $this->setUpVerifiedPassword($this->userWith2Fa, ['module' => 'TwoFactorAuth', 'action' => 'setupTwoFactorAuth']);
        $_GET['idSite'] = $this->createWebsiteAsSuperUser();

        // a secret generated for an earlier, confirmed view
        $this->setReplacementSecret($this->twoFa->generateSecret());

        $this->expectRedirectToSecondFactorConfirmation(function () {
            StaticContainer::get(Controller::class)->setupTwoFactorAuth();
        });
    }

    public function testSetupTwoFactorAuthReplacesTheDeviceWithACodeSubmittedFromAConfirmedView()
    {
        $this->setUpVerifiedPassword($this->userWith2Fa, ['module' => 'TwoFactorAuth', 'action' => 'setupTwoFactorAuth']);
        $_GET['idSite'] = $this->createWebsiteAsSuperUser();

        $newSecret = $this->twoFa->generateSecret();
        $this->setReplacementSecret($newSecret);
        $_GET['authCode'] = $this->generateValidAuthCode($newSecret);
        $_GET['authCodeNonce'] = Nonce::getNonce(Controller::AUTH_CODE_NONCE);

        StaticContainer::get(Controller::class)->setupTwoFactorAuth();

        $user = (new Model())->getUser($this->userWith2Fa);
        $this->assertSame($newSecret, $user['twofactor_secret']);
    }

    public function testSetupTwoFactorAuthReplacesTheDeviceThroughTheWholeConfirmationFlow()
    {
        $this->setUpVerifiedPassword($this->userWith2Fa, ['module' => 'TwoFactorAuth', 'action' => 'setupTwoFactorAuth']);
        $_GET['idSite'] = $this->createWebsiteAsSuperUser();

        // opening the setup page asks for the configured device first
        $message = '';
        try {
            StaticContainer::get(Controller::class)->setupTwoFactorAuth();
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        $this->assertMatchesRegularExpression('/secondFactorRequest=([0-9a-f]{32})/', $message);
        preg_match('/secondFactorRequest=([0-9a-f]{32})/', $message, $matches);

        // confirming with it leads back to the setup page, which shows the new secret
        $url = StaticContainer::get(SecondFactorVerifier::class)->confirmSecondFactor(
            $matches[1],
            $this->generateValidAuthCode($this->user2faSecret)
        );
        parse_str((string) parse_url((string) $url, PHP_URL_QUERY), $query);
        $_GET[SecondFactorVerifier::CONFIRMATION_TOKEN_PARAMETER] = $query[SecondFactorVerifier::CONFIRMATION_TOKEN_PARAMETER];

        $html = StaticContainer::get(Controller::class)->setupTwoFactorAuth();

        $newSecret = (new SessionNamespace('TwoFactorAuthenticator'))->secret;
        $this->assertNotEmpty($newSecret);
        $this->assertNotSame($this->user2faSecret, $newSecret);
        $this->assertStringContainsString($newSecret, $html);

        // the form on that page submits a code for the new device, with the used token still in the URL
        $_GET['authCode'] = $this->generateValidAuthCode($newSecret);
        $_GET['authCodeNonce'] = Nonce::getNonce(Controller::AUTH_CODE_NONCE);

        StaticContainer::get(Controller::class)->setupTwoFactorAuth();

        $user = (new Model())->getUser($this->userWith2Fa);
        $this->assertSame($newSecret, $user['twofactor_secret']);
    }

    public function testSetupTwoFactorAuthShowsAWrongCodeForTheNewDeviceWithoutAnotherConfirmation()
    {
        $this->setUpVerifiedPassword($this->userWith2Fa, ['module' => 'TwoFactorAuth', 'action' => 'setupTwoFactorAuth']);
        $_GET['idSite'] = $this->createWebsiteAsSuperUser();

        $this->setReplacementSecret($this->twoFa->generateSecret());
        $_GET['authCode'] = '000000';
        $_GET['authCodeNonce'] = Nonce::getNonce(Controller::AUTH_CODE_NONCE);

        $html = StaticContainer::get(Controller::class)->setupTwoFactorAuth();

        $this->assertStringContainsString('TwoFactorAuth_WrongAuthCodeTryAgain', $html);
        $user = (new Model())->getUser($this->userWith2Fa);
        $this->assertSame($this->user2faSecret, $user['twofactor_secret']);
    }

    public function testSetupTwoFactorAuthDoesNotReplaceTheDeviceWithASecretCreatedWithoutConfirmation()
    {
        $this->setUpVerifiedPassword($this->userWith2Fa, ['module' => 'TwoFactorAuth', 'action' => 'setupTwoFactorAuth']);
        $_GET['idSite'] = $this->createWebsiteAsSuperUser();

        // eg created while the user did not use 2FA yet
        $newSecret = $this->twoFa->generateSecret();
        $session = new SessionNamespace('TwoFactorAuthenticator');
        $session->secret = $newSecret;
        $session->secretLogin = $this->userWith2Fa;
        $_GET['authCode'] = $this->generateValidAuthCode($newSecret);
        $_GET['authCodeNonce'] = Nonce::getNonce(Controller::AUTH_CODE_NONCE);

        $this->expectRedirectToSecondFactorConfirmation(function () {
            StaticContainer::get(Controller::class)->setupTwoFactorAuth();
        });

        $user = (new Model())->getUser($this->userWith2Fa);
        $this->assertSame($this->user2faSecret, $user['twofactor_secret']);
    }

    public function testShowRecoveryCodesDoesNotReportAnEarlierRegenerateErrorAfterRegenerating()
    {
        $this->setUpVerifiedPassword($this->userWith2Fa, ['module' => 'TwoFactorAuth', 'action' => 'showRecoveryCodes']);
        $_GET['idSite'] = $this->createWebsiteAsSuperUser();
        $session = new SessionNamespace('TwoFactorAuthenticator');
        $session->regenerateFailed = $this->userWith2Fa;
        $_POST['regenerateNonce'] = Nonce::getNonce(Controller::REGENERATE_CODES_2FA_NONCE);

        $html = StaticContainer::get(Controller::class)->showRecoveryCodes();

        $this->assertStringContainsString('regenerate-success="true"', $html);
        $this->assertStringNotContainsString('regenerate-error="true"', $html);
    }

    public function testSetupTwoFactorAuthDoesNotReuseASecretGeneratedForAnotherUser()
    {
        $this->setUpVerifiedPassword($this->userWith2Fa, ['module' => 'TwoFactorAuth', 'action' => 'setupTwoFactorAuth']);
        $_GET['idSite'] = $this->createWebsiteAsSuperUser();

        $session = new SessionNamespace('TwoFactorAuthenticator');
        $session->secret = $this->twoFa->generateSecret();
        $session->secretLogin = $this->otherUserWith2Fa;

        $this->expectRedirectToSecondFactorConfirmation(function () {
            StaticContainer::get(Controller::class)->setupTwoFactorAuth();
        });
    }

    /**
     * Puts a secret into the setup session as a confirmed view of replacing the device does.
     */
    private function setReplacementSecret(string $secret): void
    {
        $session = new SessionNamespace('TwoFactorAuthenticator');
        $session->secret = $secret;
        $session->secretLogin = $this->userWith2Fa;
        $session->secretConfirmed = true;
    }

    /**
     * Creates a website the page layout can use and grants the current user super user access, which the test
     * users have.
     */
    private function createWebsiteAsSuperUser(): int
    {
        $idSite = Access::doAsSuperUser(function () {
            return Fixture::createWebsite('2024-01-01 00:00:00');
        });
        Access::getInstance()->setSuperUserAccess(true);

        return $idSite;
    }

    private function disableTwoFactorAuthExpectingRedirect(): void
    {
        // the test users are super users, so the notification email can look up the current user
        Access::getInstance()->setSuperUserAccess(true);

        try {
            StaticContainer::get(Controller::class)->disableTwoFactorAuth();
            $this->fail('Expected the action to end with a redirect');
        } catch (NoWebsiteFoundException $e) {
            // outside a web request, redirectToIndex() swallows the redirect and ends with this exception
        }
    }

    private function expectRedirectToSecondFactorConfirmation(callable $action): void
    {
        try {
            $action();
            $this->fail('Expected a redirect to the second factor confirmation');
        } catch (\Exception $e) {
            // redirects become an exception outside of a web request
            $this->assertStringContainsString('Matomo would redirect you to this URL', $e->getMessage());
            $this->assertStringContainsString('action=confirmSecondFactor', $e->getMessage());
        }
    }

    /**
     * @param array<string, string> $params
     */
    private function confirmSecondFactor(array $params, string $code): bool
    {
        $verifier = new SecondFactorVerifier();
        $url = $verifier->confirmSecondFactor($verifier->requestConfirmation($params), $code);

        if ($url === null) {
            return false;
        }

        // as the redirect back to the action does
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $_GET[SecondFactorVerifier::CONFIRMATION_TOKEN_PARAMETER] = $query[SecondFactorVerifier::CONFIRMATION_TOKEN_PARAMETER];

        return true;
    }

    private function setTwoFactorAuthVerified(string $login): void
    {
        $fingerprint = new SessionFingerprint();
        $fingerprint->initialize($login, $login . '-token');
        $fingerprint->setTwoFactorAuthenticationVerified($login);
    }

    /**
     * Puts the session into the state of a user who verified 2FA on login and recently confirmed the password for
     * the given action.
     *
     * @param array<string, string> $params
     */
    private function setUpVerifiedPassword(string $login, array $params): void
    {
        $this->setCurrentUser($login);
        $this->setTwoFactorAuthVerified($login);

        $verifier = new PasswordVerifier();
        $verifier->setDisableRedirect();
        $verifier->requirePasswordVerifiedRecently($params);
        $verifier->setPasswordVerifiedCorrectly($login);
    }

    /**
     * Like {@link setUpVerifiedPassword()} for disabling 2FA. Returns a valid disable nonce.
     */
    private function setUpVerifiedDisableFlow(string $login): string
    {
        $this->setUpVerifiedPassword($login, ['module' => 'TwoFactorAuth', 'action' => 'disableTwoFactorAuth']);

        return Nonce::getNonce(Controller::DISABLE_2FA_NONCE);
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
        $session->secretLogin = $this->userWithout2Fa;
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

    public function testOnLoginSetupTwoFactorAuthRedirectsToTheDashboardWhenUserAlreadyUsesTwoFactorAuth()
    {
        $this->setTwoFaRequired(true);
        $this->setCurrentUser($this->userWith2Fa);

        // as after completing the setup, which verifies the session
        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($this->userWith2Fa, 'pending-session-token');
        $sessionFingerprint->setTwoFactorAuthenticationVerified($this->userWith2Fa);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Matomo would redirect you to this URL: index.php');

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

    public function testOnLoginSetupTwoFactorAuthKeepsExistingSecretWhenUserAlreadyUsesTwoFactorAuth()
    {
        $this->setTwoFaRequired(true);
        $this->setCurrentUser($this->userWith2Fa);

        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($this->userWith2Fa, 'pending-session-token');

        // simulate a submitted setup form holding a valid code for a freshly generated secret
        $replacementSecret = $this->twoFa->generateSecret();
        $session = new SessionNamespace('TwoFactorAuthenticator');
        $session->secret = $replacementSecret;
        $session->secretLogin = $this->userWith2Fa;
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
        $this->assertNotNull($exception, 'The setup should have redirected');
        $this->assertStringContainsString('Matomo would redirect you to this URL: index.php', $exception->getMessage());
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

    public function testOnRequestDispatchRequiresTwoFactorAuthWhenTokenDidNotAuthenticateTheRequest()
    {
        // the session authenticated the request and kept its own token, so the supplied token is not
        // the one the current access was granted with
        $this->setCurrentUser($this->otherUserWith2Fa, 'pending-session-token');
        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($this->userWith2Fa, 'pending-session-token');

        $this->setTokenAuthRequest('a-different-token', true);

        $module = 'Widgetize';
        $action = 'iframe';
        $parameters = [];

        $plugin = new TwoFactorAuth();
        $plugin->onRequestDispatch($module, $action, $parameters);

        $this->assertSame(\Piwik\Piwik::getLoginPluginName(), $module);
        $this->assertSame('login', $action);
    }

    public function testOnRequestDispatchSkipsTwoFactorAuthWhenAuthenticatedByTokenOnly()
    {
        // the supplied token is the one the current access was granted with, which is needed eg for
        // rendering exported widgets authenticated by token
        $this->setCurrentUser($this->otherUserWith2Fa, 'a-token-that-authenticated');
        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($this->userWith2Fa, 'pending-session-token');

        $this->setTokenAuthRequest('a-token-that-authenticated', false);

        $module = 'Widgetize';
        $action = 'iframe';
        $parameters = [];

        $plugin = new TwoFactorAuth();
        $plugin->onRequestDispatch($module, $action, $parameters);

        $this->assertSame('Widgetize', $module);
        $this->assertSame('iframe', $action);
    }

    public function testOnRequestDispatchSkipsTwoFactorAuthWhenTokenAuthenticatedRequestDespiteForcedSessionAuth()
    {
        // `force_api_session=1` only asks for session authentication - when that does not happen the
        // token still authenticates the request, and such a request stays exempt from 2fa
        $this->setCurrentUser($this->otherUserWith2Fa, 'a-token-that-authenticated');
        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($this->userWith2Fa, 'pending-session-token');

        $this->setTokenAuthRequest('a-token-that-authenticated', true);

        $module = 'Widgetize';
        $action = 'iframe';
        $parameters = [];

        $plugin = new TwoFactorAuth();
        $plugin->onRequestDispatch($module, $action, $parameters);

        $this->assertSame('Widgetize', $module);
        $this->assertSame('iframe', $action);
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

    /**
     * Puts the request and the auth object into the state they have after a token was supplied and
     * `Piwik\Plugins\Login\Login::apiRequestAuthenticate()` reloaded the auth for it.
     */
    private function setTokenAuthRequest(string $tokenAuth, bool $forceApiSession): void
    {
        $_GET['token_auth'] = $tokenAuth;

        if ($forceApiSession) {
            $_GET['force_api_session'] = '1';
        }

        StaticContainer::getContainer()->set(AuthenticationToken::class, new AuthenticationToken());

        $auth = new LoginAuth();
        $auth->setLogin(null);
        $auth->setTokenAuth($tokenAuth);
        StaticContainer::getContainer()->set('Piwik\Auth', $auth);
    }

    private function setCurrentUser(string $login, ?string $tokenAuth = null): void
    {
        $auth = $this->getMockBuilder(Auth::class)
                    ->onlyMethods(['getName', 'setTokenAuth', 'getTokenAuthSecret', 'getLogin', 'setLogin', 'setPassword', 'setPasswordHash', 'authenticate'])
                    ->getMock();
        $auth->method('authenticate')
            ->willReturn(new AuthResult(AuthResult::SUCCESS, $login, $tokenAuth ?? $login . '-token'));

        Access::getInstance()->setSuperUserAccess(false);
        Access::getInstance()->reloadAccess($auth);
    }
}
