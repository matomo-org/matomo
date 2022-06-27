<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\Http;
use Piwik\Plugins\UsersManager\Emails\UserInviteEmail;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Tests\Framework\Fixture;
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
    protected $dateTime = '2013-01-23 01:23:45';

    protected $token = "13cb9dcef6cc70b02a640cee30dc8ce9";
    protected $pendingUser = array(
      'login' => '000pendingUser3',
      'email' => 'pendinguser3light@example.com'
    );


    public function setUp(): void
    {
        parent::setUp();
        $this->model = new Model();
        $this->model->addUser($this->pendingUser['login'], '', $this->pendingUser['email'], $this->dateTime);

        $this->model->attachInviteToken($this->pendingUser['login'], $this->token);
    }

    public function test_getInviteUser()
    {
        $user = $this->model->getUser($this->pendingUser['login']);
        $this->assertNotNull($user['invite_token']);
    }


    public function test_inviteUserEmail()
    {
        $token = $this->token;
        $user = $this->model->getUser($this->pendingUser['login']);
        $email = StaticContainer::getContainer()->make(UserInviteEmail::class, array(
          'currentUser' => 'admin',
          'user'        => $user,
          'token'       => $token,
          'expired'      => 7
        ));

        $content = $email->getBodyHtml();

        $this->assertStringContainsString('?module=Login&action=acceptInvitation&token=' . $token, $content,
          'error on email');

        $this->assertStringContainsString('?module=Login&action=declineInvitation&token=' . $token, $content,
          'error on email');
    }

    /**
     * @throws \Exception
     */
    public function test_addInviteUserToken()
    {
        $response = Http::sendHttpRequest(
          Fixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php?module=Login&action=acceptInvitation&token=' . $this->token,
          10
        );

        $this->assertStringContainsString('Accept Invitation', $response, 'error on accept invitation');
    }


    /**
     * @throws \Exception
     */
    public function test_declineInviteUserToken()
    {
        $response = Http::sendHttpRequest(
          Fixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php?module=Login&action=declineInvitation&token=' . $this->token,
          10
        );

        $this->assertStringContainsString('decline this Invitation', $response, 'error on accept invitation');
    }
}
