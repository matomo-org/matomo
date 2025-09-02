<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use Piwik\Date;
use Piwik\Http;
use Piwik\Piwik;
use Piwik\Plugins\LanguagesManager\Model as LanguagesManagerModel;
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
     * @var string[]
     */
    private $pendingUser = [
        'login' => '000pendingUser4',
        'email' => 'pendinguser4light@example.com'
    ];

    private $invitedUserLanguage = 'de';

    private $invitedByUserLanguage = 'cs';

    public function setUp(): void
    {
        parent::setUp();
        Fixture::createWebsite('2010-01-01 05:00:00');
        Fixture::createSuperUser();

        \Zend_Session::$_unitTestEnabled = true;
        Fixture::loadAllTranslations();

        $model = new LanguagesManagerModel();
        $model->setLanguageForUser(Fixture::ADMIN_USER_LOGIN, $this->invitedByUserLanguage);
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
        $acceptInvitationEmailJsonFilePath = PIWIK_INCLUDE_PATH . '/tmp/Login.acceptInvitation.mail.json';
        // ensure we don't have a stored email json file from previous runs
        @unlink($acceptInvitationEmailJsonFilePath);

        // generate invited user and accept invitation from via curl request
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

        // create request post data
        $requestPostData = [
            'token' => $token,
            'password' => 'Password111!',
            'passwordConfirmation' => 'Password111!',
            'email' => $this->pendingUser['email'],
            'invitation_form' => 'Confirm',
            'conditionCheck' => true,
        ];

        // set invited user's password which triggers invite acceptance confirmation email to the inviter
        Http::sendHttpRequestBy(
            'curl',
            Fixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php?module=Login&action=acceptInvitation',
            10,
            $userAgent = null,
            $destinationPath = null,
            $file = null,
            $followDepth = 0,
            $this->invitedUserLanguage, // force invite acceptance screen to German
            $acceptInvalidSslCertificate = false,
            $byteRange = false,
            $getExtendedInfo = false,
            $httpMethod = 'POST',
            $httpUsername = null,
            $httpPassword = null,
            $requestBody = $requestPostData,
            $additionalHeaders = [],
            $forcePost = true
        );

        $acceptInvitationEmail = file_get_contents($acceptInvitationEmailJsonFilePath);
        $this->assertNotEmpty($acceptInvitationEmail, 'Email about user accepting invitation is not empty');

        $acceptInvitationEmail = json_decode($acceptInvitationEmail, true);
        $this->assertIsArray($acceptInvitationEmail, 'JSON email about user accepting invitation decoded correctly');

        [$greeting, $message] = $this->extractContentFromEmailBody($acceptInvitationEmail['contents']);

        $this->assertEquals(
            Piwik::translate(
                'General_HelloUser',
                [Fixture::ADMIN_USER_LOGIN],
                $this->invitedByUserLanguage
            ),
            $greeting
        );
        $this->assertEquals(
            Piwik::translate(
                'CoreAdminHome_SecurityNotificationUserAcceptInviteBody',
                [$this->pendingUser['login']],
                $this->invitedByUserLanguage
            ),
            $message
        );
    }

    private function extractContentFromEmailBody(string $body): array
    {
        $body = preg_replace("/=[\r\n]+/", '', $body);
        preg_match('/<p>(.*?)<\/p>\s*<p>(.*?)<\/p>/', $body, $matches);
        if (count($matches) === 3) {
            return array_slice($matches, 1);
        }

        return ['', ''];
    }
}
