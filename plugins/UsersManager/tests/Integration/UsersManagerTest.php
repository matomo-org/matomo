<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use Piwik\Access;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Exception;


/**
 * @group UsersManagerTest
 * @group UsersManager
 * @group Plugins
 */
class UsersManagerTest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    /**
     * @var Model
     */
    private $model;

    public function setUp()
    {
        parent::setUp();

        \Piwik\Plugin\Manager::getInstance()->loadPlugin('UsersManager');
        \Piwik\Plugin\Manager::getInstance()->installLoadedPlugins();

        // setup the access layer
        FakeAccess::setIdSitesView(array(1, 2));
        FakeAccess::setIdSitesAdmin(array(3, 4));

        //finally we set the user as a Super User by default
        FakeAccess::$superUser = true;
        FakeAccess::$superUserLogin = 'superusertest';

        $this->api   = API::getInstance();
        $this->model = new Model();
    }

    private function _flatten($sitesAccess)
    {
        $result = array();

        foreach ($sitesAccess as $siteAccess) {
            $result[$siteAccess['site']] = $siteAccess['access'];
        }

        return $result;
    }

    private function _checkUserHasNotChanged($user, $newPassword, $newEmail = null, $newAlias = null)
    {
        if (is_null($newEmail)) {
            $newEmail = $user['email'];
        }
        if (is_null($newAlias)) {
            $newAlias = $user['alias'];
        }
        $userAfter = $this->api->getUser($user["login"]);
        unset($userAfter['date_registered']);

        // we now compute what the token auth should be, it should always be a hash of the login and the current password
        // if the password has changed then the token_auth has changed!
        $user['token_auth'] = $this->api->getTokenAuth($user["login"], md5($newPassword));
        $user['password']   = md5($newPassword);
        $user['email']      = $newEmail;
        $user['alias']      = $newAlias;
        $user['superuser_access'] = 0;
        $this->assertEquals($user, $userAfter);
    }

    /**
     * bad password => exception#
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionInvalidPassword
     */
    public function testUpdateUserBadpasswd()
    {
        $login = "login";
        $user  = array('login'    => $login,
                       'password' => "geqgeagae",
                       'email'    => "test@test.com",
                       'alias'    => "alias");

        $this->api->addUser($user['login'], $user['password'], $user['email'], $user['alias']);

        try {
            $this->api->updateUser($login, "pas");
        } catch (Exception $expected) {
            $this->_checkUserHasNotChanged($user, $user['password']);
            throw $expected;
        }
    }

    /**
     * Dataprovider
     */
    public function getAddUserInvalidLoginData()
    {
        return array(
            array(9, "password", "email@email.com", "alias"), // wrong login / integer => exception
            array("gegag'ggea'", "password", "email@email.com", "alias"), // wrong login / too short => exception
            array("gegag11gge&", "password", "email@email.com", "alias"), // wrong login / too long => exception
            array("geg'ag11gge@", "password", "email@email.com", "alias"), // wrong login / bad characters => exception
        );
    }

    /**
     * @dataProvider getAddUserInvalidLoginData
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionInvalidLogin
     */
    public function testAddUserWrongLogin($userLogin, $password, $email, $alias)
    {
        $this->api->addUser($userLogin, $password, $email, $alias);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionLoginExists
     */
    public function testAddUserExistingLogin()
    {
        $this->api->addUser("test", "password", "email@email.com", "alias");
        $this->api->addUser("test", "password2", "em2ail@email.com", "al2ias");
    }

    /**
     * @see https://github.com/piwik/piwik/issues/8548
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionLoginExists
     */
    public function testAddUserExistingLoginCaseInsensitive()
    {
        $this->api->addUser("test", "password", "email@email.com", "alias");
        $this->api->addUser("TeSt", "password2", "em2ail@email.com", "al2ias");
    }

    /**
     * Dataprovider for wrong password tests
     */
    public function getWrongPasswordTestData()
    {
        return array(
            array("geggeqgeqag", "pas", "email@email.com", "alias"), // too short -> exception
            array("geggeqgeqag", "", "email@email.com", "alias"), // empty -> exception
        );
    }

    /**
     * @dataProvider getWrongPasswordTestData
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionInvalidPassword
     */
    public function testAddUserWrongPassword($userLogin, $password, $email, $alias)
    {
        $this->api->addUser($userLogin, $password, $email, $alias);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage mail
     */
    public function testAddUserWrongEmail()
    {
        $this->api->addUser('geggeqgeqag', 'geqgeagae', "ema il@email.com", 'alias');
    }

    /**
     * empty alias => use login
     */
    public function testAddUserEmptyAlias()
    {
        $login = "geggeqgeqag";
        $this->api->addUser($login, "geqgeagae", "mgeagi@geq.com", "");
        $user = $this->api->getUser($login);
        $this->assertEquals($login, $user['alias']);
        $this->assertEquals($login, $user['login']);
    }

    /**
     * long password => should work
     * empty alias => use login
     */
    public function testAddUserLongPassword()
    {
        $login = "geggeqgeqag";
        $this->api->addUser($login, "geqgeagaegeqgeagaegeqgeagaegeqgeagaegeqgeagaegeqgeagaegeqgeagaegeqgeagaegeqgeagaegeqgeagaegeqgeagaeg", "mgeagi@geq.com", "");
        $user = $this->api->getUser($login);
        $this->assertEquals($login, $user['alias']);
        $this->assertEquals($login, $user['login']);
    }

    /**
     * no alias => use login
     */
    public function testAddUserNoAliasSpecified()
    {
        $login = "geggeqg455eqag";
        $this->api->addUser($login, "geqgeagae", "mgeagi@geq.com");
        $user = $this->api->getUser($login);
        $this->assertEquals($login, $user['alias']);
        $this->assertEquals($login, $user['login']);
    }

    /**
     * normal test case
     */
    public function testAddUser()
    {
        $login = "geggeq55eqag";
        $password = "mypassword";
        $email = "mgeag4544i@geq.com";
        $alias = "her is my alias )(&|\" '£%*(&%+))";

        $time = time();
        $this->api->addUser($login, $password, $email, $alias);
        $user = $this->api->getUser($login);

        // check that the date registered is correct
        $this->assertTrue($time <= strtotime($user['date_registered']) && strtotime($user['date_registered']) <= time(),
            "the date_registered " . strtotime($user['date_registered']) . " is different from the time() " . time());
        $this->assertTrue($user['date_registered'] <= time());

        // check that token is 32 chars
        $this->assertEquals(32, strlen($user['password']));

        // that the password has been md5
        $this->assertEquals(md5($login . md5($password)), $user['token_auth']);

        // check that all fields are the same
        $this->assertEquals($login, $user['login']);
        $this->assertEquals(md5($password), $user['password']);
        $this->assertEquals($email, $user['email']);
        $this->assertEquals($alias, $user['alias']);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionDeleteDoesNotExist
     */
    public function testSeleteUserDoesntExist()
    {
        $this->api->addUser("geggeqgeqag", "geqgeagae", "test@test.com", "alias");
        $this->api->deleteUser("geggeqggnew");
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionDeleteDoesNotExist
     */
    public function testDeleteUserEmptyUser()
    {
        $this->api->deleteUser("");
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionDeleteDoesNotExist
     */
    public function testDeleteUserNullUser()
    {
        $this->api->deleteUser(null);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionDeleteOnlyUserWithSuperUserAccess
     */
    public function testDeleteUser_ShouldFail_InCaseTheUserIsTheOnlyRemainingSuperUser()
    {
        //add user and set some rights
        $this->api->addUser("regularuser", "geqgeagae1", "test1@test.com", "alias1");
        $this->api->addUser("superuser", "geqgeagae2", "test2@test.com", "alias2");
        $this->api->setSuperUserAccess('superuser', true);

        // delete the user
        $this->api->deleteUser("superuser");
    }

    /**
     * normal case, user deleted
     */
    public function testDeleteUser()
    {
        $this->addSites(3);

        //add user and set some rights
        $this->api->addUser("geggeqgeqag", "geqgeagae", "test@test.com", "alias");
        $this->api->setUserAccess("geggeqgeqag", "view", array(1, 2));
        $this->api->setUserAccess("geggeqgeqag", "admin", array(1, 3));

        // check rights are set
        $this->assertNotEquals(array(), $this->api->getSitesAccessFromUser("geggeqgeqag"));

        // delete the user
        $this->api->deleteUser("geggeqgeqag");

        // try to get it, it should raise an exception
        try {
            $this->api->getUser("geggeqgeqag");
            $this->fail("Exception not raised.");
        } catch (Exception $expected) {
            $this->assertRegExp("(UsersManager_ExceptionUserDoesNotExist)", $expected->getMessage());
        }

        // add the same user
        $this->api->addUser("geggeqgeqag", "geqgeagae", "test@test.com", "alias");

        //checks access have been deleted
        //to do so we recreate the same user login and check if the rights are still there
        $this->assertEquals(array(), $this->api->getSitesAccessFromUser("geggeqgeqag"));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionUserDoesNotExist
     */
    public function testGetUserNoUser()
    {
        // try to get it, it should raise an exception
        $this->api->getUser("geggeqgeqag");
    }

    /**
     * normal case
     */
    public function test_GetUser()
    {
        $login = "geggeq55eqag";
        $password = "mypassword";
        $email = "mgeag4544i@geq.com";
        $alias = "";

        $this->api->addUser($login, $password, $email, $alias);
        $user = $this->api->getUser($login);

        // check that all fields are the same
        $this->assertEquals($login, $user['login']);
        $this->assertInternalType('string', $user['password']);
        $this->assertInternalType('string', $user['date_registered']);
        $this->assertEquals($email, $user['email']);

        //alias shouldn't be empty even if no alias specified
        $this->assertGreaterThan(0, strlen($user['alias']));
    }

    /**
     * no user => empty array
     */
    public function testGetUsersNoUser()
    {
        $this->assertEquals($this->api->getUsers(), array());
    }

    /**
     * normal case
     * as well as selecting specific user names, comma separated
     */
    public function testGetUsers()
    {
        $this->api->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
        $this->api->addUser("geggeqge632ge56a4qag", "geqgegeagae", "tesggt@tesgt.com", "alias");
        $this->api->addUser("geggeqgeqagqegg", "geqgeaggggae", "tesgggt@tesgt.com");

        $users = $this->api->getUsers();
        $users = $this->_removeNonTestableFieldsFromUsers($users);
        $user1 = array('login' => "gegg4564eqgeqag", 'password' => md5("geqgegagae"), 'alias' => "alias", 'email' => "tegst@tesgt.com", 'superuser_access' => 0);
        $user2 = array('login' => "geggeqge632ge56a4qag", 'password' => md5("geqgegeagae"), 'alias' => "alias", 'email' => "tesggt@tesgt.com", 'superuser_access' => 0);
        $user3 = array('login' => "geggeqgeqagqegg", 'password' => md5("geqgeaggggae"), 'alias' => 'geggeqgeqagqegg', 'email' => "tesgggt@tesgt.com", 'superuser_access' => 0);
        $expectedUsers = array($user1, $user2, $user3);
        $this->assertEquals($expectedUsers, $users);
        $this->assertEquals(array($user1), $this->_removeNonTestableFieldsFromUsers($this->api->getUsers('gegg4564eqgeqag')));
        $this->assertEquals(array($user1, $user2), $this->_removeNonTestableFieldsFromUsers($this->api->getUsers('gegg4564eqgeqag,geggeqge632ge56a4qag')));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage checkUserHasSomeAdminAccess Fake exception
     */
    public function testGetUsers_withViewAccess_shouldThrowAnException()
    {
        $this->api->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
        $this->api->addUser("geggeqge632ge56a4qag", "geqgegeagae", "tesggt@tesgt.com", "alias");
        $this->api->addUser("geggeqgeqagqegg", "geqgeaggggae", "tesgggt@tesgt.com");

        FakeAccess::clearAccess($superUser = false, $admin = array(), $view = array(1), 'gegg4564eqgeqag');

        $this->api->getUsers();
    }

    protected function _removeNonTestableFieldsFromUsers($users)
    {
        foreach ($users as &$user) {
            unset($user['token_auth']);
            unset($user['date_registered']);
        }
        return $users;
    }

    /**
     * normal case
     */
    public function testGetUsersLogin()
    {
        $this->api->addUser('gegg4564eqgeqag', 'geqgegagae', 'tegst@tesgt.com', 'alias');
        $this->api->addUser("geggeqge632ge56a4qag", "geqgegeagae", "tesggt@tesgt.com", "alias");
        $this->api->addUser("geggeqgeqagqegg", "geqgeaggggae", "tesgggt@tesgt.com");

        $logins = $this->api->getUsersLogin();

        $this->assertEquals(array("gegg4564eqgeqag", "geggeqge632ge56a4qag", "geggeqgeqagqegg"), $logins);
    }

    public function testGetUserLoginFromUserEmail()
    {
        $this->api->addUser('gegg4564eqgeqag', 'geqgegagae', 'tegst@tesgt.com', 'alias');
        $this->api->addUser("geggeqge632ge56a4qag", "geqgegeagae", "tesggt@tesgt.com", "alias");
        $this->api->addUser("geggeqgeqagqegg", "geqgeaggggae", "tesgggt@tesgt.com");

        $this->assertSame('gegg4564eqgeqag', $this->api->getUserLoginFromUserEmail('tegst@tesgt.com'));
        $this->assertSame('geggeqge632ge56a4qag', $this->api->getUserLoginFromUserEmail('tesggt@tesgt.com'));
        // test camel case should still find user
        $this->assertSame('geggeqge632ge56a4qag', $this->api->getUserLoginFromUserEmail('teSGgT@tesgt.com'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionUserDoesNotExist
     */
    public function testGetUserLoginFromUserEmail_shouldThrowException_IfUserDoesNotExist()
    {
        $this->api->getUserLoginFromUserEmail('unknownUser@teSsgt.com');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage checkUserHasSomeAdminAccess Fake exception
     */
    public function testGetUserLoginFromUserEmail_shouldThrowException_IfUserDoesNotHaveAtLeastAdminPermission()
    {
        FakeAccess::clearAccess($superUser = false, $admin =array(), $view = array(1));
        $this->api->getUserLoginFromUserEmail('tegst@tesgt.com');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionUserDoesNotExist
     */
    public function testSetUserAccessNoLogin()
    {
        $this->api->setUserAccess("nologin", "view", 1);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionAccessValues
     */
    public function testSetUserAccessWrongAccessSpecified()
    {
        $this->api->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
        $this->api->setUserAccess("gegg4564eqgeqag", "viewnotknown", 1);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionAccessValues
     */
    public function testSetUserAccess_ShouldFail_SuperUserAccessIsNotAllowed()
    {
        $this->api->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
        $this->api->setUserAccess("gegg4564eqgeqag", "superuser", 1);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionUserDoesNotExist
     */
    public function testSetUserAccess_ShouldFail_IfLoginIsConfigSuperUserLogin()
    {
        $this->api->setUserAccess('superusertest', 'view', 1);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionSuperUserAccess
     */
    public function testSetUserAccess_ShouldFail_IfLoginIsUserWithSuperUserAccess()
    {
        $this->api->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
        $this->api->setSuperUserAccess('gegg4564eqgeqag', true);

        $this->api->setUserAccess('gegg4564eqgeqag', 'view', 1);
    }

    /**
     * idsites = all => apply access to all websites with admin access
     */
    public function testSetUserAccessIdsitesIsAll()
    {
        $this->api->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");

        FakeAccess::$superUser = false;

        $this->api->setUserAccess("gegg4564eqgeqag", "view", "all");

        FakeAccess::$superUser = true;
        $access = $this->api->getSitesAccessFromUser("gegg4564eqgeqag");
        $access = $this->_flatten($access);

        /** @var Access $accessInstance */
        $accessInstance = self::$fixture->piwikEnvironment->getContainer()->get('Piwik\Access');

        FakeAccess::$superUser = false;
        $this->assertEquals(array_keys($access), $accessInstance->getSitesIdWithAdminAccess());

        // we want to test the case for which we have actually set some rights
        // if this is not OK then change the setUp method and add some admin rights for some websites
        $this->assertGreaterThan(0, count(array_keys($access)));
    }

    /**
     * idsites = all AND user is superuser=> apply access to all websites
     */
    public function testSetUserAccessIdsitesIsAllSuperuser()
    {
        FakeAccess::$superUser = true;

        $idSites = $this->addSites(5);

        $this->api->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
        $this->api->setUserAccess("gegg4564eqgeqag", "view", "all");

        $access = $this->api->getSitesAccessFromUser("gegg4564eqgeqag");
        $access = $this->_flatten($access);
        $this->assertEquals($idSites, array_keys($access));
    }

    /**
     * @expectedException \Exception
     */
    public function testSetUserAccess_ShouldNotBeAbleToSetAnyAccess_IfIdSitesIsEmpty()
    {
        $this->api->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");

        $this->api->setUserAccess("gegg4564eqgeqag", "view", array());
    }

    /**
     * normal case, access set for only one site
     */
    public function testSetUserAccessIdsitesOneSite()
    {
        $this->api->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
        $idSites = $this->addSites(1);

        $this->api->setUserAccess("gegg4564eqgeqag", "view", $idSites);

        $access = $this->api->getSitesAccessFromUser("gegg4564eqgeqag");
        $access = $this->_flatten($access);
        $this->assertEquals($idSites, array_keys($access));
    }

    /**
     * normal case, access set for multiple sites
     */
    public function testSetUserAccessIdsitesMultipleSites()
    {
        $this->api->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
        list($id1, $id2, $id3) = $this->addSites(3);

        $this->api->setUserAccess("gegg4564eqgeqag", "view", array($id1, $id3));

        $access = $this->api->getSitesAccessFromUser("gegg4564eqgeqag");
        $access = $this->_flatten($access);
        $this->assertEquals(array($id1, $id3), array_keys($access));
    }

    /**
     * normal case, string idSites comma separated access set for multiple sites
     */
    public function testSetUserAccessWithIdSitesIsStringCommaSeparated()
    {
        $this->api->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
        list($id1, $id2, $id3) = $this->addSites(3);

        $this->api->setUserAccess("gegg4564eqgeqag", "view", "1,3");

        $access = $this->api->getSitesAccessFromUser("gegg4564eqgeqag");
        $access = $this->_flatten($access);
        $this->assertEquals(array($id1, $id3), array_keys($access));
    }

    /**
     * normal case,  set different acccess to different websites for one user
     */
    public function testSetUserAccessMultipleCallDistinctAccessSameUser()
    {
        $this->api->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");

        list($id1, $id2) = $this->addSites(2);

        $this->api->setUserAccess("gegg4564eqgeqag", "view", array($id1));
        $this->api->setUserAccess("gegg4564eqgeqag", "admin", array($id2));

        $access = $this->api->getSitesAccessFromUser("gegg4564eqgeqag");
        $access = $this->_flatten($access);
        $this->assertEquals(array($id1 => 'view', $id2 => 'admin'), $access);
    }

    /**
     * normal case, set different access to different websites for multiple users
     */
    public function testSetUserAccessMultipleCallDistinctAccessMultipleUser()
    {
        $this->api->addUser("user1", "geqgegagae", "tegst@tesgt.com", "alias");
        $this->api->addUser("user2", "geqgegagae", "tegst2@tesgt.com", "alias");

        list($id1, $id2, $id3) = $this->addSites(3);

        $this->api->setUserAccess("user1", "view", array($id1, $id2));
        $this->api->setUserAccess("user2", "admin", array($id1));
        $this->api->setUserAccess("user2", "view", array($id3, $id2));

        $access1 = $this->api->getSitesAccessFromUser("user1");
        $access1 = $this->_flatten($access1);
        $access2 = $this->api->getSitesAccessFromUser("user2");
        $access2 = $this->_flatten($access2);
        $wanted1 = array($id1 => 'view', $id2 => 'view',);
        $wanted2 = array($id1 => 'admin', $id2 => 'view', $id3 => 'view');

        $this->assertEquals($wanted1, $access1);
        $this->assertEquals($wanted2, $access2);

        $access1 = $this->api->getUsersAccessFromSite($id1);
        $access2 = $this->api->getUsersAccessFromSite($id2);
        $access3 = $this->api->getUsersAccessFromSite($id3);
        $wanted1 = array('user1' => 'view', 'user2' => 'admin',);
        $wanted2 = array('user1' => 'view', 'user2' => 'view');
        $wanted3 = array('user2' => 'view');

        $this->assertEquals($wanted1, $access1);
        $this->assertEquals($wanted2, $access2);
        $this->assertEquals($wanted3, $access3);

        $access1 = $this->api->getUsersSitesFromAccess('view');
        $access2 = $this->api->getUsersSitesFromAccess('admin');
        $wanted1 = array('user1' => array($id1, $id2), 'user2' => array($id2, $id3));
        $wanted2 = array('user2' => array($id1));

        $this->assertEquals($wanted1, $access1);
        $this->assertEquals($wanted2, $access2);

        // Test getUsersWithSiteAccess
        $users = $this->api->getUsersWithSiteAccess($id1, $access = 'view');
        $this->assertEquals(1, count($users));
        $this->assertEquals('user1', $users[0]['login']);
        $users = $this->api->getUsersWithSiteAccess($id2, $access = 'view');
        $this->assertEquals(2, count($users));
        $users = $this->api->getUsersWithSiteAccess($id1, $access = 'admin');
        $this->assertEquals(1, count($users));
        $this->assertEquals('user2', $users[0]['login']);
        $users = $this->api->getUsersWithSiteAccess($id3, $access = 'admin');
        $this->assertEquals(0, count($users));
    }

    /**
     * we set access for one user for one site several times and check that it is updated
     */
    public function testSetUserAccessMultipleCallOverwriteSingleUserOneSite()
    {
        $this->api->addUser("user1", "geqgegagae", "tegst@tesgt.com", "alias");

        list($id1, $id2) = $this->addSites(2);

        $this->api->setUserAccess("user1", "view", array($id1, $id2));
        $this->api->setUserAccess("user1", "admin", array($id1));

        $access1 = $this->api->getSitesAccessFromUser("user1");
        $access1 = $this->_flatten($access1);
        $wanted1 = array($id1 => 'admin', $id2 => 'view',);

        $this->assertEquals($wanted1, $access1);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage checkUserHasSuperUserAccess Fake exception
     */
    public function testSetSuperUserAccess_ShouldFail_IfUserHasNotSuperUserPermission()
    {
        FakeAccess::$superUser= false;
        $this->api->setSuperUserAccess('nologin', false);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionUserDoesNotExist
     */
    public function testSetSuperUserAccess_ShouldFail_IfUserWithGivenLoginDoesNotExist()
    {
        $this->api->setSuperUserAccess('nologin', false);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionEditAnonymous
     */
    public function testSetSuperUserAccess_ShouldFail_IfUserIsAnonymous()
    {
        $this->api->setSuperUserAccess('anonymous', true);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionRemoveSuperUserAccessOnlySuperUser
     */
    public function testSetSuperUserAccess_ShouldFail_IfUserIsOnlyRemainingUserWithSuperUserAccess()
    {
        $this->api->addUser('login1', 'password1', 'test@example.com', false);
        $this->api->setSuperUserAccess('login1', true);

        $this->api->setSuperUserAccess('login1', false);
    }

    public function testSetSuperUserAccess_ShouldDeleteAllExistingAccessEntries()
    {
        list($id1, $id2) = $this->addSites(2);
        $this->api->addUser('login1', 'password1', 'test@example.com', false);
        $this->api->setUserAccess('login1', 'view', array($id1));
        $this->api->setUserAccess('login1', 'admin', array($id2));

        // verify user has access before setting Super User access
        $access = $this->_flatten($this->api->getSitesAccessFromUser('login1'));
        $this->assertEquals(array($id1 => 'view', $id2 => 'admin'), $access);

        $this->api->setSuperUserAccess('login1', true);

        // verify no longer any access
        $this->assertEquals(array(), $this->model->getSitesAccessFromUser('login1'));
    }

    public function testSetSuperUserAccess_ShouldAddAndRemoveSuperUserAccessOnlyForGivenLogin()
    {
        $this->api->addUser('login1', 'password1', 'test1@example.com', false);
        $this->api->addUser('login2', 'password2', 'test2@example.com', false);
        $this->api->addUser('login3', 'password3', 'test3@example.com', false);

        $this->api->setSuperUserAccess('login2', true);

        // test add Super User access
        $users = $this->api->getUsers();

        $this->assertEquals(0, $users[0]['superuser_access']);
        $this->assertEquals(1, $users[1]['superuser_access']);
        $this->assertEquals('login2', $users[1]['login']);
        $this->assertEquals(0, $users[2]['superuser_access']);

        // should also accept string '1' to add Super User access
        $this->api->setSuperUserAccess('login1', '1');
        // test remove Super User access
        $this->api->setSuperUserAccess('login2', false);

        $users = $this->api->getUsers();
        $this->assertEquals(1, $users[0]['superuser_access']);
        $this->assertEquals('login1', $users[0]['login']);
        $this->assertEquals(0, $users[1]['superuser_access']);
        $this->assertEquals(0, $users[2]['superuser_access']);

        $this->api->setSuperUserAccess('login3', true);
        // should also accept string '0' to remove Super User access
        $this->api->setSuperUserAccess('login1', '0');

        $users = $this->api->getUsers();
        $this->assertEquals(0, $users[0]['superuser_access']);
        $this->assertEquals(0, $users[1]['superuser_access']);
        $this->assertEquals('login3', $users[2]['login']);
        $this->assertEquals(1, $users[2]['superuser_access']);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionUserDoesNotExist
     */
    public function testGetSitesAccessFromUserWrongUser()
    {
        $this->api->getSitesAccessFromUser("user1");
    }

    /**
     * @expectedException \Exception
     */
    public function testGetUsersAccessFromSiteWrongIdSite()
    {
        $this->api->getUsersAccessFromSite(1);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionAccessValues
     */
    public function testGetUsersSitesFromAccessWrongSite()
    {
        $this->api->getUsersSitesFromAccess('unknown');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionUserDoesNotExist
     */
    public function testUpdateUserNonExistingLogin()
    {
        $this->api->updateUser("lolgin", "password");
    }

    /**
     * no email no alias => keep old ones
     */
    public function testUpdateUserNoEmailNoAlias()
    {
        $login = "login";
        $user  = array('login'    => $login,
                       'password' => "geqgeagae",
                       'email'    => "test@test.com",
                       'alias'    => "alias");

        $this->api->addUser($user['login'], $user['password'], $user['email'], $user['alias']);
        $this->api->updateUser($login, "passowordOK");

        $this->_checkUserHasNotChanged($user, "passowordOK");
    }

    /**
     * no email => keep old ones
     */
    public function testUpdateUserNoEmail()
    {
        $login = "login";
        $user  = array('login'    => $login,
                       'password' => "geqgeagae",
                       'email'    => "test@test.com",
                       'alias'    => "alias");

        $this->api->addUser($user['login'], $user['password'], $user['email'], $user['alias']);
        $this->api->updateUser($login, "passowordOK", null, "newalias");

        $this->_checkUserHasNotChanged($user, "passowordOK", null, "newalias");
    }

    /**
     * no alias => keep old ones
     */
    public function testUpdateUserNoAlias()
    {
        $login = "login";
        $user  = array('login'    => $login,
                       'password' => "geqgeagae",
                       'email'    => "test@test.com",
                       'alias'    => "alias");

        $this->api->addUser($user['login'], $user['password'], $user['email'], $user['alias']);
        $this->api->updateUser($login, "passowordOK", "email@geaga.com");

        $this->_checkUserHasNotChanged($user, "passowordOK", "email@geaga.com");
    }

    /**
     * check to modify as the user
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionLoginExists
     */
    public function testAddUserIAmTheUser()
    {
        FakeAccess::$identity = 'login';
        $this->testUpdateUserNoEmailNoAlias();
    }

    /**
     * check to modify as being another user => exception
     *
     * @expectedException \Exception
     */
    public function testUpdateUserIAmNotTheUser()
    {
        FakeAccess::$identity = 'login2';
        FakeAccess::$superUser = false;
        $this->testUpdateUserNoEmailNoAlias();
    }

    /**
     * normal case, reused in other tests
     */
    public function testUpdateUser()
    {
        $login = "login";
        $user  = array('login'    => $login,
                       'password' => "geqgeagae",
                       'email'    => "test@test.com",
                       'alias'    => "alias");

        $this->api->addUser($user['login'], $user['password'], $user['email'], $user['alias']);
        $this->api->updateUser($login, "passowordOK", "email@geaga.com", "NEW ALIAS");

        $this->_checkUserHasNotChanged($user, "passowordOK", "email@geaga.com", "NEW ALIAS");
    }

    /**
     * @expectedException \Exception
     */
    public function testGetUserByEmailInvalidMail()
    {
        $this->api->getUserByEmail('email@test.com');
    }

    public function testGetUserByEmail()
    {
        $user = array('login'    => "login",
                      'password' => "geqgeagae",
                      'email'    => "test@test.com",
                      'alias'    => "alias");

        $this->api->addUser($user['login'], $user['password'], $user['email'], $user['alias']);

        $userByMail = $this->api->getUserByEmail($user['email']);

        $this->assertEquals($user['login'], $userByMail['login']);
        $this->assertEquals($user['email'], $userByMail['email']);
        $this->assertEquals($user['alias'], $userByMail['alias']);
    }

    public function testGetUserPreferenceDefault()
    {
        $this->addSites(1);
        $defaultReportPref = API::PREFERENCE_DEFAULT_REPORT;
        $defaultReportDatePref = API::PREFERENCE_DEFAULT_REPORT_DATE;

        $this->assertEquals(1, $this->api->getUserPreference('someUser', $defaultReportPref));
        $this->assertEquals('yesterday', $this->api->getUserPreference('someUser', $defaultReportDatePref));
    }

    private function addSites($numberOfSites)
    {
        $idSites = array();

        for ($index = 0; $index < $numberOfSites; $index++) {
            $name      = "test" . ($index + 1);
            $idSites[] = APISitesManager::getInstance()->addSite($name, array("http://piwik.net", "http://piwik.com/test/"));
        }

        return $idSites;
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
