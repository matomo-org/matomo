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
use Piwik\Date;
use Piwik\DI;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Login\PasswordResetter;
use Piwik\Plugins\Login\PasswordResetUserIsInvalidException;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Plugins\UsersManager\Repository\UserRepository;
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
     * @var string|null
     */
    private $capturedActivationToken;

    /**
     * @var string|null
     */
    private $passwordResetEmailBody;

    /**
     * @var PasswordResetter
     */
    private $passwordResetter;

    /**
     * @var bool
     */
    private $receivedCancelEmail;

    /**
     * @var array
     */
    private $eventCancelledInfo;

    /**
     * @var array
     */
    private $eventConfirmedInfo;

    /**
     * @var array
     */
    private $eventInitiatedInfo;

    public function setUp(): void
    {
        parent::setUp();
        Fixture::createWebsite('2010-01-01 05:00:00');
        $this->passwordResetter = new PasswordResetter();
        $this->capturedToken = null;
        $this->capturedActivationToken = null;
        $this->passwordResetEmailBody = null;
        $this->receivedCancelEmail = false;
        $this->eventCancelledInfo = [];
        $this->eventConfirmedInfo = [];
        $this->eventInitiatedInfo = [];

        Manager::getInstance()->loadPluginTranslations();
    }

    public function testPasswordResetProcessWorksAsExpected()
    {
        $this->passwordResetter->setHashedPasswordForLogin('superUserLogin', $this->capturedToken);
        $this->assertSame(['superUserLogin'], $this->eventConfirmedInfo);

        $this->checkPasswordIs(self::NEWPASSWORD);
    }

    public function testsPasswordResetWorksUpToThreeTimesInAnHour()
    {
        $this->passwordResetter->initiatePasswordResetProcess('superUserLogin', self::NEWPASSWORD);

        $this->assertSame(['superUserLogin'], $this->eventInitiatedInfo);
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

        $this->assertSame($this->eventInitiatedInfo, ['superUserLogin']);
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

        try {
            $this->passwordResetter->checkValidConfirmPasswordToken('superUserLogin', $this->capturedToken);
        } catch (\Exception $e) {
            $this->fail("Expected password reset token '{$this->capturedToken}' to be valid, but it wasn't");
        }

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

    public function testPasswordResetForPendingUserSendsActivationLinkWithoutExtendingExpiry()
    {
        Request::processRequest(
            'UsersManager.inviteUser',
            [
                'userLogin' => 'pendingUser',
                'email' => 'pending@user.io',
                'initialIdSite' => 1,
                'expiryInDays' => 7,
            ]
        );

        $model = new Model();
        self::assertTrue($model->isPendingUser('pendingUser'));
        $userBeforeReset = $model->getUser('pendingUser');

        $this->passwordResetter->initiatePasswordResetProcess('pending@user.io', self::NEWPASSWORD);

        $userAfterReset = $model->getUser('pendingUser');

        self::assertSame($userBeforeReset['invite_expired_at'], $userAfterReset['invite_expired_at']);
        self::assertNotSame($userBeforeReset['invite_token'], $userAfterReset['invite_token']);
        self::assertNotEmpty($this->capturedActivationToken);
        self::assertSame(
            $model->hashTokenAuth($this->capturedActivationToken),
            $userAfterReset['invite_token']
        );
        self::assertNull($userAfterReset['invite_link_token']);
        self::assertStringContainsString('action=3DacceptInvitation', $this->passwordResetEmailBody);
        self::assertStringContainsString('This link will expire in ', $this->passwordResetEmailBody);
        self::assertStringNotContainsString('expire in 24 hours', $this->passwordResetEmailBody);
        self::assertSame(['pendingUser'], $this->eventInitiatedInfo);
    }

    public function testChangingPendingUserEmailInvalidatesRecoveryActivationLink()
    {
        Request::processRequest(
            'UsersManager.inviteUser',
            [
                'userLogin' => 'reassignedPendingUser',
                'email' => 'previous.pending@user.io',
                'initialIdSite' => 1,
                'expiryInDays' => 7,
            ]
        );

        $model = new Model();

        $this->passwordResetter->initiatePasswordResetProcess(
            'previous.pending@user.io',
            self::NEWPASSWORD
        );

        self::assertNotEmpty($this->capturedActivationToken);
        self::assertNotEmpty($model->getUserByInviteToken($this->capturedActivationToken));

        UsersManagerAPI::$UPDATE_USER_REQUIRE_PASSWORD_CONFIRMATION = false;
        UsersManagerAPI::getInstance()->updateUser(
            'reassignedPendingUser',
            false,
            'new.pending@user.io'
        );

        self::assertEmpty($model->getUserByInviteToken($this->capturedActivationToken));
    }

    public function testPasswordResetShouldNotWorkForPendingUserWithExpiredInvitation()
    {
        $this->expectException(PasswordResetUserIsInvalidException::class);
        $this->expectExceptionMessage('Invalid username or e-mail address.');

        Request::processRequest(
            'UsersManager.inviteUser',
            [
                'userLogin' => 'expiredPendingUser',
                'email' => 'expired.pending@user.io',
                'initialIdSite' => 1,
                'expiryInDays' => 7,
            ]
        );

        $model = new Model();
        $model->updateUserFields('expiredPendingUser', [
            'invite_expired_at' => Date::now()->subDay(1)->getDatetime(),
        ]);

        try {
            $this->passwordResetter->initiatePasswordResetProcess('expired.pending@user.io', self::NEWPASSWORD);
        } catch (\Exception $e) {
            self::assertNull($this->passwordResetEmailBody);
            self::assertSame([], $this->eventInitiatedInfo);

            throw $e;
        }
    }

    public function testPendingUserLookupCanBeOverridden()
    {
        Request::processRequest(
            'UsersManager.inviteUser',
            [
                'userLogin' => 'customPendingLookup',
                'email' => 'custom.pending.lookup@user.io',
                'initialIdSite' => 1,
                'expiryInDays' => 7,
            ]
        );

        $passwordResetter = new class extends PasswordResetter {
            protected function getPendingUserInformation(string $loginOrMail): ?array
            {
                return null;
            }
        };

        $this->expectException(PasswordResetUserIsInvalidException::class);

        $passwordResetter->initiatePasswordResetProcess(
            'custom.pending.lookup@user.io',
            self::NEWPASSWORD
        );
    }

    /**
     * @dataProvider provideInvitationInvalidationDuringPasswordReset
     */
    public function testPasswordResetDoesNotSendInvitationLinkIfInvitationBecomesInvalidDuringRequest(
        string $login,
        string $email,
        bool $acceptsInvitation
    ) {
        Request::processRequest(
            'UsersManager.inviteUser',
            [
                'userLogin' => $login,
                'email' => $email,
                'initialIdSite' => 1,
                'expiryInDays' => 7,
            ]
        );

        $model = new Model();
        $repository = StaticContainer::get(UserRepository::class);
        $racingRepository = $this->createMock(UserRepository::class);
        $racingRepository
            ->method('refreshInviteToken')
            ->willReturnCallback(function (
                string $userLogin,
                string $expectedInviteToken,
                string $expectedEmail
            ) use (
                $acceptsInvitation,
                $model,
                $repository
            ) {
                $fields = $acceptsInvitation
                    ? [
                        'invite_token' => null,
                        'invite_link_token' => null,
                        'invite_expired_at' => null,
                    ]
                    : [
                        'invite_expired_at' => Date::now()->subDay(1)->getDatetime(),
                    ];
                $model->updateUserFields($userLogin, $fields);

                return $repository->refreshInviteToken(
                    $userLogin,
                    $expectedInviteToken,
                    $expectedEmail
                );
            });

        StaticContainer::getContainer()->set(UserRepository::class, $racingRepository);

        try {
            $this->passwordResetter->initiatePasswordResetProcess(
                $email,
                self::NEWPASSWORD
            );
        } finally {
            StaticContainer::getContainer()->set(UserRepository::class, $repository);
        }

        $userAfterReset = $model->getUser($login);

        self::assertNull($userAfterReset['invite_link_token']);
        self::assertNull($this->passwordResetEmailBody);
        self::assertSame([], $this->eventInitiatedInfo);
        self::assertFalse(Option::get(
            PasswordResetter::getPasswordResetInfoOptionName($login)
        ));
    }

    public function provideInvitationInvalidationDuringPasswordReset(): array
    {
        return [
            'accepted invitation' => [
                'acceptedDuringReset',
                'accepted.during.reset@user.io',
                true,
            ],
            'expired invitation' => [
                'expiredDuringReset',
                'expired.during.reset@user.io',
                false,
            ],
        ];
    }

    public function testPasswordResetDoesNotSendActivationLinkIfPendingUserEmailChangesDuringRequest()
    {
        Request::processRequest(
            'UsersManager.inviteUser',
            [
                'userLogin' => 'emailChangedDuringReset',
                'email' => 'old.pending@user.io',
                'initialIdSite' => 1,
                'expiryInDays' => 7,
            ]
        );

        $model = new Model();
        $userBeforeReset = $model->getUser('emailChangedDuringReset');
        $repository = StaticContainer::get(UserRepository::class);
        $racingRepository = $this->createMock(UserRepository::class);
        $racingRepository
            ->method('refreshInviteToken')
            ->willReturnCallback(function (
                string $userLogin,
                string $expectedInviteToken,
                string $expectedEmail
            ) use ($repository) {
                UsersManagerAPI::$UPDATE_USER_REQUIRE_PASSWORD_CONFIRMATION = false;
                UsersManagerAPI::getInstance()->updateUser(
                    $userLogin,
                    false,
                    'new.pending@user.io'
                );

                return $repository->refreshInviteToken(
                    $userLogin,
                    $expectedInviteToken,
                    $expectedEmail
                );
            });

        StaticContainer::getContainer()->set(UserRepository::class, $racingRepository);

        try {
            $this->passwordResetter->initiatePasswordResetProcess(
                'old.pending@user.io',
                self::NEWPASSWORD
            );
        } finally {
            StaticContainer::getContainer()->set(UserRepository::class, $repository);
        }

        $userAfterReset = $model->getUser('emailChangedDuringReset');

        self::assertSame('new.pending@user.io', $userAfterReset['email']);
        self::assertNotSame($userBeforeReset['invite_token'], $userAfterReset['invite_token']);
        self::assertNull($userAfterReset['invite_link_token']);
        self::assertNull($this->capturedActivationToken);
        self::assertNull($this->passwordResetEmailBody);
        self::assertSame([], $this->eventInitiatedInfo);
        self::assertFalse(Option::get(
            PasswordResetter::getPasswordResetInfoOptionName('emailChangedDuringReset')
        ));
    }

    public function testPasswordResetShouldNotWorkForInvalidUserAndThrowException()
    {
        $this->expectException(PasswordResetUserIsInvalidException::class);
        $this->expectExceptionMessage('Invalid username or e-mail address.');

        $this->passwordResetter->initiatePasswordResetProcess('invalidUser', 'doesnt_matter');
    }

    public function testPasswordResetCanBeCancelled(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The token is invalid or has expired.');

        $this->passwordResetter->initiatePasswordResetProcess(self::$fixture::ADMIN_USER_LOGIN, self::NEWPASSWORD);
        $this->assertSame(['superUserLogin'], $this->eventInitiatedInfo);
        $this->assertNotEmpty($this->capturedToken);
        $this->assertFalse($this->receivedCancelEmail);

        try {
            $this->passwordResetter->checkValidConfirmPasswordToken(self::$fixture::ADMIN_USER_LOGIN, $this->capturedToken);
        } catch (\Exception $e) {
            $this->fail("Expected password reset token '{$this->capturedToken}' to be valid, but it wasn't");
        }

        $this->passwordResetter->cancelPasswordResetProcess(self::$fixture::ADMIN_USER_LOGIN, $this->capturedToken);
        $this->assertSame(['superUserLogin'], $this->eventCancelledInfo);
        $this->assertTrue($this->receivedCancelEmail);

        // password should not have changed and the token should be invalid
        $this->checkPasswordIs(self::$fixture::ADMIN_USER_PASSWORD);
        $this->passwordResetter->checkValidConfirmPasswordToken(self::$fixture::ADMIN_USER_LOGIN, $this->capturedToken);
    }

    public function testPasswordResetCanNotBeCancelledWithAnOutdatedResetToken(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The token is invalid or has expired.');

        $this->passwordResetter->initiatePasswordResetProcess(self::$fixture::ADMIN_USER_LOGIN, self::NEWPASSWORD);
        $this->assertSame(['superUserLogin'], $this->eventInitiatedInfo);
        $this->assertNotEmpty($this->capturedToken);
        $this->assertFalse($this->receivedCancelEmail);

        $oldCapturedToken = $this->capturedToken;

        $this->passwordResetter->initiatePasswordResetProcess(self::$fixture::ADMIN_USER_LOGIN, self::NEWPASSWORD);
        $this->assertNotEquals($oldCapturedToken, $this->capturedToken);
        $this->assertFalse($this->receivedCancelEmail);

        try {
            $this->passwordResetter->cancelPasswordResetProcess(self::$fixture::ADMIN_USER_LOGIN, $oldCapturedToken);
        } catch (\Exception $e) {
            // event should not have been dispatched
            $this->assertSame([], $this->eventCancelledInfo);

            throw $e;
        }
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
            'observers.global' => DI::add([
                ['Test.Mail.send', DI::value(function (PHPMailer $mail) {
                    $subjectReset = Piwik::translate('Login_PasswordResetEmailSubject');
                    $subjectCancel = Piwik::translate('Login_PasswordResetCancelEmailSubject');

                    if ($subjectReset === $mail->Subject) {
                        $body = $mail->createBody();
                        $body = preg_replace("/=[\r\n]+/", '', $body);
                        $this->passwordResetEmailBody = $body;

                        preg_match('/resetToken=[\s]*3D([a-zA-Z0-9=\s]+)/', $body, $matches);

                        if (!empty($matches[1])) {
                            $capturedToken = $matches[1];
                            $capturedToken = preg_replace('/=\s*/', '', $capturedToken);
                            $this->capturedToken = $capturedToken;
                        }

                        preg_match('/action=3DacceptInvitation&amp;token=3D([a-zA-Z0-9=\s]+)"/', $body, $matches);

                        if (!empty($matches[1])) {
                            $capturedToken = $matches[1];
                            $capturedToken = preg_replace('/=\s*/', '', $capturedToken);
                            $this->capturedActivationToken = $capturedToken;
                        }
                    }

                    if ($subjectCancel === $mail->Subject) {
                        $this->receivedCancelEmail = true;
                    }
                })],
                ['Login.resetPassword.cancelled', DI::value(function (...$eventData) {
                    $this->eventCancelledInfo = $eventData;
                })],
                ['Login.resetPassword.confirmed', DI::value(function (...$eventData) {
                    $this->eventConfirmedInfo = $eventData;
                })],
                ['Login.resetPassword.initiated', DI::value(function (...$eventData) {
                    $this->eventInitiatedInfo = $eventData;
                })],
            ]),
        ];
    }
}
