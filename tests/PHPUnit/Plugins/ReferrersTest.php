<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
require_once 'Referrers/Referrers.php';

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Period;

class ReferrersTest extends PHPUnit_Framework_TestCase
{
    /**
     * Dataprovider serving all search engine data
     */
    public function getSearchEngines()
    {
        include PIWIK_PATH_TEST_TO_ROOT . '/core/DataFiles/SearchEngines.php';

        $searchEngines = array();
        foreach ($GLOBALS['Piwik_SearchEngines'] AS $url => $searchEngine) {
            $searchEngines[] = array($url, $searchEngine);
        }
        return $searchEngines;
    }

    /**
     * search engine has at least one keyword
     *
     * @group Plugins
     *
     * @dataProvider getSearchEngines
     */
    public function testMissingSearchEngineKeyword($url, $searchEngine)
    {
        // Get list of search engines and first appearing URL
        static $searchEngines = array();

        $name = parse_url('http://' . $url);
        if (!array_key_exists($searchEngine[0], $searchEngines)) {
            $searchEngines[$searchEngine[0]] = $url;

            $this->assertTrue(!empty($searchEngine[1]), $name['host']);
        }
    }

    /**
     * search engine is defined in DataFiles/SearchEngines.php but there's no favicon
     *
     * @group Plugins
     *
     * @dataProvider getSearchEngines
     */
    public function testMissingSearchEngineIcons($url, $searchEngine)
    {
        // Get list of existing favicons
        $favicons = scandir(PIWIK_PATH_TEST_TO_ROOT . '/plugins/Referrers/images/searchEngines/');

        // Get list of search engines and first appearing URL
        static $searchEngines = array();

        $name = parse_url('http://' . $url);
        if (!array_key_exists($searchEngine[0], $searchEngines)) {
            $searchEngines[$searchEngine[0]] = $url;

            $this->assertTrue(in_array($name['host'] . '.png', $favicons), $name['host']);
        }
    }

    /**
     * favicon exists but there's no corresponding search engine defined in DataFiles/SearchEngines.php
     *
     * @group Plugins
     */
    public function testObsoleteSearchEngineIcons()
    {
        include PIWIK_PATH_TEST_TO_ROOT . '/core/DataFiles/SearchEngines.php';

        // Get list of search engines and first appearing URL
        $searchEngines = array();
        foreach ($GLOBALS['Piwik_SearchEngines'] as $url => $searchEngine) {
            $name = parse_url('http://' . $url);
            if (!array_key_exists($name['host'], $searchEngines)) {
                $searchEngines[$name['host']] = true;
            }
        }

        // Get list of existing favicons
        $favicons = scandir(PIWIK_PATH_TEST_TO_ROOT . '/plugins/Referrers/images/searchEngines/');
        foreach ($favicons as $name) {
            if ($name[0] == '.' || strpos($name, 'xx.') === 0) {
                continue;
            }

            $host = substr($name, 0, -4);
            $this->assertTrue(array_key_exists($host, $searchEngines), $host);
        }
    }

    /**
     * get search engine host from url
     *
     * @group Plugins
     */
    public function testGetSearchEngineHostFromUrl()
    {
        $data = array(
            'http://www.google.com/cse' => array('www.google.com', 'www.google.com/cse'),
            'http://www.google.com'     => array('www.google.com', 'www.google.com'),
        );

        foreach ($data as $url => $expected) {
            $this->assertEquals($expected[0], \Piwik\Plugins\Referrers\getSearchEngineHostFromUrl($url));
            $this->assertEquals($expected[1], \Piwik\Plugins\Referrers\getSearchEngineHostPathFromUrl($url));
        }
    }

    /**
     * Dataprovider for testGetSearchEngineUrlFromUrlAndKeyword
     */
    public function getSearchEngineUrlFromUrlAndKeywordTestData()
    {
        return array(
            array('http://apollo.lv/portal/search/', 'piwik', 'http://apollo.lv/portal/search/?cof=FORID%3A11&q=piwik&search_where=www'),
            array('http://bing.com/images/search', 'piwik', 'http://bing.com/images/search/?q=piwik'),
            array('http://google.com', 'piwik', 'http://google.com/search?q=piwik'),
        );
    }

    /**
     * get search engine url from name and keyword
     *
     * @group Plugins
     *
     * @dataProvider getSearchEngineUrlFromUrlAndKeywordTestData
     */
    public function testGetSearchEngineUrlFromUrlAndKeyword($url, $keyword, $expected)
    {
        include PIWIK_PATH_TEST_TO_ROOT . '/core/DataFiles/SearchEngines.php';
        $this->assertEquals($expected, \Piwik\Plugins\Referrers\getSearchEngineUrlFromUrlAndKeyword($url, $keyword));
    }

    /**
     * Dataprovider for getSocialNetworkFromDomainTestData
     */
    public function getSocialNetworkFromDomainTestData()
    {
        return array(
            array('http://www.facebook.com', 'Facebook'),
            array('http://www.facebook.com/piwik', 'Facebook'),
            array('http://m.facebook.com', 'Facebook'),
            array('https://m.facebook.com', 'Facebook'),
            array('m.facebook.com', 'Facebook'),
            array('http://lastfm.com.tr', 'Last.fm'),
            array('http://t.co/test', 'Twitter'),
            array('http://xxt.co/test', \Piwik\Piwik::translate('General_Unknown')),
            array('asdfasdfadsf.com', \Piwik\Piwik::translate('General_Unknown')),
            array('http://xwayn.com', \Piwik\Piwik::translate('General_Unknown')),
            array('http://live.com/test', \Piwik\Piwik::translate('General_Unknown')),
        );
    }

    /**
     * @group Plugins
     *
     * @dataProvider getSocialNetworkFromDomainTestData
     */
    public function testGetSocialNetworkFromDomain($url, $expected)
    {
        include PIWIK_INCLUDE_PATH . '/core/DataFiles/Socials.php';
        $this->assertEquals($expected, \Piwik\Plugins\Referrers\getSocialNetworkFromDomain($url));
    }

    public function getSocialsLogoFromUrlTestData()
    {
        return array(
            array('http://www.facebook.com', 'facebook.com.png'),
            array('www.facebook.com', 'facebook.com.png',),
            array('http://lastfm.com.tr', 'last.fm.png'),
            array('http://asdfasdf.org/test', 'xx.png'),
            array('http://www.google.com', 'xx.png'),
        );
    }

    /**
     * @group Plugins
     *
     * @dataProvider getSocialsLogoFromUrlTestData
     */
    public function testGetSocialsLogoFromUrl($url, $expected)
    {
        include PIWIK_INCLUDE_PATH . '/core/DataFiles/Socials.php';
        $this->assertContains($expected, \Piwik\Plugins\Referrers\getSocialsLogoFromUrl($url));
    }


    public function isSocialUrlTestData()
    {
        return array(
            array('http://www.facebook.com', 'Facebook', true),
            array('http://www.facebook.com', 'Twitter', false),
            array('http://m.facebook.com', false, true),
            array('http://lastfm.com.tr', 'Last.fm', true),
            array('http://asdfasdf.org/test', false, false),
            array('http://asdfasdf.com/test', 'Facebook', false),
        );
    }

    /**
     * @group Plugins
     *
     * @dataProvider isSocialUrlTestData
     */
    public function testIsSocialUrl($url, $assumedSocial, $expected)
    {
        include PIWIK_INCLUDE_PATH . '/core/DataFiles/Socials.php';
        $this->assertEquals($expected, \Piwik\Plugins\Referrers\isSocialUrl($url, $assumedSocial));
    }

    public function removeUrlProtocolTestData()
    {
        return array(
            array('http://www.facebook.com', 'www.facebook.com'),
            array('https://bla.fr', 'bla.fr'),
            array('ftp://bla.fr', 'bla.fr'),
            array('udp://bla.fr', 'bla.fr'),
            array('bla.fr', 'bla.fr'),
            array('ASDasdASDDasd', 'ASDasdASDDasd'),
        );
    }

    /**
     * @group Plugins
     *
     * @dataProvider removeUrlProtocolTestData
     */
    public function testRemoveUrlProtocol($url, $expected)
    {
        $this->assertEquals($expected, \Piwik\Plugins\Referrers\removeUrlProtocol($url));
    }
}
