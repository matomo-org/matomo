<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Integration;
use Piwik\Cache;
use Piwik\Plugins\SitesManager\API;
use Piwik\Plugins\SitesManager\SiteUrls;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group SitesManager
 * @group SiteUrlsTest
 * @group Plugins
 */
class SiteUrlsTest extends IntegrationTestCase
{
    /**
     * @var SiteUrls
     */
    private $siteUrls;

    /**
     * @var API
     */
    private $api;

    public function setUp()
    {
        parent::setUp();

        $this->siteUrls = new SiteUrls();
        $this->api = API::getInstance();

        SiteUrls::clearSitesCache();
    }

    public function testGetAllSiteUrls_shouldReturnAnEmptyArray_IfThereAreNoSites()
    {
        $this->assertSiteUrls(array());
    }

    public function testGetAllSiteUrls_shouldReturnUrlsForEachSiteId()
    {
        $this->addSite('http://www.example.com'); // only one main URL
        $this->assertSiteUrls(array(1 => array('http://www.example.com')));

        $this->addSite('http://www.example.com', 'http://www.piwik.org'); // main URL and alias URL
        $this->assertSiteUrls(array(1 => array('http://www.example.com'), 2 => array('http://www.example.com', 'http://www.piwik.org')));

        $this->api->addSiteAliasUrls(2, 'http://piwik.org');
        $this->assertSiteUrls(array(1 => array('http://www.example.com'), 2 => array('http://www.example.com', 'http://piwik.org', 'http://www.piwik.org')));

        $this->api->setSiteAliasUrls(2, array());
        $this->assertSiteUrls(array(1 => array('http://www.example.com'), 2 => array('http://www.example.com')));
    }

    public function testGetAllCachedSiteUrls_shouldReturnAnEmptyArray_IfThereAreNoSites()
    {
        $this->assertCachedSiteUrls(array());
    }

    public function testGetAllCachedSiteUrls_ShouldReturnCorrectResultEvenIfItIsCachedAsWeClearTheCacheOnAnyChange()
    {
        $this->addSite('http://www.example.com'); // only one main URL
        $this->assertCachedSiteUrls(array(1 => array('http://www.example.com')));

        $this->addSite('http://www.example.com', 'http://www.piwik.org'); // main URL and alias URL
        $this->assertCachedSiteUrls(array(1 => array('http://www.example.com'), 2 => array('http://www.example.com', 'http://www.piwik.org')));

        $this->api->addSiteAliasUrls(2, 'http://piwik.org');
        $this->assertCachedSiteUrls(array(1 => array('http://www.example.com'), 2 => array('http://www.example.com', 'http://piwik.org', 'http://www.piwik.org')));

        $this->api->setSiteAliasUrls(2, array());
        $this->assertCachedSiteUrls(array(1 => array('http://www.example.com'), 2 => array('http://www.example.com')));

        $this->api->updateSite(1, 'siteName3', array('http://updated.example.com', 'http://2.example.com'));
        $this->assertCachedSiteUrls(array(1 => array('http://updated.example.com', 'http://2.example.com'), 2 => array('http://www.example.com')));
    }

    public function testGetAllCachedSiteUrls_ShouldWriteACacheFile()
    {
        // make sure cache is empty
        $this->assertValueInCache(false);

        $this->addSite('http://www.example.com');
        $this->siteUrls->getAllCachedSiteUrls();

        // make sure we have a cached result
        $this->assertValueInCache(array(1 => array('http://www.example.com')));
    }

    public function test_clearSitesCache_ShouldActuallyDeleteACache()
    {
        $this->addSite('http://www.example.com');
        $this->siteUrls->getAllCachedSiteUrls();

        // make sure we have a cached result
        $this->assertValueInCache(array(1 => array('http://www.example.com')));

        SiteUrls::clearSitesCache();

        // make sure is empty now
        $this->assertValueInCache(false);
    }

    public function testGetAllCachedSiteUrls_ShouldReadFromTheCacheFile()
    {
        $urlsToFake = array(1 => 'Whatever');
        $cache      = $this->buildCache();
        $cache->save('allSiteUrlsPerSite', $urlsToFake, 600);

        $actual = $this->siteUrls->getAllCachedSiteUrls();

        $this->assertEquals($urlsToFake, $actual);
    }

    public function test_groupUrlsByHost_shouldReturnEmptyArray_WhenNoUrlsGiven()
    {
        $this->assertSame(array(), $this->siteUrls->groupUrlsByHost(array()));
        $this->assertSame(array(), $this->siteUrls->groupUrlsByHost(null));
    }

    public function test_groupUrlsByHost_shouldGroupByHost_WithOneSiteAndDifferentDomains_shouldRemoveWwwAndDefaultToPathSlash()
    {
        $idSite = 1;
        $oneSite = array(
            $idSite => array(
                'http://apache.piwik',
                'http://www.example.com',  // should remove www.
                'https://example.org',     // should handle https or other protocol
                'http://apache.piwik/',    // same as initial one but with slash at the end, should not add idsite twice
                'http://third.www.com'     // should not remove www. in the middle of a domain
            )
        );

        $expected = array(
            'apache.piwik'  => array('/' => array($idSite)),
            'example.com'   => array('/' => array($idSite)),
            'example.org'   => array('/' => array($idSite)),
            'third.www.com' => array('/' => array($idSite)),
        );

        $this->assertSame($expected, $this->siteUrls->groupUrlsByHost($oneSite));
    }

    public function test_groupUrlsByHost_shouldGroupByHost_WithDifferentDomainsAndPathsShouldListPathByNumberOfDirectoriesAndConvertToLowerCase()
    {
        $idSite = 1;
        $idSite2 = 2;
        $idSite3 = 3;
        $idSite4 = 4;
        $idSite5 = 5;

        $urls = array(
            $idSite => array(
                'http://apache.piwik/test', 'http://apache.piWik', 'http://apache.piwik/foo/bAr/', 'http://apache.piwik/Foo/SECOND'
            ),
            $idSite2 => array(
                'http://apache.piwik/test/', 'http://example.oRg', 'http://apache.piwik/foo/secOnd'
            ),
            $idSite3 => array(
                'http://apache.piwik/', 'http://apache.piwik/third', 'http://exampLe.com', 'http://example.org/foo/test/two'
            ),
            $idSite4 => array(),
            $idSite5 => array('invalidUrl', 'ftp://example.org/'),
        );

        $expected = array(
            'apache.piwik' => array(
                '/foo/second/' => array($idSite, $idSite2),
                '/foo/bar/' => array($idSite),
                '/third/' => array($idSite3),
                '/test/' => array($idSite, $idSite2),
                '/' => array($idSite, $idSite3)
            ),
            'example.org' => array(
                '/foo/test/two/' => array($idSite3),
                '/' => array($idSite2, $idSite5)
            ),
            'example.com' => array(
                '/' => array($idSite3)
            ),
        );

        $this->assertSame($expected, $this->siteUrls->groupUrlsByHost($urls));
    }

    /**
     * @dataProvider getTestIdSitesMatchingUrl
     */
    public function test_getIdSitesMatchingUrl($expectedMatchSites, $parsedUrl)
    {
        $urlsGroupedByHost = array(
            'apache.piwik' => array(
                '/foo/second/' => array(2),
                '/foo/sec/' => array(4),
                '/foo/bar/' => array(1),
                '/third/' => array(3),
                '/test/' => array(1, 2),
                '/' => array(1, 3)
            ),
            'example.org' => array(
                '/foo/test/two/' => array(3),
                '/foo/second/' => array(6),
                '/' => array(2, 5)
            ),
            'example.com' => array(
                '/' => array(3)
            ),
        );
        $matchedSites = $this->siteUrls->getIdSitesMatchingUrl($parsedUrl, $urlsGroupedByHost);

        $this->assertSame($expectedMatchSites, $matchedSites);
    }

    public function getTestIdSitesMatchingUrl()
    {
        return array(
            array(array(1,3), array('host' => 'apache.piwik')),
            array(array(1,3), array('host' => 'apache.piwik', 'path' => '/')),
            array(array(1,3), array('host' => 'apache.piwik', 'path' => 'nomatch')), // no other URL matches a site so we fall back to domain match
            array(array(1,3), array('host' => 'apache.piwik', 'path' => '/nomatch')),
            array(array(2), array('host' => 'apache.piwik', 'path' => '/foo/second')),
            array(array(2), array('host' => 'apache.piwik', 'path' => '/foo/second/')), // it shouldn't matter if slash is at end or not
            array(array(2), array('host' => 'apache.piwik', 'path' => '/foo/second/test')), // it should find best match
            array(array(4), array('host' => 'apache.piwik', 'path' => '/foo/sec/test')), // it should not use /foo/second for these
            array(array(4), array('host' => 'apache.piwik', 'path' => '/foo/sec/')),
            array(array(4), array('host' => 'apache.piwik', 'path' => '/foo/sec')),
            array(array(1,3), array('host' => 'apache.piwik', 'path' => '/foo')),
            array(array(2,5), array('host' => 'example.org')),
            array(array(2,5), array('host' => 'example.org', 'path' => '/')),
            array(array(2,5), array('host' => 'example.org', 'path' => 'any/nonmatching/path')),
            array(array(6), array('host' => 'example.org', 'path' => '/foo/second')),
            array(array(6), array('host' => 'example.org', 'path' => '/foo/second/test')),
            array(array(3), array('host' => 'example.com')),
            array(null, array('host' => 'example.pro')),
            array(null, array('host' => 'example.pro', 'path' => '/any')),
        );
    }

    /**
     * @dataProvider getTestPathMatchingUrl
     */
    public function test_getPathMatchingUrl($expectedMatchSites, $parsedUrl)
    {
        $urlsGroupedByHost = array(
            'apache.piwik' => array(
                '/foo/second/' => array(2),
                '/foo/sec/' => array(4),
                '/foo/bar/' => array(1),
                '/third/' => array(3),
                '/test/' => array(1, 2),
                '/' => array(1, 3)
            ),
            'example.org' => array(
                '/foo/test/two/' => array(3),
                '/foo/second/' => array(6),
                '/' => array(2, 5)
            ),
        );
        $matchedSites = $this->siteUrls->getPathMatchingUrl($parsedUrl, $urlsGroupedByHost);

        $this->assertSame($expectedMatchSites, $matchedSites);
    }

    public function getTestPathMatchingUrl()
    {
        return array(
            array('/', array('host' => 'apache.piwik')),
            array('/', array('host' => 'apache.piwik', 'path' => '/')),
            array('/', array('host' => 'apache.piwik', 'path' => '')),
            array(null, array('host' => 'test.piwik')),
            array(null, array('host' => 'test.apache.piwik')), // we do not match subdomains, only exact domain match

            array('/foo/bar/', array('host' => 'apache.piwik', 'path' => '/foo/bar')),
            array('/foo/bar/', array('host' => 'apache.piwik', 'path' => '/foo/bar/')),
            array('/foo/bar/', array('host' => 'apache.piwik', 'path' => '/foo/bar/baz/')),
            array('/', array('host' => 'apache.piwik', 'path' => '/foo/baz/bar/')),
            array('/third/', array('host' => 'apache.piwik', 'path' => '/third/bar/baz/')),

            array('/foo/second/', array('host' => 'example.org', 'path' => '/foo/second/')),
            array('/', array('host' => 'example.org', 'path' => '/foo/secon')),
            array(null, array('host' => 'example.pro', 'path' => '/foo/second/')),
        );
    }

    private function assertSiteUrls($expectedUrls)
    {
        $urls = $this->siteUrls->getAllSiteUrls();
        $this->assertEquals($expectedUrls, $urls);
    }

    private function assertCachedSiteUrls($expectedUrls)
    {
        $urls = $this->siteUrls->getAllCachedSiteUrls();
        $this->assertEquals($expectedUrls, $urls);
    }

    private function addSite($urls)
    {
        $this->api->addSite('siteName', func_get_args());
    }

    private function assertValueInCache($value)
    {
        $cache    = $this->buildCache();
        $siteUrls = $cache->fetch('allSiteUrlsPerSite');

        $this->assertEquals($value, $siteUrls);
    }

    private function buildCache()
    {
        return Cache::getLazyCache();
    }
}
