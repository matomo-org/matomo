<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class Tracker_ActionTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $userFile = dirname(__FILE__) . '/../../../resources/Tracker/Action.config.ini.php';
        $config = Piwik_Config::getInstance();
        $config->clear();
        $config->setTestEnvironment($userFile, false);

        Piwik_PluginsManager::getInstance()->loadPlugins(array('SitesManager'));
    }

    protected function setUpRootAccess()
    {
        $pseudoMockAccess = new FakeAccess;
        FakeAccess::$superUser = true;
        Zend_Registry::set('access', $pseudoMockAccess);
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
     * @group Core
     * @group Tracker
     * @group Tracker_Action
     * @dataProvider getTestUrls
     */
    public function testExcludeQueryParametersNone($url, $filteredUrl)
    {
        $this->setUpRootAccess();
        $idSite = Piwik_SitesManager_API::getInstance()->addSite("site1", array('http://example.org'), $ecommerce = 0,
            $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null,
            $excludedIps = '', $excludedQueryParameters = '', $timezone = null, $currency = null,
            $group = null, $startDate = null, $excludedUserAgents = null, $keepURLFragments = 1);
        $this->assertEquals($filteredUrl[0], Piwik_Tracker_Action::excludeQueryParametersFromUrl($url, $idSite));
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
     * @group Core
     * @group Tracker
     * @group Tracker_Action
     * @dataProvider getTestUrlsHashtag
     */
    public function testRemoveTrailingHashtag($url, $expectedUrl)
    {
        $this->assertEquals(Piwik_Tracker_Action::reconstructNormalizedUrl($url, Piwik_Tracker_Action::$urlPrefixMap['http://']), $expectedUrl);
    }


    /**
     * Testing with some website specific parameters excluded
     * @group Core
     * @group Tracker
     * @group Tracker_Action
     * @dataProvider getTestUrls
     */
    public function testExcludeQueryParametersSiteExcluded($url, $filteredUrl)
    {
        $excludedQueryParameters = 'p4, p2, var[value][date]';
        $this->setUpRootAccess();
        $idSite = Piwik_SitesManager_API::getInstance()->addSite("site1", array('http://example.org'), $ecommerce = 0,
            $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null,
            $excludedIps = '', $excludedQueryParameters, $timezone = null, $currency = null,
            $group = null, $startDate = null, $excludedUserAgents = null, $keepURLFragments = 1);
        $this->assertEquals($filteredUrl[1], Piwik_Tracker_Action::excludeQueryParametersFromUrl($url, $idSite));
    }

    /**
     * Testing with some website specific and some global excluded query parameters
     * @group Core
     * @group Tracker
     * @group Tracker_Action
     * @dataProvider getTestUrls
     */
    public function testExcludeQueryParametersSiteAndGlobalExcluded($url, $filteredUrl)
    {
        // testing also that query parameters are case insensitive 
        $excludedQueryParameters = 'P2,var[value][date]';
        $excludedGlobalParameters = 'blabla, P4';
        $this->setUpRootAccess();
        $idSite = Piwik_SitesManager_API::getInstance()->addSite("site1", array('http://example.org'), $ecommerce = 0,
            $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null,
            $excludedIps = '', $excludedQueryParameters, $timezone = null, $currency = null,
            $group = null, $startDate = null, $excludedUserAgents = null, $keepURLFragments = 1);
        Piwik_SitesManager_API::getInstance()->setGlobalExcludedQueryParameters($excludedGlobalParameters);
        $this->assertEquals($filteredUrl[1], Piwik_Tracker_Action::excludeQueryParametersFromUrl($url, $idSite));
    }


    public function getExtractUrlData()
    {
        return array(
            // outlinks
            array(
                'request'  => array('link' => 'http://example.org'),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org',
                                    'type' => Piwik_Tracker_Action::TYPE_OUTLINK),
            ),
            // outlinks with custom name
            array(
                'request'  => array('link' => 'http://example.org', 'action_name' => 'Example.org'),
                'expected' => array('name' => 'Example.org',
                                    'url'  => 'http://example.org',
                                    'type' => Piwik_Tracker_Action::TYPE_OUTLINK),
            ),
            // keep the case in urls, but trim
            array(
                'request'  => array('link' => '    http://example.org/Category/Test/      '),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org/Category/Test/',
                                    'type' => Piwik_Tracker_Action::TYPE_OUTLINK),
            ),

            // trim the custom name
            array(
                'request'  => array('link' => '    http://example.org/Category/Test/      ', 'action_name' => '  Example dot org '),
                'expected' => array('name' => 'Example dot org',
                                    'url'  => 'http://example.org/Category/Test/',
                                    'type' => Piwik_Tracker_Action::TYPE_OUTLINK),
            ),

            // downloads
            array(
                'request'  => array('download' => 'http://example.org/*$test.zip'),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org/*$test.zip',
                                    'type' => Piwik_Tracker_Action::TYPE_DOWNLOAD),
            ),

            // downloads with custom name
            array(
                'request'  => array('download' => 'http://example.org/*$test.zip', 'action_name' => 'Download test.zip'),
                'expected' => array('name' => 'Download test.zip',
                                    'url'  => 'http://example.org/*$test.zip',
                                    'type' => Piwik_Tracker_Action::TYPE_DOWNLOAD),
            ),

            // keep the case and multiple / in urls
            array(
                'request'  => array('download' => 'http://example.org/CATEGORY/test///test.pdf'),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org/CATEGORY/test///test.pdf',
                                    'type' => Piwik_Tracker_Action::TYPE_DOWNLOAD),
            ),

            // page view
            array(
                'request'  => array('url' => 'http://example.org/'),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org/',
                                    'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
            ),
            array(
                'request'  => array('url' => 'http://example.org/', 'action_name' => 'Example.org Website'),
                'expected' => array('name' => 'Example.org Website',
                                    'url'  => 'http://example.org/',
                                    'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
            ),
            array(
                'request'  => array('url' => 'http://example.org/CATEGORY/'),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org/CATEGORY/',
                                    'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
            ),
            array(
                'request'  => array('url' => 'http://example.org/CATEGORY/TEST', 'action_name' => 'Example.org / Category / test /'),
                'expected' => array('name' => 'Example.org/Category/test',
                                    'url'  => 'http://example.org/CATEGORY/TEST',
                                    'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
            ),
            array(
                'request'  => array('url' => 'http://example.org/?2,123'),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org/?2,123',
                                    'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
            ),

            // empty request
            array(
                'request'  => array(),
                'expected' => array('name' => null, 'url' => '',
                                    'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
            ),
            array(
                'request'  => array('name' => null, 'url' => "\n"),
                'expected' => array('name' => null, 'url' => '',
                                    'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
            ),
            array(
                'request'  => array('url'         => 'http://example.org/category/',
                                    'action_name' => 'custom name with/one delimiter/two delimiters/'),
                'expected' => array('name' => 'custom name with/one delimiter/two delimiters',
                                    'url'  => 'http://example.org/category/',
                                    'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
            ),
            array(
                'request'  => array('url'         => 'http://example.org/category/',
                                    'action_name' => 'http://custom action name look like url/'),
                'expected' => array('name' => 'http:/custom action name look like url',
                                    'url'  => 'http://example.org/category/',
                                    'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
            ),
            // testing: delete tab, trimmed, not strtolowered
            array(
                'request'  => array('url' => "http://example.org/category/test///test  wOw      "),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org/category/test///test  wOw',
                                    'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
            ),
            // testing: inclusion of zero values in action name
            array(
                'request'  => array('url' => "http://example.org/category/1/0/t/test"),
                'expected' => array('name' => null,
                                    'url'  => 'http://example.org/category/1/0/t/test',
                                    'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
            ),
            // testing: action name ("Test &hellip;") - expect decoding of some html entities
            array(
                'request'  => array('url'         => 'http://example.org/ACTION/URL',
                                    'action_name' => "Test &hellip;"),
                'expected' => array('name' => 'Test …',
                                    'url'  => 'http://example.org/ACTION/URL',
                                    'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
            ),
            // testing: action name ("Special &amp; chars") - expect no conversion of html special chars
            array(
                'request'  => array('url'         => 'http://example.org/ACTION/URL',
                                    'action_name' => "Special &amp; chars"),
                'expected' => array('name' => 'Special &amp; chars',
                                    'url'  => 'http://example.org/ACTION/URL',
                                    'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
            ),
            // testing: action name ("Tést") - handle wide character
            array(
                'request'  => array('url'         => 'http://example.org/ACTION/URL',
                                    'action_name' => "Tést"),
                'expected' => array('name' => 'Tést',
                                    'url'  => 'http://example.org/ACTION/URL',
                                    'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
            ),
            // testing: action name ("Tést") - handle UTF-8 byte sequence
            array(
                'request'  => array('url'         => 'http://example.org/ACTION/URL',
                                    'action_name' => "T\xc3\xa9st"),
                'expected' => array('name' => 'Tést',
                                    'url'  => 'http://example.org/ACTION/URL',
                                    'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
            ),
            // testing: action name ("Tést") - invalid UTF-8 (e.g., ISO-8859-1) is not handled
            array(
                'request'  => array('url'         => 'http://example.org/ACTION/URL',
                                    'action_name' => "T\xe9st"),
                'expected' => array('name' => version_compare(PHP_VERSION, '5.2.5') === -1 ? 'T\xe9st' : 'Tést',
                                    'url'  => 'http://example.org/ACTION/URL',
                                    'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
            ),
        );
    }

    /**
     * @dataProvider getExtractUrlData
     * @group Core
     * @group Tracker
     * @group Tracker_Action
     */
    public function testExtractUrlAndActionNameFromRequest($request, $expected)
    {
        $this->setUpRootAccess();
        $idSite = Piwik_SitesManager_API::getInstance()->addSite("site1", array('http://example.org'));
        $action = new Test_Piwik_TrackerAction_extractUrlAndActionNameFromRequest();
        $action->setRequest($request);
        $action->setIdSite($idSite);
        $this->assertEquals($action->public_extractUrlAndActionNameFromRequest(), $expected);
    }
}

class Test_Piwik_TrackerAction_extractUrlAndActionNameFromRequest extends Piwik_Tracker_Action
{
    public function public_extractUrlAndActionNameFromRequest()
    {
        return $this->extractUrlAndActionNameFromRequest();
    }
}
