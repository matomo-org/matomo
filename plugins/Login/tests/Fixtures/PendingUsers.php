<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Login\tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Tests\Framework\Fixture;

/**
 * Generates tracker testing data for our APITest
 *
 * This Simple fixture adds one website and tracks one visit with couple pageviews and an ecommerce conversion
 */
class PendingUsers extends Fixture
{

    public $dateTime = '2013-01-23 01:23:45';
    public $idSite = 1;
    public $users = array();


    public $pendingUser = array(
      'login' => '000pendingUser2',
      'email' => 'pendinguser2light@example.com'
    );

    public $token = "13cb9dcef6cc70b02a640cee30dc8ce9";


    public function __construct()
    {

    }

    public function setUp(): void
    {
        $this->setUpWebsite();
        $this->setUpUser();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsite()
    {
        Fixture::createWebsite('2010-01-01 00:00:00', $ecommerce = 0, "example");
    }

    protected function setUpUser()
    {
        $model = new Model();
        $model->addUser($this->pendingUser['login'], '', $this->pendingUser['email'], $this->dateTime,
          false);

        $model->addTokenAuth($this->pendingUser['login'], $this->token, "Invite Token",
          Date::now()->getDatetime(),
          Date::now()->addDay(7)->getDatetime());

    }
}