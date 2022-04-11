<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use Piwik\API\Request;
use Piwik\Date;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\Login\Login\Controller as LoginController;

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
    }

    public function test_getInviteUser()
    {
        $user = $this->model->getUser($this->pendingUser['login']);
        $this->assertEquals(null, $user['invited_at']);

    }


    public function test_addInviteUserToken()
    {
        $this->model->addTokenAuth($this->pendingUser['login'], $this->token, "Invite Token",
          Date::now()->getDatetime(),
          Date::now()->addDay(7)->getDatetime());

        $user = $this->model->getUserByTokenAuth($this->token);
        $this->assertEquals(null, $user['invited_at']);
    }

}
