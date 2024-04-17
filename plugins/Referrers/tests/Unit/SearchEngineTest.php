<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\tests;

use Piwik\Plugins\Referrers\SearchEngine;
use Spyc;

/**
 * @group SearchEngine
 */
class SearchEngineTest extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        // inject definitions to avoid database usage
        $yml = file_get_contents(PIWIK_PATH_TEST_TO_ROOT . SearchEngine::DEFINITION_FILE);
        SearchEngine::getInstance()->loadYmlData($yml);
        parent::setUpBeforeClass();
    }

    public function getSearchEngineUrls()
    {
        return Spyc::YAMLLoad(PIWIK_PATH_TEST_TO_ROOT . '/tests/resources/extractSearchEngineInformationFromUrlTests.yml');
    }

    /**
     * @dataProvider getSearchEngineUrls
     */
    public function testExtractInformationFromUrl($url, $engine, $keywords)
    {
        $returnedValue = SearchEngine::getInstance()->extractInformationFromUrl($url);

        $expectedValue = false;

        if (!empty($engine)) {
            $expectedValue = array('name' => $engine, 'keywords' => $keywords);
        }

        $this->assertEquals($expectedValue, $returnedValue);
    }

    public function testSearchEnginesDefinedCorrectly()
    {
        $searchEngines = array();
        foreach (SearchEngine::getInstance()->getDefinitions() as $host => $info) {
            if (isset($info['backlink']) && $info['backlink'] !== false) {
                $this->assertTrue(strrpos($info['backlink'], "{k}") !== false, $host . " search URL is not defined correctly, must contain the macro {k}");
            }

            if (!array_key_exists($info['name'], $searchEngines)) {
                $searchEngines[$info['name']] = true;

                $this->assertTrue(strpos($host, '{}') === false, $host . " search URL is the master record and should not contain {}");
            }

            if (isset($info['charsets']) && $info['charsets'] !== false) {
                $this->assertTrue(is_array($info['charsets']) || is_string($info['charsets']), $host . ' charsets must be either a string or an array');

                if (is_string($info['charsets'])) {
                    $this->assertTrue(trim($info['charsets']) !== '', $host . ' charsets cannot be an empty string');
                    $this->assertTrue(strpos($info['charsets'], ' ') === false, $host . ' charsets cannot contain spaces');
                }

                if (is_array($info['charsets'])) {
                    $this->assertTrue(count($info['charsets']) > 0, $host . ' charsets cannot be an empty array');
                    $this->assertTrue(strpos(serialize($info['charsets']), '""') === false, $host . ' charsets in array cannot be empty stringss');
                    $this->assertTrue(strpos(serialize($info['charsets']), ' ') === false, $host . ' charsets in array cannot contain spaces');
                }
            }
        }
    }

    /**
     * Dataprovider for testGetBackLinkFromUrlAndKeyword
     */
    public function getBackLinkFromUrlAndKeywordTestData()
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
     * @dataProvider getBackLinkFromUrlAndKeywordTestData
     */
    public function testGetBackLinkFromUrlAndKeyword($url, $keyword, $expected)
    {
        $this->assertEquals($expected, SearchEngine::getInstance()->getBackLinkFromUrlAndKeyword($url, $keyword));
    }

    /**
     * Dataprovider serving all search engine data
     */
    public function getAllSearchEngines()
    {
        $yml = file_get_contents(PIWIK_PATH_TEST_TO_ROOT . SearchEngine::DEFINITION_FILE);
        SearchEngine::getInstance()->loadYmlData($yml);
        $searchEngines = array();
        foreach (SearchEngine::getInstance()->getDefinitions() as $url => $searchEngine) {
            $searchEngines[] = array($url, $searchEngine);
        }
        return $searchEngines;
    }

    /**
     * search engine has at least one keyword
     *
     * @dataProvider getAllSearchEngines
     */
    public function testMissingSearchEngineKeyword($url, $searchEngine)
    {
        $name = parse_url('http://' . $url);
        $this->assertTrue(!empty($searchEngine['params']) || !empty($searchEngine['hiddenkeyword']), $name['host']);
    }

    /**
     * search engine is defined but there's no favicon
     *
     * @dataProvider getAllSearchEngines
     */
    public function testMissingSearchEngineIcons($url, $searchEngine)
    {
        // Get list of existing favicons
        $favicons = scandir(PIWIK_PATH_TEST_TO_ROOT . '/plugins/Morpheus/icons/dist/searchEngines/');

        // Get list of search engines and first appearing URL
        static $searchEngines = [];

        $name = parse_url('http://' . $url);
        if (!array_key_exists($searchEngine['name'], $searchEngines)) {
            $searchEngines[$searchEngine['name']] = $url;

            $this->assertTrue(in_array($name['host'] . '.png', $favicons), $name['host']);
        }

        $this->assertTrue(true); // ensure there is an assertion, to prevent warning
    }

    /**
     * favicon exists but there's no corresponding search engine defined
     */
    public function testObsoleteSearchEngineIcons()
    {
        // Get list of search engines and first appearing URL
        $searchEngines = array();
        foreach (SearchEngine::getInstance()->getDefinitions() as $url => $searchEngine) {
            $name = parse_url('http://' . $url);
            if (!array_key_exists($name['host'], $searchEngines)) {
                $searchEngines[$name['host']] = true;
            }
        }

        // Get list of existing favicons
        $favicons = scandir(PIWIK_PATH_TEST_TO_ROOT . '/plugins/Morpheus/icons/dist/searchEngines/');
        foreach ($favicons as $name) {
            if ($name[0] == '.' || strpos($name, 'xx.') === 0) {
                continue;
            }

            $host = substr($name, 0, -4);
            $this->assertTrue(array_key_exists($host, $searchEngines), $host);
        }
    }
}
