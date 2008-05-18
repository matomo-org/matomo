<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', getcwd().'/../../');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT."tests/config_test.php";
}
require_once "Database.test.php";

require_once 'SitesManager/API.php';

class Test_Piwik_SitesManager extends Test_Database
{
    public function setUp()
    {
    	parent::setUp();

		// setup the access layer
    	$pseudoMockAccess = new FakeAccess;
		FakeAccess::$superUser = true;
		Zend_Registry::set('access', $pseudoMockAccess);
    }
    
    /**
     * empty name -> exception
     */
	public function test_addSite_emptyName()
    {
    	try {
    		Piwik_SitesManager_API::addSite("",array("http://piwik.net"));
    	}
    	catch (Exception $expected) {
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * no urls -> exception
     */
    public function test_addSite_noUrls()
    {
    	try {
    		Piwik_SitesManager_API::addSite("name",array());
    	}
    	catch (Exception $expected) {
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * wrong urls -> exception
     */
    public function test_addSite_wrongUrls1()
    {
    	try {
    		Piwik_SitesManager_API::addSite("name",array(""));
    	}
    	catch (Exception $expected) {
            return;
        }
        $this->fail("Exception not raised.");
    }
    /**
     * wrong urls -> exception
     */
    public function test_addSite_wrongUrls2()
    {
    	try {
    		Piwik_SitesManager_API::addSite("name","");
    	}
    	catch (Exception $expected) {
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * wrong urls -> exception
     */
    public function test_addSite_wrongUrls3()
    {
    	try {
    		Piwik_SitesManager_API::addSite("name","httpww://piwik.net");
    	}
    	catch (Exception $expected) {
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * wrong urls -> exception
     */
    public function test_addSite_wrongUrls4()
    {
    	try {
    		Piwik_SitesManager_API::addSite("name","httpww://piwik.net/gqg~#");
    	}
    	catch (Exception $expected) {
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * one url -> one main_url and nothing inserted as alias urls
     */
    public function test_addSite_oneUrl()
    {
    	$url = "http://piwik.net/";
    	$urlOK = "http://piwik.net";
    	$idsite = Piwik_SitesManager_API::addSite("name",$url);
    	$this->assertIsA( $idsite,'int');
    	
    	$siteInfo = Piwik_SitesManager_API::getSiteFromId($idsite);
    	$this->assertEqual($siteInfo['main_url'], $urlOK);
    	
    	$siteUrls = Piwik_SitesManager_API::getSiteUrlsFromId($idsite);
    	$this->assertTrue(count($siteUrls)===1);
    }
    
    /**
     * several urls -> one main_url and others as alias urls
     */
    public function test_addSite_severalUrls()
    {
    	$urls = array("http://piwik.net/","http://piwik.com","https://piwik.net/test/");
    	$urlsOK = array("http://piwik.net","http://piwik.com","https://piwik.net/test");
    	$idsite = Piwik_SitesManager_API::addSite("super website",$urls);
    	$this->assertIsA( $idsite,'int');
    	
    	$siteInfo = Piwik_SitesManager_API::getSiteFromId($idsite);
    	$this->assertEqual($siteInfo['main_url'], $urlsOK[0]);
    	
    	$siteUrls = Piwik_SitesManager_API::getSiteUrlsFromId($idsite);
    	$this->assertEqual($siteUrls, $urlsOK);
    }
    
    /**
     * strange name
     */
    public function test_addSite_strangeName()
    {
    	$name = "supertest(); ~@@()''!£\$'%%^'!£";
    	$idsite = Piwik_SitesManager_API::addSite($name,"http://piwik.net");
    	$this->assertIsA( $idsite,'int');
    	
    	$siteInfo = Piwik_SitesManager_API::getSiteFromId($idsite);
    	$this->assertEqual($siteInfo['name'], $name);
    	
    }
    /**
     * normal case
     */
    public function test_addSite()
    {
    	$name = "website ";
    	$idsite = Piwik_SitesManager_API::addSite($name,array("http://piwik.net","http://piwik.com/test/"));
    	$this->assertIsA( $idsite,'int');
    	
    	$siteInfo = Piwik_SitesManager_API::getSiteFromId($idsite);
    	$this->assertEqual($siteInfo['name'], $name);
    	$this->assertEqual($siteInfo['main_url'], "http://piwik.net");
    	
    	$siteUrls = Piwik_SitesManager_API::getSiteUrlsFromId($idsite);
    	$this->assertEqual($siteUrls, array("http://piwik.net","http://piwik.com/test"));
    	   	
    	return $idsite;
    }
    
    /**
     * no duplicate -> all the urls are saved
     */
    public function test_addSiteUrls_noDuplicate()
    {
    	$idsite = $this->test_addSite();
    	
    	$siteUrlsBefore = Piwik_SitesManager_API::getSiteUrlsFromId($idsite);
    	
    	$toAdd = array(	"http://piwik1.net",
						"http://piwik2.net", 
						"http://piwik3.net/test/", 
						"http://localhost/test", 
						"http://localho5.st/test", 
						"http://l42578gqege.f4", 
						"http://super.com/test/test/atqata675675/te"
						);
    	$toAddValid = array("http://piwik1.net",
							"http://piwik2.net", 
							"http://piwik3.net/test",
							"http://localhost/test", 
							"http://localho5.st/test", 
							"http://l42578gqege.f4", 
							"http://super.com/test/test/atqata675675/te");
    	
    	$insertedUrls = Piwik_SitesManager_API::addSiteAliasUrls($idsite, $toAdd);
    	$this->assertEqual($insertedUrls, count($toAdd));
    	
    	$siteUrlsAfter = Piwik_SitesManager_API::getSiteUrlsFromId($idsite);
    	
    	$shouldHave = array_merge($siteUrlsBefore, $toAddValid);
    	sort($shouldHave);
    	
    	sort($siteUrlsAfter);
    	
    	$this->assertEqual($shouldHave, $siteUrlsAfter);
    }
    
    /**
     * duplicate -> don't save the already existing URLs
     */
    public function test_addSiteUrls_duplicate()
    {    	
    	$idsite = $this->test_addSite();
    	
    	$siteUrlsBefore = Piwik_SitesManager_API::getSiteUrlsFromId($idsite);
    	
    	$toAdd = array_merge($siteUrlsBefore, array("http://piwik1.net","http://piwik2.net"));
    	
    	$insertedUrls = Piwik_SitesManager_API::addSiteAliasUrls($idsite, $toAdd);
    	$this->assertEqual($insertedUrls, count($toAdd) - count($siteUrlsBefore));
    	
    	$siteUrlsAfter = Piwik_SitesManager_API::getSiteUrlsFromId($idsite);
    	
    	$shouldHave = $toAdd;
    	sort($shouldHave);
    	
    	sort($siteUrlsAfter);
    	
    	$this->assertEqual($shouldHave, $siteUrlsAfter);
    }
    
    /**
     * case empty array => nothing happens
     */
    public function test_addSiteUrls_noUrlsToAdd1()
    {
    	$idsite = $this->test_addSite();
    	
    	$siteUrlsBefore = Piwik_SitesManager_API::getSiteUrlsFromId($idsite);
    	
    	$toAdd = array();
    	
    	$insertedUrls = Piwik_SitesManager_API::addSiteAliasUrls($idsite, $toAdd);
    	$this->assertEqual($insertedUrls, count($toAdd));
    	
    	$siteUrlsAfter = Piwik_SitesManager_API::getSiteUrlsFromId($idsite);
    	
    	$shouldHave = $siteUrlsBefore;
    	sort($shouldHave);
    	
    	sort($siteUrlsAfter);
    	
    	$this->assertEqual($shouldHave, $siteUrlsAfter);
    }
    
    /**
     * case array only duplicate => nothing happens
     */
    public function test_addSiteUrls_noUrlsToAdd2()
    {
    	$idsite = $this->test_addSite();
    	
    	$siteUrlsBefore = Piwik_SitesManager_API::getSiteUrlsFromId($idsite);
    	
    	$toAdd = $siteUrlsBefore;
    	
    	$insertedUrls = Piwik_SitesManager_API::addSiteAliasUrls($idsite, $toAdd);
    	$this->assertEqual($insertedUrls, 0);
    	
    	$siteUrlsAfter = Piwik_SitesManager_API::getSiteUrlsFromId($idsite);
    	
    	$shouldHave = $siteUrlsBefore;
    	sort($shouldHave);
    	
    	sort($siteUrlsAfter);
    	
    	$this->assertEqual($shouldHave, $siteUrlsAfter);
    }
    
    /**
     * wrong format urls => exception
     */
    public function test_addSiteUrls_wrongUrlsFormat1()
    {
    	
    	$idsite = $this->test_addSite();
    	
    	$toAdd = array("http://pi''.com");
    	
    	try {
    		$insertedUrls = Piwik_SitesManager_API::addSiteAliasUrls($idsite, $toAdd);
    	}
    	catch (Exception $expected) {
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * wrong format urls => exception
     */
    public function test_addSiteUrls_wrongUrlsFormat2()
    {
    	
    	$idsite = $this->test_addSite();
    	
    	$toAdd = array("http://pi^.com");
    	
    	try {
    		$insertedUrls = Piwik_SitesManager_API::addSiteAliasUrls($idsite, $toAdd);
    	}
    	catch (Exception $expected) {
            return;
        }
        $this->fail("Exception not raised.");
    }
    /**
     * wrong format urls => exception
     */
    public function test_addSiteUrls_wrongUrlsFormat3()
    {
    	
    	$idsite = $this->test_addSite();
    	
    	$toAdd = array("http://pigeq.com/test{}");
    	
    	try {
    		$insertedUrls = Piwik_SitesManager_API::addSiteAliasUrls($idsite, $toAdd);
    	}
    	catch (Exception $expected) {
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    
    /**
     * wrong idsite => no exception because simply no access to this resource
     */
    public function test_addSiteUrls_wrongIdSite1()
    {
    	$toAdd = array("http://pigeq.com/test");
    	try {
    		$insertedUrls = Piwik_SitesManager_API::addSiteAliasUrls(-1, $toAdd);
    	}
    	catch (Exception $expected) {
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * wrong idsite => exception
     */
    public function test_addSiteUrls_wrongIdSite2()
    {
    	$toAdd = array("http://pigeq.com/test");
    	
    	try {
    		$insertedUrls = Piwik_SitesManager_API::addSiteAliasUrls(155, $toAdd);
    	}
    	catch (Exception $expected) {
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * no Id -> empty array
     */
    function test_getAllSitesId_noId()
    {
    	$ids = Piwik_SitesManager_API::getAllSitesId();
    	$this->assertEqual(array(),$ids);
    }
    
    /**
     * several Id -> normal array
     */
    function test_getAllSitesId_severalId()
    {
    	$name="tetq";
    	$idsites = array(
    				Piwik_SitesManager_API::addSite($name,array("http://piwik.net","http://piwik.com/test/")),
    				Piwik_SitesManager_API::addSite($name,array("http://piwik.net","http://piwik.com/test/")),
    				Piwik_SitesManager_API::addSite($name,array("http://piwik.net","http://piwik.com/test/")),
    				Piwik_SitesManager_API::addSite($name,array("http://piwik.net","http://piwik.com/test/")),
    				Piwik_SitesManager_API::addSite($name,array("http://piwik.net","http://piwik.com/test/")),
    		);
    	
    	$ids = Piwik_SitesManager_API::getAllSitesId();
    	$this->assertEqual($idsites,$ids);
    }
    
    /**
     * wrong id => exception
     */
    function test_getSiteFromId_wrongId1()
    {
    	
    	try {
    		$siteInfo = Piwik_SitesManager_API::getSiteFromId(0);
    	}
    	catch (Exception $expected) {
            return;
        }
        $this->fail("Exception not raised.");
    	
    }
    /**
     * wrong id => exception
     */
    function test_getSiteFromId_wrongId2()
    {
    	
    	try {
    		$siteInfo = Piwik_SitesManager_API::getSiteFromId("x1");
    	}
    	catch (Exception $expected) {
            return;
        }
        $this->fail("Exception not raised.");
    	
    }
    /**
     * wrong id : no access => exception
     */
    function test_getSiteFromId_wrongId3()
    {
    	$idsite = Piwik_SitesManager_API::addSite("site",array("http://piwik.net","http://piwik.com/test/"));
    	$this->assertEqual($idsite,1);
    	
    	// set noaccess to site 1
		FakeAccess::setIdSitesView (array(2));
		FakeAccess::setIdSitesAdmin (array());
    	
    	try {
    		$siteInfo = Piwik_SitesManager_API::getSiteFromId(1);
    	}
    	catch (Exception $expected) {
            return;
        }
        $this->fail("Exception not raised.");
    	
    }
    /**
     * normal case
     */
    function test_getSiteFromId_normalId()
    {
    	$name = "website ''";
    	$idsite = Piwik_SitesManager_API::addSite($name,array("http://piwik.net","http://piwik.com/test/"));
    	$this->assertIsA( $idsite,'int');
    	
    	$siteInfo = Piwik_SitesManager_API::getSiteFromId($idsite);
    	$this->assertEqual($siteInfo['name'], $name);
    	$this->assertEqual($siteInfo['main_url'], "http://piwik.net");
    }
    
    
    /**
     * there is no admin site available -> array()
     */
    function test_getSitesWithAdminAccess_noResult()
    {
		FakeAccess::setIdSitesAdmin (array());
    	
    	$sites = Piwik_SitesManager_API::getSitesWithAdminAccess();
    	$this->assertEqual($sites, array());
    }
    
    /**
     * normal case, admin and view and noaccess website => return only admin
     */
    function test_getSitesWithAdminAccess()
    {
    	$idsite = Piwik_SitesManager_API::addSite("site1",array("http://piwik.net","http://piwik.com/test/"));
    	$idsite = Piwik_SitesManager_API::addSite("site2",array("http://piwik.com/test/"));
    	$idsite = Piwik_SitesManager_API::addSite("site3",array("http://piwik.org"));
    	
    	$resultWanted = array(
    		0 => array("idsite" => 1, "name" => "site1", "main_url" =>"http://piwik.net"),
    		1 => array("idsite" => 3, "name" => "site3", "main_url" =>"http://piwik.org"),
    	);
    		
		FakeAccess::setIdSitesAdmin (array(1,3));
		
    	$sites = Piwik_SitesManager_API::getSitesWithAdminAccess();
    	
    	// we dont test the ts_created
    	unset($sites[0]['ts_created']);
    	unset($sites[1]['ts_created']);
    	$this->assertEqual($sites, $resultWanted);
    }
    
    /**
     * there is no admin site available -> array()
     */
    function test_getSitesWithViewAccess_noResult()
    {
		FakeAccess::setIdSitesView (array());
		FakeAccess::setIdSitesAdmin (array());
    	
    	$sites = Piwik_SitesManager_API::getSitesWithViewAccess();
    	$this->assertEqual($sites, array());
    }
    
    /**
     * normal case, admin and view and noaccess website => return only admin
     */
    function test_getSitesWithViewAccess()
    {
    	$idsite = Piwik_SitesManager_API::addSite("site1",array("http://piwik.net","http://piwik.com/test/"));
    	$idsite = Piwik_SitesManager_API::addSite("site2",array("http://piwik.com/test/"));
    	$idsite = Piwik_SitesManager_API::addSite("site3",array("http://piwik.org"));
    	
    	$resultWanted = array(
    		0 => array("idsite" => 1, "name" => "site1", "main_url" =>"http://piwik.net"),
    		1 => array("idsite" => 3, "name" => "site3", "main_url" =>"http://piwik.org"),
    	);
    		
		FakeAccess::setIdSitesView (array(1,3));
		FakeAccess::setIdSitesAdmin (array());
    	
    	$sites = Piwik_SitesManager_API::getSitesWithViewAccess();
    	// we dont test the ts_created
    	unset($sites[0]['ts_created']);
    	unset($sites[1]['ts_created']);
    	$this->assertEqual($sites, $resultWanted);
    }
    
    /**
     * there is no admin site available -> array()
     */
    function test_getSitesWithAtLeastViewAccess_noResult()
    {
		FakeAccess::setIdSitesView (array());
		FakeAccess::setIdSitesAdmin (array());
    	
    	$sites = Piwik_SitesManager_API::getSitesWithAtLeastViewAccess();
    	$this->assertEqual($sites, array());
    }
    
    /**
     * normal case, admin and view and noaccess website => return only admin
     */
    function test_getSitesWithAtLeastViewAccess()
    {
    	$idsite = Piwik_SitesManager_API::addSite("site1",array("http://piwik.net","http://piwik.com/test/"));
    	$idsite = Piwik_SitesManager_API::addSite("site2",array("http://piwik.com/test/"));
    	$idsite = Piwik_SitesManager_API::addSite("site3",array("http://piwik.org"));
    	
    	$resultWanted = array(
    		0 => array("idsite" => 1, "name" => "site1", "main_url" =>"http://piwik.net"),
    		1 => array("idsite" => 3, "name" => "site3", "main_url" =>"http://piwik.org"),
    	);
    		
		FakeAccess::setIdSitesView (array(1,3));
		FakeAccess::setIdSitesAdmin (array());
    	
    	$sites = Piwik_SitesManager_API::getSitesWithAtLeastViewAccess();
    	// we dont test the ts_created
    	unset($sites[0]['ts_created']);
    	unset($sites[1]['ts_created']);
    	$this->assertEqual($sites, $resultWanted);
    }
    
    
    /**
     * no urls for this site => array()
     */
    function test_getSiteUrlsFromId_noUrls()
    {
    	$idsite = Piwik_SitesManager_API::addSite("site1",array("http://piwik.net"));
    	
    	$urls = Piwik_SitesManager_API::getSiteUrlsFromId($idsite);
    	$this->assertEqual(array("http://piwik.net"),$urls);
    }
    
    /**
     * normal case
     */
    function test_getSiteUrlsFromId_manyUrls()
    {
    	$site = array("http://piwik.net",
						"http://piwik.org",	
						"http://piwik.org",	
						"http://piwik.com");
		sort($site);
		
    	$idsite = Piwik_SitesManager_API::addSite("site1",$site);
    	

    	$siteWanted = array("http://piwik.net",
						"http://piwik.org",	
						"http://piwik.com");
		sort($siteWanted);
    	$urls = Piwik_SitesManager_API::getSiteUrlsFromId($idsite);
    	
    	
    	$this->assertEqual($siteWanted, $urls);
    }
    
    /**
     * wrongId => exception
     */
    function test_getSiteUrlsFromId_wrongId()
    {
		FakeAccess::setIdSitesView (array(3));
		FakeAccess::setIdSitesAdmin (array());
    	
    	try {
    		Piwik_SitesManager_API::getSiteUrlsFromId(1);
    	}
    	catch (Exception $expected) {
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    
    
    /**
     * wrong id=> exception
     */
    function test_replaceSiteUrls_wrongIdsite()
    {
    	
    	try {
    		Piwik_SitesManager_API::replaceSiteUrls(-1, array("http://piwik.net"));
    	}
    	catch (Exception $expected) {
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * no urls => exception
     */
    function test_replaceSiteUrls_noUrls()
    {
    	$idsite = Piwik_SitesManager_API::addSite("site1","http://test.com");
    	try {
    		Piwik_SitesManager_API::replaceSiteUrls($idsite, array());
	        $this->fail("Exception not raised.");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("(SitesManager_ExceptionNoUrl)", $expected->getMessage());
        }
    }
    
    /**
     * normal case -> main_url replaced
     */
    function test_replaceSiteUrls_oneUrls()
    {
    	$idsite = Piwik_SitesManager_API::addSite("site1","http://test.com");
    	$this->assertEqual(
    				Piwik_SitesManager_API::replaceSiteUrls($idsite, array("http://piwiknew.com")),
    				1);
    	$site = Piwik_SitesManager_API::getSiteFromId($idsite);
    	$this->assertEqual($site['main_url'], "http://piwiknew.com");
    }
    
    /**
     * normal case => main_url replaced and alias urls inserted
     */
    function test_replaceSiteUrls_severalUrls()
    {
    	$urls = array("http://piwiknew.com",
						"http://piwiknew.net",
						"http://piwiknew.org",
						"http://piwiknew.fr");
    	$idsite = Piwik_SitesManager_API::addSite("site1","http://test.com");
    	$this->assertEqual(
    				Piwik_SitesManager_API::replaceSiteUrls($idsite, 
    							$urls),
    				4);

    	$all = Piwik_SitesManager_API::getSiteUrlsFromId($idsite);
    	$this->assertEqual($all[0], $urls[0]);
    	sort($all);
    	sort($urls);

    	$this->assertEqual($all, $urls);
    }
    
    
    /**
     * one url => no change to alias urls
     */
    function test_updateSite_oneUrl()
    {
    	$urls = array("http://piwiknew.com",
						"http://piwiknew.net",
						"http://piwiknew.org",
						"http://piwiknew.fr");
    	$idsite = Piwik_SitesManager_API::addSite("site1",$urls);
    	
    	$newMainUrl = "http://main.url";
    	Piwik_SitesManager_API::updateSite($idsite, "test toto@{}",$newMainUrl );
    	
    	$allUrls = Piwik_SitesManager_API::getSiteUrlsFromId($idsite);
    	
    	$this->assertEqual($allUrls[0], $newMainUrl);
    	$a1 = array_slice($allUrls,1); sort($a1);
    	$a2 = array_slice($urls,1); sort($a2);
    	$this->assertEqual($a1,$a2);
    }
    
    /**
     * strange name and NO URL => name ok, main_url not updated
     */
    function test_updateSite_strangeNameNoUrl()
    {
    	$idsite = Piwik_SitesManager_API::addSite("site1","http://main.url");
    	$newName ="test toto@{'786'}";
    	
    	Piwik_SitesManager_API::updateSite($idsite, $newName );
    	
    	$site = Piwik_SitesManager_API::getSiteFromId($idsite);
    	
    	$this->assertEqual($site['name'],$newName);
    	// url didn't change because parameter url NULL in updateSite
    	$this->assertEqual($site['main_url'],"http://main.url");
    	
    	$this->assertEqual(count(null),0);
    	
    }
    
    /**
     * several urls => both main and alias are updated
     */
    function test_updateSite_severalUrls()
    {
    	$urls = array("http://piwiknew.com",
						"http://piwiknew.net",
						"http://piwiknew.org",
						"http://piwiknew.fr");
    	$idsite = Piwik_SitesManager_API::addSite("site1",$urls);
    	
    	$newurls = array("http://piwiknew2.com",
						"http://piwiknew2.net",
						"http://piwiknew2.org",
						"http://piwiknew2.fr");
    	Piwik_SitesManager_API::updateSite($idsite, "test toto@{}",$newurls );
    	
    	$allUrls = Piwik_SitesManager_API::getSiteUrlsFromId($idsite);
    	sort($allUrls);
    	sort($newurls);
    	
    	$this->assertEqual($allUrls,$newurls);

    }
}

