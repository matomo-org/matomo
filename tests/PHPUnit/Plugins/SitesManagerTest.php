<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Access;
use Piwik\Plugins\SitesManager\API;
use Piwik\Plugins\UsersManager\API as APIUsersManager;
use Piwik\Site;

class SitesManagerTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        // setup the access layer
        $pseudoMockAccess = new FakeAccess;
        FakeAccess::$superUser = true;
        Access::setSingletonInstance($pseudoMockAccess);
    }

    /**
     * empty name -> exception
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testAddSiteEmptyName()
    {
        try {
            API::getInstance()->addSite("", array("http://piwik.net"));
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * DataProvider for testAddSiteWrongUrls
     */
    public function getInvalidUrlData()
    {
        return array(
            array(array()), // no urls
            array(array("")),
            array(""),
            array("httpww://piwik.net"),
            array("httpww://piwik.net/gqg~#"),
        );
    }

    /**
     * wrong urls -> exception
     *
     * @dataProvider getInvalidUrlData
     * @group Plugins
     * @group SitesManager
     */
    public function testAddSiteWrongUrls($url)
    {
        try {
            API::getInstance()->addSite("name", $url);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Test with valid IPs
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testAddSiteExcludedIpsAndtimezoneAndCurrencyAndExcludedQueryParametersValid()
    {
        $ips = '1.2.3.4,1.1.1.*,1.2.*.*,1.*.*.*';
        $timezone = 'Europe/Paris';
        $currency = 'EUR';
        $excludedQueryParameters = 'p1,P2, P33333';
        $expectedExcludedQueryParameters = 'p1,P2,P33333';
        $excludedUserAgents = " p1,P2, \nP3333 ";
        $expectedExcludedUserAgents = "p1,P2,P3333";
        $idsite = API::getInstance()->addSite("name", "http://piwik.net/", $ecommerce = 1,
            $siteSearch = 1, $searchKeywordParameters = 'search,param', $searchCategoryParameters = 'cat,category',
            $ips, $excludedQueryParameters, $timezone, $currency, $group = null, $startDate = null, $excludedUserAgents);
        $siteInfo = API::getInstance()->getSiteFromId($idsite);
        $this->assertEquals($ips, $siteInfo['excluded_ips']);
        $this->assertEquals($timezone, $siteInfo['timezone']);
        $this->assertEquals($currency, $siteInfo['currency']);
        $this->assertEquals($ecommerce, $siteInfo['ecommerce']);
        $this->assertTrue(Site::isEcommerceEnabledFor($idsite));
        $this->assertEquals($siteSearch, $siteInfo['sitesearch']);
        $this->assertTrue(Site::isSiteSearchEnabledFor($idsite));
        $this->assertEquals($searchKeywordParameters, $siteInfo['sitesearch_keyword_parameters']);
        $this->assertEquals($searchCategoryParameters, $siteInfo['sitesearch_category_parameters']);
        $this->assertEquals($expectedExcludedQueryParameters, $siteInfo['excluded_parameters']);
        $this->assertEquals($expectedExcludedUserAgents, $siteInfo['excluded_user_agents']);
    }

    /**
     * dataProvider for testAddSiteExcludedIpsNotValid
     */
    public function getInvalidIPsData()
    {
        return array(
            array('35817587341'),
            array('ieagieha'),
            array('1.2.3'),
            array('*.1.1.1'),
            array('*.*.1.1'),
            array('*.*.*.1'),
            array('1.1.1.1.1'),
        );
    }

    /**
     * Test with invalid IPs
     *
     * @dataProvider getInvalidIPsData
     * @group Plugins
     * @group SitesManager
     */
    public function testAddSiteExcludedIpsNotValid($ip)
    {
        try {
            API::getInstance()->addSite("name", "http://piwik.net/", $ecommerce = 0,
                $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null, $ip);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * one url -> one main_url and nothing inserted as alias urls
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testAddSiteOneUrl()
    {
        $url = "http://piwik.net/";
        $urlOK = "http://piwik.net";
        $idsite = API::getInstance()->addSite("name", $url);
        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_INT, $idsite);

        $siteInfo = API::getInstance()->getSiteFromId($idsite);
        $this->assertEquals($urlOK, $siteInfo['main_url']);
        $this->assertEquals(date('Y-m-d'), date('Y-m-d', strtotime($siteInfo['ts_created'])));

        $siteUrls = API::getInstance()->getSiteUrlsFromId($idsite);
        $this->assertEquals(1, count($siteUrls));
    }

    /**
     * several urls -> one main_url and others as alias urls
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testAddSiteSeveralUrls()
    {
        $urls = array("http://piwik.net/", "http://piwik.com", "https://piwik.net/test/");
        $urlsOK = array("http://piwik.net", "http://piwik.com", "https://piwik.net/test");
        $idsite = API::getInstance()->addSite("super website", $urls);
        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_INT, $idsite);

        $siteInfo = API::getInstance()->getSiteFromId($idsite);
        $this->assertEquals($urlsOK[0], $siteInfo['main_url']);

        $siteUrls = API::getInstance()->getSiteUrlsFromId($idsite);
        $this->assertEquals($urlsOK, $siteUrls);
    }

    /**
     * strange name
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testAddSiteStrangeName()
    {
        $name = "supertest(); ~@@()''!£\$'%%^'!£ போ";
        $idsite = API::getInstance()->addSite($name, "http://piwik.net");
        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_INT, $idsite);

        $siteInfo = API::getInstance()->getSiteFromId($idsite);
        $this->assertEquals($name, $siteInfo['name']);

    }

    /**
     * adds a site
     * use by several other unit tests
     */
    protected function _addSite()
    {
        $name = "website ";
        $idsite = API::getInstance()->addSite($name, array("http://piwik.net", "http://piwik.com/test/"));
        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_INT, $idsite);

        $siteInfo = API::getInstance()->getSiteFromId($idsite);
        $this->assertEquals($name, $siteInfo['name']);
        $this->assertEquals("http://piwik.net", $siteInfo['main_url']);

        $siteUrls = API::getInstance()->getSiteUrlsFromId($idsite);
        $this->assertEquals(array("http://piwik.net", "http://piwik.com/test"), $siteUrls);

        return $idsite;
    }

    /**
     * no duplicate -> all the urls are saved
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testAddSiteUrlsnoDuplicate()
    {
        $idsite = $this->_addSite();

        $siteUrlsBefore = API::getInstance()->getSiteUrlsFromId($idsite);

        $toAdd = array("http://piwik1.net",
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

        $insertedUrls = API::getInstance()->addSiteAliasUrls($idsite, $toAdd);
        $this->assertEquals(count($toAdd), $insertedUrls);

        $siteUrlsAfter = API::getInstance()->getSiteUrlsFromId($idsite);

        $shouldHave = array_merge($siteUrlsBefore, $toAddValid);
        sort($shouldHave);

        sort($siteUrlsAfter);

        $this->assertEquals($shouldHave, $siteUrlsAfter);
    }

    /**
     * duplicate -> don't save the already existing URLs
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testAddSiteUrlsDuplicate()
    {
        $idsite = $this->_addSite();

        $siteUrlsBefore = API::getInstance()->getSiteUrlsFromId($idsite);

        $toAdd = array_merge($siteUrlsBefore, array("http://piwik1.net", "http://piwik2.net"));

        $insertedUrls = API::getInstance()->addSiteAliasUrls($idsite, $toAdd);
        $this->assertEquals(count($toAdd) - count($siteUrlsBefore), $insertedUrls);

        $siteUrlsAfter = API::getInstance()->getSiteUrlsFromId($idsite);

        $shouldHave = $toAdd;
        sort($shouldHave);

        sort($siteUrlsAfter);

        $this->assertEquals($shouldHave, $siteUrlsAfter);
    }

    /**
     * case empty array => nothing happens
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testAddSiteUrlsNoUrlsToAdd1()
    {
        $idsite = $this->_addSite();

        $siteUrlsBefore = API::getInstance()->getSiteUrlsFromId($idsite);

        $toAdd = array();

        $insertedUrls = API::getInstance()->addSiteAliasUrls($idsite, $toAdd);
        $this->assertEquals(count($toAdd), $insertedUrls);

        $siteUrlsAfter = API::getInstance()->getSiteUrlsFromId($idsite);

        $shouldHave = $siteUrlsBefore;
        sort($shouldHave);

        sort($siteUrlsAfter);

        $this->assertEquals($shouldHave, $siteUrlsAfter);
    }

    /**
     * case array only duplicate => nothing happens
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testAddSiteUrlsNoUrlsToAdd2()
    {
        $idsite = $this->_addSite();

        $siteUrlsBefore = API::getInstance()->getSiteUrlsFromId($idsite);

        $toAdd = $siteUrlsBefore;

        $insertedUrls = API::getInstance()->addSiteAliasUrls($idsite, $toAdd);
        $this->assertEquals(0, $insertedUrls);

        $siteUrlsAfter = API::getInstance()->getSiteUrlsFromId($idsite);

        $shouldHave = $siteUrlsBefore;
        sort($shouldHave);

        sort($siteUrlsAfter);

        $this->assertEquals($shouldHave, $siteUrlsAfter);
    }

    /**
     * wrong format urls => exception
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testAddSiteUrlsWrongUrlsFormat3()
    {
        try {
            $idsite = $this->_addSite();
            $toAdd = array("http:mpigeq");
            $insertedUrls = API::getInstance()->addSiteAliasUrls($idsite, $toAdd);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * wrong idsite => no exception because simply no access to this resource
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testAddSiteUrlsWrongIdSite1()
    {
        try {
            $toAdd = array("http://pigeq.com/test");
            $insertedUrls = API::getInstance()->addSiteAliasUrls(-1, $toAdd);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * wrong idsite => exception
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testAddSiteUrlsWrongIdSite2()
    {
        try {
            $toAdd = array("http://pigeq.com/test");
            $insertedUrls = API::getInstance()->addSiteAliasUrls(155, $toAdd);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * no Id -> empty array
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testGetAllSitesIdNoId()
    {
        $ids = API::getInstance()->getAllSitesId();
        $this->assertEquals(array(), $ids);
    }

    /**
     * several Id -> normal array
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testGetAllSitesIdSeveralId()
    {
        $name = "tetq";
        $idsites = array(
            API::getInstance()->addSite($name, array("http://piwik.net", "http://piwik.com/test/")),
            API::getInstance()->addSite($name, array("http://piwik.net", "http://piwik.com/test/")),
            API::getInstance()->addSite($name, array("http://piwik.net", "http://piwik.com/test/")),
            API::getInstance()->addSite($name, array("http://piwik.net", "http://piwik.com/test/")),
            API::getInstance()->addSite($name, array("http://piwik.net", "http://piwik.com/test/")),
        );

        $ids = API::getInstance()->getAllSitesId();
        $this->assertEquals($idsites, $ids);
    }

    /**
     * wrong id => exception
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testGetSiteFromIdWrongId1()
    {
        try {
            $siteInfo = API::getInstance()->getSiteFromId(0);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * wrong id => exception
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testGetSiteFromIdWrongId2()
    {
        try {
            $siteInfo = API::getInstance()->getSiteFromId("x1");
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * wrong id : no access => exception
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testGetSiteFromIdWrongId3()
    {
        $idsite = API::getInstance()->addSite("site", array("http://piwik.net", "http://piwik.com/test/"));
        $this->assertEquals(1, $idsite);

        // set noaccess to site 1
        FakeAccess::setIdSitesView(array(2));
        FakeAccess::setIdSitesAdmin(array());

        try {
            $siteInfo = API::getInstance()->getSiteFromId(1);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * normal case
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testGetSiteFromIdNormalId()
    {
        $name = "website ''";
        $idsite = API::getInstance()->addSite($name, array("http://piwik.net", "http://piwik.com/test/"));
        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_INT, $idsite);

        $siteInfo = API::getInstance()->getSiteFromId($idsite);
        $this->assertEquals($name, $siteInfo['name']);
        $this->assertEquals("http://piwik.net", $siteInfo['main_url']);
    }


    /**
     * there is no admin site available -> array()
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testGetSitesWithAdminAccessNoResult()
    {
        FakeAccess::setIdSitesAdmin(array());

        $sites = API::getInstance()->getSitesWithAdminAccess();
        $this->assertEquals(array(), $sites);
    }

    /**
     * normal case, admin and view and noaccess website => return only admin
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testGetSitesWithAdminAccess()
    {
        $idsite = API::getInstance()->addSite("site1", array("http://piwik.net", "http://piwik.com/test/"));
        $idsite = API::getInstance()->addSite("site2", array("http://piwik.com/test/"));
        $idsite = API::getInstance()->addSite("site3", array("http://piwik.org"));

        $resultWanted = array(
            0 => array("idsite" => 1, "name" => "site1", "main_url" => "http://piwik.net", "ecommerce" => 0, "excluded_ips" => "", 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', 'excluded_parameters' => '', 'excluded_user_agents' => '', 'timezone' => 'UTC', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0),
            1 => array("idsite" => 3, "name" => "site3", "main_url" => "http://piwik.org", "ecommerce" => 0, "excluded_ips" => "", 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', 'excluded_parameters' => '', 'excluded_user_agents' => '', 'timezone' => 'UTC', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0),
        );

        FakeAccess::setIdSitesAdmin(array(1, 3));

        $sites = API::getInstance()->getSitesWithAdminAccess();

        // we dont test the ts_created
        unset($sites[0]['ts_created']);
        unset($sites[1]['ts_created']);
        $this->assertEquals($resultWanted, $sites);
    }

    /**
     * there is no admin site available -> array()
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testGetSitesWithViewAccessNoResult()
    {
        FakeAccess::setIdSitesView(array());
        FakeAccess::setIdSitesAdmin(array());

        $sites = API::getInstance()->getSitesWithViewAccess();
        $this->assertEquals(array(), $sites);
    }

    /**
     * normal case, admin and view and noaccess website => return only admin
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testGetSitesWithViewAccess()
    {
        $idsite = API::getInstance()->addSite("site1", array("http://piwik.net", "http://piwik.com/test/"));
        $idsite = API::getInstance()->addSite("site2", array("http://piwik.com/test/"));
        $idsite = API::getInstance()->addSite("site3", array("http://piwik.org"));

        $resultWanted = array(
            0 => array("idsite" => 1, "name" => "site1", "main_url" => "http://piwik.net", "ecommerce" => 0, 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', "excluded_ips" => "", 'excluded_parameters' => '', 'excluded_user_agents' => '', 'timezone' => 'UTC', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0),
            1 => array("idsite" => 3, "name" => "site3", "main_url" => "http://piwik.org", "ecommerce" => 0, 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', "excluded_ips" => "", 'excluded_parameters' => '', 'excluded_user_agents' => '', 'timezone' => 'UTC', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0),
        );

        FakeAccess::setIdSitesView(array(1, 3));
        FakeAccess::setIdSitesAdmin(array());

        $sites = API::getInstance()->getSitesWithViewAccess();
        // we dont test the ts_created
        unset($sites[0]['ts_created']);
        unset($sites[1]['ts_created']);
        $this->assertEquals($resultWanted, $sites);
    }

    /**
     * there is no admin site available -> array()
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testGetSitesWithAtLeastViewAccessNoResult()
    {
        FakeAccess::setIdSitesView(array());
        FakeAccess::setIdSitesAdmin(array());

        $sites = API::getInstance()->getSitesWithAtLeastViewAccess();
        $this->assertEquals(array(), $sites);
    }

    /**
     * normal case, admin and view and noaccess website => return only admin
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testGetSitesWithAtLeastViewAccess()
    {
        $idsite = API::getInstance()->addSite("site1", array("http://piwik.net", "http://piwik.com/test/"), $ecommerce = 1);
        $idsite = API::getInstance()->addSite("site2", array("http://piwik.com/test/"));
        $idsite = API::getInstance()->addSite("site3", array("http://piwik.org"));

        $resultWanted = array(
            0 => array("idsite" => 1, "name" => "site1", "main_url" => "http://piwik.net", "ecommerce" => 1, "excluded_ips" => "", 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', 'excluded_parameters' => '', 'excluded_user_agents' => '', 'timezone' => 'UTC', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0),
            1 => array("idsite" => 3, "name" => "site3", "main_url" => "http://piwik.org", "ecommerce" => 0, "excluded_ips" => "", 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', 'excluded_parameters' => '', 'excluded_user_agents' => '', 'timezone' => 'UTC', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0),
        );

        FakeAccess::setIdSitesView(array(1, 3));
        FakeAccess::setIdSitesAdmin(array());

        $sites = API::getInstance()->getSitesWithAtLeastViewAccess();
        // we dont test the ts_created
        unset($sites[0]['ts_created']);
        unset($sites[1]['ts_created']);
        $this->assertEquals($resultWanted, $sites);
    }

    /**
     * no urls for this site => array()
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testGetSiteUrlsFromIdNoUrls()
    {
        $idsite = API::getInstance()->addSite("site1", array("http://piwik.net"));

        $urls = API::getInstance()->getSiteUrlsFromId($idsite);
        $this->assertEquals(array("http://piwik.net"), $urls);
    }

    /**
     * normal case
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testGetSiteUrlsFromIdManyUrls()
    {
        $site = array("http://piwik.net",
                      "http://piwik.org",
                      "http://piwik.org",
                      "http://piwik.com");
        sort($site);

        $idsite = API::getInstance()->addSite("site1", $site);

        $siteWanted = array("http://piwik.net",
                            "http://piwik.org",
                            "http://piwik.com");
        sort($siteWanted);
        $urls = API::getInstance()->getSiteUrlsFromId($idsite);


        $this->assertEquals($siteWanted, $urls);
    }

    /**
     * wrongId => exception
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testGetSiteUrlsFromIdWrongId()
    {
        try {
            FakeAccess::setIdSitesView(array(3));
            FakeAccess::setIdSitesAdmin(array());
            API::getInstance()->getSiteUrlsFromId(1);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * one url => no change to alias urls
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testUpdateSiteOneUrl()
    {
        $urls = array("http://piwiknew.com",
                      "http://piwiknew.net",
                      "http://piwiknew.org",
                      "http://piwiknew.fr");
        $idsite = API::getInstance()->addSite("site1", $urls);

        $newMainUrl = "http://main.url";

        // Also test that the group was set to empty, and is searchable
        $websites = API::getInstance()->getSitesFromGroup('');
        $this->assertEquals(1, count($websites));

        // the Update doesn't change the group field
        API::getInstance()->updateSite($idsite, "test toto@{}", $newMainUrl);
        $websites = API::getInstance()->getSitesFromGroup('');
        $this->assertEquals(1, count($websites));

        // Updating the group to something
        $group = 'something';
        API::getInstance()->updateSite($idsite, "test toto@{}", $newMainUrl, $ecommerce = 0, $ss = true, $ss_kwd = null, $ss_cat = '', $ips = null, $parametersExclude = null, $timezone = null, $currency = null, $group);
        $websites = API::getInstance()->getSitesFromGroup($group);
        $this->assertEquals(1, count($websites));
        $this->assertEquals(date('Y-m-d'), date('Y-m-d', strtotime($websites[0]['ts_created'])));

        // Updating the group to nothing
        $group = '';
        API::getInstance()->updateSite($idsite, "test toto@{}", $newMainUrl, $ecommerce = 0, $ss = false, $ss_kwd = '', $ss_cat = null, $ips = null, $parametersExclude = null, $timezone = null, $currency = null, $group, $startDate = '2010-01-01');
        $websites = API::getInstance()->getSitesFromGroup($group);
        $this->assertEquals(1, count($websites));
        $this->assertEquals('2010-01-01', date('Y-m-d', strtotime($websites[0]['ts_created'])));

        $allUrls = API::getInstance()->getSiteUrlsFromId($idsite);
        $this->assertEquals($newMainUrl, $allUrls[0]);
        $aliasUrls = array_slice($allUrls, 1);
        $this->assertEquals(array(), $aliasUrls);
    }

    /**
     * strange name and NO URL => name ok, main_url not updated
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testUpdateSiteStrangeNameNoUrl()
    {
        $idsite = API::getInstance()->addSite("site1", "http://main.url");
        $newName = "test toto@{'786'}";

        API::getInstance()->updateSite($idsite, $newName);

        $site = API::getInstance()->getSiteFromId($idsite);

        $this->assertEquals($newName, $site['name']);
        // url didn't change because parameter url NULL in updateSite
        $this->assertEquals("http://main.url", $site['main_url']);
    }

    /**
     * several urls => both main and alias are updated
     * also test the update of group field
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testUpdateSiteSeveralUrlsAndGroup()
    {
        $urls = array("http://piwiknew.com",
                      "http://piwiknew.net",
                      "http://piwiknew.org",
                      "http://piwiknew.fr");

        $group = 'GROUP Before';
        $idsite = API::getInstance()->addSite("site1", $urls, $ecommerce = 1,
            $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null,
            $excludedIps = null, $excludedQueryParameters = null, $timezone = null, $currency = null, $group, $startDate = '2011-01-01');

        $websites = API::getInstance()->getSitesFromGroup($group);
        $this->assertEquals(1, count($websites));

        $newurls = array("http://piwiknew2.com",
                         "http://piwiknew2.net",
                         "http://piwiknew2.org",
                         "http://piwiknew2.fr");

        $groupAfter = '   GROUP After';
        API::getInstance()->updateSite($idsite, "test toto@{}", $newurls, $ecommerce = 0,
            $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null,
            $excludedIps = null, $excludedQueryParameters = null, $timezone = null, $currency = null, $groupAfter);

        // no result for the group before update 
        $websites = API::getInstance()->getSitesFromGroup($group);
        $this->assertEquals(0, count($websites));

        // Testing that the group was updated properly (and testing that the group value is trimmed before inserted/searched)
        $websites = API::getInstance()->getSitesFromGroup($groupAfter . ' ');
        $this->assertEquals(1, count($websites));
        $this->assertEquals('2011-01-01', date('Y-m-d', strtotime($websites[0]['ts_created'])));

        // Test fetch website groups
        $expectedGroups = array(trim($groupAfter));
        $fetched = API::getInstance()->getSitesGroups();
        $this->assertEquals($expectedGroups, $fetched);

        $allUrls = API::getInstance()->getSiteUrlsFromId($idsite);
        sort($allUrls);
        sort($newurls);
        $this->assertEquals($newurls, $allUrls);
    }

    /**
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testGetSitesGroups()
    {
        $groups = array('group1', ' group1 ', '', 'group2');
        $expectedGroups = array('group1', '', 'group2');
        foreach ($groups as $group) {
            API::getInstance()->addSite("test toto@{}", 'http://example.org', $ecommerce = 1, $siteSearch = null, $searchKeywordParameters = null, $searchCategoryParameters = null, $excludedIps = null, $excludedQueryParameters = null, $timezone = null, $currency = null, $group);
        }

        $this->assertEquals($expectedGroups, API::getInstance()->getSitesGroups());
    }


    public function getInvalidTimezoneData()
    {
        return array(
            array('UTC+15'),
            array('Paris'),
        );
    }

    /**
     *
     * @dataProvider getInvalidTimezoneData
     * @group Plugins
     * @group SitesManager
     */
    public function testAddSitesInvalidTimezone($timezone)
    {
        try {
            $idsite = API::getInstance()->addSite("site1", array('http://example.org'), $ecommerce = 0,
                $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null, $ip = '', $params = '', $timezone);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testAddSitesInvalidCurrency()
    {
        try {
            $invalidCurrency = '€';
            $idsite = API::getInstance()->addSite("site1", array('http://example.org'), $ecommerce = 0,
                $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null, '', 'UTC', $invalidCurrency);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testSetDefaultTimezoneAndCurrencyAndExcludedQueryParametersAndExcludedIps()
    {
        // test that they return default values
        $defaultTimezone = API::getInstance()->getDefaultTimezone();
        $this->assertEquals('UTC', $defaultTimezone);
        $defaultCurrency = API::getInstance()->getDefaultCurrency();
        $this->assertEquals('USD', $defaultCurrency);
        $excludedIps = API::getInstance()->getExcludedIpsGlobal();
        $this->assertEquals('', $excludedIps);
        $excludedQueryParameters = API::getInstance()->getExcludedQueryParametersGlobal();
        $this->assertEquals('', $excludedQueryParameters);

        // test that when not specified, defaults are set as expected  
        $idsite = API::getInstance()->addSite("site1", array('http://example.org'));
        $site = new Site($idsite);
        $this->assertEquals('UTC', $site->getTimezone());
        $this->assertEquals('USD', $site->getCurrency());
        $this->assertEquals('', $site->getExcludedQueryParameters());
        $this->assertEquals('', $site->getExcludedIps());
        $this->assertEquals(false, $site->isEcommerceEnabled());

        // set the global timezone and get it
        $newDefaultTimezone = 'UTC+5.5';
        API::getInstance()->setDefaultTimezone($newDefaultTimezone);
        $defaultTimezone = API::getInstance()->getDefaultTimezone();
        $this->assertEquals($newDefaultTimezone, $defaultTimezone);

        // set the default currency and get it
        $newDefaultCurrency = 'EUR';
        API::getInstance()->setDefaultCurrency($newDefaultCurrency);
        $defaultCurrency = API::getInstance()->getDefaultCurrency();
        $this->assertEquals($newDefaultCurrency, $defaultCurrency);

        // set the global IPs to exclude and get it
        $newGlobalExcludedIps = '1.1.1.*,1.1.*.*,150.1.1.1';
        API::getInstance()->setGlobalExcludedIps($newGlobalExcludedIps);
        $globalExcludedIps = API::getInstance()->getExcludedIpsGlobal();
        $this->assertEquals($newGlobalExcludedIps, $globalExcludedIps);

        // set the global URL query params to exclude and get it
        $newGlobalExcludedQueryParameters = 'PHPSESSID,blabla, TesT';
        // removed the space
        $expectedGlobalExcludedQueryParameters = 'PHPSESSID,blabla,TesT';
        API::getInstance()->setGlobalExcludedQueryParameters($newGlobalExcludedQueryParameters);
        $globalExcludedQueryParameters = API::getInstance()->getExcludedQueryParametersGlobal();
        $this->assertEquals($expectedGlobalExcludedQueryParameters, $globalExcludedQueryParameters);

        // create a website and check that default currency and default timezone are set
        // however, excluded IPs and excluded query Params are not returned
        $idsite = API::getInstance()->addSite("site1", array('http://example.org'), $ecommerce = 0,
            $siteSearch = 0, $searchKeywordParameters = 'test1,test2', $searchCategoryParameters = 'test2,test1',
            '', '', $newDefaultTimezone);
        $site = new Site($idsite);
        $this->assertEquals($newDefaultTimezone, $site->getTimezone());
        $this->assertEquals(date('Y-m-d'), $site->getCreationDate()->toString());
        $this->assertEquals($newDefaultCurrency, $site->getCurrency());
        $this->assertEquals('', $site->getExcludedIps());
        $this->assertEquals('', $site->getExcludedQueryParameters());
        $this->assertEquals('test1,test2', $site->getSearchKeywordParameters());
        $this->assertEquals('test2,test1', $site->getSearchCategoryParameters());
        $this->assertFalse($site->isSiteSearchEnabled());
        $this->assertFalse(Site::isSiteSearchEnabledFor($idsite));
        $this->assertFalse($site->isEcommerceEnabled());
        $this->assertFalse(Site::isEcommerceEnabledFor($idsite));
    }

    /**
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testGetSitesIdFromSiteUrlSuperUser()
    {
        $idsite = API::getInstance()->addSite("site1", array("http://piwik.net", "http://piwik.com"));
        $idsite = API::getInstance()->addSite("site2", array("http://piwik.com", "http://piwik.net"));
        $idsite = API::getInstance()->addSite("site3", array("http://piwik.com", "http://piwik.org"));

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.org');
        $this->assertTrue(count($idsites) == 1);

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://www.piwik.org');
        $this->assertTrue(count($idsites) == 1);

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.net');
        $this->assertTrue(count($idsites) == 2);

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.com');
        $this->assertTrue(count($idsites) == 3);
    }

    /**
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testGetSitesIdFromSiteUrlUser()
    {
        $idsite = API::getInstance()->addSite("site1", array("http://www.piwik.net", "http://piwik.com"));
        $idsite = API::getInstance()->addSite("site2", array("http://piwik.com", "http://piwik.net"));
        $idsite = API::getInstance()->addSite("site3", array("http://piwik.com", "http://piwik.org"));

        $saveAccess = Access::getInstance();

        APIUsersManager::getInstance()->addUser("user1", "geqgegagae", "tegst@tesgt.com", "alias");
        APIUsersManager::getInstance()->setUserAccess("user1", "view", array(1));

        APIUsersManager::getInstance()->addUser("user2", "geqgegagae", "tegst2@tesgt.com", "alias");
        APIUsersManager::getInstance()->setUserAccess("user2", "view", array(1));
        APIUsersManager::getInstance()->setUserAccess("user2", "admin", array(3));

        APIUsersManager::getInstance()->addUser("user3", "geqgegagae", "tegst3@tesgt.com", "alias");
        APIUsersManager::getInstance()->setUserAccess("user3", "view", array(1, 2));
        APIUsersManager::getInstance()->setUserAccess("user3", "admin", array(3));

        $pseudoMockAccess = new FakeAccess;
        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'user1';
        FakeAccess::setIdSitesView(array(1));
        FakeAccess::setIdSitesAdmin(array());
        Access::setSingletonInstance($pseudoMockAccess);
        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.com');
        $this->assertEquals(1, count($idsites));

        // testing URL normalization
        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://www.piwik.com');
        $this->assertEquals(1, count($idsites));
        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.net');
        $this->assertEquals(1, count($idsites));

        $pseudoMockAccess = new FakeAccess;
        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'user2';
        FakeAccess::setIdSitesView(array(1));
        FakeAccess::setIdSitesAdmin(array(3));
        Access::setSingletonInstance($pseudoMockAccess);
        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.com');
        $this->assertEquals(2, count($idsites));

        $pseudoMockAccess = new FakeAccess;
        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'user3';
        FakeAccess::setIdSitesView(array(1, 2));
        FakeAccess::setIdSitesAdmin(array(3));
        Access::setSingletonInstance($pseudoMockAccess);
        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.com');
        $this->assertEquals(3, count($idsites));

        Access::setSingletonInstance($saveAccess);
    }

    /**
     *
     * @group Plugins
     * @group SitesManager
     */
    public function testGetSitesFromTimezones()
    {
        $idsite1 = API::getInstance()->addSite("site3", array("http://piwik.org"), null, $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null, null, null, 'UTC');
        $idsite2 = API::getInstance()->addSite("site3", array("http://piwik.org"), null, $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null, null, null, 'Pacific/Auckland');
        $idsite3 = API::getInstance()->addSite("site3", array("http://piwik.org"), null, $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null, null, null, 'Pacific/Auckland');
        $idsite4 = API::getInstance()->addSite("site3", array("http://piwik.org"), null, $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null, null, null, 'UTC+10');
        $result = API::getInstance()->getSitesIdFromTimezones(array('UTC+10', 'Pacific/Auckland'));
        $this->assertEquals(array($idsite2, $idsite3, $idsite4), $result);
    }
}
