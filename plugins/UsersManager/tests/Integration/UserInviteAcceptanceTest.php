<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use PHPMailer\PHPMailer\PHPMailer;
use Piwik\Date;
use Piwik\Http;
use Piwik\Piwik;
use Piwik\Plugins\LanguagesManager\Model as LanguagesManagerModel;
use Piwik\Plugins\Login\Controller;
use Piwik\Plugins\UsersManager\Model as UsersManagerModel;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group UsersManager
 * @group UserInviteTests
 * @group UserInviteAcceptanceTest
 * @group Plugins
 */
class UserInviteAcceptanceTest extends IntegrationTestCase
{
    /**
     * @var Controller
     */
    private $controller;

    /**
     * @var array
     */
    private $post;

    /**
     * @var string[]
     */
    private $pendingUser = [
        'login' => '000pendingUser4',
        'email' => 'pendinguser4light@example.com'
    ];

    /**
     * @var string
     */
    private $greeting = '';

    /**
     * @var string
     */
    private $message = '';

    private $invitedUserLanguage = 'de';

    private $invitedByUserLanguage = 'cs';

    public function setUp(): void
    {
        parent::setUp();
        Fixture::createWebsite('2010-01-01 05:00:00');
        Fixture::createSuperUser();
        $this->controller = new Controller();
        $this->post = $_POST;
        $_POST = [];

        \Zend_Session::$_unitTestEnabled = true;
        Fixture::loadAllTranslations();

        $model = new LanguagesManagerModel();
        $model->setLanguageForUser(Fixture::ADMIN_USER_LOGIN, $this->invitedByUserLanguage);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $_POST = $this->post;
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);

        $fixture->extraTestEnvVars['loadRealTranslations'] = true;
    }

    private function generateTestUser(): array
    {
        // generate new user
        $userLogin = $this->pendingUser['login'];
        $userEmail = $this->pendingUser['email'];
        $usersModel = new UsersManagerModel();
        $usersModel->addUser($userLogin, $passwordHash = '', $userEmail, Date::now()->getDatetime());
        $usersModel->updateUserFields($userLogin, ['invited_by' => Fixture::ADMIN_USER_LOGIN]);
        $token = $usersModel->generateRandomInviteToken();
        $usersModel->attachInviteToken($userLogin, $token, $expiryInDays = 1);

        return [$userEmail, $token];
    }

    public function testAcceptingUserInviteSendsEmailToInviterInTheirLanguage()
    {
        [, $token] = $this->generateTestUser();
        $response = Http::sendHttpRequest(
            Fixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php?module=Login&action=acceptInvitation&token=' . $token,
            10,
            $userAgent = null,
            $destinationPath = null,
            $followDepth = 0,
            $this->invitedUserLanguage // force invite acceptance screen to German
        );

        // translate('General_SetPassword') for German is "Passwort setzen"
        $this->assertStringContainsString('Passwort setzen', $response, 'error on accept invite page');

        // simulate completing accept invitation form
        $_POST['token'] = $token;
        $_POST['password'] = 'Password111!';
        $_POST['passwordConfirmation'] = 'Password111!';
        $_POST['email'] = $this->pendingUser['email'];
        $_POST['invitation_form'] = 'Confirm';
        $_POST['conditionCheck'] = true;

        try {
            $this->controller->acceptInvitation();
        } catch (\Exception $e) {
            // browser redirection exception is ok, otherwise re-throw
            if (!str_starts_with($e->getMessage(), 'If you were using a browser, Matomo would redirect you to this URL')) {
                throw $e;
            }
        }

        $this->assertEquals(
            Piwik::translate(
                'General_HelloUser',
                [Fixture::ADMIN_USER_LOGIN],
                $this->invitedByUserLanguage
            ),
            $this->greeting
        );
        $this->assertEquals(
            Piwik::translate(
                'CoreAdminHome_SecurityNotificationUserAcceptInviteBody',
                [$this->pendingUser['login']],
                $this->invitedByUserLanguage
            ),
            $this->message
        );
    }

    public function provideContainerConfig()
    {
        return [
            'observers.global' => \Piwik\DI::add([
                ['Test.Mail.send', \Piwik\DI::value(function (PHPMailer $mail) {
                    $body = $mail->createBody();
                    $body = quoted_printable_decode($body);
                    $body = preg_replace("/=[\r\n]+/", '', $body);
                    preg_match('/<p>(.*?)<\/p>\s*<p>(.*?)<\/p>/', $body, $matches);
                    if (count($matches) === 3) {
                        $this->greeting = $matches[1];
                        $this->message = $matches[2];
                    }
                })],
            ]),
        ];
    }
}
