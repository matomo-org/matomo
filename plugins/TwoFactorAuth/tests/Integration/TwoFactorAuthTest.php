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
use Piwik\Session\SessionFingerprint;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeDao;
use Piwik\Plugins\TwoFactorAuth\Dao\TwoFaSecretRandomGenerator;
use Piwik\Plugins\TwoFactorAuth\SystemSettings;
use Piwik\Plugins\TwoFactorAuth\TwoFactorAuthentication;
use Piwik\Plugins\TwoFactorAuth\Controller;
use Piwik\Plugins\TwoFactorAuth\TwoFactorAuth;
use Piwik\Plugins\TwoFactorAuth\Validator;
use Piwik\Plugins\UsersManager\API;
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
        unset($_GET['authCode']);
    }

    public function tearDown(): void
    {
        unset($_GET['authCode']);
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

    public function testOnDeleteUserRemovesAllRecoveryCodesWhenUsingTwoFa()
    {
        $this->assertNotEmpty($this->dao->getAllRecoveryCodesForLogin($this->userWith2Fa));
        Request::processRequest('UsersManager.deleteUser', array(
            'userLogin' => $this->userWith2Fa,
        ));
        $this->assertEmpty($this->dao->getAllRecoveryCodesForLogin($this->userWith2Fa));
    }

    public function testOnDeleteUserDoesNotFailToDeleteUserNotUsingTwoFa()
    {
        $this->expectNotToPerformAssertions();
        Request::processRequest('UsersManager.deleteUser', array(
            'userLogin' => $this->userWithout2Fa,
        ));
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
