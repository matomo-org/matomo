<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\tests\Integration;


use PHPMailer\PHPMailer\PHPMailer;
use Piwik\Access;
use Piwik\Auth;
use Piwik\Container\StaticContainer;
use Piwik\Option;
use Piwik\Plugin\Manager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\Login\PasswordResetter;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Tests\Framework\Fixture;

/**
 * @group PasswordResetterTest
 */
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

    public function setUp(): void
    {
        parent::setUp();
        $this->passwordResetter = new PasswordResetter();
        $this->userModel = new Model();
        $this->capturedToken = null;

        Manager::getInstance()->loadPluginTranslations();
    }

    public function test_passwordReset_processWorksAsExpected()
    {
        $this->passwordResetter->setHashedPasswordForLogin('superUserLogin', $this->capturedToken);

        $this->checkPasswordIs(self::NEWPASSWORD);
    }

    public function tests_passwordReset_worksUpToThreeTimesInAnHour()
    {
        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);

        $this->assertNotEmpty($this->capturedToken);

        $token = $this->capturedToken;
        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);
        $this->assertNotEquals($token, $this->capturedToken);

        $token = $this->capturedToken;
        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);
        $this->assertNotEquals($token, $this->capturedToken);
    }

    public function test_passwordReset_notAllowedMoreThanThreeTimesInAnHour()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You have requested too many password resets recently. A new request can be made in one hour. If you have problems resetting your password, please contact your administrator for help.');

        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);

        $this->assertNotEmpty($this->capturedToken);

        $token = $this->capturedToken;
        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);
        $this->assertNotEquals($token, $this->capturedToken);

        $token = $this->capturedToken;
        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);
        $this->assertNotEquals($token, $this->capturedToken);

        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);
    }

    public function test_passwordReset_newRequestAllowedAfterAnHour()
    {
        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);

        $optionName = $this->passwordResetter->getPasswordResetInfoOptionName('superUserLogin');
        $data = json_decode(Option::get($optionName), true);

        $data['timestamp'] = time()-3601;
        $data['requests'] = 3;

        Option::set($optionName, json_encode($data));

        $this->assertTrue($this->passwordResetter->doesResetPasswordHashMatchesPassword(self::NEWPASSWORD, $data['hash']));
        $this->assertFalse($this->passwordResetter->doesResetPasswordHashMatchesPassword('foobar', $data['hash']));

        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);

        $optionName = $this->passwordResetter->getPasswordResetInfoOptionName('superUserLogin');
        $data = json_decode(Option::get($optionName), true);

        $this->assertEquals(1, $data['requests']);
    }

    public function test_passwordReset_shouldNotAllowTokenToBeUsedMoreThanOnce()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token is invalid or has expired');

        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);
        $this->assertNotEmpty($this->capturedToken);

        $this->passwordResetter->checkValidConfirmPasswordToken('superUserLogin', $this->capturedToken);
        $this->checkPasswordIs(self::NEWPASSWORD);

        sleep(1);

        $oldCapturedToken = $this->capturedToken;
        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', 'anotherpassword');
        $this->assertNotEquals($oldCapturedToken, $this->capturedToken);

        $this->passwordResetter->checkValidConfirmPasswordToken('superUserLogin', $oldCapturedToken);
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

    public function test_passwordReset_shouldNotAllowOldTokenToBeUsedAfterAnotherResetRequest()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token is invalid or has expired');

        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);
        $this->assertNotEmpty($this->capturedToken);

        sleep(1);

        $oldCapturedToken = $this->capturedToken;
        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);
        $this->assertNotEquals($oldCapturedToken, $this->capturedToken);

        $this->passwordResetter->checkValidConfirmPasswordToken('superUserLogin', $oldCapturedToken);
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
            'observers.global' => \DI\add([
                ['Test.Mail.send', \DI\value(function (PHPMailer $mail) {
                    $body = $mail->createBody();
                    $body = preg_replace("/=[\r\n]+/", '', $body);
                    preg_match('/resetToken=[\s]*3D([a-zA-Z0-9=\s]+)<\/p>/', $body, $matches);
                    if (!empty($matches[1])) {
                        $capturedToken = $matches[1];
                        $capturedToken = preg_replace('/=\s*/', '', $capturedToken);
                        $this->capturedToken = $capturedToken;
                    }
                })],
            ]),
        ];
    }
}