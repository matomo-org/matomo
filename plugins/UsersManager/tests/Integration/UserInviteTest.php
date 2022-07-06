<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use Piwik\Date;
use Piwik\Http;
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
        $this->model->addUser($this->pendingUser['login'], '', $this->pendingUser['email'], $this->dateTime, 1);
    }

    public function test_getInviteUser()
    {
        $user = $this->model->getUser($this->pendingUser['login']);
        $this->assertEquals('pending', $user['invite_status']);

    }


    public function test_addInviteUserToken()
    {
        $this->model->addTokenAuth($this->pendingUser['login'], $this->token, "Invite Token",
          Date::now()->getDatetime(),
          Date::now()->addDay(7)->getDatetime());

        $response = Http::sendHttpRequest(Fixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php?module=Login&action=acceptInvitation&token=' . $this->token,
          10);

        $this->assertStringContainsString('Accept invitation', $response, 'error on accept invitation');
    }

}
