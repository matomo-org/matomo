<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class UsersManagerTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        Piwik_PluginsManager::getInstance()->loadPlugin('UsersManager');
        Piwik_PluginsManager::getInstance()->installLoadedPlugins();

        // setup the access layer
        $pseudoMockAccess = new FakeAccess;
        FakeAccess::setIdSitesView(array(1, 2));
        FakeAccess::setIdSitesAdmin(array(3, 4));

        //finally we set the user as a super user by default
        FakeAccess::$superUser = true;
        FakeAccess::$superUserLogin = 'superusertest';
        Zend_Registry::set('access', $pseudoMockAccess);

        // we make sure the tests don't depend on the config file content
        Piwik_Config::getInstance()->superuser = array(
            'login'    => 'superusertest',
            'password' => 'passwordsuperusertest',
            'email'    => 'superuser@example.com'
        );
    }

    private function _flatten($sitesAccess)
    {
        $result = array();
        ;

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
        $userAfter = Piwik_UsersManager_API::getInstance()->getUser($user["login"]);
        unset($userAfter['date_registered']);

        // we now compute what the token auth should be, it should always be a hash of the login and the current password
        // if the password has changed then the token_auth has changed!
        $user['token_auth'] = Piwik_UsersManager_API::getInstance()->getTokenAuth($user["login"], md5($newPassword));

        $user['password'] = md5($newPassword);
        $user['email'] = $newEmail;
        $user['alias'] = $newAlias;
        $this->assertEquals($user, $userAfter);
    }

    /**
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testAllSuperUserIncluded()
    {
        Piwik_Config::getInstance()->superuser = array(
            'login'    => 'superusertest',
            'password' => 'passwordsuperusertest',
            'email'    => 'superuser@example.com'
        );

        $user = array('login'    => 'user',
                      'password' => "geqgeagae",
                      'email'    => "test@test.com",
                      'alias'    => "alias");
        Piwik_UsersManager_API::getInstance()->addUser($user['login'], $user['password'], $user['email'], $user['alias']);

        $exceptionNotRaised = false;
        try {
            Piwik_UsersManager_API::getInstance()->addUser('superusertest', 'te', 'fake@fale.co', 'ega');
            $exceptionNotRaised = true;
        } catch (Exception $expected) {
            $this->assertRegExp("(UsersManager_ExceptionSuperUser)", $expected->getMessage());
        }
        try {
            Piwik_UsersManager_API::getInstance()->updateUser('superusertest', 'te', 'fake@fale.co', 'ega');
            $exceptionNotRaised = true;
        } catch (Exception $expected) {
            $this->assertRegExp("(UsersManager_ExceptionSuperUser)", $expected->getMessage());
        }
        try {
            Piwik_UsersManager_API::getInstance()->deleteUser('superusertest', 'te', 'fake@fale.co', 'ega');
            $exceptionNotRaised = true;
        } catch (Exception $expected) {
            $this->assertRegExp("(UsersManager_ExceptionSuperUser)", $expected->getMessage());
        }
        try {
            Piwik_UsersManager_API::getInstance()->deleteUser('superusertest', 'te', 'fake@fale.co', 'ega');
            $exceptionNotRaised = true;
        } catch (Exception $expected) {
            $this->assertRegExp("(UsersManager_ExceptionSuperUser)", $expected->getMessage());
        }
        if ($exceptionNotRaised) {
            $this->fail();
        }
    }

    /**
     * bad password => exception
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testUpdateUserBadpasswd()
    {
        $login = "login";
        $user = array('login'    => $login,
                      'password' => "geqgeagae",
                      'email'    => "test@test.com",
                      'alias'    => "alias");

        Piwik_UsersManager_API::getInstance()->addUser($user['login'], $user['password'], $user['email'], $user['alias']);


        try {
            Piwik_UsersManager_API::getInstance()->updateUser($login, "pas");
        } catch (Exception $expected) {
            $this->assertRegExp("(UsersManager_ExceptionInvalidPassword)", $expected->getMessage());

            $this->_checkUserHasNotChanged($user, $user['password']);
            return;
        }
        $this->fail("Exception not raised.");

    }

    /**
     * Dataprovider
     */
    public function getAddUserInvalidLoginData()
    {
        return array(
            array(12, "password", "email@email.com", "alias"), // wrong login / integer => exception
            array("gegag'ggea'", "password", "email@email.com", "alias"), // wrong login / too short => exception
            array("gegag11gge&", "password", "email@email.com", "alias"), // wrong login / too long => exception
            array("geg'ag11gge@", "password", "email@email.com", "alias"), // wrong login / bad characters => exception
        );
    }

    /**
     *
     * @dataProvider getAddUserInvalidLoginData
     * @group Plugins
     * @group UsersManager
     */
    public function testAddUserWrongLogin($userLogin, $password, $email, $alias)
    {
        try {
            Piwik_UsersManager_API::getInstance()->addUser($userLogin, $password, $email, $alias);
        } catch (Exception $expected) {
            $this->assertRegExp("(UsersManager_ExceptionInvalidLogin)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }

    /**
     * existing login => exception
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testAddUserExistingLogin()
    {
        try {
            Piwik_UsersManager_API::getInstance()->addUser("test", "password", "email@email.com", "alias");
            Piwik_UsersManager_API::getInstance()->addUser("test", "password2", "em2ail@email.com", "al2ias");
        } catch (Exception $expected) {
            $this->assertRegExp("(UsersManager_ExceptionLoginExists)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");

    }

    /**
     * Dataprovider for wrong password tests
     */
    public function getWrongPasswordTestData()
    {
        return array(
            array("geggeqgeqag", "pas", "email@email.com", "alias"), // too short -> exception
            array("ghqgeggg", "gegageqqqqqqqgeqgqeg84897897897897g122", "email@email.com", "alias"), // too long -> exception
            array("geggeqgeqag", "", "email@email.com", "alias"), // empty -> exception
        );
    }

    /**
     *
     * @dataProvider getWrongPasswordTestData
     * @group Plugins
     * @group UsersManager
     */
    public function testAddUserWrongPassword($userLogin, $password, $email, $alias)
    {
        try {
            Piwik_UsersManager_API::getInstance()->addUser($userLogin, $password, $email, $alias);
        } catch (Exception $expected) {
            $this->assertRegExp("(UsersManager_ExceptionInvalidPassword)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }

    /**
     * Dataprovider for wrong email tests
     */
    public function getWrongEmailTestData()
    {
        return array(
            array("geggeqgeqag", "geqgeagae", "ema'il@email.com", "alias"),
            array("geggeqgeqag", "geqgeagae", "@email.com", "alias"),
            array("geggeqgeqag", "geqgeagae", "email@.com", "alias"),
            array("geggeqgeqag", "geqgeagae", "email@4.", "alias"),
        );
    }

    /**
     *
     * @dataProvider getWrongEmailTestData
     * @group Plugins
     * @group UsersManager
     */
    public function testAddUserWrongEmail($userLogin, $password, $email, $alias)
    {
        try {
            Piwik_UsersManager_API::getInstance()->addUser($userLogin, $password, $email, $alias);
        } catch (Exception $expected) {
            $this->assertRegExp("(mail)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }

    /**
     * empty email => exception
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testAddUserEmptyEmail()
    {

        try {
            Piwik_UsersManager_API::getInstance()->addUser("geggeqgeqag", "geqgeagae", "", "alias");
        } catch (Exception $expected) {
            $this->assertRegExp("(mail)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }

    /**
     * empty alias => use login
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testAddUserEmptyAlias()
    {
        $login = "geggeqgeqag";
        Piwik_UsersManager_API::getInstance()->addUser($login, "geqgeagae", "mgeagi@geq.com", "");
        $user = Piwik_UsersManager_API::getInstance()->getUser($login);
        $this->assertEquals($login, $user['alias']);
        $this->assertEquals($login, $user['login']);
    }

    /**
     * no alias => use login
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testAddUserNoAliasSpecified()
    {
        $login = "geggeqg455eqag";
        Piwik_UsersManager_API::getInstance()->addUser($login, "geqgeagae", "mgeagi@geq.com");
        $user = Piwik_UsersManager_API::getInstance()->getUser($login);
        $this->assertEquals($login, $user['alias']);
        $this->assertEquals($login, $user['login']);
    }

    /**
     * normal test case
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testAddUser()
    {
        $login = "geggeq55eqag";
        $password = "mypassword";
        $email = "mgeag4544i@geq.com";
        $alias = "her is my alias )(&|\" '£%*(&%+))";

        $time = time();
        Piwik_UsersManager_API::getInstance()->addUser($login, $password, $email, $alias);
        $user = Piwik_UsersManager_API::getInstance()->getUser($login);

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
     * user doesnt exist => exception
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testSeleteUserDoesntExist()
    {
        Piwik_UsersManager_API::getInstance()->addUser("geggeqgeqag", "geqgeagae", "test@test.com", "alias");

        try {
            Piwik_UsersManager_API::getInstance()->deleteUser("geggeqggnew");
        } catch (Exception $expected) {
            $this->assertRegExp("(UsersManager_ExceptionDeleteDoesNotExist)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }

    /**
     * empty name, doesnt exists =>exception
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testDeleteUserEmptyUser()
    {
        try {
            Piwik_UsersManager_API::getInstance()->deleteUser("");
        } catch (Exception $expected) {
            $this->assertRegExp("(UsersManager_ExceptionDeleteDoesNotExist)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }

    /**
     * null user,, doesnt exists => exception
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testDeleteUserNullUser()
    {
        try {
            Piwik_UsersManager_API::getInstance()->deleteUser(null);
        } catch (Exception $expected) {
            $this->assertRegExp("(UsersManager_ExceptionDeleteDoesNotExist)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }

    /**
     * normal case, user deleted
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testDeleteUser()
    {
        //create the 3 websites
        $idsite = Piwik_SitesManager_API::getInstance()->addSite("site1", array("http://piwik.net", "http://piwik.com/test/"));
        $idsite = Piwik_SitesManager_API::getInstance()->addSite("site2", array("http://piwik.com/test/"));
        $idsite = Piwik_SitesManager_API::getInstance()->addSite("site3", array("http://piwik.org"));

        //add user and set some rights
        Piwik_UsersManager_API::getInstance()->addUser("geggeqgeqag", "geqgeagae", "test@test.com", "alias");
        Piwik_UsersManager_API::getInstance()->setUserAccess("geggeqgeqag", "view", array(1, 2));
        Piwik_UsersManager_API::getInstance()->setUserAccess("geggeqgeqag", "admin", array(1, 3));

        // check rights are set
        $this->assertNotEquals(array(), Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("geggeqgeqag"));

        // delete the user
        Piwik_UsersManager_API::getInstance()->deleteUser("geggeqgeqag");

        // try to get it, it should raise an exception
        try {
            $user = Piwik_UsersManager_API::getInstance()->getUser("geggeqgeqag");
            $this->fail("Exception not raised.");
        } catch (Exception $expected) {
            $this->assertRegExp("(UsersManager_ExceptionUserDoesNotExist)", $expected->getMessage());
        }

        // add the same user
        Piwik_UsersManager_API::getInstance()->addUser("geggeqgeqag", "geqgeagae", "test@test.com", "alias");

        //checks access have been deleted
        //to do so we recreate the same user login and check if the rights are still there
        $this->assertEquals(array(), Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("geggeqgeqag"));
    }


    /**
     * no user => exception
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testGetUserNoUser()
    {
        // try to get it, it should raise an exception
        try {
            $user = Piwik_UsersManager_API::getInstance()->getUser("geggeqgeqag");
        } catch (Exception $expected) {
            $this->assertRegExp("(UsersManager_ExceptionUserDoesNotExist)", $expected->getMessage());
            return;
        }

        $this->fail("Exception not raised.");
    }

    /**
     * normal case
     *
     * @group Plugins
     * @group UsersManager
     */
    public function test_GetUser()
    {
        $login = "geggeq55eqag";
        $password = "mypassword";
        $email = "mgeag4544i@geq.com";
        $alias = "";

        Piwik_UsersManager_API::getInstance()->addUser($login, $password, $email, $alias);
        $user = Piwik_UsersManager_API::getInstance()->getUser($login);

        // check that all fields are the same
        $this->assertEquals($login, $user['login']);
        $this->assertInternalType('string', $user['password']);
        $this->assertInternalType('string', $user['date_registered']);
        $this->assertEquals($email, $user['email']);

        //alias shouldnt be empty even if no alias specified
        $this->assertGreaterThan(0, strlen($user['alias']));
    }

    /**
     * no user => empty array
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testGetUsersNoUser()
    {
        $this->assertEquals(Piwik_UsersManager_API::getInstance()->getUsers(), array());
    }

    /**
     * normal case
     * as well as selecting specific user names, comma separated
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testGetUsers()
    {
        Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
        Piwik_UsersManager_API::getInstance()->addUser("geggeqge632ge56a4qag", "geqgegeagae", "tesggt@tesgt.com", "alias");
        Piwik_UsersManager_API::getInstance()->addUser("geggeqgeqagqegg", "geqgeaggggae", "tesgggt@tesgt.com");

        $users = Piwik_UsersManager_API::getInstance()->getUsers();
        $users = $this->_removeNonTestableFieldsFromUsers($users);
        $user1 = array('login' => "gegg4564eqgeqag", 'password' => md5("geqgegagae"), 'alias' => "alias", 'email' => "tegst@tesgt.com");
        $user2 = array('login' => "geggeqge632ge56a4qag", 'password' => md5("geqgegeagae"), 'alias' => "alias", 'email' => "tesggt@tesgt.com");
        $user3 = array('login' => "geggeqgeqagqegg", 'password' => md5("geqgeaggggae"), 'alias' => 'geggeqgeqagqegg', 'email' => "tesgggt@tesgt.com");
        $expectedUsers = array($user1, $user2, $user3);
        $this->assertEquals($expectedUsers, $users);
        $this->assertEquals(array($user1), $this->_removeNonTestableFieldsFromUsers(Piwik_UsersManager_API::getInstance()->getUsers('gegg4564eqgeqag')));
        $this->assertEquals(array($user1, $user2), $this->_removeNonTestableFieldsFromUsers(Piwik_UsersManager_API::getInstance()->getUsers('gegg4564eqgeqag,geggeqge632ge56a4qag')));

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
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testGetUsersLogin()
    {

        Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
        Piwik_UsersManager_API::getInstance()->addUser("geggeqge632ge56a4qag", "geqgegeagae", "tesggt@tesgt.com", "alias");
        Piwik_UsersManager_API::getInstance()->addUser("geggeqgeqagqegg", "geqgeaggggae", "tesgggt@tesgt.com");

        $logins = Piwik_UsersManager_API::getInstance()->getUsersLogin();

        $this->assertEquals(array("gegg4564eqgeqag", "geggeqge632ge56a4qag", "geggeqgeqagqegg"), $logins);
    }


    /**
     * no login => exception
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testSetUserAccessNoLogin()
    {
        // try to get it, it should raise an exception
        try {
            Piwik_UsersManager_API::getInstance()->setUserAccess("nologin", "view", 1);
        } catch (Exception $expected) {
            $this->assertRegExp("(UsersManager_ExceptionUserDoesNotExist)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }

    /**
     * wrong access specified  => exception
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testSetUserAccessWrongAccess()
    {
        Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");

        // try to get it, it should raise an exception
        try {
            Piwik_UsersManager_API::getInstance()->setUserAccess("gegg4564eqgeqag", "viewnotknown", 1);
        } catch (Exception $expected) {
            $this->assertRegExp("(UsersManager_ExceptionAccessValues)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }

    /**
     * idsites = all => apply access to all websites with admin access
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testSetUserAccessIdsitesIsAll()
    {
        Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");

        FakeAccess::$superUser = false;

        Piwik_UsersManager_API::getInstance()->setUserAccess("gegg4564eqgeqag", "view", "all");

        FakeAccess::$superUser = true;
        $access = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("gegg4564eqgeqag");
        $access = $this->_flatten($access);

        FakeAccess::$superUser = false;
        $this->assertEquals(array_keys($access), FakeAccess::getSitesIdWithAdminAccess());

        // we want to test the case for which we have actually set some rights
        // if this is not OK then change the setUp method and add some admin rights for some websites
        $this->assertGreaterThan(0, count(array_keys($access)));
    }

    /**
     * idsites = all AND user is superuser=> apply access to all websites
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testSetUserAccessIdsitesIsAllSuperuser()
    {
        FakeAccess::$superUser = true;

        $id1 = Piwik_SitesManager_API::getInstance()->addSite("test", array("http://piwik.net", "http://piwik.com/test/"));
        $id2 = Piwik_SitesManager_API::getInstance()->addSite("test2", array("http://piwik.net", "http://piwik.com/test/"));
        $id3 = Piwik_SitesManager_API::getInstance()->addSite("test3", array("http://piwik.net", "http://piwik.com/test/"));
        $id4 = Piwik_SitesManager_API::getInstance()->addSite("test4", array("http://piwik.net", "http://piwik.com/test/"));
        $id5 = Piwik_SitesManager_API::getInstance()->addSite("test5", array("http://piwik.net", "http://piwik.com/test/"));

        Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
        Piwik_UsersManager_API::getInstance()->setUserAccess("gegg4564eqgeqag", "view", "all");

        $access = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("gegg4564eqgeqag");
        $access = $this->_flatten($access);
        $this->assertEquals(array($id1, $id2, $id3, $id4, $id5), array_keys($access));

    }

    /**
     * idsites is empty => no acccess set
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testSetUserAccessIdsitesEmpty()
    {
        Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");

        try {
            Piwik_UsersManager_API::getInstance()->setUserAccess("gegg4564eqgeqag", "view", array());
            $access = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("gegg4564eqgeqag");
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * normal case, access set for only one site
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testSetUserAccessIdsitesOneSite()
    {
        Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
        $id1 = Piwik_SitesManager_API::getInstance()->addSite("test", array("http://piwik.net", "http://piwik.com/test/"));

        Piwik_UsersManager_API::getInstance()->setUserAccess("gegg4564eqgeqag", "view", array(1));

        $access = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("gegg4564eqgeqag");
        $access = $this->_flatten($access);
        $this->assertEquals(array(1), array_keys($access));
    }

    /**
     * normal case, access set for multiple sites
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testSetUserAccessIdsitesMultipleSites()
    {

        Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
        $id1 = Piwik_SitesManager_API::getInstance()->addSite("test", array("http://piwik.net", "http://piwik.com/test/"));
        $id2 = Piwik_SitesManager_API::getInstance()->addSite("test", array("http://piwik.net", "http://piwik.com/test/"));
        $id3 = Piwik_SitesManager_API::getInstance()->addSite("test", array("http://piwik.net", "http://piwik.com/test/"));

        Piwik_UsersManager_API::getInstance()->setUserAccess("gegg4564eqgeqag", "view", array($id1, $id3));

        $access = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("gegg4564eqgeqag");
        $access = $this->_flatten($access);
        $this->assertEquals(array($id1, $id3), array_keys($access));

    }

    /**
     * normal case, string idSites comma separated access set for multiple sites
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testSetUserAccessWithIdSitesIsStringCommaSeparated()
    {
        Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
        $id1 = Piwik_SitesManager_API::getInstance()->addSite("test", array("http://piwik.net", "http://piwik.com/test/"));
        $id2 = Piwik_SitesManager_API::getInstance()->addSite("test", array("http://piwik.net", "http://piwik.com/test/"));
        $id3 = Piwik_SitesManager_API::getInstance()->addSite("test", array("http://piwik.net", "http://piwik.com/test/"));

        Piwik_UsersManager_API::getInstance()->setUserAccess("gegg4564eqgeqag", "view", "1,3");

        $access = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("gegg4564eqgeqag");
        $access = $this->_flatten($access);
        $this->assertEquals(array($id1, $id3), array_keys($access));
    }

    /**
     * normal case,  set different acccess to different websites for one user
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testSetUserAccessMultipleCallDistinctAccessSameUser()
    {
        Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
        $id1 = Piwik_SitesManager_API::getInstance()->addSite("test", array("http://piwik.net", "http://piwik.com/test/"));
        $id2 = Piwik_SitesManager_API::getInstance()->addSite("test", array("http://piwik.net", "http://piwik.com/test/"));

        Piwik_UsersManager_API::getInstance()->setUserAccess("gegg4564eqgeqag", "view", array($id1));
        Piwik_UsersManager_API::getInstance()->setUserAccess("gegg4564eqgeqag", "admin", array($id2));

        $access = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("gegg4564eqgeqag");
        $access = $this->_flatten($access);
        $this->assertEquals(array($id1 => 'view', $id2 => 'admin'), $access);
    }

    /**
     * normal case, set different access to different websites for multiple users
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testSetUserAccessMultipleCallDistinctAccessMultipleUser()
    {
        Piwik_UsersManager_API::getInstance()->addUser("user1", "geqgegagae", "tegst@tesgt.com", "alias");
        Piwik_UsersManager_API::getInstance()->addUser("user2", "geqgegagae", "tegst2@tesgt.com", "alias");
        $id1 = Piwik_SitesManager_API::getInstance()->addSite("test1", array("http://piwik.net", "http://piwik.com/test/"));
        $id2 = Piwik_SitesManager_API::getInstance()->addSite("test2", array("http://piwik.net", "http://piwik.com/test/"));
        $id3 = Piwik_SitesManager_API::getInstance()->addSite("test2", array("http://piwik.net", "http://piwik.com/test/"));

        Piwik_UsersManager_API::getInstance()->setUserAccess("user1", "view", array($id1, $id2));
        Piwik_UsersManager_API::getInstance()->setUserAccess("user2", "admin", array($id1));
        Piwik_UsersManager_API::getInstance()->setUserAccess("user2", "view", array($id3, $id2));

        $access1 = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("user1");
        $access1 = $this->_flatten($access1);
        $access2 = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("user2");
        $access2 = $this->_flatten($access2);
        $wanted1 = array($id1 => 'view', $id2 => 'view',);
        $wanted2 = array($id1 => 'admin', $id2 => 'view', $id3 => 'view');

        $this->assertEquals($wanted1, $access1);
        $this->assertEquals($wanted2, $access2);


        $access1 = Piwik_UsersManager_API::getInstance()->getUsersAccessFromSite($id1);
        $access2 = Piwik_UsersManager_API::getInstance()->getUsersAccessFromSite($id2);
        $access3 = Piwik_UsersManager_API::getInstance()->getUsersAccessFromSite($id3);
        $wanted1 = array('user1' => 'view', 'user2' => 'admin',);
        $wanted2 = array('user1' => 'view', 'user2' => 'view');
        $wanted3 = array('user2' => 'view');

        $this->assertEquals($wanted1, $access1);
        $this->assertEquals($wanted2, $access2);
        $this->assertEquals($wanted3, $access3);

        $access1 = Piwik_UsersManager_API::getInstance()->getUsersSitesFromAccess('view');
        $access2 = Piwik_UsersManager_API::getInstance()->getUsersSitesFromAccess('admin');
        $wanted1 = array('user1' => array($id1, $id2), 'user2' => array($id2, $id3));
        $wanted2 = array('user2' => array($id1));

        $this->assertEquals($wanted1, $access1);
        $this->assertEquals($wanted2, $access2);

        // Test getUsersWithSiteAccess
        $users = Piwik_UsersManager_API::getInstance()->getUsersWithSiteAccess($id1, $access = 'view');
        $this->assertEquals(1, count($users));
        $this->assertEquals('user1', $users[0]['login']);
        $users = Piwik_UsersManager_API::getInstance()->getUsersWithSiteAccess($id2, $access = 'view');
        $this->assertEquals(2, count($users));
        $users = Piwik_UsersManager_API::getInstance()->getUsersWithSiteAccess($id1, $access = 'admin');
        $this->assertEquals(1, count($users));
        $this->assertEquals('user2', $users[0]['login']);
        $users = Piwik_UsersManager_API::getInstance()->getUsersWithSiteAccess($id3, $access = 'admin');
        $this->assertEquals(0, count($users));
    }

    /**
     * we set access for one user for one site several times and check that it is updated
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testSetUserAccessMultipleCallOverwriteSingleUserOneSite()
    {
        Piwik_UsersManager_API::getInstance()->addUser("user1", "geqgegagae", "tegst@tesgt.com", "alias");

        $id1 = Piwik_SitesManager_API::getInstance()->addSite("test1", array("http://piwik.net", "http://piwik.com/test/"));
        $id2 = Piwik_SitesManager_API::getInstance()->addSite("test2", array("http://piwik.net", "http://piwik.com/test/"));

        Piwik_UsersManager_API::getInstance()->setUserAccess("user1", "view", array($id1, $id2));
        Piwik_UsersManager_API::getInstance()->setUserAccess("user1", "admin", array($id1));

        $access1 = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("user1");
        $access1 = $this->_flatten($access1);
        $wanted1 = array($id1 => 'admin', $id2 => 'view',);

        $this->assertEquals($wanted1, $access1);
    }

    /**
     * wrong user => exception
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testGetSitesAccessFromUserWrongUser()
    {
        try {
            $access1 = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("user1");
        } catch (Exception $expected) {
            $this->assertRegExp("(UsersManager_ExceptionUserDoesNotExist)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }

    /**
     *wrong idsite => exception
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testGetUsersAccessFromSiteWrongSite()
    {
        try {
            $access1 = Piwik_UsersManager_API::getInstance()->getUsersAccessFromSite(1);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * wrong access =>exception
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testGetUsersSitesFromAccessWrongSite()
    {
        try {
            $access1 = Piwik_UsersManager_API::getInstance()->getUsersSitesFromAccess('unknown');
        } catch (Exception $expected) {
            $this->assertRegExp("(UsersManager_ExceptionAccessValues)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }

    /**
     * non existing login => exception
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testUpdateUserWrongLogin()
    {
        try {
            Piwik_UsersManager_API::getInstance()->updateUser("lolgin", "password");
        } catch (Exception $expected) {
            $this->assertRegExp("(UsersManager_ExceptionUserDoesNotExist)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }


    /**
     * no email no alias => keep old ones
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testUpdateUserNoEmailNoAlias()
    {
        $login = "login";
        $user = array('login'    => $login,
                      'password' => "geqgeagae",
                      'email'    => "test@test.com",
                      'alias'    => "alias");

        Piwik_UsersManager_API::getInstance()->addUser($user['login'], $user['password'], $user['email'], $user['alias']);

        Piwik_UsersManager_API::getInstance()->updateUser($login, "passowordOK");

        $this->_checkUserHasNotChanged($user, "passowordOK");
    }

    /**
     *no email => keep old ones
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testUpdateUserNoEmail()
    {
        $login = "login";
        $user = array('login'    => $login,
                      'password' => "geqgeagae",
                      'email'    => "test@test.com",
                      'alias'    => "alias");

        Piwik_UsersManager_API::getInstance()->addUser($user['login'], $user['password'], $user['email'], $user['alias']);

        Piwik_UsersManager_API::getInstance()->updateUser($login, "passowordOK", null, "newalias");

        $this->_checkUserHasNotChanged($user, "passowordOK", null, "newalias");
    }

    /**
     * no alias => keep old ones
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testUpdateUserNoAlias()
    {
        $login = "login";
        $user = array('login'    => $login,
                      'password' => "geqgeagae",
                      'email'    => "test@test.com",
                      'alias'    => "alias");

        Piwik_UsersManager_API::getInstance()->addUser($user['login'], $user['password'], $user['email'], $user['alias']);

        Piwik_UsersManager_API::getInstance()->updateUser($login, "passowordOK", "email@geaga.com");

        $this->_checkUserHasNotChanged($user, "passowordOK", "email@geaga.com");
    }

    /**
     * check to modify as the user
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testUpdateUserIAmTheUser()
    {
        FakeAccess::$identity = 'login';
        $this->testUpdateUserNoEmailNoAlias();
    }

    /**
     * check to modify as being another user => exception
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testUpdateUserIAmNotTheUser()
    {
        try {
            FakeAccess::$identity = 'login2';
            FakeAccess::$superUser = false;
            $this->testUpdateUserNoEmailNoAlias();
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * normal case, reused in other tests
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testUpdateUser()
    {
        $login = "login";
        $user = array('login'    => $login,
                      'password' => "geqgeagae",
                      'email'    => "test@test.com",
                      'alias'    => "alias");

        Piwik_UsersManager_API::getInstance()->addUser($user['login'], $user['password'], $user['email'], $user['alias']);

        Piwik_UsersManager_API::getInstance()->updateUser($login, "passowordOK", "email@geaga.com", "NEW ALIAS");

        $this->_checkUserHasNotChanged($user, "passowordOK", "email@geaga.com", "NEW ALIAS");
    }

    /**
     * test getUserByEmail invalid mail
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testGetUserByEmailInvalidMail()
    {
        try {
            $userByMail = Piwik_UsersManager_API::getInstance()->getUserByEmail('email@test.com');
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * test getUserByEmail
     *
     * @group Plugins
     * @group UsersManager
     */
    public function testGetUserByEmail()
    {
        $user = array('login'    => "login",
                      'password' => "geqgeagae",
                      'email'    => "test@test.com",
                      'alias'    => "alias");

        Piwik_UsersManager_API::getInstance()->addUser($user['login'], $user['password'], $user['email'], $user['alias']);

        $userByMail = Piwik_UsersManager_API::getInstance()->getUserByEmail($user['email']);

        $this->assertEquals($user['login'], $userByMail['login']);
        $this->assertEquals($user['email'], $userByMail['email']);
        $this->assertEquals($user['alias'], $userByMail['alias']);
    }
    
    /**
     * @group Plugins
     * @group UsersManager
     */
    public function testGetUserPreferenceDefault()
    {
        $defaultReportPref = Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT;
        $defaultReportDatePref = Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT_DATE;
        
        $this->assertEquals(1,
            Piwik_UsersManager_API::getInstance()->getUserPreference('someUser', $defaultReportPref));
        $this->assertEquals('yesterday',
            Piwik_UsersManager_API::getInstance()->getUserPreference('someUser', $defaultReportDatePref));
    }
}
