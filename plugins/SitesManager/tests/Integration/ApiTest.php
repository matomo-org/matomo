<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Integration;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugins\IntranetMeasurable\Type as IntranetType;
use Piwik\Plugins\MobileAppMeasurable;
use Piwik\Plugins\SitesManager\SitesManager;
use Piwik\Plugins\WebsiteMeasurable\Type as WebsiteType;
use Piwik\Plugins\SitesManager\API;
use Piwik\Plugins\SitesManager\Model;
use Piwik\Plugins\UsersManager\API as APIUsersManager;
use Piwik\Measurable\Measurable;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Exception;

/**
 * Class Plugins_SitesManagerTest
 *
 * @group Plugins
 * @group ApiTest
 * @group SitesManager
 */
class ApiTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Plugin\Manager::getInstance()->activatePlugin('MobileAppMeasurable');

        // setup the access layer
        FakeAccess::$superUser = true;
    }

    public function tearDown(): void
    {
        parent::tearDown();

        Fixture::resetTranslations();
    }

    /**
     * empty name -> exception
     */
    public function testAddSiteWithEmptyNameThrowsException()
    {
        $this->expectException(\Exception::class);

        API::getInstance()->addSite("", ["http://piwik.net"]);
    }

    /**
     * DataProvider for testAddSiteWrongUrls
     */
    public function getInvalidUrlData()
    {
        return [
            [[]], // no urls
            [[""]],
            [""],
            ["5http://piwik.net"],
            ["???://piwik.net"],
        ];
    }

    /**
     * wrong urls -> exception
     *
     * @dataProvider getInvalidUrlData
     */
    public function testAddSiteWithWrongUrlsThrowsException($url)
    {
        $this->expectException(\Exception::class);
        API::getInstance()->addSite("name", $url);
    }

    /**
     * @dataProvider getDifferentTypesDataProvider
     */
    public function testAddSiteWhenTypeIsKnown($expectedWebsiteType)
    {
        $this->addSiteTest($expectedWebsiteType);
    }

    private function addSiteTest($expectedWebsiteType, $settingValues = null)
    {
        $ips = '1.2.3.4,1.1.1.*,1.2.*.*,1.*.*.*';
        $timezone = 'Europe/Paris';
        $currency = 'EUR';
        $excludedQueryParameters = 'p1,P2, P33333';
        $expectedExcludedQueryParameters = 'p1,P2,P33333';
        $excludedUserAgents = " p1,P2, \nP3333 ";
        $expectedExcludedUserAgents = "p1,P2,P3333";
        $excludedReferrers = 'http://www.paypal.com,https://amazon.com';
        $keepUrlFragment = 1;
        $idsite = API::getInstance()->addSite(
            "name",
            "http://piwik.net/",
            $ecommerce = 1,
            $siteSearch = 1,
            $searchKeywordParameters = 'search,param',
            $searchCategoryParameters = 'cat,category',
            $ips,
            $excludedQueryParameters,
            $timezone,
            $currency,
            $group = null,
            $startDate = null,
            $excludedUserAgents,
            $keepUrlFragment,
            $expectedWebsiteType,
            $settingValues,
            null,
            $excludedReferrers
        );
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
        $this->assertEquals('superUserLogin', $siteInfo['creator_login']);
        $this->assertEquals('superUserLogin', Site::getCreatorLoginFor($idsite));

        $this->assertEquals($searchKeywordParameters, $siteInfo['sitesearch_keyword_parameters']);
        $this->assertEquals($searchCategoryParameters, $siteInfo['sitesearch_category_parameters']);
        $this->assertEquals($expectedExcludedQueryParameters, $siteInfo['excluded_parameters']);
        $this->assertEquals($expectedExcludedUserAgents, $siteInfo['excluded_user_agents']);
        $this->assertEquals($excludedReferrers, $siteInfo['excluded_referrers']);

        return $siteInfo;
    }

    /**
     * dataProvider for testAddSiteExcludedIpsNotValid
     */
    public function getInvalidIPsData()
    {
        return [
            ['35817587341'],
            ['ieagieha'],
            ['1.2.3'],
            ['*.1.1.1'],
            ['*.*.1.1'],
            ['*.*.*.1'],
            ['1.1.1.1.1'],
        ];
    }

    /**
     * Test with invalid IPs
     *
     * @dataProvider getInvalidIPsData
     */
    public function testAddSiteWithInvalidExcludedIpsThrowsException($ip)
    {
        $this->expectException(\Exception::class);
        API::getInstance()->addSite(
            "name",
            "http://piwik.net/",
            $ecommerce = 0,
            $siteSearch = 1,
            $searchKeywordParameters = null,
            $searchCategoryParameters = null,
            $ip
        );
    }

    /**
     * one url -> one main_url and nothing inserted as alias urls
     */
    public function testAddSiteWithOneUrlSucceedsAndCreatesNoAliasUrls()
    {
        $url = "http://piwik.net/";
        $urlOK = "http://piwik.net";
        $idsite = API::getInstance()->addSite("name", $url);
        self::assertIsInt($idsite);

        $siteInfo = API::getInstance()->getSiteFromId($idsite);
        $this->assertEquals($urlOK, $siteInfo['main_url']);
        $this->assertEquals(date('Y-m-d'), date('Y-m-d', strtotime($siteInfo['ts_created'])));

        $siteUrls = API::getInstance()->getSiteUrlsFromId($idsite);
        $this->assertEquals(1, count($siteUrls));
    }

    /**
     * several urls -> one main_url and others as alias urls
     */
    public function testAddSiteWithSeveralUrlsSucceedsAndCreatesAliasUrls()
    {
        $urls = ["http://piwik.net/", "http://piwik.com", "https://piwik.net/test/", "piwik.net/another/test"];
        $urlsOK = ["http://piwik.net", "http://piwik.com", "http://piwik.net/another/test", "https://piwik.net/test"];
        $idsite = API::getInstance()->addSite("super website", $urls);
        self::assertIsInt($idsite);

        $siteInfo = API::getInstance()->getSiteFromId($idsite);
        $this->assertEquals($urlsOK[0], $siteInfo['main_url']);

        $siteUrls = API::getInstance()->getSiteUrlsFromId($idsite);
        $this->assertEquals($urlsOK, $siteUrls);
    }

    /**
     * strange name
     */
    public function testAddSiteWithStrangeNameSucceeds()
    {
        $name = "supertest(); ~@@()''!£\$'%%^'!£ போ";
        $idsite = API::getInstance()->addSite($name, "http://piwik.net");
        self::assertIsInt($idsite);

        $siteInfo = API::getInstance()->getSiteFromId($idsite);
        $this->assertEquals($name, $siteInfo['name']);
    }

    /**
     * @dataProvider getDifferentTypesDataProvider
     */
    public function testAddSiteShouldFailAndNotCreatedASiteIfASettingIsInvalid($type)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('SitesManager_OnlyMatchedUrlsAllowed');

        try {
            $settings = ['WebsiteMeasurable' => [['name' => 'exclude_unknown_urls', 'value' => 'fooBar']]];
            $this->addSiteWithType($type, $settings);
        } catch (Exception $e) {
            // make sure no site created
            $ids = API::getInstance()->getAllSitesId();
            $this->assertEquals([], $ids);

            throw $e;
        }
    }

    public function testAddSiteShouldFailAndNotCreateASiteIfTypeIsInvalid()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid website type notexistingtype');

        try {
            $settings = ['WebsiteMeasurable' => [['name' => 'exclude_unknown_urls', 'value' => 'fooBar']]];
            $this->addSiteWithType('notexistingtype', $settings);
        } catch (Exception $e) {
            // make sure no site created
            $ids = API::getInstance()->getAllSitesId();
            $this->assertEquals([], $ids);

            throw $e;
        }
    }

    public function testAddSiteShouldSavePassedMeasurableSettingsIfSettingsAreValid()
    {
        $type = WebsiteType::ID;
        $settings = ['WebsiteMeasurable' => [['name' => 'urls', 'value' => ['http://www.piwik.org']]]];
        $idSite = $this->addSiteWithType($type, $settings);

        $this->assertSame(1, $idSite);

        $settings = $this->getWebsiteMeasurable($idSite);
        $urls = $settings->urls->getValue();

        $this->assertSame(['http://www.piwik.org'], $urls);
    }

    /**
     * @return \Piwik\Plugins\WebsiteMeasurable\MeasurableSettings
     */
    private function getWebsiteMeasurable($idSite)
    {
        $settings = StaticContainer::get('Piwik\Plugin\SettingsProvider');
        return $settings->getMeasurableSettings('WebsiteMeasurable', $idSite, null);
    }

    /**
     * adds a site
     * use by several other unit tests
     */
    protected function addSite()
    {
        $name = "website ";
        $idsite = API::getInstance()->addSite($name, ["http://piwik.net", "http://piwik.com/test/"]);
        self::assertIsInt($idsite);

        $siteInfo = API::getInstance()->getSiteFromId($idsite);
        $this->assertEquals($name, $siteInfo['name']);
        $this->assertEquals("http://piwik.net", $siteInfo['main_url']);

        $siteUrls = API::getInstance()->getSiteUrlsFromId($idsite);
        $this->assertEquals(["http://piwik.net", "http://piwik.com/test"], $siteUrls);

        return $idsite;
    }

    private function addSiteWithType($type, $settings)
    {
        return API::getInstance()->addSite(
            "name",
            "http://piwik.net/",
            $ecommerce = 0,
            $siteSearch = 1,
            $searchKeywordParameters = null,
            $searchCategoryParameters = null,
            $ip = null,
            $excludedQueryParameters = null,
            $timezone = null,
            $currency = null,
            $group = null,
            $startDate = null,
            $excludedUserAgents = null,
            $keepURLFragments = null,
            $type,
            $settings
        );
    }

    private function updateSiteSettings($idSite, $newSiteName, $settings)
    {
        return API::getInstance()->updateSite(
            $idSite,
            $newSiteName,
            $urls = null,
            $ecommerce = null,
            $siteSearch = null,
            $searchKeywordParameters = null,
            $searchCategoryParameters = null,
            $excludedIps = null,
            $excludedQueryParameters = null,
            $timezone = null,
            $currency = null,
            $group = null,
            $startDate = null,
            $excludedUserAgents = null,
            $keepURLFragments = null,
            $type = null,
            $settings
        );
    }

    /**
     * no duplicate -> all the urls are saved
     */
    public function testAddSiteAliasUrlsWithUniqueUrlsSavesAllUrls()
    {
        $idsite = $this->addSite();

        $siteUrlsBefore = API::getInstance()->getSiteUrlsFromId($idsite);

        $toAdd = ["http://piwik1.net",
                       "http://piwik2.net",
                       "http://piwik3.net/test/",
                       "http://localhost/test",
                       "http://localho5.st/test",
                       "http://l42578gqege.f4",
                       "http://super.com/test/test/atqata675675/te"
        ];
        $toAddValid = ["http://piwik1.net",
                            "http://piwik2.net",
                            "http://piwik3.net/test",
                            "http://localhost/test",
                            "http://localho5.st/test",
                            "http://l42578gqege.f4",
                            "http://super.com/test/test/atqata675675/te"];

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
    public function testAddSiteAliasUrlsWithDuplicateUrlsRemovesDuplicatesBeforeSaving()
    {
        $idsite = $this->addSite();

        $siteUrlsBefore = API::getInstance()->getSiteUrlsFromId($idsite);

        $toAdd = array_merge($siteUrlsBefore, ["http://piwik1.net", "http://piwik2.net"]);

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
    public function testAddSiteAliasUrlsWithNoUrlsDoesNothing()
    {
        $idsite = $this->addSite();

        $siteUrlsBefore = API::getInstance()->getSiteUrlsFromId($idsite);

        $toAdd = [];

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
    public function testAddSiteAliasUrlsWithAlreadyPersistedUrlsDoesNothing()
    {
        $idsite = $this->addSite();

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
    public function testAddSiteAliasUrlsWithIncorrectFormatThrowsException3()
    {
        $this->expectException(\Exception::class);

        $idsite = $this->addSite();
        $toAdd = ["http:mpigeq"];
        API::getInstance()->addSiteAliasUrls($idsite, $toAdd);
    }

    /**
     * wrong idsite => no exception because simply no access to this resource
     */
    public function testAddSiteAliasUrlsWithWrongIdSiteThrowsException()
    {
        $this->expectException(\Exception::class);
        $toAdd = ["http://pigeq.com/test"];
        API::getInstance()->addSiteAliasUrls(-1, $toAdd);
    }

    /**
     * wrong idsite => exception
     */
    public function testAddSiteAliasUrlsWithWrongIdSiteThrowsException2()
    {
        $this->expectException(\Exception::class);
        $toAdd = ["http://pigeq.com/test"];
        API::getInstance()->addSiteAliasUrls(155, $toAdd);
    }

    public function testAddSiteCorrectlySavesExcludeUnknownUrlsSetting()
    {
        $idSite = API::getInstance()->addSite(
            "site",
            ["http://piwik.net"],
            $ecommerce = null,
            $siteSearch = null,
            $searchKeywordParams = null,
            $searchCategoryParams = null,
            $excludedIps = null,
            $excludedQueryParams = null,
            $timezone = null,
            $currency = null,
            $group = null,
            $startDate = null,
            $excludedUserAgents = null,
            $keepUrlFragments = null,
            $type = null,
            $settings = null,
            $excludeUnknownUrls = true
        );

        $site = API::getInstance()->getSiteFromId($idSite);
        $this->assertEquals(1, $site['exclude_unknown_urls']);
    }

    /**
     * no Id -> empty array
     */
    public function testGetAllSitesIdReturnsNothingWhenNoSitesSaved()
    {
        $ids = API::getInstance()->getAllSitesId();
        $this->assertEquals([], $ids);
    }

    /**
     * several Id -> normal array
     */
    public function testGetAllSitesIdReturnsAllIdsWhenMultipleSitesPersisted()
    {
        $name = "tetq";
        $idsites = [
            API::getInstance()->addSite($name, ["http://piwik.net", "http://piwik.com/test/"]),
            API::getInstance()->addSite($name, ["http://piwik.net", "http://piwik.com/test/"]),
            API::getInstance()->addSite($name, ["http://piwik.net", "http://piwik.com/test/"]),
            API::getInstance()->addSite($name, ["http://piwik.net", "http://piwik.com/test/"]),
            API::getInstance()->addSite($name, ["http://piwik.net", "http://piwik.com/test/"]),
        ];

        $ids = API::getInstance()->getAllSitesId();
        $this->assertEquals($idsites, $ids);
    }

    /**
     * wrong id => exception
     */
    public function testGetSiteFromIdWithWrongIdThrowsException1()
    {
        $this->expectException(\Exception::class);
        API::getInstance()->getSiteFromId(0);
    }

    /**
     * wrong id => exception
     */
    public function testGetSiteFromIdWithWrongIdThrowsException2()
    {
        $this->expectException(\Exception::class);
        API::getInstance()->getSiteFromId("x1");
    }

    /**
     * wrong id : no access => exception
     */
    public function testGetSiteFromIdThrowsExceptionWhenTheUserDoesNotHavaAcessToTheSite()
    {
        $this->expectException(\Exception::class);
        $idsite = API::getInstance()->addSite("site", ["http://piwik.net", "http://piwik.com/test/"]);
        $this->assertEquals(1, $idsite);

        // set noaccess to site 1
        FakeAccess::setIdSitesView([2]);
        FakeAccess::setIdSitesAdmin([]);

        API::getInstance()->getSiteFromId(1);
    }

    /**
     * normal case
     */
    public function testGetSiteFromIdWithNormalIdReturnsTheCorrectSite()
    {
        $name = "website ''";
        $idsite = API::getInstance()->addSite($name, ["http://piwik.net", "http://piwik.com/test/"]);
        self::assertIsInt($idsite);

        $siteInfo = API::getInstance()->getSiteFromId($idsite);
        $this->assertEquals($name, $siteInfo['name']);
        $this->assertEquals("http://piwik.net", $siteInfo['main_url']);
    }

    /**
     * there is no admin site available -> array()
     */
    public function testGetSitesWithAdminAccessReturnsNothingWhenUserHasNoAdminAccess()
    {
        FakeAccess::setIdSitesAdmin([]);

        $sites = API::getInstance()->getSitesWithAdminAccess();
        $this->assertEquals([], $sites);
    }

    /**
     * normal case, admin and view and noaccess website => return only admin
     */
    public function testGetSitesWithAdminAccessShouldOnlyReturnSitesHavingActuallyAdminAccess()
    {
        API::getInstance()->addSite("site1", ["http://piwik.net", "http://piwik.com/test/"]);
        API::getInstance()->addSite("site2", ["http://piwik.com/test/"]);
        API::getInstance()->addSite("site3", ["http://piwik.org"], null, null, null, null, null, null, 'Asia/Tokyo');

        $resultWanted = [
            0 => ["idsite" => 1, "name" => "site1", "main_url" => "http://piwik.net", "ecommerce" => 0, "excluded_ips" => "", 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', 'excluded_parameters' => '', 'excluded_user_agents' => '', 'excluded_referrers' => '', 'timezone' => 'UTC', 'timezone_name' => 'SitesManager_Format_Utc', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0, 'type' => 'website', 'exclude_unknown_urls' => 0, 'currency_name' => 'USD'],
            1 => ["idsite" => 3, "name" => "site3", "main_url" => "http://piwik.org", "ecommerce" => 0, "excluded_ips" => "", 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', 'excluded_parameters' => '', 'excluded_user_agents' => '', 'excluded_referrers' => '', 'timezone' => 'Asia/Tokyo', 'timezone_name' => 'Intl_Country_JP', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0, 'type' => 'website', 'exclude_unknown_urls' => 0, 'currency_name' => 'USD'],
        ];

        FakeAccess::setIdSitesAdmin([1, 3]);

        $sites = API::getInstance()->getSitesWithAdminAccess();

        // we don't test the ts_created
        unset($sites[0]['ts_created']);
        unset($sites[1]['ts_created']);
        $this->assertEquals($resultWanted, $sites);
    }

    /**
     * Get the list of admin access sites with a site ID excluded.
     */
    public function testGetSitesWithAdminAccessShouldOnlyReturnSitesHavingActuallyAdminAccessFiltered()
    {
        API::getInstance()->addSite("site1", ["http://piwik.net", "http://piwik.com/test/"]);
        API::getInstance()->addSite("site2", ["http://piwik.com/test/"]);
        API::getInstance()->addSite("site3", ["http://piwik.org"], null, null, null, null, null, null, 'Asia/Tokyo');

        $resultWanted = [
            0 => ["idsite" => 3, "name" => "site3", "main_url" => "http://piwik.org", "ecommerce" => 0, "excluded_ips" => "", 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', 'excluded_parameters' => '', 'excluded_user_agents' => '', 'excluded_referrers' => '', 'timezone' => 'Asia/Tokyo', 'timezone_name' => 'Intl_Country_JP', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0, 'type' => 'website', 'exclude_unknown_urls' => 0, 'currency_name' => 'USD'],
        ];

        FakeAccess::setIdSitesAdmin([1, 3]);

        $sites = API::getInstance()->getSitesWithAdminAccess(false, false, false, [1]);
        $this->assertIsArray($sites);
        $this->assertCount(1, $sites);

        // we don't test the ts_created
        unset($sites[0]['ts_created']);
        $this->assertEquals($resultWanted, $sites);
    }

    /**
     * Get the list of admin access sites with all site IDs excluded.
     */
    public function testGetSitesWithAdminAccessShouldOnlyReturnSitesHavingActuallyAdminAccessAllFiltered()
    {
        API::getInstance()->addSite("site1", ["http://piwik.net", "http://piwik.com/test/"]);
        API::getInstance()->addSite("site2", ["http://piwik.com/test/"]);
        API::getInstance()->addSite("site3", ["http://piwik.org"], null, null, null, null, null, null, 'Asia/Tokyo');

        FakeAccess::setIdSitesAdmin([1, 3]);

        $sites = API::getInstance()->getSitesWithAdminAccess(false, false, false, [1,2,3]);
        $this->assertIsArray($sites);
        $this->assertCount(0, $sites);
    }

    public function testGetSitesWithAdminAccessShouldApplyLimitIfSet()
    {
        $this->createManySitesWithAdminAccess(40);

        // should return all sites by default
        $sites = API::getInstance()->getSitesWithAdminAccess();
        $this->assertReturnedSitesContainsSiteIds(range(1, 40), $sites);

        // return only 5 sites
        $sites = API::getInstance()->getSitesWithAdminAccess(false, false, 5);
        $this->assertReturnedSitesContainsSiteIds([1, 2, 3, 4, 5], $sites);

        // return only 10 sites
        $sites = API::getInstance()->getSitesWithAdminAccess(false, false, 10);
        $this->assertReturnedSitesContainsSiteIds(range(1, 10), $sites);
    }

    public function testGetSitesWithAdminAccessShouldApplyPatternIfSetAndFindBySiteName()
    {
        $this->createManySitesWithAdminAccess(40);

        // by site name
        $sites = API::getInstance()->getSitesWithAdminAccess(false, 'site38');
        $this->assertReturnedSitesContainsSiteIds([38], $sites);
    }

    public function testGetSitesWithAdminAccessShouldApplyPatternIfSetAndFindByUrl()
    {
        $this->createManySitesWithAdminAccess(40);

        $sites = API::getInstance()->getSitesWithAdminAccess(false, 'piwik38.o');
        $this->assertReturnedSitesContainsSiteIds([38], $sites);
    }

    public function testGetSitesWithAdminAccessShouldApplyPatternAndFindMany()
    {
        $this->createManySitesWithAdminAccess(40);

        $sites = API::getInstance()->getSitesWithAdminAccess(false, '5');
        $this->assertReturnedSitesContainsSiteIds([5, 15, 25, 35], $sites);
    }

    public function testGetSitesWithAdminAccessShouldApplyPatternAndLimit()
    {
        $this->createManySitesWithAdminAccess(40);

        $sites = API::getInstance()->getSitesWithAdminAccess(false, '5', 2);
        $this->assertReturnedSitesContainsSiteIds([5, 15], $sites);
    }

    public function testGetMessagesToWarnOnSiteRemovalShouldReturnDefaultValue()
    {
        $pluginManager = Plugin\Manager::getInstance();
        if ($pluginManager->isPluginActivated('TagManager')) {
            $pluginManager->deactivatePlugin('TagManager');
        }
        API::getInstance()->addSite("site1", ["http://piwik.net", "http://piwik.com"]);
        API::getInstance()->addSite("site2", ["http://piwik.com", "http://piwik.net"]);
        $this->assertEmpty(API::getInstance()->getMessagesToWarnOnSiteRemoval(1));
        $this->assertEmpty(API::getInstance()->getMessagesToWarnOnSiteRemoval(2));
    }

    public function testGetMessagesToWarnOnSiteRemovalShouldThrowExceptionIfNotSuperUser()
    {
        $this->expectException(\Exception::class);
        $this->createManySitesWithAdminAccess(1);
        API::getInstance()->getMessagesToWarnOnSiteRemoval(1);
    }

    private function createManySitesWithAdminAccess($numSites)
    {
        for ($i = 1; $i <= $numSites; $i++) {
            API::getInstance()->addSite("site" . $i, ["http://piwik$i.org"]);
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
    public function testGetSitesWithViewAccessReturnsNothingIfUserHasNoViewOrAdminAccess()
    {
        FakeAccess::setIdSitesView([]);
        FakeAccess::setIdSitesAdmin([]);

        $sites = API::getInstance()->getSitesWithViewAccess();
        $this->assertEquals([], $sites);
    }

    /**
     * normal case, admin and view and noaccess website => return only admin
     */
    public function testGetSitesWithViewAccessReturnsSitesWithViewAccess()
    {
        API::getInstance()->addSite("site1", ["http://piwik.net", "http://piwik.com/test/"]);
        API::getInstance()->addSite("site2", ["http://piwik.com/test/"]);
        API::getInstance()->addSite("site3", ["http://piwik.org"]);

        $resultWanted = [
            0 => ["idsite" => 1, "name" => "site1", "main_url" => "http://piwik.net", "ecommerce" => 0, 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', "excluded_ips" => "", 'excluded_parameters' => '', 'excluded_user_agents' => '', 'excluded_referrers' => '', 'timezone' => 'UTC', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0, 'type' => 'website', 'exclude_unknown_urls' => 0, 'timezone_name' => 'SitesManager_Format_Utc', 'currency_name' => 'USD'],
            1 => ["idsite" => 3, "name" => "site3", "main_url" => "http://piwik.org", "ecommerce" => 0, 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', "excluded_ips" => "", 'excluded_parameters' => '', 'excluded_user_agents' => '', 'excluded_referrers' => '', 'timezone' => 'UTC', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0, 'type' => 'website', 'exclude_unknown_urls' => 0, 'timezone_name' => 'SitesManager_Format_Utc', 'currency_name' => 'USD'],
        ];

        FakeAccess::setIdSitesView([1, 3]);
        FakeAccess::setIdSitesAdmin([]);

        $sites = API::getInstance()->getSitesWithViewAccess();

        // we don't test the ts_created

        unset($sites[0]['ts_created']);
        unset($sites[1]['ts_created']);
        $this->assertEquals($resultWanted, $sites);
    }

    /**
     * there is no admin site available -> array()
     */
    public function testGetSitesWithAtLeastViewAccessReturnsNothingWhenUserHasNoAccess()
    {
        FakeAccess::setIdSitesView([]);
        FakeAccess::setIdSitesAdmin([]);

        $sites = API::getInstance()->getSitesWithAtLeastViewAccess();
        $this->assertEquals([], $sites);
    }

    /**
     * normal case, admin and view and noaccess website => return only admin
     */
    public function testGetSitesWithAtLeastViewAccessReturnsSitesWithViewAccess()
    {
        API::getInstance()->addSite("site1", ["http://piwik.net", "http://piwik.com/test/"], $ecommerce = 1);
        API::getInstance()->addSite("site2", ["http://piwik.com/test/"]);
        API::getInstance()->addSite("site3", ["http://piwik.org"]);

        $resultWanted = [
            0 => ["idsite" => 1, "name" => "site1", "main_url" => "http://piwik.net", "ecommerce" => 1, "excluded_ips" => "", 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', 'excluded_parameters' => '', 'excluded_user_agents' => '', 'excluded_referrers' => '', 'timezone' => 'UTC', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0, 'type' => 'website', 'exclude_unknown_urls' => 0, 'timezone_name' => 'SitesManager_Format_Utc', 'currency_name' => 'USD'],
            1 => ["idsite" => 3, "name" => "site3", "main_url" => "http://piwik.org", "ecommerce" => 0, "excluded_ips" => "", 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', 'excluded_parameters' => '', 'excluded_user_agents' => '', 'excluded_referrers' => '', 'timezone' => 'UTC', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0, 'type' => 'website', 'exclude_unknown_urls' => 0, 'timezone_name' => 'SitesManager_Format_Utc', 'currency_name' => 'USD'],
        ];

        FakeAccess::setIdSitesView([1, 3]);
        FakeAccess::setIdSitesAdmin([]);

        $sites = API::getInstance()->getSitesWithAtLeastViewAccess();
        // we don't test the ts_created
        unset($sites[0]['ts_created']);
        unset($sites[1]['ts_created']);
        $this->assertEquals($resultWanted, $sites);
    }

    /**
     * no urls for this site => array()
     */
    public function testGetSiteUrlsFromIdReturnsMainUrlOnlyWhenNoAliasUrls()
    {
        $idsite = API::getInstance()->addSite("site1", ["http://piwik.net"]);

        $urls = API::getInstance()->getSiteUrlsFromId($idsite);
        $this->assertEquals(["http://piwik.net"], $urls);
    }

    /**
     * normal case
     */
    public function testGetSiteUrlsFromIdReturnsMainAndAliasUrls()
    {
        $site = ["http://piwik.net",
                      "http://piwik.org",
                      "http://piwik.org",
                      "http://piwik.com"];
        sort($site);

        $idsite = API::getInstance()->addSite("site1", $site);

        $siteWanted = ["http://piwik.net",
                            "http://piwik.org",
                            "http://piwik.com"];
        sort($siteWanted);
        $urls = API::getInstance()->getSiteUrlsFromId($idsite);

        $this->assertEquals($siteWanted, $urls);
    }

    /**
     * wrongId => exception
     */
    public function testGetSiteUrlsFromIdThrowsExceptionWhenSiteIdIsIncorrect()
    {
        $this->expectException(\Exception::class);
        FakeAccess::setIdSitesView([3]);
        FakeAccess::setIdSitesAdmin([]);
        API::getInstance()->getSiteUrlsFromId(1);
    }

    /**
     * one url => no change to alias urls
     */
    public function testUpdateSiteWithOneUrlRemovesAliasUrlsAndUpdatesTheSiteCorrectly()
    {
        $urls = ["http://piwiknew.com",
                      "http://piwiknew.net",
                      "http://piwiknew.org",
                      "http://piwiknew.fr"];
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
        $type = MobileAppMeasurable\Type::ID;
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
        $this->assertEquals([], $aliasUrls);
    }

    /**
     * strange name and NO URL => name ok, main_url not updated
     */
    public function testUpdateSiteWithStrangeNameAndNoAliasUrlsUpdatesTheNameButNoUrls()
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
    public function testUpdateSiteWithSeveralUrlsAndGroupUpdatesGroupAndUrls()
    {
        $urls = ["http://piwiknew.com",
                      "http://piwiknew.net",
                      "http://piwiknew.org",
                      "http://piwiknew.fr"];

        $group = 'GROUP Before';
        $idsite = API::getInstance()->addSite(
            "site1",
            $urls,
            $ecommerce = 1,
            $siteSearch = 1,
            $searchKeywordParameters = null,
            $searchCategoryParameters = null,
            $excludedIps = null,
            $excludedQueryParameters = null,
            $timezone = null,
            $currency = null,
            $group,
            $startDate = '2011-01-01'
        );

        $websites = API::getInstance()->getSitesFromGroup($group);
        $this->assertEquals(1, count($websites));

        $newurls = ["http://piwiknew2.com",
                         "http://piwiknew2.net",
                         "http://piwiknew2.org",
                         "http://piwiknew2.fr"];

        $groupAfter = '   GROUP After';
        API::getInstance()->updateSite(
            $idsite,
            "test toto@{}",
            $newurls,
            $ecommerce = 0,
            $siteSearch = 1,
            $searchKeywordParameters = null,
            $searchCategoryParameters = null,
            $excludedIps = null,
            $excludedQueryParameters = null,
            $timezone = null,
            $currency = null,
            $groupAfter
        );

        // no result for the group before update
        $websites = API::getInstance()->getSitesFromGroup($group);
        $this->assertEquals(0, count($websites));

        // Testing that the group was updated properly (and testing that the group value is trimmed before inserted/searched)
        $websites = API::getInstance()->getSitesFromGroup($groupAfter . ' ');
        $this->assertEquals(1, count($websites));
        $this->assertEquals('2011-01-01', date('Y-m-d', strtotime($websites[0]['ts_created'])));

        // Test fetch website groups
        $expectedGroups = [trim($groupAfter)];
        $fetched = API::getInstance()->getSitesGroups();
        $this->assertEquals($expectedGroups, $fetched);

        $allUrls = API::getInstance()->getSiteUrlsFromId($idsite);
        sort($allUrls);
        sort($newurls);
        $this->assertEquals($newurls, $allUrls);
    }

    public function testUpdateSiteShouldFailAndNotUpdateSiteIfASettingIsInvalid()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('SitesManager_OnlyMatchedUrlsAllowed');

        $type  = MobileAppMeasurable\Type::ID;
        $idSite = $this->addSiteWithType($type, []);

        try {
            $settings = ['MobileAppMeasurable' => [['name' => 'exclude_unknown_urls', 'value' => 'fooBar']]];
            $this->updateSiteSettings($idSite, 'newSiteName', $settings);
        } catch (Exception $e) {
            // verify nothing was updated (not even the name)
            $measurable = new Measurable($idSite);
            $this->assertNotEquals('newSiteName', $measurable->getName());

            throw $e;
        }
    }

    public function testUpdateSiteShouldSavePassedMeasurableSettingsIfSettingsAreValid()
    {
        $type = WebsiteType::ID;
        $idSite = $this->addSiteWithType($type, []);

        $this->assertSame(1, $idSite);

        $settings = ['WebsiteMeasurable' => [['name' => 'urls', 'value' => ['http://www.piwik.org']]]];

        $this->updateSiteSettings($idSite, 'newSiteName', $settings);

        $settings = $this->getWebsiteMeasurable($idSite);

        // verify it was updated
        $measurable = new Measurable($idSite);
        $this->assertSame('newSiteName', $measurable->getName());
        $this->assertSame(['http://www.piwik.org'], $settings->urls->getValue());
    }

    public function testUpdateSiteCorrectlySavesExcludedUnknownUrlSettings()
    {
        $idSite = API::getInstance()->addSite("site1", ["http://piwik.net"]);

        $site = API::getInstance()->getSiteFromId($idSite);
        $this->assertEquals(0, $site['exclude_unknown_urls']);

        API::getInstance()->updateSite(
            $idSite,
            $siteName = null,
            $urls = null,
            $ecommerce = null,
            $siteSearch = null,
            $searchKeywordParams = null,
            $searchCategoryParams = null,
            $excludedIps = null,
            $excludedQueryParameters = null,
            $timzeone = null,
            $currency = null,
            $group = null,
            $startDate = null,
            $excludedUserAgents = null,
            $keepUrlFragments = null,
            $type = null,
            $settings = null,
            $excludeUnknownUrls = true
        );

        $site = API::getInstance()->getSiteFromId($idSite);
        $this->assertEquals(1, $site['exclude_unknown_urls']);
    }

    public function testUpdateSiteWithInvalidTypeFails()
    {
        $idSite = $this->addSiteWithType('website', []);

        $site = API::getInstance()->getSiteFromId($idSite);
        $this->assertEquals(0, $site['exclude_unknown_urls']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid website type invalidtype');

        API::getInstance()->updateSite(
            $idSite,
            $siteName = 'new site name',
            $urls = null,
            $ecommerce = true,
            $siteSearch = false,
            $searchKeywordParams = null,
            $searchCategoryParams = null,
            $excludedIps = null,
            $excludedQueryParameters = null,
            $timzeone = null,
            $currency = 'NZD',
            $group = null,
            $startDate = null,
            $excludedUserAgents = null,
            $keepUrlFragments = null,
            $type = 'invalidtype',
            $settings = null,
            $excludeUnknownUrls = true
        );
    }

    /**
     * @dataProvider getDifferentTypesDataProvider
     */
    public function testUpdateSiteWithDifferentTypes($type)
    {
        $idSite = $this->addSiteWithType('website', []);

        $site = API::getInstance()->getSiteFromId($idSite);
        $this->assertEquals(0, $site['exclude_unknown_urls']);

        API::getInstance()->updateSite(
            $idSite,
            $siteName = 'new site name',
            $urls = null,
            $ecommerce = true,
            $siteSearch = false,
            $searchKeywordParams = null,
            $searchCategoryParams = null,
            $excludedIps = null,
            $excludedQueryParameters = null,
            $timzeone = null,
            $currency = 'NZD',
            $group = null,
            $startDate = null,
            $excludedUserAgents = null,
            $keepUrlFragments = null,
            $type,
            $settings = null,
            $excludeUnknownUrls = true
        );

        $site = API::getInstance()->getSiteFromId($idSite);
        $this->assertEquals('new site name', $site['name']);
        $this->assertEquals(1, $site['exclude_unknown_urls']);
        $this->assertEquals(1, $site['ecommerce']);
        $this->assertEquals(0, $site['sitesearch']);
        $this->assertEquals('NZD', $site['currency']);
    }

    public function getDifferentTypesDataProvider()
    {
        return [
            [WebsiteType::ID],
            [MobileAppMeasurable\Type::ID],
            [IntranetType::ID],
        ];
    }

    public function testDeleteShouldNotDeleteASiteInCaseThereIsOnlyOneSite()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('SitesManager_ExceptionDeleteSite');

        $siteId1 = $this->addSite();

        $this->assertHasSite($siteId1);

        try {
            API::getInstance()->deleteSite($siteId1);
            $this->fail('an expected exception was not raised');
        } catch (Exception $e) {
            $this->assertHasSite($siteId1);
            throw $e;
        }
    }

    public function testDeleteShouldTriggerExceptionIfGivenSiteDoesNotExist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('website id = 99999498 not found');

        API::getInstance()->deleteSite(99999498);
    }

    public function testDeleteShouldActuallyRemoveAnExistingSiteButOnlyTheGivenSite()
    {
        $this->addSite();
        $siteId1 = $this->addSite();
        $siteId2 = $this->addSite();

        $this->assertHasSite($siteId1);
        $this->assertHasSite($siteId2);

        API::getInstance()->deleteSite($siteId1);

        $this->assertHasNotSite($siteId1);
        $this->assertHasSite($siteId2);
    }

    public function testDeleteShouldTriggerAnEventOnceSiteWasActuallyDeleted()
    {
        $called = 0;
        $deletedSiteId = null;

        Piwik::addAction('SitesManager.deleteSite.end', function ($param) use (&$called, &$deletedSiteId) {
            $called++;
            $deletedSiteId = $param;
        });

        $this->addSite();
        $siteId1 = $this->addSite();

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
        $groups = ['group1', ' group1 ', '', 'group2'];
        $expectedGroups = ['group1', '', 'group2'];
        foreach ($groups as $group) {
            API::getInstance()->addSite("test toto@{}", 'http://example.org', $ecommerce = 1, $siteSearch = null, $searchKeywordParameters = null, $searchCategoryParameters = null, $excludedIps = null, $excludedQueryParameters = null, $timezone = null, $currency = null, $group);
        }

        $this->assertEqualsCanonicalizing($expectedGroups, API::getInstance()->getSitesGroups());
    }

    public function getInvalidTimezoneData()
    {
        return [
            ['UTC+15'],
            ['Paris'],
        ];
    }

    /**
     *
     * @dataProvider getInvalidTimezoneData
     */
    public function testAddSiteWithInvalidTimezoneThrowsException($timezone)
    {
        $this->expectException(\Exception::class);

        API::getInstance()->addSite(
            "site1",
            ['http://example.org'],
            $ecommerce = 0,
            $siteSearch = 1,
            $searchKeywordParameters = null,
            $searchCategoryParameters = null,
            $ip = '',
            $params = '',
            $timezone
        );
    }

    public function testAddSiteWithInvalidCurrencyThrowsException()
    {
        $this->expectException(\Exception::class);

        $invalidCurrency = '€';
        API::getInstance()->addSite(
            "site1",
            ['http://example.org'],
            $ecommerce = 0,
            $siteSearch = 1,
            $searchKeywordParameters = null,
            $searchCategoryParameters = null,
            '',
            'UTC',
            $invalidCurrency
        );
    }

    public function testSetDefaultTimezoneAndCurrencyAndExcludedQueryParametersAndExcludedIpsUpdatesDefaultsCorrectly()
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
        $idsite = API::getInstance()->addSite("site1", ['http://example.org']);
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
        $idsite = API::getInstance()->addSite(
            "site1",
            ['http://example.org'],
            $ecommerce = 0,
            $siteSearch = 0,
            $searchKeywordParameters = 'test1,test2',
            $searchCategoryParameters = 'test2,test1',
            '',
            '',
            $newDefaultTimezone
        );
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

    public function testGetSitesIdFromSiteUrlAsSuperUserReturnsTheRequestedSiteIds()
    {
        API::getInstance()->addSite("site1", ["http://piwik.net", "http://piwik.com"]);
        API::getInstance()->addSite("site2", ["http://piwik.com", "http://piwik.net"]);
        API::getInstance()->addSite("site3", ["http://piwik.com", "http://piwik.org"]);

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.org');
        $this->assertTrue(count($idsites) == 1);

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://www.piwik.org');
        $this->assertTrue(count($idsites) == 1);

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.net');
        $this->assertTrue(count($idsites) == 2);

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.com');
        $this->assertTrue(count($idsites) == 3);
    }

    public function testGetSitesIdFromSiteUrlMatchesBothHttpAndHttpsUrlsAsSuperUser()
    {
        API::getInstance()->addSite("site1", ["https://piwik.org", "http://example.com", "fb://special-url"]);

        $this->assertGetSitesIdFromSiteUrlMatchesBothHttpAndHttpsUrls();
    }

    public function testGetSitesIdFromSiteUrlMatchesBothHttpAndHttpsUrlsAsUserWithViewPermission()
    {
        API::getInstance()->addSite("site1", ["https://piwik.org", "http://example.com", "fb://special-url"]);

        APIUsersManager::getInstance()->addUser("user1", "geqgegagae", "tegst@tesgt.com");
        APIUsersManager::getInstance()->setUserAccess("user1", "view", [1]);

        // Make sure we're not Super user
        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'user1';
        $this->assertFalse(Piwik::hasUserSuperUserAccess());

        $this->assertGetSitesIdFromSiteUrlMatchesBothHttpAndHttpsUrls();
    }

    private function assertGetSitesIdFromSiteUrlMatchesBothHttpAndHttpsUrls()
    {
        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.org');
        $this->assertTrue(count($idsites) == 1);

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('piwik.org');
        $this->assertTrue(count($idsites) == 1);

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('https://www.piwik.org');
        $this->assertTrue(count($idsites) == 1);

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('https://example.com');
        $this->assertTrue(count($idsites) == 1);

        $idsites = API::getInstance()->getSitesIdFromSiteUrl("fb://special-url");
        $this->assertTrue(count($idsites) == 1);

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('https://random-example.com');
        $this->assertTrue(count($idsites) == 0);

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('not-found.piwik.org');
        $this->assertTrue(count($idsites) == 0);

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('piwik.org/not-found/');
        $this->assertTrue(count($idsites) == 0);
    }

    public function testGetSitesIdFromSiteUrlAsUser()
    {
        API::getInstance()->addSite("site1", ["http://www.piwik.net", "https://piwik.com"]);
        API::getInstance()->addSite("site2", ["http://piwik.com", "http://piwik.net"]);
        API::getInstance()->addSite("site3", ["http://piwik.com", "http://piwik.org"]);

        APIUsersManager::getInstance()->addUser("user1", "geqgegagae", "tegst@tesgt.com");
        APIUsersManager::getInstance()->setUserAccess("user1", "view", [1]);

        APIUsersManager::getInstance()->addUser("user2", "geqgegagae", "tegst2@tesgt.com");
        APIUsersManager::getInstance()->setUserAccess("user2", "view", [1]);
        APIUsersManager::getInstance()->setUserAccess("user2", "admin", [3]);

        APIUsersManager::getInstance()->addUser("user3", "geqgegagae", "tegst3@tesgt.com");
        APIUsersManager::getInstance()->setUserAccess("user3", "view", [1, 2]);
        APIUsersManager::getInstance()->setUserAccess("user3", "admin", [3]);

        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'user1';
        FakeAccess::setIdSitesView([1]);
        FakeAccess::setIdSitesAdmin([]);

        $this->assertFalse(Piwik::hasUserSuperUserAccess());
        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.com');
        $this->assertEquals(1, count($idsites));

        // testing URL normalization
        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://www.piwik.com');
        $this->assertEquals(1, count($idsites));
        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.net');
        $this->assertEquals(1, count($idsites));

        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'user2';
        FakeAccess::setIdSitesView([1]);
        FakeAccess::setIdSitesAdmin([3]);

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.com');
        $this->assertEquals(2, count($idsites));

        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'user3';
        FakeAccess::setIdSitesView([1, 2]);
        FakeAccess::setIdSitesAdmin([3]);

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.com');
        $this->assertEquals(3, count($idsites));

        $idsites = API::getInstance()->getSitesIdFromSiteUrl('https://www.piwik.com');
        $this->assertEquals(3, count($idsites));
    }

    public function testGetSitesFromTimezonesReturnsCorrectIdSites()
    {
        API::getInstance()->addSite("site3", ["http://piwik.org"], null, $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null, null, null, 'UTC');
        $idsite2 = API::getInstance()->addSite("site3", ["http://piwik.org"], null, $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null, null, null, 'Pacific/Auckland');
        $idsite3 = API::getInstance()->addSite("site3", ["http://piwik.org"], null, $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null, null, null, 'Pacific/Auckland');
        $idsite4 = API::getInstance()->addSite("site3", ["http://piwik.org"], null, $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null, null, null, 'UTC+10');
        $result = API::getInstance()->getSitesIdFromTimezones(['UTC+10', 'Pacific/Auckland']);
        $this->assertEquals([$idsite2, $idsite3, $idsite4], $result);
    }

    /**
     * Get the list of filtered sites with no sites available.
     */
    public function testGetPatternMatchSitesNoneAvailable()
    {
        $sites = API::getInstance()->getPatternMatchSites('%');
        $this->assertIsArray($sites);
        $this->assertCount(0, $sites);
    }

    /**
     * Get the list of filtered sites.
     */
    public function testGetPatternMatchSites()
    {
        API::getInstance()->addSite("site1", ["http://piwik.net", "http://piwik.com/test/"]);
        API::getInstance()->addSite("site2", ["http://piwik.com/test/"]);
        API::getInstance()->addSite("site3", ["http://piwik.org"], null, null, null, null, null, null, 'Asia/Tokyo');

        $resultWanted = [
            0 => ["idsite" => 1, "name" => "site1", "main_url" => "http://piwik.net", "ecommerce" => 0, "excluded_ips" => "", 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', 'excluded_parameters' => '', 'excluded_user_agents' => '', 'excluded_referrers' => '', 'timezone' => 'UTC', 'timezone_name' => 'SitesManager_Format_Utc', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0, 'type' => 'website', 'exclude_unknown_urls' => 0, 'currency_name' => 'USD', 'creator_login' => 'superUserLogin'],
            1 => ["idsite" => 2, "name" => "site2", "main_url" => "http://piwik.com/test", "ecommerce" => 0, "excluded_ips" => "", 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', 'excluded_parameters' => '', 'excluded_user_agents' => '', 'excluded_referrers' => '', 'timezone' => 'UTC', 'timezone_name' => 'SitesManager_Format_Utc', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0, 'type' => 'website', 'exclude_unknown_urls' => 0, 'currency_name' => 'USD', 'creator_login' => 'superUserLogin'],
            2 => ["idsite" => 3, "name" => "site3", "main_url" => "http://piwik.org", "ecommerce" => 0, "excluded_ips" => "", 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', 'excluded_parameters' => '', 'excluded_user_agents' => '', 'excluded_referrers' => '', 'timezone' => 'Asia/Tokyo', 'timezone_name' => 'Intl_Country_JP', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0, 'type' => 'website', 'exclude_unknown_urls' => 0, 'currency_name' => 'USD', 'creator_login' => 'superUserLogin'],
        ];

        $sites = API::getInstance()->getPatternMatchSites('%');
        $this->assertIsArray($sites);
        $this->assertCount(3, $sites);

        // we don't test the ts_created
        unset($sites[0]['ts_created']);
        unset($sites[1]['ts_created']);
        unset($sites[2]['ts_created']);
        $this->assertEquals($resultWanted, $sites);
    }

    /**
     * Get the list sites filtered by site name.
     */
    public function testGetPatternMatchSitesFilteringBySiteName()
    {
        API::getInstance()->addSite("site1", ["http://piwik.net", "http://piwik.com/test/"]);
        API::getInstance()->addSite("site2", ["http://piwik.com/test/"]);
        API::getInstance()->addSite("site3", ["http://piwik.org"], null, null, null, null, null, null, 'Asia/Tokyo');

        $resultWanted = [
            0 => ["idsite" => 2, "name" => "site2", "main_url" => "http://piwik.com/test", "ecommerce" => 0, "excluded_ips" => "", 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', 'excluded_parameters' => '', 'excluded_user_agents' => '', 'excluded_referrers' => '', 'timezone' => 'UTC', 'timezone_name' => 'SitesManager_Format_Utc', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0, 'type' => 'website', 'exclude_unknown_urls' => 0, 'currency_name' => 'USD', 'creator_login' => 'superUserLogin'],
        ];

        $sites = API::getInstance()->getPatternMatchSites('site2');
        $this->assertIsArray($sites);
        $this->assertCount(1, $sites);

        // we don't test the ts_created
        unset($sites[0]['ts_created']);
        $this->assertEquals($resultWanted, $sites);
    }

    /**
     * Get the list of filtered sites with a site ID excluded.
     */
    public function testGetPatternMatchSitesFiltered()
    {
        API::getInstance()->addSite("site1", ["http://piwik.net", "http://piwik.com/test/"]);
        API::getInstance()->addSite("site2", ["http://piwik.com/test/"]);
        API::getInstance()->addSite("site3", ["http://piwik.org"], null, null, null, null, null, null, 'Asia/Tokyo');

        $resultWanted = [
            0 => ["idsite" => 2, "name" => "site2", "main_url" => "http://piwik.com/test", "ecommerce" => 0, "excluded_ips" => "", 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', 'excluded_parameters' => '', 'excluded_user_agents' => '', 'excluded_referrers' => '', 'timezone' => 'UTC', 'timezone_name' => 'SitesManager_Format_Utc', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0, 'type' => 'website', 'exclude_unknown_urls' => 0, 'currency_name' => 'USD', 'creator_login' => 'superUserLogin'],
            1 => ["idsite" => 3, "name" => "site3", "main_url" => "http://piwik.org", "ecommerce" => 0, "excluded_ips" => "", 'sitesearch' => 1, 'sitesearch_keyword_parameters' => '', 'sitesearch_category_parameters' => '', 'excluded_parameters' => '', 'excluded_user_agents' => '', 'excluded_referrers' => '', 'timezone' => 'Asia/Tokyo', 'timezone_name' => 'Intl_Country_JP', 'currency' => 'USD', 'group' => '', 'keep_url_fragment' => 0, 'type' => 'website', 'exclude_unknown_urls' => 0, 'currency_name' => 'USD', 'creator_login' => 'superUserLogin'],
        ];

        $sites = API::getInstance()->getPatternMatchSites('%', false, [1]);
        $this->assertIsArray($sites);
        $this->assertCount(2, $sites);

        // we don't test the ts_created
        unset($sites[0]['ts_created']);
        unset($sites[1]['ts_created']);
        $this->assertEquals($resultWanted, $sites);
    }


    public function testSetGlobalExcludedReferrersWithEmptyValue()
    {
        API::getInstance()->setGlobalExcludedReferrers('');
        $excludedReferrers = Option::get('SitesManager_ExcludedReferrersGlobal');
        $this->assertEquals('', $excludedReferrers);
    }

    public function testSetGlobalExcludedReferrersWithValidValue()
    {
        API::getInstance()->setGlobalExcludedReferrers('example.com');
        $excludedReferrers = Option::get('SitesManager_ExcludedReferrersGlobal');
        $this->assertEquals('example.com', $excludedReferrers);


        API::getInstance()->setGlobalExcludedReferrers('.example.com');
        $excludedReferrers = Option::get('SitesManager_ExcludedReferrersGlobal');
        $this->assertEquals('.example.com', $excludedReferrers);


        API::getInstance()->setGlobalExcludedReferrers('http://example.com/path');
        $excludedReferrers = Option::get('SitesManager_ExcludedReferrersGlobal');
        $this->assertEquals('http://example.com/path', $excludedReferrers);
    }

    /**
     * @dataProvider getExclusionTypesAndExpectedResults
     */
    public function testGetExcludedQueryParametersGlobalShowsCorrectParamsDependingOnExclusionType($exclusionType, $expected): void
    {
        $this->setCommonPIIParamsInConfig(['common_one','common_two','common_three']);

        Option::set(API::OPTION_EXCLUDE_TYPE_QUERY_PARAMS_GLOBAL, $exclusionType);
        Option::set(API::OPTION_EXCLUDED_QUERY_PARAMETERS_GLOBAL, 'one,two');

        $this->assertEquals(
            $expected,
            API::getInstance()->getExcludedQueryParametersGlobal()
        );
    }

    public function getExclusionTypesAndExpectedResults(): \Generator
    {
        yield 'common session parameters' => ['common_session_parameters', ''];
        yield 'matomo recommended PII' => ['matomo_recommended_pii', implode(',', ['common_one','common_two','common_three'])];
        yield 'custom' => ['custom', 'one,two'];
        yield 'empty exclusion type' => ['', 'one,two'];
        yield 'false exclusion type' => [false, 'one,two'];
    }

    /**
     * @dataProvider getExclusionTypesWithUrlParamsAndExpectedResults
     */
    public function testGetExclusionTypeForQueryParamsReturnsCorrectType($exclusionTypeSetting, string $excludedQueryParamsGlobal, string $expectedType): void
    {
        if ($exclusionTypeSetting === null) {
            Option::delete(API::OPTION_EXCLUDE_TYPE_QUERY_PARAMS_GLOBAL);
        } else {
            Option::set(API::OPTION_EXCLUDE_TYPE_QUERY_PARAMS_GLOBAL, $exclusionTypeSetting);
        }

        Option::set(API::OPTION_EXCLUDED_QUERY_PARAMETERS_GLOBAL, $excludedQueryParamsGlobal);

        $this->assertEquals(
            $expectedType,
            API::getInstance()->getExclusionTypeForQueryParams()
        );
    }

    public function getExclusionTypesWithUrlParamsAndExpectedResults(): \Generator
    {
        yield 'option exists already in options store' => ['common_session_parameters', '', 'common_session_parameters'];
        yield 'option doesnt exist and excluded query parameters has data' => [null, 'myapp_name,myapp_email', 'custom'];
        yield 'option doesnt exist and excluded query parameters has no data' => [null, '', 'common_session_parameters'];
    }

    public function testSetGlobalQueryParamExclusionThrowsExceptionWhenInvalidExclusionTypeProvided(): void
    {
        $this->expectExceptionMessage('General_ValidatorErrorXNotWhitelisted');

        Api::getInstance()->setGlobalQueryParamExclusion('invalid');
    }

    /**
     * @dataProvider setGlobalQueryParamExclusionThrowsExceptionWhenQueryParametersNotPassedWhenCustomType
     */
    public function testSetGlobalQueryParamExclusionThrowsExceptionWhenQueryParametersNotPassedWhenCustomType(string $queryParameters): void
    {
        $this->expectExceptionMessage('SitesManager_ExceptionEmptyQueryParamsForCustomType');

        Api::getInstance()->setGlobalQueryParamExclusion(
            SitesManager::URL_PARAM_EXCLUSION_TYPE_NAME_CUSTOM,
            $queryParameters
        );
    }

    public function setGlobalQueryParamExclusionThrowsExceptionWhenQueryParametersNotPassedWhenCustomType(): \Generator
    {
        yield 'blank 0 length string' => [''];
        yield 'string with just white space' => ['    '];
        yield 'comma seperated with no values' => [',, ,,,'];
    }

    public function testSetGlobalQueryParamExclusionThrowsExceptionWhenQueryParametersPassedWhenNotCustomType(): void
    {
        $this->expectExceptionMessage('ExceptionNonEmptyQueryParamsForNonCustomType');

        Api::getInstance()->setGlobalQueryParamExclusion(
            SitesManager::URL_PARAM_EXCLUSION_TYPE_NAME_MATOMO_RECOMMENDED_PII,
            'exclude_this'
        );
    }

    public function testSetGlobalQueryParamExclusionDeletesSavedExclusionsWhenNotCustomType(): void
    {
        Option::set(API::OPTION_EXCLUDED_QUERY_PARAMETERS_GLOBAL, 'this_is_excluded_legacy');

        Api::getInstance()->setGlobalQueryParamExclusion(
            SitesManager::URL_PARAM_EXCLUSION_TYPE_NAME_COMMON_SESSION_PARAMETERS
        );

        $this->assertEmpty(Api::getInstance()->getExcludedQueryParametersGlobal());
    }

    /**
     * @dataProvider setGlobalQueryParamExclusionPersistsSettingsSuccessfully
     */
    public function testSetGlobalQueryParamExclusionPersistsSettingsSuccessfully(
        string $exclusionType,
        ?string $excludedQueryParamsGlobal,
        string $expectedQueryParamsGlobal,
        string $expectedExclusionType
    ): void {
        $this->setCommonPIIParamsInConfig(['common_one','common_two','common_three']);
        Api::getInstance()->setGlobalQueryParamExclusion(
            $exclusionType,
            $excludedQueryParamsGlobal
        );

        $this->assertEquals(
            $expectedQueryParamsGlobal,
            Api::getInstance()->getExcludedQueryParametersGlobal()
        );
        $this->assertEquals(
            $expectedExclusionType,
            Api::getInstance()->getExclusionTypeForQueryParams()
        );
    }

    public function setGlobalQueryParamExclusionPersistsSettingsSuccessfully(): \Generator
    {
        yield 'common session parameters' => ['common_session_parameters', null, '', 'common_session_parameters'];
        yield 'matomo recommended pii' => [
            'matomo_recommended_pii',
            null,
            implode(',', ['common_one','common_two','common_three']),
            'matomo_recommended_pii'
        ];
        yield 'custom' => ['custom', 'one,two', 'one,two', 'custom'];
    }

    /**
     * @dataProvider deprecatedSetGlobalExcludedQueryParametersShouldReturnExpectedParameters
     */
    public function testDeprecatedSetGlobalExcludedQueryParametersShouldReturnExpectedParameters(string $excludedParameters, string $expectedExclusionType): void
    {
        Api::getInstance()->setGlobalExcludedQueryParameters($excludedParameters);

        $this->assertEquals($excludedParameters, Api::getInstance()->getExcludedQueryParametersGlobal());
        $this->assertEquals($expectedExclusionType, Api::getInstance()->getExclusionTypeForQueryParams());
    }

    public function deprecatedSetGlobalExcludedQueryParametersShouldReturnExpectedParameters(): \Generator
    {
        yield 'non empty list of exclusions' => ['one,two,three', 'custom'];
        yield 'empty list of exclusions' => ['', 'common_session_parameters'];
    }

    private function setCommonPIIParamsInConfig(array $urlParams): void
    {
        $config = Config::getInstance();
        $sitesManager = $config->SitesManager;
        $sitesManager['CommonPIIParams'] = $urlParams;
        $config->SitesManager = $sitesManager;
        $config->forceSave();
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
