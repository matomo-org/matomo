<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\UsersManager\tests\Fixtures;

use Piwik\Plugins\UsersManager\API;
use Piwik\Tests\Framework\Fixture;

/**
 * Generates tracker testing data for our APITest
 *
 * This Simple fixture adds one website and tracks one visit with couple pageviews and an ecommerce conversion
 */
class ManyUsers extends Fixture
{
    public $dateTime = '2013-01-23 01:23:45';
    public $idSite = 1;

    public $users = array(
        'login1' => array(),
        'login2' => array('view' => array(1,3,5),   'admin' => array(2,6)),
        'login3' => array('view' => array(),        'admin' => array()), // no access to any site
        'login4' => array('view' => array(6),       'admin' => array()), // only access to one with view
        'login5' => array('view' => array(),        'admin' => array(3)), // only access to one with admin
        'login6' => array('view' => array(),        'admin' => array(6,3)), // access to a couple of sites with admin
        'login7' => array('view' => array(2,1,6,3), 'admin' => array()), // access to a couple of sites with view
        'login8' => array('view' => array(4,7),     'admin' => array(2,5)), // access to a couple of sites with admin and view
    );

    public function setUp()
    {
        $this->setUpWebsite();
        $this->setUpUsers();
    }

    public function tearDown()
    {
        // empty
    }

    private function setUpWebsite()
    {
        for ($i=0; $i < 7; $i++) {
            Fixture::createWebsite('2010-01-01 00:00:00');
        }
    }

    protected function setUpUsers()
    {
        $api = API::getInstance();
        foreach ($this->users as $login => $permissions) {
            $api->addUser($login, 'password', $login . '@example.com');
            foreach ($permissions as $access => $idSites) {
                if (!empty($idSites)) {
                    $api->setUserAccess($login, $access, $idSites);
                }
            }
            $user = $api->getUser($login);
            $this->users[$login]['token'] = $user['token_auth'];
        }

        $api->setSuperUserAccess('login1', true);
    }

}