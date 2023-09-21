<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use PHPMailer\PHPMailer\PHPMailer;
use Piwik\API\Request;
use Piwik\Date;
use Piwik\EventDispatcher;
use Piwik\Http;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Plugins\UsersManager\Tasks;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group UsersManager
 * @group UserInviteTests
 * @group UserInvite
 * @group Plugins
 */
class UserInviteTest extends IntegrationTestCase
{
    /**
     * @var Model
     */
    private $model;

    protected $pendingUser = [
        'login' => '000pendingUser3',
        'email' => 'pendinguser3light@example.com'
    ];
    protected $capturedToken = null;


    public function setUp(): void
    {
        parent::setUp();
        Fixture::createSuperUser();
        Fixture::createWebsite('2010-01-01 05:00:00');
        $this->model = new Model();
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);

        $fixture->extraTestEnvVars['loadRealTranslations'] = true;
    }

    public function testCopyLink()
    {
        Request::processRequest(
            'UsersManager.inviteUser',
            [
                'userLogin' => $this->pendingUser['login'],
                'email' => $this->pendingUser['email'],
                'initialIdSite' => 1,
                'expiryInDays' => 7
            ]
        );

        $link = Request::processRequest(
            'UsersManager.generateInviteLink',
            [
                'userLogin' => $this->pendingUser['login'],
                'expiryInDays' => 7
            ]
        );

        $response = Http::sendHttpRequest(
            $link,
            10
        );

        $this->assertStringContainsString('Password', $response, 'error on accept invite page');
    }

    public function testInviteUser()
    {
        Request::processRequest(
            'UsersManager.inviteUser',
            [
                'userLogin' => $this->pendingUser['login'],
                'email' => $this->pendingUser['email'],
                'initialIdSite' => 1,
                'expiryInDays' => 7
            ]
        );

        $user = $this->model->getUser($this->pendingUser['login']);

        // check token in database matches token in email
        self::assertEquals($user['invite_token'], $this->model->hashTokenAuth($this->capturedToken));

        $response = Http::sendHttpRequest(
            Fixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php?module=Login&action=acceptInvitation&token=' . $this->capturedToken,
            10
        );

        $this->assertStringContainsString('Password', $response, 'error on accept invite page');

        $response = Http::sendHttpRequest(
            Fixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php?module=Login&action=declineInvitation&token=' . $this->capturedToken,
            10
        );

        $this->assertStringContainsString('Decline this invitation', $response, 'error on decline invite page');

        // move date after expire time, but before deletion time
        Date::$now = Date::today()->addDay(8)->getTimestamp();

        $eventWasFired = false;

        EventDispatcher::getInstance()->addObserver('UsersManager.deleteUser', function ($userLogin) use (&$eventWasFired) {
            self::assertEquals($this->pendingUser['login'], $userLogin);
            $eventWasFired = true;
        });

        $tasks = new Tasks(new Model(), API::getInstance());
        $tasks->cleanUpExpiredInvites();

        // Task should not have removed the user yet, as expiry date time is not 3 days ago
        self::assertIsArray($this->model->getUser($this->pendingUser['login']));

        self::assertFalse($eventWasFired);

        // move date after expire  and deletion time
        Date::$now = Date::today()->addDay(3)->getTimestamp();

        $tasks->cleanUpExpiredInvites();

        // Task should have removed the user now
        self::assertEmpty($this->model->getUser($this->pendingUser['login']));

        self::assertTrue($eventWasFired);
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
            'observers.global' => \Piwik\DI::add([
                ['Test.Mail.send', \Piwik\DI::value(function (PHPMailer $mail) {
                    $body = $mail->createBody();
                    $body = preg_replace("/=[\r\n]+/", '', $body);
                    preg_match('/&token=[\s]*3D([a-zA-Z0-9=\s]+)"/', $body, $matches);
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
