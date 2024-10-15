<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\tests\Integration;

use PHPMailer\PHPMailer\PHPMailer;
use Piwik\Access;
use Piwik\API\Request;
use Piwik\Auth;
use Piwik\Container\StaticContainer;
use Piwik\Option;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Login\PasswordResetter;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group PasswordResetterTest
 */
class PasswordResetterTest extends IntegrationTestCase
{
    public const NEWPASSWORD = 'newpassword';

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
        Fixture::createWebsite('2010-01-01 05:00:00');
        $this->passwordResetter = new PasswordResetter();
        $this->capturedToken = null;

        Manager::getInstance()->loadPluginTranslations();
    }

    public function testPasswordResetProcessWorksAsExpected()
    {
        $this->passwordResetter->setHashedPasswordForLogin('superUserLogin', $this->capturedToken);

        $this->checkPasswordIs(self::NEWPASSWORD);
    }

    public function testsPasswordResetWorksUpToThreeTimesInAnHour()
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

    public function testPasswordResetNotAllowedMoreThanThreeTimesInAnHour()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You requested too many password resets recently. A new request can be made in one hour. Your administrator can help you if that doesn\'t work.');

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

    public function testPasswordResetNewRequestAllowedAfterAnHour()
    {
        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);

        $optionName = $this->passwordResetter->getPasswordResetInfoOptionName('superUserLogin');
        $data = json_decode(Option::get($optionName), true);

        $data['timestamp'] = time() - 3601;
        $data['requests'] = 3;

        Option::set($optionName, json_encode($data));

        $this->assertTrue($this->passwordResetter->doesResetPasswordHashMatchesPassword(self::NEWPASSWORD, $data['hash']));
        $this->assertFalse($this->passwordResetter->doesResetPasswordHashMatchesPassword('foobar', $data['hash']));

        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);

        $optionName = $this->passwordResetter->getPasswordResetInfoOptionName('superUserLogin');
        $data = json_decode(Option::get($optionName), true);

        $this->assertEquals(1, $data['requests']);
    }

    public function testPasswordResetShouldNotAllowTokenToBeUsedMoreThanOnce()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The token is invalid or has expired.');

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

    public function testPasswordResetShouldNeverGenerateTheSameToken()
    {
        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);
        $this->assertNotEmpty($this->capturedToken);

        sleep(1);

        $oldCapturedToken = $this->capturedToken;
        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);
        $this->assertNotEquals($oldCapturedToken, $this->capturedToken);
    }

    public function testPasswordResetShouldNotAllowOldTokenToBeUsedAfterAnotherResetRequest()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The token is invalid or has expired.');

        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);
        $this->assertNotEmpty($this->capturedToken);

        sleep(1);

        $oldCapturedToken = $this->capturedToken;
        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);
        $this->assertNotEquals($oldCapturedToken, $this->capturedToken);

        $this->passwordResetter->checkValidConfirmPasswordToken('superUserLogin', $oldCapturedToken);
    }

    public function testPasswordResetShouldNotWorkForPendingUser()
    {
        self::expectException(\Exception::class);
        self::expectExceptionMessage('Invalid username or e-mail address.');

        Request::processRequest(
            'UsersManager.inviteUser',
            [
                'userLogin' => 'pendingUser',
                'email' => 'pending@user.io',
                'initialIdSite' => 1,
                'expiryInDays' => 7
            ]
        );

        $model = new Model();
        self::assertTrue($model->isPendingUser('pendingUser'));

        $this->passwordResetter->initiatePasswordResetProcess('pendingUser', self::NEWPASSWORD);
    }

    public function testPasswordResetCanBeCancelled(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The token is invalid or has expired.');

        $this->passwordResetter->initiatePasswordResetProcess(self::$fixture::ADMIN_USER_LOGIN, self::NEWPASSWORD);
        $this->assertNotEmpty($this->capturedToken);

        $this->passwordResetter->checkValidConfirmPasswordToken(self::$fixture::ADMIN_USER_LOGIN, $this->capturedToken);
        $this->passwordResetter->cancelPasswordResetProcess(self::$fixture::ADMIN_USER_LOGIN, $this->capturedToken);

        // password should not have changed and the token should be invalid
        $this->checkPasswordIs(self::$fixture::ADMIN_USER_PASSWORD);
        $this->passwordResetter->checkValidConfirmPasswordToken(self::$fixture::ADMIN_USER_LOGIN, $this->capturedToken);
    }

    public function testPasswordResetCanNotBeCancelledWithAnOutdatedResetToken(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The token is invalid or has expired.');

        $this->passwordResetter->initiatePasswordResetProcess(self::$fixture::ADMIN_USER_LOGIN, self::NEWPASSWORD);
        $this->assertNotEmpty($this->capturedToken);

        $oldCapturedToken = $this->capturedToken;

        $this->passwordResetter->initiatePasswordResetProcess(self::$fixture::ADMIN_USER_LOGIN, self::NEWPASSWORD);
        $this->assertNotEquals($oldCapturedToken, $this->capturedToken);

        $this->passwordResetter->cancelPasswordResetProcess(self::$fixture::ADMIN_USER_LOGIN, $oldCapturedToken);
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
            'observers.global' => \Piwik\DI::add([
                ['Test.Mail.send', \Piwik\DI::value(function (PHPMailer $mail) {
                    $body = $mail->createBody();
                    $body = preg_replace("/=[\r\n]+/", '', $body);
                    preg_match('/resetToken=[\s]*3D([a-zA-Z0-9=\s]+)/', $body, $matches);
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
