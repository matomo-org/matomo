<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests;

use Piwik\Access\Role\View;
use Piwik\Access\Role\Write;
use Piwik\Auth\Password;
use Piwik\Common;
use Piwik\Db;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Plugins\UsersManager\UsersManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Access\Role\Admin;


/**
 * @group UsersManager
 * @group APITest
 * @group Plugins
 */
class ModelTest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    /**
     * @var Model
     */
    private $model;

    private $login = 'userLogin';

    public function setUp(): void
    {
        parent::setUp();

        $this->api = API::getInstance();
        $this->model = new Model();

        FakeAccess::clearAccess();
        FakeAccess::$superUser = true;

        Fixture::createWebsite('2014-01-01 00:00:00');
        Fixture::createWebsite('2014-01-01 00:00:00');
        Fixture::createWebsite('2014-01-01 00:00:00');
        $this->api->addUser($this->login, 'password', 'userlogin@password.de');
    }

    public function test_getSitesAccessFromUser_noAccess()
    {
        $this->assertSame(array(), $this->model->getSitesAccessFromUser($this->login));
    }

    public function test_getSitesAccessFromUser_accessOneSite()
    {
        $this->model->addUserAccess($this->login, Write::ID, array(2));
        $this->assertEquals(array(
            array('site' => '2', 'access' => Write::ID)
        ), $this->model->getSitesAccessFromUser($this->login));
    }

    public function test_getSitesAccessFromUser_multipleSites()
    {
        $this->model->addUserAccess($this->login, Write::ID, array(3));
        $this->model->addUserAccess($this->login, Write::ID, array(2));
        $this->model->addUserAccess($this->login, View::ID, array(1));
        $this->assertEquals(array(
            array('site' => '3', 'access' => Write::ID),
            array('site' => '2', 'access' => Write::ID),
            array('site' => '1', 'access' => View::ID),
        ), $this->model->getSitesAccessFromUser($this->login));
    }

    public function test_getSitesAccessFromUser_multipleSitesSomeNoLongerExist()
    {
        $this->model->addUserAccess($this->login, Write::ID, array(3));
        $this->model->addUserAccess($this->login, Write::ID, array(2));
        $this->model->addUserAccess($this->login, View::ID, array(1));
        SitesManagerAPI::getInstance()->deleteSite(2);
        SitesManagerAPI::getInstance()->deleteSite(1);
        $this->assertEquals(array(
            array('site' => '3', 'access' => Write::ID),
        ), $this->model->getSitesAccessFromUser($this->login));
    }

    public function test_getSitesAccessFromUser_siteDeletedManually()
    {
        $this->model->addUserAccess($this->login, Write::ID, array(3));
        $this->model->addUserAccess($this->login, Write::ID, array(2));
        $this->model->addUserAccess($this->login, View::ID, array(1));
        Db::query('DELETE FROM ' . Common::prefixTable('site') . ' where idsite = 1');
        Db::query('DELETE FROM ' . Common::prefixTable('site') . ' where idsite = 2');
        $this->assertEquals(array(
            array('site' => '3', 'access' => Write::ID),
        ), $this->model->getSitesAccessFromUser($this->login));
    }

}
