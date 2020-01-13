<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TwoFactorAuth\tests\Integration;

use Piwik\API\Request;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeDao;
use Piwik\Plugins\TwoFactorAuth\Dao\TwoFaSecretRandomGenerator;
use Piwik\Plugins\TwoFactorAuth\SystemSettings;
use Piwik\Plugins\TwoFactorAuth\TwoFactorAuthentication;
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

    public function setUp()
    {
        parent::setUp();

        foreach ([$this->userWith2Fa, $this->userWithout2Fa] as $user) {
            API::getInstance()->addUser($user, $this->userPassword, $user . '@matomo.org');
            $userUpdater = new UserUpdater();
            $userUpdater->setSuperUserAccessWithoutCurrentPassword($user, 1);
        }

        $this->dao = StaticContainer::get(RecoveryCodeDao::class);
        $this->settings = new SystemSettings();
        $secretGenerator = new TwoFaSecretRandomGenerator();
        $this->twoFa = new TwoFactorAuthentication($this->settings, $this->dao, $secretGenerator);

        $this->dao->createRecoveryCodesForLogin($this->userWith2Fa);
        $this->twoFa->saveSecret($this->userWith2Fa, $this->user2faSecret);
        unset($_GET['authCode']);
    }

    public function tearDown()
    {
        unset($_GET['authCode']);
    }

    public function test_onCreateAppSpecificTokenAuth_canAuthenticateWhenUserNotUsesTwoFA()
    {
        $token = Request::processRequest('UsersManager.createAppSpecificTokenAuth', array(
            'userLogin' => $this->userWithout2Fa,
            'md5Password' => md5($this->userPassword),
            'description' => 'twofa test'
        ));
        $this->assertEquals(32, strlen($token));
    }

    public function test_onCreateAppSpecificTokenAuth_returnsRandomTokenWhenNotAuthenticatedEvenWhen2FAenabled()
    {
        $token = Request::processRequest('UsersManager.createAppSpecificTokenAuth', array(
            'userLogin' => $this->userWith2Fa,
            'md5Password' => md5('invalidPAssword'),
            'description' => 'twofa test'
        ));
        $this->assertEquals(32, strlen($token));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage TwoFactorAuth_MissingAuthCodeAPI
     */
    public function test_onCreateAppSpecificTokenAuth_throwsErrorWhenMissingTokenWhenUsing2FaAndAuthenticatedCorrectly()
    {
        Request::processRequest('UsersManager.createAppSpecificTokenAuth', array(
            'userLogin' => $this->userWith2Fa,
            'md5Password' => md5($this->userPassword),
            'description' => 'twofa test'
        ));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage TwoFactorAuth_InvalidAuthCode
     */
    public function test_onCreateAppSpecificTokenAuth_throwsErrorWhenInvalidTokenWhenUsing2FaAndAuthenticatedCorrectly()
    {
        $_GET['authCode'] = '111222';
        Request::processRequest('UsersManager.createAppSpecificTokenAuth', array(
            'userLogin' => $this->userWith2Fa,
            'md5Password' => md5($this->userPassword),
            'description' => 'twofa test'
        ));
    }

    public function test_onCreateAppSpecificTokenAuth_returnsCorrectTokenWhenProvidingCorrectAuthTokenOnAuthentication()
    {
        $_GET['authCode'] = $this->generateValidAuthCode($this->user2faSecret);
        $token = Request::processRequest('UsersManager.createAppSpecificTokenAuth', array(
            'userLogin' => $this->userWith2Fa,
            'md5Password' => md5($this->userPassword)
        ));
        $this->assertEquals(32, strlen($token));
    }

    public function test_onDeleteUser_RemovesAllRecoveryCodesWhenUsingTwoFa()
    {
        $this->assertNotEmpty($this->dao->getAllRecoveryCodesForLogin($this->userWith2Fa));
        Request::processRequest('UsersManager.deleteUser', array(
            'userLogin' => $this->userWith2Fa
        ));
        $this->assertEmpty($this->dao->getAllRecoveryCodesForLogin($this->userWith2Fa));
    }

    public function test_onDeleteUser_DoesNotFailToAddUserNotUsingTwoFa()
    {
        Request::processRequest('UsersManager.deleteUser', array(
            'userLogin' => $this->userWithout2Fa
        ));
    }

    private function generateValidAuthCode($secret)
    {
        $code = new \TwoFactorAuthenticator();
        return $code->getCode($secret);
    }
}
