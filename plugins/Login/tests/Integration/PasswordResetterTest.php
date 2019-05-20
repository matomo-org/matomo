<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\tests\Integration;


use Piwik\Access;
use Piwik\Auth;
use Piwik\Container\StaticContainer;
use Piwik\Mail;
use Piwik\Plugin\Manager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\Login\PasswordResetter;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Tests\Framework\Fixture;

class PasswordResetterTest extends IntegrationTestCase
{
    const NEWPASSWORD = 'newpassword';

    /**
     * @var Model
     */
    private $userModel;

    /**
     * @var string
     */
    private $capturedToken;

    /**
     * @var PasswordResetter
     */
    private $passwordResetter;

    public function setUp()
    {
        parent::setUp();
        $this->passwordResetter = new PasswordResetter();
        $this->userModel = new Model();
        $this->capturedToken = null;

        Manager::getInstance()->loadPluginTranslations();
    }

    public function test_passwordReset_processWorksAsExpected()
    {
        $user = $this->userModel->getUser('superUserLogin');
        $password = $user['password'];
        $passwordModified = $user['ts_password_modified'];

        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);

        $this->assertNotEmpty($this->capturedToken);

        $user = $this->userModel->getUser('superUserLogin');
        $this->assertEquals($password, $user['password']);
        $this->assertEquals($passwordModified, $user['ts_password_modified']);

        $this->passwordResetter->confirmNewPassword('superUserLogin', $this->capturedToken);

        $this->checkPasswordIs(self::NEWPASSWORD);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Token is invalid or has expired
     */
    public function test_passwordReset_shouldNotAllowTokenToBeUsedMoreThanOnce()
    {
        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);
        $this->assertNotEmpty($this->capturedToken);

        $this->passwordResetter->confirmNewPassword('superUserLogin', $this->capturedToken);
        $this->checkPasswordIs(self::NEWPASSWORD);

        sleep(1);

        $oldCapturedToken = $this->capturedToken;
        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', 'anotherpassword');
        $this->assertNotEquals($oldCapturedToken, $this->capturedToken);

        $this->passwordResetter->confirmNewPassword('superUserLogin', $oldCapturedToken);
    }

    public function test_passwordReset_shouldNeverGenerateTheSameToken()
    {
        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);
        $this->assertNotEmpty($this->capturedToken);

        sleep(1);

        $oldCapturedToken = $this->capturedToken;
        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);
        $this->assertNotEquals($oldCapturedToken, $this->capturedToken);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Token is invalid or has expired
     */
    public function test_passwordReset_shouldNotAllowOldTokenToBeUsedAfterAnotherResetRequest()
    {
        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);
        $this->assertNotEmpty($this->capturedToken);

        sleep(1);

        $oldCapturedToken = $this->capturedToken;
        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);
        $this->assertNotEquals($oldCapturedToken, $this->capturedToken);

        $this->passwordResetter->confirmNewPassword('superUserLogin', $oldCapturedToken);
    }

    /**
     * @param Fixture $fixture
     */
    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
        $fixture->extraTestEnvVars['loadRealTranslations'] = true;
    }

    private function checkPasswordIs($pwd)
    {
        $auth = StaticContainer::get(Auth::class);
        $auth->setLogin('superUserLogin');
        $auth->setPassword($pwd);
        $auth->setTokenAuth(null);
        $auth->setPasswordHash(null);

        $result = Access::getInstance()->reloadAccess($auth);
        $this->assertTrue($result);
    }

    public function provideContainerConfig()
    {
        return [
            'observers.global' => [
                ['Mail.send', function (Mail $mail) {
                    $body = $mail->getBodyHtml(true);
                    preg_match('/resetToken=3D([a-zA-Z0-9=\s]+)<\/p>/', $body, $matches);
                    if (!empty($matches[1])) {
                        $capturedToken = $matches[1];
                        $capturedToken = preg_replace('/=\s*/', '', $capturedToken);
                        $this->capturedToken = $capturedToken;
                    }
                }],
            ],
        ];
    }
}