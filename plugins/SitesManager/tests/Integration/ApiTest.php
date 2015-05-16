<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Integration;

use Piwik\Access;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API;
use Piwik\Plugins\SitesManager\Model;
use Piwik\Plugins\UsersManager\API as APIUsersManager;
use Piwik\Site;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Exception;
use PHPUnit_Framework_Constraint_IsType;

/**
 * Class Plugins_SitesManagerTest
 *
 * @group Plugins
 * @group ApiTest
 * @group SitesManager
 */
class ApiTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();

        // setup the access layer
        FakeAccess::$superUser = true;
    }

    /**
     * empty name -> exception
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
        $expectedWebsiteType = 'mobile-\'app';
        $keepUrlFragment = 1;
        $idsite = API::getInstance()->addSite("name", "http://piwik.net/", $ecommerce = 1,
            $siteSearch = 1, $searchKeywordParameters = 'search,param', $searchCategoryParameters = 'cat,category',
            $ips, $excludedQueryParameters, $timezone, $currency, $group = null, $startDate = null, $excludedUserAgents,
            $keepUrlFragment, $expectedWebsiteType);
        $siteInfo = API::getInstance()->getSiteFromId($idsite);
        $this->assertEquals($ips, $siteInfo['excluded_ips']);
        $this->assertEquals($timezone, $siteInfo['timezone']);
        $this->assertEquals($currency, $siteInfo['currency']);
        $this->assertEquals($ecommerce, $siteInfo['ecommerce']);
        $this->assertTrue(Site::isEcommerceEnabledFor($idsite));
        $this->assertEquals($siteSearch, $siteInfo['sitesearch']);
        $this->assertTrue(Site::isSiteSearchEnabledFor($idsite));
        $this->assertEquals($expectedWebsiteType, $siteInfo['type']);
        $this->assertEquals($expectedWebsiteType, Site::getTypeFor($idsite));

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
     */
    public function testAddSiteUrlsWrongUrlsFormat3()
    {
        try {
            $idsite = $this->_addSite();
            $toAdd = array("http:mpigeq");
            API::getInstance()->addSiteAliasUrls($idsite, $toAdd);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * wrong idsite => no exception because simply no access to this resource
     */
    public function testAddSiteUrlsWrongIdSite1()
    {
        try {
            $toAdd = array("http://pigeq.com/test");
            API::getInstance()->addSiteAliasUrls(-1, $toAdd);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * wrong idsite => exception
     */
    public function testAddSiteUrlsWrongIdSite2()
    {
        try {
            $toAdd = array("http://pigeq.com/test");
            API::getInstance()->addSiteAliasUrls(155, $toAdd);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * no Id -> empty array
     */
    public function testGetAllSitesIdNoId()
    {
        $ids = API::getInstance()->getAllSitesId();
        $this->assertEquals(array(), $ids);
    }

    /**
     * several Id -> normal array
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
     */
    public function testGetSiteFromIdWrongId1()
    {
        try {
            API::getInstance()->getSiteFromId(0);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * wrong id => exception
     */
    public function testGetSiteFromIdWrongId2()
    {
        try {
            API::getInstance()->getSiteFromId("x1");
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * wrong id : no access => exception
     */
    public function testGetSiteFromIdWrongId3()
    {
        $idsite = API::getInstance()->addSite("site", array("http://piwik.net", "http://piwik.com/test/"));
        $this->assertEquals(1, $idsite);

        // set noaccess to site 1
        FakeAccess::setIdSitesView(array(2));
        FakeAccess::setIdSitesAdmin(array());

        try {
            API::getInstance()->getSiteFromId(1);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * normal case
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
     */
    public function testGetSitesWithAdminAccessNoResult()
    {
        FakeAccess::setIdSitesAdmin(array());

        $sites = API::getInstance()->getSitesWithAdminAccess();
        $this->assertEquals(array(), $sites);
    }

    /**
     * normal case, admin and view and noaccess website => return only admin
     */
    public function testGetSitesWithAdminAccess_shouldOnlyReturnSitesHavingActuallyAdminAccess()
    {
        API::getInstance()->addSite("site1", array("http://piwik.net", "http://piwik.com/test/"));
        API::getInstance()->addSite("site2", array("http://piwik.com/test/"));
        API::getInstance()->addSite("site3", array("http://piwik.org"));

        $resultWanted = array(
            0 => array("idsite" => 1, "name" => "site1", "main_url" => "http://piwik.net", "ecommerce" => 0, "excluded_ips" => "", 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', 'excluded_parameters' => '', 'excluded_user_agents' => '', 'timezone' => 'UTC', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0, 'type' => 'website'),
            1 => array("idsite" => 3, "name" => "site3", "main_url" => "http://piwik.org", "ecommerce" => 0, "excluded_ips" => "", 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', 'excluded_parameters' => '', 'excluded_user_agents' => '', 'timezone' => 'UTC', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0, 'type' => 'website'),
        );

        FakeAccess::setIdSitesAdmin(array(1, 3));

        $sites = API::getInstance()->getSitesWithAdminAccess();

        // we dont test the ts_created
        unset($sites[0]['ts_created']);
        unset($sites[1]['ts_created']);
        $this->assertEquals($resultWanted, $sites);
    }

    public function testGetSitesWithAdminAccess_shouldApplyLimit_IfSet()
    {
        $this->createManySitesWithAdminAccess(40);

        // should return all sites by default
        $sites = API::getInstance()->getSitesWithAdminAccess();
        $this->assertReturnedSitesContainsSiteIds(range(1, 40), $sites);

        // return only 5 sites
        $sites = API::getInstance()->getSitesWithAdminAccess(false, false, 5);
        $this->assertReturnedSitesContainsSiteIds(array(1, 2, 3, 4, 5), $sites);

        // return only 10 sites
        $sites = API::getInstance()->getSitesWithAdminAccess(false, false, 10);
        $this->assertReturnedSitesContainsSiteIds(range(1, 10), $sites);
    }

    public function testGetSitesWithAdminAccess_shouldApplyPattern_IfSetAndFindBySiteName()
    {
        $this->createManySitesWithAdminAccess(40);

        // by site name
        $sites = API::getInstance()->getSitesWithAdminAccess(false, 'site38');
        $this->assertReturnedSitesContainsSiteIds(array(38), $sites);
    }

    public function testGetSitesWithAdminAccess_shouldApplyPattern_IfSetAndFindByUrl()
    {
        $this->createManySitesWithAdminAccess(40);

        $sites = API::getInstance()->getSitesWithAdminAccess(false, 'piwik38.o');
        $this->assertReturnedSitesContainsSiteIds(array(38), $sites);
    }

    public function testGetSitesWithAdminAccess_shouldApplyPattern_AndFindMany()
    {
        $this->createManySitesWithAdminAccess(40);

        $sites = API::getInstance()->getSitesWithAdminAccess(false, '5');
        $this->assertReturnedSitesContainsSiteIds(array(5, 15, 25, 35), $sites);
    }

    public function testGetSitesWithAdminAccess_shouldApplyPatternAndLimit()
    {
        $this->createManySitesWithAdminAccess(40);

        $sites = API::getInstance()->getSitesWithAdminAccess(false, '5', 2);
        $this->assertReturnedSitesContainsSiteIds(array(5, 15), $sites);
    }

    private function createManySitesWithAdminAccess($numSites)
    {
        for ($i = 1; $i <= $numSites; $i++) {
            API::getInstance()->addSite("site" . $i, array("http://piwik$i.org"));
        }

        FakeAccess::setIdSitesAdmin(range(1, $numSites));
    }

    private function assertReturnedSitesContainsSiteIds($expectedSiteIds, $sites)
    {
        $this->assertCount(count($expectedSiteIds), $sites);

        foreach ($sites as $site) {
            $key = array_search($site['idsite'], $expectedSiteIds);
            $this->assertNotFalse($key, 'Did not find expected siteId "' . $site['idsite'] . '" in the expected siteIds');
            unset($expectedSiteIds[$key]);
        }

        $siteIds = var_export($expectedSiteIds, 1);
        $this->assertEmpty($expectedSiteIds, 'Not all expected sites were found, remaining site ids: ' . $siteIds);
    }

    /**
     * there is no admin site available -> array()
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
     */
    public function testGetSitesWithViewAccess()
    {
        API::getInstance()->addSite("site1", array("http://piwik.net", "http://piwik.com/test/"));
        API::getInstance()->addSite("site2", array("http://piwik.com/test/"));
        API::getInstance()->addSite("site3", array("http://piwik.org"));

        $resultWanted = array(
            0 => array("idsite" => 1, "name" => "site1", "main_url" => "http://piwik.net", "ecommerce" => 0, 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', "excluded_ips" => "", 'excluded_parameters' => '', 'excluded_user_agents' => '', 'timezone' => 'UTC', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0, 'type' => 'website'),
            1 => array("idsite" => 3, "name" => "site3", "main_url" => "http://piwik.org", "ecommerce" => 0, 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', "excluded_ips" => "", 'excluded_parameters' => '', 'excluded_user_agents' => '', 'timezone' => 'UTC', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0, 'type' => 'website'),
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
     */
    public function testGetSitesWithAtLeastViewAccess()
    {
        API::getInstance()->addSite("site1", array("http://piwik.net", "http://piwik.com/test/"), $ecommerce = 1);
        API::getInstance()->addSite("site2", array("http://piwik.com/test/"));
        API::getInstance()->addSite("site3", array("http://piwik.org"));

        $resultWanted = array(
            0 => array("idsite" => 1, "name" => "site1", "main_url" => "http://piwik.net", "ecommerce" => 1, "excluded_ips" => "", 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', 'excluded_parameters' => '', 'excluded_user_agents' => '', 'timezone' => 'UTC', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0, 'type' => 'website'),
            1 => array("idsite" => 3, "name" => "site3", "main_url" => "http://piwik.org", "ecommerce" => 0, "excluded_ips" => "", 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', 'excluded_parameters' => '', 'excluded_user_agents' => '', 'timezone' => 'UTC', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0, 'type' => 'website'),
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
     */
    public function testGetSiteUrlsFromIdNoUrls()
    {
        $idsite = API::getInstance()->addSite("site1", array("http://piwik.net"));

        $urls = API::getInstance()->getSiteUrlsFromId($idsite);
        $this->assertEquals(array("http://piwik.net"), $urls);
    }

    /**
     * normal case
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
        $type = 'mobileAppTest';
        API::getInstance()->updateSite($idsite, "test toto@{}", $newMainUrl, $ecommerce = 0, $ss = false, $ss_kwd = '', $ss_cat = null, $ips = null, $parametersExclude = null, $timezone = null, $currency = null, $group, $startDate = '2010-01-01', $excludedUserAgent = null, $keepUrlFragment = 1, $type);
        $websites = API::getInstance()->getSitesFromGroup($group);
        $this->assertEquals(1, count($websites));
        $this->assertEquals('2010-01-01', date('Y-m-d', strtotime($websites[0]['ts_created'])));

        // Test setting the website type
        $this->assertEquals($type, Site::getTypeFor($idsite));

        // Check Alias URLs contain only main url
        $allUrls = API::getInstance()->getSiteUrlsFromId($idsite);
        $this->assertEquals($newMainUrl, $allUrls[0]);
        $aliasUrls = array_slice($allUrls, 1);
        $this->assertEquals(array(), $aliasUrls);

    }

    /**
     * strange name and NO URL => name ok, main_url not updated
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
     * @expectedException Exception
     * @expectedExceptionMessage SitesManager_ExceptionDeleteSite
     */
    public function test_delete_ShouldNotDeleteASiteInCaseThereIsOnlyOneSite()
    {
        $siteId1 = $this->_addSite();

        $this->assertHasSite($siteId1);

        try {
            API::getInstance()->deleteSite($siteId1);
            $this->fail('an expected exception was not raised');
        } catch (Exception $e) {
            $this->assertHasSite($siteId1);
            throw $e;
        }
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage website id = 99999498 not found
     */
    public function test_delete_ShouldTriggerException_IfGivenSiteDoesNotExist()
    {
        API::getInstance()->deleteSite(99999498);
    }

    public function test_delete_ShouldActuallyRemoveAnExistingSiteButOnlyTheGivenSite()
    {
        $this->_addSite();
        $siteId1 = $this->_addSite();
        $siteId2 = $this->_addSite();

        $this->assertHasSite($siteId1);
        $this->assertHasSite($siteId2);

        API::getInstance()->deleteSite($siteId1);

        $this->assertHasNotSite($siteId1);
        $this->assertHasSite($siteId2);
    }

    public function test_delete_ShouldTriggerAnEventOnceSiteWasActuallyDeleted()
    {
        $called = 0;
        $deletedSiteId = null;

        Piwik::addAction('SitesManager.deleteSite.end', function ($param) use (&$called, &$deletedSiteId) {
            $called++;
            $deletedSiteId = $param;
        });

        $this->_addSite();
        $siteId1 = $this->_addSite();

        API::getInstance()->deleteSite($siteId1);

        $this->assertSame(1, $called);
        $this->assertSame($siteId1, $deletedSiteId);
    }

    private function assertHasSite($idSite)
    {
        $model = new Model();
        $siteInfo = $model->getSiteFromId($idSite);
        $this->assertNotEmpty($siteInfo);
    }

    private function assertHasNotSite($idSite)
    {
        $model = new Model();
        $siteInfo = $model->getSiteFromId($idSite);
        $this->assertEmpty($siteInfo);
    }

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
     */
    public function testAddSitesInvalidTimezone($timezone)
    {
        try {
            API::getInstance()->addSite("site1", array('http://example.org'), $ecommerce = 0,
                $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null, $ip = '', $params = '', $timezone);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    public function testAddSitesInvalidCurrency()
    {
        try {
            $invalidCurrency = '€';
            API::getInstance()->addSite("site1", array('http://example.org'), $ecommerce = 0,
                $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null, '', 'UTC', $invalidCurrency);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

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

    public function testGetSitesIdFromSiteUrlSuperUser()
    {
        API::getInstance()->addSite("site1", array("http://piwik.net", "http://piwik.com"));
        API::getInstance()->addSite("site2", array("http://piwik.com", "http://piwik.net"));
        API::getInstance()->addSite("site3", array("http://piwik.com", "http://piwik.org"));

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.org');
        $this->assertTrue(count($idsites) == 1);

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://www.piwik.org');
        $this->assertTrue(count($idsites) == 1);

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.net');
        $this->assertTrue(count($idsites) == 2);

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.com');
        $this->assertTrue(count($idsites) == 3);
    }

    public function testGetSitesIdFromSiteUrlUser()
    {
        API::getInstance()->addSite("site1", array("http://www.piwik.net", "http://piwik.com"));
        API::getInstance()->addSite("site2", array("http://piwik.com", "http://piwik.net"));
        API::getInstance()->addSite("site3", array("http://piwik.com", "http://piwik.org"));

        APIUsersManager::getInstance()->addUser("user1", "geqgegagae", "tegst@tesgt.com", "alias");
        APIUsersManager::getInstance()->setUserAccess("user1", "view", array(1));

        APIUsersManager::getInstance()->addUser("user2", "geqgegagae", "tegst2@tesgt.com", "alias");
        APIUsersManager::getInstance()->setUserAccess("user2", "view", array(1));
        APIUsersManager::getInstance()->setUserAccess("user2", "admin", array(3));

        APIUsersManager::getInstance()->addUser("user3", "geqgegagae", "tegst3@tesgt.com", "alias");
        APIUsersManager::getInstance()->setUserAccess("user3", "view", array(1, 2));
        APIUsersManager::getInstance()->setUserAccess("user3", "admin", array(3));

        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'user1';
        FakeAccess::setIdSitesView(array(1));
        FakeAccess::setIdSitesAdmin(array());
        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.com');
        $this->assertEquals(1, count($idsites));

        // testing URL normalization
        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://www.piwik.com');
        $this->assertEquals(1, count($idsites));
        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.net');
        $this->assertEquals(1, count($idsites));

        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'user2';
        FakeAccess::setIdSitesView(array(1));
        FakeAccess::setIdSitesAdmin(array(3));
        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.com');
        $this->assertEquals(2, count($idsites));

        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'user3';
        FakeAccess::setIdSitesView(array(1, 2));
        FakeAccess::setIdSitesAdmin(array(3));
        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.com');
        $this->assertEquals(3, count($idsites));
    }

    public function testGetSitesFromTimezones()
    {
        API::getInstance()->addSite("site3", array("http://piwik.org"), null, $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null, null, null, 'UTC');
        $idsite2 = API::getInstance()->addSite("site3", array("http://piwik.org"), null, $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null, null, null, 'Pacific/Auckland');
        $idsite3 = API::getInstance()->addSite("site3", array("http://piwik.org"), null, $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null, null, null, 'Pacific/Auckland');
        $idsite4 = API::getInstance()->addSite("site3", array("http://piwik.org"), null, $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null, null, null, 'UTC+10');
        $result = API::getInstance()->getSitesIdFromTimezones(array('UTC+10', 'Pacific/Auckland'));
        $this->assertEquals(array($idsite2, $idsite3, $idsite4), $result);
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
