<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Config;
use Piwik\Plugins\SitesManager\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tracker\Action;
use Piwik\Tracker\ActionPageview;
use Piwik\Tracker\PageUrl;
use Piwik\Tracker\Request;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group ActionTest
 */
class ActionTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $section = Config::getInstance()->Tracker;
        $section['default_action_url'] = '/';
        $section['campaign_var_name']  = 'campaign_param_name,piwik_campaign,matomo_campaign,utm_campaign,test_campaign_name';
        $section['action_url_category_delimiter'] = '/';
        $section['campaign_keyword_var_name']     = 'piwik_kwd,matomo_kwd,utm_term,test_piwik_kwd';
        Config::getInstance()->Tracker = $section;

        PluginManager::getInstance()->loadPlugins(array('Actions', 'SitesManager'));

        Fixture::loadAllTranslations();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        Fixture::resetTranslations();
    }

    protected function setUpRootAccess()
    {
        FakeAccess::$superUser = true;
    }

    public function test_isCustomActionRequest()
    {
        $request = new Request(array('ca' => '1'));
        $this->assertTrue(Action::isCustomActionRequest($request));

        $request = new Request(array('ca' => '0'));
        $this->assertFalse(Action::isCustomActionRequest($request));

        $request = new Request(array());
        $this->assertFalse(Action::isCustomActionRequest($request));
    }

    public function test_factory_notDefaultsToPageViewWhenCustomPluginRequest()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Request was meant for a plugin which is no longer activated. Request needs to be ignored.');
        $this->setUpRootAccess();
        $idSite = API::getInstance()->addSite("site1", array('http://example.org'));
        $request = new Request(array('ca' => '1', 'idsite' => $idSite));

        Action::factory($request);
    }

    public function test_factory_defaultsToPageviewWhenNotCustomPluginRequest()
    {
        $this->setUpRootAccess();
        $idSite = API::getInstance()->addSite("site1", array('http://example.org'));
        $request = new Request(array('idsite' => $idSite));

        $action =  Action::factory($request);
        $this->assertTrue($action instanceof ActionPageview);
    }

    public function getTestUrls()
    {
        $campaignNameParam = 'test_campaign_name';
        $campaignKwdParam = 'test_piwik_kwd';

        $urls = array(
            // a wrongly formatted url (parse_url returns false)
            array('http:////wrongurl',
                  array(false,
                        false)),

            // a URL with all components
            array('http://username:password@hostname:80/path?phpSESSID=value#anchor',
                  array('http://username:password@hostname:80/path#anchor',
                        'http://username:password@hostname:80/path#anchor')),

            // a standard url with excluded campaign parameters
            array('http://a.com/index?p1=v1&' . $campaignNameParam . '=Adwords-CPC&' . $campaignKwdParam . '=My killer keyword',
                  array('http://a.com/index?p1=v1',
                        'http://a.com/index?p1=v1')),

            // a standard url with excluded campaign parameters, GA style
            array('http://a.com/index?p1=v1&utm_campaign=Adwords-CPC&utm_term=My killer keyword',
                  array('http://a.com/index?p1=v1',
                        'http://a.com/index?p1=v1')),

            // testing with capital parameter
            array('http://a.com/index?p1=v1&P2=v2&p3=v3',
                  array('http://a.com/index?p1=v1&P2=v2&p3=v3',
                        'http://a.com/index?p1=v1&p3=v3')),

            // testing with array []
            array('http://a.com/index?p1=v1&p2[]=v;2a&p2[]=v2b&p2[]=v2c&p3=v3&p4=v4',
                  array('http://a.com/index?p1=v1&p2[]=v;2a&p2[]=v2b&p2[]=v2c&p3=v3&p4=v4',
                        'http://a.com/index?p1=v1&p3=v3')),

            // testing with missing value
            array('http://a.com/index?p1=v1&p2=&p3=v3&p4',
                  array('http://a.com/index?p1=v1&p2=&p3=v3&p4',
                        'http://a.com/index?p1=v1&p3=v3')),
            array('http://a.com/index?p1&p2=v2&p3=v3&p4',
                  array('http://a.com/index?p1&p2=v2&p3=v3&p4',
                        'http://a.com/index?p1&p3=v3')),

            // testing with extra &&
            array('http://a.com/index?p1=v1&&p2=v;2&p3=v%3b3&p4=v4&&',
                  array('http://a.com/index?p1=v1&p2=v;2&p3=v%3b3&p4=v4',
                        'http://a.com/index?p1=v1&p3=v%3b3')),

            // encode entities
            array('http://a.com/index?p1=v1&p2%5B%5D=v2&p3=v3&p4=v4',
                  array('http://a.com/index?p1=v1&p2[]=v2&p3=v3&p4=v4',
                        'http://a.com/index?p1=v1&p3=v3')),
            array('http://a.com/index?var%5Bvalue%5D%5Bdate%5D=01.01.2012',
                  array('http://a.com/index?var[value][date]=01.01.2012',
                        'http://a.com/index')),

            // matrix parameters
            array('http://a.com/index;jsessionid=value;p1=v1;p2=v2',
                  array('http://a.com/index?p1=v1&p2=v2',
                        'http://a.com/index?p1=v1')),
            array('http://a.com/index;jsessionid=value?p1=v1&p2=v2',
                  array('http://a.com/index?p1=v1&p2=v2',
                        'http://a.com/index?p1=v1')),
        );

        return $urls;
    }

    /**
     * No excluded query parameters specified, apart from the standard "session" parameters, always excluded
     *
     * @dataProvider getTestUrls
     */
    public function testExcludeQueryParametersNone($url, $filteredUrl)
    {
        $this->setUpRootAccess();
        $idSite = API::getInstance()->addSite(
            "site1",
            array('http://example.org'),
            $ecommerce = 0,
            $siteSearch = 1,
            $searchKeywordParameters = null,
            $searchCategoryParameters = null,
            $excludedIps = '',
            $excludedQueryParameters = '',
            $timezone = null,
            $currency = null,
            $group = null,
            $startDate = null,
            $excludedUserAgents = null,
            $keepURLFragments = 1
        );
        $this->assertEquals($filteredUrl[0], PageUrl::excludeQueryParametersFromUrl($url, $idSite));
    }

    public function getTestAdvertisingClickIdUrls()
    {
        return [
            ['https://www.example.com?gclid=1234', 'https://www.example.com'],
            ['https://www.example.com?fbclid=1234', 'https://www.example.com'],
            ['https://www.example.com?msclkid=1234', 'https://www.example.com'],
            ['https://www.example.com?yclid=1234', 'https://www.example.com'],
            ['https://www.example.com/path1?gclid=1234', 'https://www.example.com/path1'],
            ['https://www.example.com/path2?fbclid=1234', 'https://www.example.com/path2'],
            ['https://www.example.com/path3?msclkid=1234', 'https://www.example.com/path3'],
            ['https://www.example.com/path4?yclid=1234', 'https://www.example.com/path4'],
            ['https://www.example.com/path5?twclid=1234', 'https://www.example.com/path5'],
            ['https://www.example.com/path6?wbraid=1234', 'https://www.example.com/path6'],
            ['https://www.example.com/path7?gbraid=1234', 'https://www.example.com/path7'],
            ['https://www.example.com?random=1234', 'https://www.example.com?random=1234'],
            ['https://www.example.com?random=1234&yclid=qwerty', 'https://www.example.com?random=1234'],
        ];
    }

    /**
     * No excluded query parameters specified, apart from the standard "session" parameters, always excluded
     *
     * @dataProvider getTestAdvertisingClickIdUrls
     */
    public function testExcludeQueryParametersAdvertisingClickIds($url, $filteredUrl)
    {
        $this->setUpRootAccess();
        $idSite = API::getInstance()->addSite(
            "site1",
            array('http://example.org'),
            $ecommerce = 0,
            $siteSearch = 1,
            $searchKeywordParameters = null,
            $searchCategoryParameters = null,
            $excludedIps = '',
            $excludedQueryParameters = '',
            $timezone = null,
            $currency = null,
            $group = null,
            $startDate = null,
            $excludedUserAgents = null,
            $keepURLFragments = 1
        );
        $this->assertEquals($filteredUrl, PageUrl::excludeQueryParametersFromUrl($url, $idSite));
    }

    public function getTestUrlsHashtag()
    {
        $urls = array(
            // URL, Expected URL
            array('wrongurl/#', 'http://wrongurl/'),
            array('wrongurl/#t', 'http://wrongurl/#t'),
            array('wrongurl/#test', 'http://wrongurl/#test'),
            array('wrongurl/#test=1', 'http://wrongurl/#test=1'),
            array('wrongurl/#test=1#', 'http://wrongurl/#test=1'),
        );
        return $urls;
    }

    /**
     * Test removing hash tag
     * @dataProvider getTestUrlsHashtag
     */
    public function testRemoveTrailingHashtag($url, $expectedUrl)
    {
        $this->assertEquals(PageUrl::reconstructNormalizedUrl($url, PageUrl::$urlPrefixMap['http://']), $expectedUrl);
    }

    /**
     * Testing with some website specific parameters excluded
     * @dataProvider getTestUrls
     */
    public function testExcludeQueryParametersSiteExcluded($url, $filteredUrl)
    {
        $excludedQueryParameters = 'p4, p2, var[value][date]';
        $this->setUpRootAccess();
        $idSite = API::getInstance()->addSite(
            "site1",
            array('http://example.org'),
            $ecommerce = 0,
            $siteSearch = 1,
            $searchKeywordParameters = null,
            $searchCategoryParameters = null,
            $excludedIps = '',
            $excludedQueryParameters,
            $timezone = null,
            $currency = null,
            $group = null,
            $startDate = null,
            $excludedUserAgents = null,
            $keepURLFragments = 1
        );
        $this->assertEquals($filteredUrl[1], PageUrl::excludeQueryParametersFromUrl($url, $idSite));
    }

    /**
     * Testing with some website specific parameters excluded using regular expressions
     * @dataProvider getTestUrls
     */
    public function testExcludeQueryParametersRegExSiteExcluded($url, $filteredUrl)
    {
        $excludedQueryParameters = '/p[4|2]/, /^var.*/';
        $this->setUpRootAccess();
        $idSite = API::getInstance()->addSite(
            "site1",
            array('http://example.org'),
            $ecommerce = 0,
            $siteSearch = 1,
            $searchKeywordParameters = null,
            $searchCategoryParameters = null,
            $excludedIps = '',
            $excludedQueryParameters,
            $timezone = null,
            $currency = null,
            $group = null,
            $startDate = null,
            $excludedUserAgents = null,
            $keepURLFragments = 1
        );
        $this->assertEquals($filteredUrl[1], PageUrl::excludeQueryParametersFromUrl($url, $idSite));
    }

    /**
     * Testing with some website specific and some global excluded query parameters
     * @dataProvider getTestUrls
     */
    public function testExcludeQueryParametersSiteAndGlobalExcluded($url, $filteredUrl)
    {
        // testing also that query parameters are case insensitive
        $excludedQueryParameters = 'P2,var[value][date]';
        $excludedGlobalParameters = 'blabla, P4';
        $this->setUpRootAccess();
        $idSite = API::getInstance()->addSite(
            "site1",
            array('http://example.org'),
            $ecommerce = 0,
            $siteSearch = 1,
            $searchKeywordParameters = null,
            $searchCategoryParameters = null,
            $excludedIps = '',
            $excludedQueryParameters,
            $timezone = null,
            $currency = null,
            $group = null,
            $startDate = null,
            $excludedUserAgents = null,
            $keepURLFragments = 1
        );
        API::getInstance()->setGlobalExcludedQueryParameters($excludedGlobalParameters);
        $this->assertEquals($filteredUrl[1], PageUrl::excludeQueryParametersFromUrl($url, $idSite));
    }

    public function getExtractUrlData()
    {
        return array(
            // outlinks
            array(
                'request'  => array('link' => 'http://example.org'),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org',
                                    'type' => Action::TYPE_OUTLINK),
            ),
            // outlinks with custom name -> no custom name
            array(
                'request'  => array('link' => 'http://example.org', 'action_name' => 'Example.org'),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org',
                                    'type' => Action::TYPE_OUTLINK),
            ),
            // keep the case in urls, but trim
            array(
                'request'  => array('link' => '    http://example.org/Category/Test/      '),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org/Category/Test/',
                                    'type' => Action::TYPE_OUTLINK),
            ),

            // no custom name
            array(
                'request'  => array('link' => '    http://example.org/Category/Test/      ', 'action_name' => '  Example dot org '),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org/Category/Test/',
                                    'type' => Action::TYPE_OUTLINK),
            ),

            // downloads
            array(
                'request'  => array('download' => 'http://example.org/*$test.zip'),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org/*$test.zip',
                                    'type' => Action::TYPE_DOWNLOAD),
            ),

            // downloads with custom name -> no custom name
            array(
                'request'  => array('download' => 'http://example.org/*$test.zip', 'action_name' => 'Download test.zip'),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org/*$test.zip',
                                    'type' => Action::TYPE_DOWNLOAD),
            ),

            // keep the case and multiple / in urls
            array(
                'request'  => array('download' => 'http://example.org/CATEGORY/test///test.pdf'),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org/CATEGORY/test///test.pdf',
                                    'type' => Action::TYPE_DOWNLOAD),
            ),

            // page view
            array(
                'request'  => array('url' => 'http://example.org/'),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org/',
                                    'type' => Action::TYPE_PAGE_URL),
            ),
            array(
                'request'  => array('url' => 'http://example.org/', 'action_name' => 'Example.org Website'),
                'expected' => array('name' => 'Example.org Website',
                                    'url'  => 'http://example.org/',
                                    'type' => Action::TYPE_PAGE_URL),
            ),
            array(
                'request'  => array('url' => 'http://example.org/CATEGORY/'),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org/CATEGORY/',
                                    'type' => Action::TYPE_PAGE_URL),
            ),
            array(
                'request'  => array('url' => 'http://example.org/CATEGORY/TEST',
                                    'action_name' => 'Example.org / Category / test /'),
                'expected' => array('name' => 'Example.org / Category / test /',
                                    'url'  => 'http://example.org/CATEGORY/TEST',
                                    'type' => Action::TYPE_PAGE_URL),
            ),
            array(
                'request'  => array('url' => 'http://example.org/?2,123'),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org/?2,123',
                                    'type' => Action::TYPE_PAGE_URL),
            ),

            // empty request
            array(
                'request'  => array(),
                'expected' => array('name' => null, 'url' => '',
                                    'type' => Action::TYPE_PAGE_URL),
            ),
            array(
                'request'  => array('name' => null, 'url' => "\n"),
                'expected' => array('name' => null, 'url' => '',
                                    'type' => Action::TYPE_PAGE_URL),
            ),
            array(
                'request'  => array('url'         => 'http://example.org/category/',
                                    'action_name' => 'custom name with/one delimiter/two delimiters/'),
                'expected' => array('name' => 'custom name with/one delimiter/two delimiters/',
                                    'url'  => 'http://example.org/category/',
                                    'type' => Action::TYPE_PAGE_URL),
            ),
            array(
                'request'  => array('url'         => 'http://example.org/category/',
                                    'action_name' => 'http://custom action name look like url/'),
                'expected' => array('name' => 'http://custom action name look like url/',
                                    'url'  => 'http://example.org/category/',
                                    'type' => Action::TYPE_PAGE_URL),
            ),
            // testing: delete tab, trimmed, not strtolowered
            array(
                'request'  => array('url' => "http://example.org/category/test///test  wOw      "),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org/category/test///test  wOw',
                                    'type' => Action::TYPE_PAGE_URL),
            ),
            // testing: inclusion of zero values in action name
            array(
                'request'  => array('url' => "http://example.org/category/1/0/t/test"),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org/category/1/0/t/test',
                                    'type' => Action::TYPE_PAGE_URL),
            ),
            // testing: action name ("Test &hellip;") - expect decoding of some html entities
            array(
                'request'  => array('url'         => 'http://example.org/ACTION/URL',
                                    'action_name' => "Test &hellip;"),
                'expected' => array('name' => 'Test …',
                                    'url'  => 'http://example.org/ACTION/URL',
                                    'type' => Action::TYPE_PAGE_URL),
            ),
            // testing: action name ("Special &amp; chars") - expect no conversion of html special chars
            array(
                'request'  => array('url'         => 'http://example.org/ACTION/URL',
                                    'action_name' => "Special &amp; chars"),
                'expected' => array('name' => 'Special &amp; chars',
                                    'url'  => 'http://example.org/ACTION/URL',
                                    'type' => Action::TYPE_PAGE_URL),
            ),
            // testing: action name ("Tést") - handle wide character
            array(
                'request'  => array('url'         => 'http://example.org/ACTION/URL',
                                    'action_name' => "Tést"),
                'expected' => array('name' => 'Tést',
                                    'url'  => 'http://example.org/ACTION/URL',
                                    'type' => Action::TYPE_PAGE_URL),
            ),
            // testing: action name ("Tést") - handle UTF-8 byte sequence
            array(
                'request'  => array('url'         => 'http://example.org/ACTION/URL',
                                    'action_name' => "T\xc3\xa9st"),
                'expected' => array('name' => 'Tést',
                                    'url'  => 'http://example.org/ACTION/URL',
                                    'type' => Action::TYPE_PAGE_URL),
            ),
            // testing: action name ("Tést") - invalid UTF-8 (e.g., ISO-8859-1) is not handled
            array(
                'request'  => array('url'         => 'http://example.org/ACTION/URL',
                                    'action_name' => "T\xe9st"),
                'expected' => array('name' => version_compare(PHP_VERSION, '5.2.5') === -1 ? 'T\xe9st' : 'Tést',
                                    'url'  => 'http://example.org/ACTION/URL',
                                    'type' => Action::TYPE_PAGE_URL),
            ),
            array(
                'request'  => array('url' => 'http://example.org/', 'action_name' => ' not trimmed   '),
                'expected' => array('name' => 'not trimmed',
                                    'url'  => 'http://example.org/',
                                    'type' => Action::TYPE_PAGE_URL),
            ),
        );
    }

    /**
     * @dataProvider getExtractUrlData
     */
    public function testExtractUrlAndActionNameFromRequest($request, $expected)
    {
        $this->setUpRootAccess();
        $idSite = API::getInstance()->addSite("site1", array('http://example.org'));
        $request['idsite'] = $idSite;
        $request = new Request($request);

        $action = Action::factory($request);

        $processed = array(
          'name' => $action->getActionName(),
          'url' => $action->getActionUrl(),
          'type' => $action->getActionType(),
        );

        $this->assertEquals($expected, $processed);
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
