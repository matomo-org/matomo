<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

require_once PIWIK_PATH_TEST_TO_ROOT . '/tests/core/Database.test.php';

class Test_Piwik_UsersManager extends Test_Database
{
    function setUp()
    {
    	parent::setUp();
    	
		// setup the access layer
    	$pseudoMockAccess = new FakeAccess;
		FakeAccess::setIdSitesView( array(1,2));
		FakeAccess::setIdSitesAdmin( array(3,4));
		
		//finally we set the user as a super user by default
		FakeAccess::$superUser = true;
		Zend_Registry::set('access', $pseudoMockAccess);
		
		// we make sure the tests don't depend on the config file content
		Zend_Registry::get('config')->superuser = array(
			'login'=>'superusertest',
			'password'=>'passwordsuperusertest',
			'email'=>'superuser@example.com'
		);
    }

	private function _flatten($sitesAccess)
	{
		$result = array();;

		foreach($sitesAccess as $siteAccess)
		{
			$result[ $siteAccess['site'] ] = $siteAccess['access'];
		}
		return $result;
	}

    private function _checkUserHasNotChanged($user, $newPassword, $newEmail = null, $newAlias= null)
    {
    	if(is_null($newEmail))
    	{
    		$newEmail = $user['email'];
    	}
    	if(is_null($newAlias))
    	{
    		$newAlias = $user['alias'];
    	}
    	$userAfter = Piwik_UsersManager_API::getInstance()->getUser($user["login"]);
    	unset($userAfter['date_registered']);
    	
    	// we now compute what the token auth should be, it should always be a hash of the login and the current password
    	// if the password has changed then the token_auth has changed!
    	$user['token_auth']= Piwik_UsersManager_API::getInstance()->getTokenAuth($user["login"], md5($newPassword) );
    	
    	$user['password']=md5($newPassword);
    	$user['email']=$newEmail;
    	$user['alias']=$newAlias;
    	$this->assertEqual($user,$userAfter);
    }
    
    function test_all_superUserIncluded()
    {
    	Zend_Registry::get('config')->superuser = array(
			'login'=>'superusertest',
			'password'=>'passwordsuperusertest',
			'email'=>'superuser@example.com'
		);
		
    	$user = array( 'login'=>'user',
    					'password'=>"geqgeagae",
    					'email'=>"test@test.com",
    					'alias'=>"alias");
    	Piwik_UsersManager_API::getInstance()->addUser($user['login'],$user['password'] ,$user['email'] ,$user['alias'] );
    
    	try {
	    	Piwik_UsersManager_API::getInstance()->addUser('superusertest','te','fake@fale.co','ega');
	    	$this->fail();
    	} catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionSuperUser)", $expected->getMessage());
    	}
    	try {
	    	Piwik_UsersManager_API::getInstance()->updateUser('superusertest','te','fake@fale.co','ega');
	    	$this->fail();
    	} catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionSuperUser)", $expected->getMessage());
    	}
    	try {
	    	Piwik_UsersManager_API::getInstance()->deleteUser('superusertest','te','fake@fale.co','ega');
	    	$this->fail();
    	} catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionSuperUser)", $expected->getMessage());
    	}
    	try {
	    	Piwik_UsersManager_API::getInstance()->deleteUser('superusertest','te','fake@fale.co','ega');
	    	$this->fail();
    	} catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionSuperUser)", $expected->getMessage());
    	}
    	$this->pass();	
    }
    /**
     * bad password => exception
     */
    function test_updateUser_badpasswd()
    {
    	$login="login";
    	$user = array('login'=>$login,
    					'password'=>"geqgeagae",
    					'email'=>"test@test.com",
    					'alias'=>"alias");
    					
    	Piwik_UsersManager_API::getInstance()->addUser($user['login'],$user['password'] ,$user['email'] ,$user['alias'] );
		
		
    	try {
    		Piwik_UsersManager_API::getInstance()->updateUser(  $login, "pas");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionInvalidPassword)", $expected->getMessage());
    		
    		$this->_checkUserHasNotChanged($user,$user['password']);
            return;
        }
        $this->fail("Exception not raised.");
        
    }
//} class test{
    
    /**
     * wrong login / integer => exception
     */
    function test_addUser_wrongLogin1()
    {
    	try {
    		Piwik_UsersManager_API::getInstance()->addUser(12, "password", "email@email.com", "alias");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionInvalidLogin)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * wrong login / too short => exception
     */
    function test_addUser_wrongLogin2()
    {
    	try {
    		Piwik_UsersManager_API::getInstance()->addUser("gegag'ggea'", "password", "email@email.com", "alias");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionInvalidLogin)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }
    /**
     * wrong login / too long => exception
     */
    function test_addUser_wrongLogin3()
    {
    	try {
    		Piwik_UsersManager_API::getInstance()->addUser("gegag11gge@", "password", "email@email.com", "alias");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionInvalidLogin)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * wrong login / bad characters => exception
     */
    function test_addUser_wrongLogin4()
    {
    	try {
    		Piwik_UsersManager_API::getInstance()->addUser("geg'ag11gge@", "password", "email@email.com", "alias");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionInvalidLogin)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * existing login => exception
     */
    function test_addUser_existingLogin()
    {
    	try {
    		Piwik_UsersManager_API::getInstance()->addUser("test", "password", "email@email.com", "alias");
    		Piwik_UsersManager_API::getInstance()->addUser("test", "password2", "em2ail@email.com", "al2ias");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionLoginExists)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    	
    }
    
    /**
     * too short -> exception
     */
    function test_addUser_wrongPassword1()
    {
    	try {
    		Piwik_UsersManager_API::getInstance()->addUser("geggeqgeqag", "pas", "email@email.com", "alias");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionInvalidPassword)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }
    /**
     * too long -> exception
     */
    function test_addUser_wrongPassword2()
    {
    	try {
    		Piwik_UsersManager_API::getInstance()->addUser("ghqgeggg", "gegageqqqqqqqgeqgqeg84897897897897g122", "email@email.com", "alias");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionInvalidPassword)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * empty -> exception
     */
    function test_addUser_wrongPassword3()
    {
    	try {
    		Piwik_UsersManager_API::getInstance()->addUser("geggeqgeqag", "", "email@email.com", "alias");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionInvalidPassword)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }
    /**
     * wrong email => exception
     */
    function test_addUser_wrongEmail1()
    {
    	try {
    		Piwik_UsersManager_API::getInstance()->addUser("geggeqgeqag", "geqgeagae", "ema'il@email.com", "alias");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(mail)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * wrong email => exception
     */
    function test_addUser_wrongEmail2()
    {
    	try {
    		Piwik_UsersManager_API::getInstance()->addUser("geggeqgeqag", "geqgeagae", "@email.com", "alias");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(mail)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }
    /**
     * wrong email => exception
     */
    function test_addUser_wrongEmail3()
    {
    	try {
    		Piwik_UsersManager_API::getInstance()->addUser("geggeqgeqag", "geqgeagae", "email@.com", "alias");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(mail)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * wrong email => exception
     */
    function test_addUser_wrongEmail4()
    {
    	try {
    		Piwik_UsersManager_API::getInstance()->addUser("geggeqgeqag", "geqgeagae", "email@4.", "alias");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(mail)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * empty email => exception
     */
    function test_addUser_emptyEmail()
    {
    	
    	try {
    		Piwik_UsersManager_API::getInstance()->addUser("geggeqgeqag", "geqgeagae", "", "alias");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(mail)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * empty alias => use login
     */
    function test_addUser_emptyAlias()
    {
    	$login ="geggeqgeqag";
		Piwik_UsersManager_API::getInstance()->addUser($login, "geqgeagae", "mgeagi@geq.com", "");
    	$user = Piwik_UsersManager_API::getInstance()->getUser($login);
    	$this->assertEqual($user['alias'], $login);
    	$this->assertEqual($user['login'], $login);
    	
    }
    /**
     * no alias => use login
     */
    function test_addUser_noAliasSpecified()
    {
    	$login ="geggeqg455eqag";
		Piwik_UsersManager_API::getInstance()->addUser($login, "geqgeagae", "mgeagi@geq.com");
    	$user = Piwik_UsersManager_API::getInstance()->getUser($login);
    	$this->assertEqual($user['alias'], $login);
    	$this->assertEqual($user['login'], $login);
    	
    }
    
    /**
     * normal test case
     * 
     */
    function test_addUser()
    {
    	$login ="geggeq55eqag";
    	$password = "mypassword";
    	$email = "mgeag4544i@geq.com";
    	$alias = "her is my alias )(&|\" '£%*(&%+))";
		
    	$time = time();
		Piwik_UsersManager_API::getInstance()->addUser($login, $password, $email, $alias);
    	$user = Piwik_UsersManager_API::getInstance()->getUser($login);
		
	    // check that the date registered is correct
		$this->assertTrue( strtotime($user['date_registered']) ==  $time , 
				"the date_registered ".strtotime($user['date_registered'])." is different from the time() ". time());
		$this->assertTrue($user['date_registered'] <= time() );
		
	    // check that token is 32 chars
		$this->assertEqual(strlen($user['password']), 32);
		
	    // that the password has been md5
		$this->assertEqual($user['token_auth'],  md5($login.md5($password)));
		
	    // check that all fields are the same
		$this->assertEqual($user['login'], $login);
		$this->assertEqual($user['password'], md5($password));
		$this->assertEqual($user['email'], $email);
		$this->assertEqual($user['alias'], $alias);
		
    }
    
    /**
     * user doesnt exist => exception
     */
    function test_deleteUser_doesntExist()
    {	
		Piwik_UsersManager_API::getInstance()->addUser("geggeqgeqag", "geqgeagae", "test@test.com", "alias");
		
    	try {
			Piwik_UsersManager_API::getInstance()->deleteUser("geggeqggnew");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionDeleteDoesNotExist)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * empty name, doesnt exists =>exception
     */
    function test_deleteUser_emptyUser()
    {
    	try {
			Piwik_UsersManager_API::getInstance()->deleteUser("");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionDeleteDoesNotExist)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * null user,, doesnt exists =>exception
     */
    function test_deleteUser_nullUser()
    {
    	try {
			Piwik_UsersManager_API::getInstance()->deleteUser(null);
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionDeleteDoesNotExist)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * normal case, user deleted
     */
    function test_deleteUser()
    {
    	
    	//create the 3 websites
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site1",array("http://piwik.net","http://piwik.com/test/"));
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site2",array("http://piwik.com/test/"));
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site3",array("http://piwik.org"));
    	
    	//add user and set some rights
    	Piwik_UsersManager_API::getInstance()->addUser("geggeqgeqag", "geqgeagae", "test@test.com", "alias");
    	Piwik_UsersManager_API::getInstance()->setUserAccess("geggeqgeqag", "view", array(1,2));
    	Piwik_UsersManager_API::getInstance()->setUserAccess("geggeqgeqag", "admin", array(1,3));
		
		// check rights are set
		$this->assertNotEqual(Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("geggeqgeqag"), array());
		
		// delete the user
		Piwik_UsersManager_API::getInstance()->deleteUser("geggeqgeqag");
		
		// try to get it, it should raise an exception
		try {
    		$user = Piwik_UsersManager_API::getInstance()->getUser("geggeqgeqag");
	        $this->fail("Exception not raised.");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionUserDoesNotExist)", $expected->getMessage());
        }
        // add the same user
        Piwik_UsersManager_API::getInstance()->addUser("geggeqgeqag", "geqgeagae", "test@test.com", "alias");
		
		//checks access have been deleted
		//to do so we recreate the same user login and check if the rights are still there
		$this->assertEqual(Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("geggeqgeqag"), array());
    
    }
    
	
    /**
     * no user => exception
     */
    function test_getUser_noUser()
    {
    	// try to get it, it should raise an exception
		try {
    		$user = Piwik_UsersManager_API::getInstance()->getUser("geggeqgeqag");
	        $this->fail("Exception not raised.");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionUserDoesNotExist)", $expected->getMessage());
        }
    }
	
    /**
     * normal case
     */
    function test_getUser()
    {
    	$login ="geggeq55eqag";
    	$password = "mypassword";
    	$email = "mgeag4544i@geq.com";
    	$alias = "";
		
		Piwik_UsersManager_API::getInstance()->addUser($login, $password, $email, $alias);
    	$user = Piwik_UsersManager_API::getInstance()->getUser($login);
						
	    // check that all fields are the same
		$this->assertEqual($user['login'], $login);
		$this->assertIsA($user['password'], 'string');
		$this->assertIsA($user['date_registered'], 'string');
		$this->assertEqual($user['email'], $email);
		
		//alias shouldnt be empty even if no alias specified
		$this->assertTrue( strlen($user['alias']) > 0);
    }
    
    
    /**
     * no user => empty array
     */
    function test_getUsers_noUser()
    {
    	$this->assertEqual(Piwik_UsersManager_API::getInstance()->getUsers(), array());
    }
    
    /**
     * normal case
     */
    function test_getUsers()
    {
    	
    	Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
    	Piwik_UsersManager_API::getInstance()->addUser("geggeqge632ge56a4qag", "geqgegeagae", "tesggt@tesgt.com", "alias");
    	Piwik_UsersManager_API::getInstance()->addUser("geggeqgeqagqegg", "geqgeaggggae", "tesgggt@tesgt.com");
    	
    	   $users = Piwik_UsersManager_API::getInstance()->getUsers();
    	   foreach($users as &$user)
    	   {
    	   	unset($user['token_auth']);
    	   	unset($user['date_registered']);
    	   }
    	$this->assertEqual($users, 
    		array(
    			array('login' => "gegg4564eqgeqag", 'password' => md5("geqgegagae"),    'alias' => "alias", 'email' => "tegst@tesgt.com"),
    			array('login' => "geggeqge632ge56a4qag",  'password' => md5("geqgegeagae"),'alias' =>  "alias",  'email' => "tesggt@tesgt.com"),
    			array('login' => "geggeqgeqagqegg",  'password' => md5("geqgeaggggae"),  'alias' => 'geggeqgeqagqegg','email' => "tesgggt@tesgt.com"),
    		)
    	);
    	
    }
    
    /**
     * normal case
     */
    function test_getUsersLogin()
    {
    	
    	Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
    	Piwik_UsersManager_API::getInstance()->addUser("geggeqge632ge56a4qag", "geqgegeagae", "tesggt@tesgt.com", "alias");
    	Piwik_UsersManager_API::getInstance()->addUser("geggeqgeqagqegg", "geqgeaggggae", "tesgggt@tesgt.com");
    	
    	$logins = Piwik_UsersManager_API::getInstance()->getUsersLogin();
    	
    	$this->assertEqual($logins, 
    		array(  "gegg4564eqgeqag", "geggeqge632ge56a4qag",  "geggeqgeqagqegg")
    		); 
    }
    
    
    /**
     * no login => exception
     */
    function test_setUserAccess_noLogin()
    {
    	// try to get it, it should raise an exception
		try {
    		Piwik_UsersManager_API::getInstance()->setUserAccess("nologin", "view", 1);
	        $this->fail("Exception not raised.");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionUserDoesNotExist)", $expected->getMessage());
        }
    	
    }
    
    /**
     * wrong access specified  => exception
     */
    function test_setUserAccess_wrongAccess()
    {
    	Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
    	
    	// try to get it, it should raise an exception
		try {
    		Piwik_UsersManager_API::getInstance()->setUserAccess("gegg4564eqgeqag", "viewnotknown", 1);
	        $this->fail("Exception not raised.");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionAccessValues)", $expected->getMessage());
        }
    }
    
    /**
     * idsites = all => apply access to all websites with admin access
     */
    function test_setUserAccess_idsitesIsAll()
    {
    	Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
    	
    	FakeAccess::$superUser = false;
    	
    	Piwik_UsersManager_API::getInstance()->setUserAccess("gegg4564eqgeqag", "view", "all");
    	
    	FakeAccess::$superUser = true;
    	$access = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("gegg4564eqgeqag");
	$access = $this->_flatten($access);
    	
    	FakeAccess::$superUser = false;
    	$this->assertEqual( array_keys($access), FakeAccess::getSitesIdWithAdminAccess());
    	
    	// we want to test the case for which we have actually set some rights
    	// if this is not OK then change the setUp method and add some admin rights for some websites
    	$this->assertTrue( count(array_keys($access)) > 0);

    }
    
    /**
     * idsites = all AND user is superuser=> apply access to all websites
     */
    function test_setUserAccess_idsitesIsAllSuperuser()
    {
    	FakeAccess::$superUser = true;
    	
    	$id1=Piwik_SitesManager_API::getInstance()->addSite("test",array("http://piwik.net","http://piwik.com/test/"));
		$id2=Piwik_SitesManager_API::getInstance()->addSite("test2",array("http://piwik.net","http://piwik.com/test/"));
    	$id3=Piwik_SitesManager_API::getInstance()->addSite("test3",array("http://piwik.net","http://piwik.com/test/"));
    	$id4=Piwik_SitesManager_API::getInstance()->addSite("test4",array("http://piwik.net","http://piwik.com/test/"));
    	$id5=Piwik_SitesManager_API::getInstance()->addSite("test5",array("http://piwik.net","http://piwik.com/test/"));
    	
		Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
    	Piwik_UsersManager_API::getInstance()->setUserAccess("gegg4564eqgeqag", "view", "all");
    	
    	$access = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("gegg4564eqgeqag");
	$access = $this->_flatten($access);
    	$this->assertEqual( array($id1,$id2,$id3,$id4,$id5), array_keys($access));
    	
    }
    
    /**
     * idsites is empty => no acccess set
     */
    function test_setUserAccess_idsitesEmpty()
    {
    	Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
    	
    	Piwik_UsersManager_API::getInstance()->setUserAccess("gegg4564eqgeqag", "view", array());
    	
    	$access = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("gegg4564eqgeqag");
	$access = $this->_flatten($access);
    	$this->assertEqual( array(), $access);
    	
    }
    
    /**
     * normal case, access set for only one site
     */
    function test_setUserAccess_idsitesOneSite()
    {
    	Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
    	$id1=Piwik_SitesManager_API::getInstance()->addSite("test",array("http://piwik.net","http://piwik.com/test/"));
		
    	Piwik_UsersManager_API::getInstance()->setUserAccess("gegg4564eqgeqag", "view", array(1));
    	
    	$access = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("gegg4564eqgeqag");
	$access = $this->_flatten($access);
    	$this->assertEqual( array(1), array_keys($access));
    }

    /**
     * normal case, access set for multiple sites
     */
    function test_setUserAccess_idsitesMultipleSites()
    {
    	
    	Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
    	$id1=Piwik_SitesManager_API::getInstance()->addSite("test",array("http://piwik.net","http://piwik.com/test/"));
		$id2=Piwik_SitesManager_API::getInstance()->addSite("test",array("http://piwik.net","http://piwik.com/test/"));
		$id3=Piwik_SitesManager_API::getInstance()->addSite("test",array("http://piwik.net","http://piwik.com/test/"));
		
    	Piwik_UsersManager_API::getInstance()->setUserAccess("gegg4564eqgeqag", "view", array($id1,$id3));
    	
    	$access = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("gegg4564eqgeqag");
	$access = $this->_flatten($access);
    	$this->assertEqual( array($id1,$id3), array_keys($access));
    }
    /**
     * normal case, string idSites comma separated access set for multiple sites
     */
    function test_setUserAccess_withIdSitesIsStringCommaSeparated()
    {
    	
    	Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
    	$id1=Piwik_SitesManager_API::getInstance()->addSite("test",array("http://piwik.net","http://piwik.com/test/"));
		$id2=Piwik_SitesManager_API::getInstance()->addSite("test",array("http://piwik.net","http://piwik.com/test/"));
		$id3=Piwik_SitesManager_API::getInstance()->addSite("test",array("http://piwik.net","http://piwik.com/test/"));
		
    	Piwik_UsersManager_API::getInstance()->setUserAccess("gegg4564eqgeqag", "view", "1,3");
    	
    	$access = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("gegg4564eqgeqag");
	$access = $this->_flatten($access);
    	$this->assertEqual( array($id1,$id3), array_keys($access));
    }
    
    
    /**
     * normal case,  set different acccess to different websites for one user
     */
    function test_setUserAccess_multipleCallDistinctAccessSameUser()
    {
    	Piwik_UsersManager_API::getInstance()->addUser("gegg4564eqgeqag", "geqgegagae", "tegst@tesgt.com", "alias");
    	$id1=Piwik_SitesManager_API::getInstance()->addSite("test",array("http://piwik.net","http://piwik.com/test/"));
		$id2=Piwik_SitesManager_API::getInstance()->addSite("test",array("http://piwik.net","http://piwik.com/test/"));
		
    	Piwik_UsersManager_API::getInstance()->setUserAccess("gegg4564eqgeqag", "view", array($id1));
    	Piwik_UsersManager_API::getInstance()->setUserAccess("gegg4564eqgeqag", "admin", array($id2));
    	
    	$access = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("gegg4564eqgeqag");
	$access = $this->_flatten($access);
    	$this->assertEqual( array($id1=>'view',$id2=>'admin'), $access);
    }
    
    /**
     * normal case, set different access to different websites for multiple users
     */
    function test_setUserAccess_multipleCallDistinctAccessMultipleUser()
    {
    	Piwik_UsersManager_API::getInstance()->addUser("user1", "geqgegagae", "tegst@tesgt.com", "alias");
    	Piwik_UsersManager_API::getInstance()->addUser("user2", "geqgegagae", "tegst2@tesgt.com", "alias");
    	$id1=Piwik_SitesManager_API::getInstance()->addSite("test1",array("http://piwik.net","http://piwik.com/test/"));
		$id2=Piwik_SitesManager_API::getInstance()->addSite("test2",array("http://piwik.net","http://piwik.com/test/"));
		$id3=Piwik_SitesManager_API::getInstance()->addSite("test2",array("http://piwik.net","http://piwik.com/test/"));
		
    	Piwik_UsersManager_API::getInstance()->setUserAccess("user1", "view", array($id1,$id2));
    	Piwik_UsersManager_API::getInstance()->setUserAccess("user2", "admin", array($id1));
    	Piwik_UsersManager_API::getInstance()->setUserAccess("user2", "view", array($id3));
    	
    	$access1 = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("user1");
	$access1 = $this->_flatten($access1);
    	$access2 = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("user2");
	$access2 = $this->_flatten($access2);
    	$wanted1 = array( $id1 => 'view', $id2 => 'view', );
    	$wanted2 = array( $id1 => 'admin', $id3 => 'view' );
    	
    	$this->assertEqual($access1, $wanted1);
    	$this->assertEqual($access2, $wanted2);
    	
    	
    	$access1 = Piwik_UsersManager_API::getInstance()->getUsersAccessFromSite($id1);
    	$access2 = Piwik_UsersManager_API::getInstance()->getUsersAccessFromSite($id2);
    	$access3 = Piwik_UsersManager_API::getInstance()->getUsersAccessFromSite($id3);
    	$wanted1 = array( 'user1' => 'view', 'user2' => 'admin', );
    	$wanted2 = array( 'user1' => 'view' );
    	$wanted3 = array( 'user2' => 'view' );
    	
    	$this->assertEqual($access1, $wanted1);
    	$this->assertEqual($access2, $wanted2);
    	$this->assertEqual($access3, $wanted3);
    	
    	$access1 = Piwik_UsersManager_API::getInstance()->getUsersSitesFromAccess('view');
    	$access2 = Piwik_UsersManager_API::getInstance()->getUsersSitesFromAccess('admin');
    	$wanted1 = array( 'user1' => array($id1,$id2), 'user2' => array($id3) );
    	$wanted2 = array( 'user2' => array($id1) );
    	
    	$this->assertEqual($access1, $wanted1);
    	$this->assertEqual($access2, $wanted2);
 
    }
    
    /**
     * we set access for one user for one site several times and check that it is updated
     */
    function test_setUserAccess_multipleCallOverwriteSingleUserOneSite()
    {
    	Piwik_UsersManager_API::getInstance()->addUser("user1", "geqgegagae", "tegst@tesgt.com", "alias");
    	
    	$id1=Piwik_SitesManager_API::getInstance()->addSite("test1",array("http://piwik.net","http://piwik.com/test/"));
		$id2=Piwik_SitesManager_API::getInstance()->addSite("test2",array("http://piwik.net","http://piwik.com/test/"));
		
    	Piwik_UsersManager_API::getInstance()->setUserAccess("user1", "view", array($id1,$id2));
    	Piwik_UsersManager_API::getInstance()->setUserAccess("user1", "admin", array($id1));
    	
    	$access1 = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("user1");
	$access1 = $this->_flatten($access1);
    	$wanted1 = array( $id1 => 'admin', $id2 => 'view', );
    	
    	$this->assertEqual($access1, $wanted1);
    	
    }
    
    
    /**
     * wrong user =>exception
     */
    function test_getSitesAccessFromUser_wrongUser()
    {
    	try {
    		$access1 = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser("user1");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionUserDoesNotExist)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     *wrong idsite =>exception
     */
    function test_getUsersAccessFromSite_wrongSite()
    {
    	try {
    		$access1 = Piwik_UsersManager_API::getInstance()->getUsersAccessFromSite(1);
    	}
    	catch (Exception $expected) {
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * wrong access =>exception
     */
    function test_getUsersSitesFromAccess_wrongSite()
    {
    	try {
    		$access1 = Piwik_UsersManager_API::getInstance()->getUsersSitesFromAccess('unknown');
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionAccessValues)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    
    /**
     * non existing login => exception
     */
    function test_updateUser_wrongLogin()
    {
    	try {
    		Piwik_UsersManager_API::getInstance()->updateUser(  "lolgin", "password");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(UsersManager_ExceptionUserDoesNotExist)", $expected->getMessage());
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    
    /**
     * no email no alias => keep old ones
     */
    function test_updateUser_noemailnoalias()
    {
    	$login="login";
    	$user = array('login'=>$login,
    					'password'=>"geqgeagae",
    					'email'=>"test@test.com",
    					'alias'=>"alias");
    					
    	Piwik_UsersManager_API::getInstance()->addUser($user['login'],$user['password'] ,$user['email'] ,$user['alias'] );
		
    	Piwik_UsersManager_API::getInstance()->updateUser(  $login, "passowordOK");
    	
    	$this->_checkUserHasNotChanged($user, "passowordOK");
    }
    
    /**
     *no email => keep old ones
     */
    function test_updateUser_noemail()
    {
    	
    	$login="login";
    	$user = array('login'=>$login,
    					'password'=>"geqgeagae",
    					'email'=>"test@test.com",
    					'alias'=>"alias");
    					
    	Piwik_UsersManager_API::getInstance()->addUser($user['login'],$user['password'] ,$user['email'] ,$user['alias'] );
		
    	Piwik_UsersManager_API::getInstance()->updateUser(  $login, "passowordOK", null, "newalias");
    	
    	$this->_checkUserHasNotChanged($user, "passowordOK", null, "newalias");
    }
    
    /**
     * no alias => keep old ones
     */
    function test_updateUser_noalias()
    {
    	
    	$login="login";
    	$user = array('login'=>$login,
    					'password'=>"geqgeagae",
    					'email'=>"test@test.com",
    					'alias'=>"alias");
    					
    	Piwik_UsersManager_API::getInstance()->addUser($user['login'],$user['password'] ,$user['email'] ,$user['alias'] );
		
    	Piwik_UsersManager_API::getInstance()->updateUser(  $login, "passowordOK", "email@geaga.com");
    	
    	$this->_checkUserHasNotChanged($user, "passowordOK", "email@geaga.com");
    }
    
    /**
     * check to modify as the user
     */
    function test_updateUser_IAmTheUser()
    {
    	FakeAccess::$identity = 'login';
    	$this->test_updateUser_noemailnoalias();
    	
    }
    /**
     * check to modify as being another user => exception
     */
    function test_updateUser_IAmNOTTheUser()
    {
    	
    	FakeAccess::$identity = 'login2';
    	FakeAccess::$superUser = false;
    	try{
    		$this->test_updateUser_noemailnoalias();
    		
    	}catch (Exception $expected) {
    		return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * normal case, reused in other tests
     */
    function test_updateUser()
    {
    	
    	$login="login";
    	$user = array('login'=>$login,
    					'password'=>"geqgeagae",
    					'email'=>"test@test.com",
    					'alias'=>"alias");
    					
    	Piwik_UsersManager_API::getInstance()->addUser($user['login'],$user['password'] ,$user['email'] ,$user['alias'] );
		
    	Piwik_UsersManager_API::getInstance()->updateUser(  $login, "passowordOK", "email@geaga.com", "NEW ALIAS");
    	
    	$this->_checkUserHasNotChanged($user, "passowordOK", "email@geaga.com", "NEW ALIAS");
    }
    
}
