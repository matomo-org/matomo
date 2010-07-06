<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

require_once PIWIK_PATH_TEST_TO_ROOT . '/tests/core/Database.test.php';

class Test_Piwik_SitesManager extends Test_Database
{
	public function __construct()
	{
		Piwik_PluginsManager::getInstance()->unloadPlugin('ExampleFeedburner');
		parent::__construct();
	}
    public function setUp()
    {
    	parent::setUp();

		// setup the access layer
    	$pseudoMockAccess = new FakeAccess;
		FakeAccess::$superUser = true;
		Zend_Registry::set('access', $pseudoMockAccess);
		
		// clear static Site cache
		Piwik_Site::clearCache();
    }
    
    /**
     * empty name -> exception
     */
	public function test_addSite_emptyName()
    {
    	try {
    		Piwik_SitesManager_API::getInstance()->addSite("",array("http://piwik.net"));
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
    		Piwik_SitesManager_API::getInstance()->addSite("name",array());
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
    		Piwik_SitesManager_API::getInstance()->addSite("name",array(""));
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
    		Piwik_SitesManager_API::getInstance()->addSite("name","");
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
    		Piwik_SitesManager_API::getInstance()->addSite("name","httpww://piwik.net");
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
    		Piwik_SitesManager_API::getInstance()->addSite("name","httpww://piwik.net/gqg~#");
    	}
    	catch (Exception $expected) {
            return;
        }
        $this->fail("Exception not raised.");
    }
    
    /**
     * Test with valid IPs
     */
    public function test_addSite_excludedIpsAndtimezoneAndCurrencyAndExcludedQueryParameters_valid()
    {
    	$ips = '1.2.3.4,1.1.1.*,1.2.*.*,1.*.*.*';
    	$timezone = 'Europe/Paris'; 
    	$currency = 'EUR';
    	$excludedQueryParameters = 'p1,P2, P33333';
    	$expectedExcludedQueryParameters = 'p1,P2,P33333';
		$idsite = Piwik_SitesManager_API::getInstance()->addSite("name","http://piwik.net/", $ips, $excludedQueryParameters,$timezone, $currency);
    	$siteInfo = Piwik_SitesManager_API::getInstance()->getSiteFromId($idsite);
    	$this->assertEqual($siteInfo['excluded_ips'], $ips);
    	$this->assertEqual($siteInfo['timezone'], $timezone);
    	$this->assertEqual($siteInfo['currency'], $currency);
    	$this->assertEqual($siteInfo['excluded_parameters'], $expectedExcludedQueryParameters);
    }
    
    /**
     * Test with invalid IPs
     */
    public function test_addSite_excludedIps_notValid()
    {
    	$invalidIps = array(
    		'35817587341',
    		'ieagieha',
    		'1.2.3',
    		'*.1.1.1',
    		'*.*.1.1',
    		'*.*.*.1',
    		'*.*.*.*',
    		'1.1.1.1.1',
    	); 
    	foreach($invalidIps as $ip)
    	{
    		$raised = false;
    		try {
    			$idsite = Piwik_SitesManager_API::getInstance()->addSite("name","http://piwik.net/", $ip);
    		} catch(Exception $e) {
    			$raised = true;
    		}
    		if(!$raised) 
    		{
    			$this->fail('was expecting invalid IP exception to raise');
    		}
    	}
    	$this->pass();
    }
    
    /**
     * one url -> one main_url and nothing inserted as alias urls
     */
    public function test_addSite_oneUrl()
    {
    	$url = "http://piwik.net/";
    	$urlOK = "http://piwik.net";
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("name",$url);
    	$this->assertIsA( $idsite,'int');
    	
    	$siteInfo = Piwik_SitesManager_API::getInstance()->getSiteFromId($idsite);
    	$this->assertEqual($siteInfo['main_url'], $urlOK);
    	
    	$siteUrls = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($idsite);
    	$this->assertTrue(count($siteUrls)===1);
    }
    
    /**
     * several urls -> one main_url and others as alias urls
     */
    public function test_addSite_severalUrls()
    {
    	$urls = array("http://piwik.net/","http://piwik.com","https://piwik.net/test/");
    	$urlsOK = array("http://piwik.net","http://piwik.com","https://piwik.net/test");
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("super website",$urls);
    	$this->assertIsA( $idsite,'int');
    	
    	$siteInfo = Piwik_SitesManager_API::getInstance()->getSiteFromId($idsite);
    	$this->assertEqual($siteInfo['main_url'], $urlsOK[0]);
    	
    	$siteUrls = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($idsite);
    	$this->assertEqual($siteUrls, $urlsOK);
    }
    
    /**
     * strange name
     */
    public function test_addSite_strangeName()
    {
    	$name = "supertest(); ~@@()''!£\$'%%^'!£ போ";
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite($name,"http://piwik.net");
    	$this->assertIsA( $idsite,'int');
    	
    	$siteInfo = Piwik_SitesManager_API::getInstance()->getSiteFromId($idsite);
    	$this->assertEqual($siteInfo['name'], $name);
    	
    }
    /**
     * normal case
     */
    public function test_addSite()
    {
    	$name = "website ";
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite($name,array("http://piwik.net","http://piwik.com/test/"));
    	$this->assertIsA( $idsite,'int');
    	
    	$siteInfo = Piwik_SitesManager_API::getInstance()->getSiteFromId($idsite);
    	$this->assertEqual($siteInfo['name'], $name);
    	$this->assertEqual($siteInfo['main_url'], "http://piwik.net");
    	
    	$siteUrls = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($idsite);
    	$this->assertEqual($siteUrls, array("http://piwik.net","http://piwik.com/test"));
    	   	
    	return $idsite;
    }
    
    /**
     * no duplicate -> all the urls are saved
     */
    public function test_addSiteUrls_noDuplicate()
    {
    	$idsite = $this->test_addSite();
    	
    	$siteUrlsBefore = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($idsite);
    	
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
    	
    	$insertedUrls = Piwik_SitesManager_API::getInstance()->addSiteAliasUrls($idsite, $toAdd);
    	$this->assertEqual($insertedUrls, count($toAdd));
    	
    	$siteUrlsAfter = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($idsite);
    	
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
    	
    	$siteUrlsBefore = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($idsite);
    	
    	$toAdd = array_merge($siteUrlsBefore, array("http://piwik1.net","http://piwik2.net"));
    	
    	$insertedUrls = Piwik_SitesManager_API::getInstance()->addSiteAliasUrls($idsite, $toAdd);
    	$this->assertEqual($insertedUrls, count($toAdd) - count($siteUrlsBefore));
    	
    	$siteUrlsAfter = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($idsite);
    	
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
    	
    	$siteUrlsBefore = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($idsite);
    	
    	$toAdd = array();
    	
    	$insertedUrls = Piwik_SitesManager_API::getInstance()->addSiteAliasUrls($idsite, $toAdd);
    	$this->assertEqual($insertedUrls, count($toAdd));
    	
    	$siteUrlsAfter = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($idsite);
    	
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
    	
    	$siteUrlsBefore = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($idsite);
    	
    	$toAdd = $siteUrlsBefore;
    	
    	$insertedUrls = Piwik_SitesManager_API::getInstance()->addSiteAliasUrls($idsite, $toAdd);
    	$this->assertEqual($insertedUrls, 0);
    	
    	$siteUrlsAfter = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($idsite);
    	
    	$shouldHave = $siteUrlsBefore;
    	sort($shouldHave);
    	
    	sort($siteUrlsAfter);
    	
    	$this->assertEqual($shouldHave, $siteUrlsAfter);
    }
    /**
     * wrong format urls => exception
     */
    public function test_addSiteUrls_wrongUrlsFormat3()
    {
    	$idsite = $this->test_addSite();
    	$toAdd = array("htt{}p://pigeq.com/test");
    	try {
    		$insertedUrls = Piwik_SitesManager_API::getInstance()->addSiteAliasUrls($idsite, $toAdd);
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
    		$insertedUrls = Piwik_SitesManager_API::getInstance()->addSiteAliasUrls(-1, $toAdd);
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
    		$insertedUrls = Piwik_SitesManager_API::getInstance()->addSiteAliasUrls(155, $toAdd);
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
    	$ids = Piwik_SitesManager_API::getInstance()->getAllSitesId();
    	$this->assertEqual(array(),$ids);
    }
    
    /**
     * several Id -> normal array
     */
    function test_getAllSitesId_severalId()
    {
    	$name="tetq";
    	$idsites = array(
    				Piwik_SitesManager_API::getInstance()->addSite($name,array("http://piwik.net","http://piwik.com/test/")),
    				Piwik_SitesManager_API::getInstance()->addSite($name,array("http://piwik.net","http://piwik.com/test/")),
    				Piwik_SitesManager_API::getInstance()->addSite($name,array("http://piwik.net","http://piwik.com/test/")),
    				Piwik_SitesManager_API::getInstance()->addSite($name,array("http://piwik.net","http://piwik.com/test/")),
    				Piwik_SitesManager_API::getInstance()->addSite($name,array("http://piwik.net","http://piwik.com/test/")),
    		);
    	
    	$ids = Piwik_SitesManager_API::getInstance()->getAllSitesId();
    	$this->assertEqual($idsites,$ids);
    }
    
    /**
     * wrong id => exception
     */
    function test_getSiteFromId_wrongId1()
    {
    	
    	try {
    		$siteInfo = Piwik_SitesManager_API::getInstance()->getSiteFromId(0);
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
    		$siteInfo = Piwik_SitesManager_API::getInstance()->getSiteFromId("x1");
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
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site",array("http://piwik.net","http://piwik.com/test/"));
    	$this->assertEqual($idsite,1);
    	
    	// set noaccess to site 1
		FakeAccess::setIdSitesView (array(2));
		FakeAccess::setIdSitesAdmin (array());
    	
    	try {
    		$siteInfo = Piwik_SitesManager_API::getInstance()->getSiteFromId(1);
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
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite($name,array("http://piwik.net","http://piwik.com/test/"));
    	$this->assertIsA( $idsite,'int');
    	
    	$siteInfo = Piwik_SitesManager_API::getInstance()->getSiteFromId($idsite);
    	$this->assertEqual($siteInfo['name'], $name);
    	$this->assertEqual($siteInfo['main_url'], "http://piwik.net");
    }
    
    
    /**
     * there is no admin site available -> array()
     */
    function test_getSitesWithAdminAccess_noResult()
    {
		FakeAccess::setIdSitesAdmin (array());
    	
    	$sites = Piwik_SitesManager_API::getInstance()->getSitesWithAdminAccess();
    	$this->assertEqual($sites, array());
    }
    
    /**
     * normal case, admin and view and noaccess website => return only admin
     */
    function test_getSitesWithAdminAccess()
    {
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site1",array("http://piwik.net","http://piwik.com/test/"));
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site2",array("http://piwik.com/test/"));
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site3",array("http://piwik.org"));
    	
    	$resultWanted = array(
    		0 => array("idsite" => 1, "name" => "site1", "main_url" =>"http://piwik.net", "excluded_ips" => "", 'excluded_parameters' => '', 'timezone' => 'UTC', 'currency' => 'USD'),
    		1 => array("idsite" => 3, "name" => "site3", "main_url" =>"http://piwik.org", "excluded_ips" => "", 'excluded_parameters' => '', 'timezone' => 'UTC', 'currency' => 'USD'),
    	);
    		
		FakeAccess::setIdSitesAdmin (array(1,3));
		
    	$sites = Piwik_SitesManager_API::getInstance()->getSitesWithAdminAccess();
    	
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
    	
    	$sites = Piwik_SitesManager_API::getInstance()->getSitesWithViewAccess();
    	$this->assertEqual($sites, array());
    }
    
    /**
     * normal case, admin and view and noaccess website => return only admin
     */
    function test_getSitesWithViewAccess()
    {
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site1",array("http://piwik.net","http://piwik.com/test/"));
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site2",array("http://piwik.com/test/"));
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site3",array("http://piwik.org"));
    	
    	$resultWanted = array(
    		0 => array("idsite" => 1, "name" => "site1", "main_url" =>"http://piwik.net", "excluded_ips" => "", 'excluded_parameters' => '', 'timezone' => 'UTC', 'currency' => 'USD'),
    		1 => array("idsite" => 3, "name" => "site3", "main_url" =>"http://piwik.org", "excluded_ips" => "", 'excluded_parameters' => '', 'timezone' => 'UTC', 'currency' => 'USD'),
    	);
    		
		FakeAccess::setIdSitesView (array(1,3));
		FakeAccess::setIdSitesAdmin (array());
    	
    	$sites = Piwik_SitesManager_API::getInstance()->getSitesWithViewAccess();
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
    	
    	$sites = Piwik_SitesManager_API::getInstance()->getSitesWithAtLeastViewAccess();
    	$this->assertEqual($sites, array());
    }
    
    /**
     * normal case, admin and view and noaccess website => return only admin
     */
    function test_getSitesWithAtLeastViewAccess()
    {
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site1",array("http://piwik.net","http://piwik.com/test/"));
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site2",array("http://piwik.com/test/"));
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site3",array("http://piwik.org"));
    	
    	$resultWanted = array(
    		0 => array("idsite" => 1, "name" => "site1", "main_url" =>"http://piwik.net", "excluded_ips" => "", 'excluded_parameters' => '', 'timezone' => 'UTC', 'currency' => 'USD'),
    		1 => array("idsite" => 3, "name" => "site3", "main_url" =>"http://piwik.org", "excluded_ips" => "", 'excluded_parameters' => '', 'timezone' => 'UTC', 'currency' => 'USD'),
    	);
    		
		FakeAccess::setIdSitesView (array(1,3));
		FakeAccess::setIdSitesAdmin (array());
    	
    	$sites = Piwik_SitesManager_API::getInstance()->getSitesWithAtLeastViewAccess();
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
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site1",array("http://piwik.net"));
    	
    	$urls = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($idsite);
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
		
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site1",$site);
    	

    	$siteWanted = array("http://piwik.net",
						"http://piwik.org",	
						"http://piwik.com");
		sort($siteWanted);
    	$urls = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($idsite);
    	
    	
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
    		Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId(1);
    	}
    	catch (Exception $expected) {
            return;
        }
        $this->fail("Exception not raised.");
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
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site1",$urls);
    	
    	$newMainUrl = "http://main.url";
    	Piwik_SitesManager_API::getInstance()->updateSite($idsite, "test toto@{}", $newMainUrl );
    	
    	$allUrls = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($idsite);
    	
    	$this->assertEqual($allUrls[0], $newMainUrl);
    	$aliasUrls = array_slice($allUrls,1);
    	$this->assertEqual($aliasUrls,array());
    }
    
    /**
     * strange name and NO URL => name ok, main_url not updated
     */
    function test_updateSite_strangeNameNoUrl()
    {
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site1","http://main.url");
    	$newName ="test toto@{'786'}";
    	
    	Piwik_SitesManager_API::getInstance()->updateSite($idsite, $newName );
    	
    	$site = Piwik_SitesManager_API::getInstance()->getSiteFromId($idsite);
    	
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
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site1",$urls);
    	
    	$newurls = array("http://piwiknew2.com",
						"http://piwiknew2.net",
						"http://piwiknew2.org",
						"http://piwiknew2.fr");
    	Piwik_SitesManager_API::getInstance()->updateSite($idsite, "test toto@{}",$newurls );
    	
    	$allUrls = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($idsite);
    	sort($allUrls);
    	sort($newurls);
    	
    	$this->assertEqual($allUrls,$newurls);

    }

    function test_addSites_invalidTimezone()
    {
    	// trying invalid timezones
    	try {
    		$idsite = Piwik_SitesManager_API::getInstance()->addSite("site1",array('http://example.org'), '', '', 'UTC+15');
    		$this->fail('invalid timezone should raise an exception');
    	} catch(Exception $e) {
    	}
    	try {
    		$idsite = Piwik_SitesManager_API::getInstance()->addSite("site1",array('http://example.org'), '', '', 'Paris');
    		$this->fail('invalid timezone should raise an exception');
    	} catch(Exception $e) {
    	}
    	$this->pass();
    }
    
    function test_addSites_invalidCurrency()
    {
    	$invalidCurrency = '€';
    	try {
    		$idsite = Piwik_SitesManager_API::getInstance()->addSite("site1",array('http://example.org'), '', 'UTC', $invalidCurrency);
    		$this->fail('invalid currency should raise an exception');
    	} catch(Exception $e) {
    	}
    	$this->pass();
    }
    
    function test_setDefaultTimezoneAndCurrencyAndExcludedQueryParametersAndExcludedIps()
    {
    	// test that they return default values
    	$defaultTimezone = Piwik_SitesManager_API::getInstance()->getDefaultTimezone();
    	$this->assertEqual($defaultTimezone, 'UTC');
    	$defaultCurrency = Piwik_SitesManager_API::getInstance()->getDefaultCurrency();
    	$this->assertEqual($defaultCurrency, 'USD');
    	$excludedIps = Piwik_SitesManager_API::getInstance()->getExcludedIpsGlobal();
    	$this->assertEqual($excludedIps, '');
    	$excludedQueryParameters = Piwik_SitesManager_API::getInstance()->getExcludedQueryParametersGlobal();
    	$this->assertEqual($excludedQueryParameters, '');
    
    	// test that when not specified, defaults are set as expected  
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site1",array('http://example.org'));
    	$site = new Piwik_Site($idsite);
    	$this->assertEqual($site->getTimezone(), 'UTC');
    	$this->assertEqual($site->getCurrency(), 'USD');
    	$this->assertEqual($site->getExcludedQueryParameters(), '');
    	$this->assertEqual($site->getExcludedIps(), '');

    	// set the global timezone and get it
    	$newDefaultTimezone = 'UTC+5.5';
    	Piwik_SitesManager_API::getInstance()->setDefaultTimezone($newDefaultTimezone);
    	$defaultTimezone = Piwik_SitesManager_API::getInstance()->getDefaultTimezone();
    	$this->assertEqual($defaultTimezone, $newDefaultTimezone);

    	// set the default currency and get it
    	$newDefaultCurrency = 'EUR';
    	Piwik_SitesManager_API::getInstance()->setDefaultCurrency($newDefaultCurrency);
    	$defaultCurrency = Piwik_SitesManager_API::getInstance()->getDefaultCurrency();
    	$this->assertEqual($defaultCurrency, $newDefaultCurrency);
    	
    	// set the global IPs to exclude and get it
    	$newGlobalExcludedIps = '1.1.1.*,1.1.*.*,150.1.1.1';
    	Piwik_SitesManager_API::getInstance()->setGlobalExcludedIps($newGlobalExcludedIps);
    	$globalExcludedIps = Piwik_SitesManager_API::getInstance()->getExcludedIpsGlobal();
    	$this->assertEqual($globalExcludedIps, $newGlobalExcludedIps);
    	
    	// set the global URL query params to exclude and get it
    	$newGlobalExcludedQueryParameters = 'PHPSESSID,blabla, TesT';
    	// removed the space
    	$expectedGlobalExcludedQueryParameters = 'PHPSESSID,blabla,TesT';
    	Piwik_SitesManager_API::getInstance()->setGlobalExcludedQueryParameters($newGlobalExcludedQueryParameters);
    	$globalExcludedQueryParameters = Piwik_SitesManager_API::getInstance()->getExcludedQueryParametersGlobal();
    	$this->assertEqual($globalExcludedQueryParameters, $expectedGlobalExcludedQueryParameters);

    	// create a website and check that default currency and default timezone are set
    	// however, excluded IPs and excluded query Params are not returned
    	$idsite = Piwik_SitesManager_API::getInstance()->addSite("site1",array('http://example.org'), '', '', $newDefaultTimezone);
    	$site = new Piwik_Site($idsite);
    	$this->assertEqual($site->getTimezone(), $newDefaultTimezone);
    	$this->assertEqual($site->getCreationDate()->toString(), date('Y-m-d'));
    	$this->assertEqual($site->getCurrency(), $newDefaultCurrency);
    	$this->assertEqual($site->getExcludedIps(), '');
    	$this->assertEqual($site->getExcludedQueryParameters(), '');
    }
    
    function test_getSitesIdFromSiteUrl_SuperUser()
    {
		$idsite = Piwik_SitesManager_API::getInstance()->addSite("site1",array("http://piwik.net","http://piwik.com"));
		$idsite = Piwik_SitesManager_API::getInstance()->addSite("site2",array("http://piwik.com","http://piwik.net"));
		$idsite = Piwik_SitesManager_API::getInstance()->addSite("site3",array("http://piwik.com","http://piwik.org"));

		$idsites = Piwik_SitesManager_API::getInstance()->getSitesIdFromSiteUrl('http://piwik.org');
		$this->assertTrue(count($idsites) == 1);

		$idsites = Piwik_SitesManager_API::getInstance()->getSitesIdFromSiteUrl('http://piwik.net');
		$this->assertTrue(count($idsites) == 2);

		$idsites = Piwik_SitesManager_API::getInstance()->getSitesIdFromSiteUrl('http://piwik.com');
		$this->assertTrue(count($idsites) == 3);
	}

	function test_getSitesIdFromSiteUrl_User()
	{
		$idsite = Piwik_SitesManager_API::getInstance()->addSite("site1",array("http://piwik.net","http://piwik.com"));
		$idsite = Piwik_SitesManager_API::getInstance()->addSite("site2",array("http://piwik.com","http://piwik.net"));
		$idsite = Piwik_SitesManager_API::getInstance()->addSite("site3",array("http://piwik.com","http://piwik.org"));

		$saveAccess = Zend_Registry::get('access');

		Piwik_UsersManager_API::getInstance()->addUser("user1", "geqgegagae", "tegst@tesgt.com", "alias");
		Piwik_UsersManager_API::getInstance()->setUserAccess("user1", "view", array(1));

    	Piwik_UsersManager_API::getInstance()->addUser("user2", "geqgegagae", "tegst2@tesgt.com", "alias");
		Piwik_UsersManager_API::getInstance()->setUserAccess("user2", "view", array(1));
		Piwik_UsersManager_API::getInstance()->setUserAccess("user2", "admin", array(3));

    	Piwik_UsersManager_API::getInstance()->addUser("user3", "geqgegagae", "tegst3@tesgt.com", "alias");
		Piwik_UsersManager_API::getInstance()->setUserAccess("user3", "view", array(1,2));
		Piwik_UsersManager_API::getInstance()->setUserAccess("user3", "admin", array(3));

    	$pseudoMockAccess = new FakeAccess;
		FakeAccess::$superUser = false;
		FakeAccess::$identity = 'user1';
		FakeAccess::setIdSitesView (array(1));
		FakeAccess::setIdSitesAdmin (array());
		Zend_Registry::set('access', $pseudoMockAccess);
		$idsites = Piwik_SitesManager_API::getInstance()->getSitesIdFromSiteUrl('http://piwik.com');
		$this->assertTrue(count($idsites) == 1);

    	$pseudoMockAccess = new FakeAccess;
		FakeAccess::$superUser = false;
		FakeAccess::$identity = 'user2';
		FakeAccess::setIdSitesView (array(1));
		FakeAccess::setIdSitesAdmin (array(3));
		Zend_Registry::set('access', $pseudoMockAccess);
		$idsites = Piwik_SitesManager_API::getInstance()->getSitesIdFromSiteUrl('http://piwik.com');
		$this->assertTrue(count($idsites) == 2);

    	$pseudoMockAccess = new FakeAccess;
		FakeAccess::$superUser = false;
		FakeAccess::$identity = 'user3';
		FakeAccess::setIdSitesView (array(1,2));
		FakeAccess::setIdSitesAdmin (array(3));
		Zend_Registry::set('access', $pseudoMockAccess);
		$idsites = Piwik_SitesManager_API::getInstance()->getSitesIdFromSiteUrl('http://piwik.com');
		$this->assertTrue(count($idsites) == 3);

		Zend_Registry::set('access', $saveAccess);
	}
}
