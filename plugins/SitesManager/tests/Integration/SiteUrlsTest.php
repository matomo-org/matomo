<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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

    public function setUp(): void
    {
        parent::setUp();

        $this->siteUrls = new SiteUrls();
        $this->api = API::getInstance();

        SiteUrls::clearSitesCache();
    }

    public function testGetAllSiteUrlsShouldReturnAnEmptyArrayIfThereAreNoSites()
    {
        $this->assertSiteUrls([]);
    }

    public function testGetAllSiteUrlsShouldReturnUrlsForEachSiteId()
    {
        $this->addSite('http://www.example.com'); // only one main URL
        $this->assertSiteUrls([1 => ['http://www.example.com']]);

        $this->addSite('http://www.example.com', 'http://www.piwik.org'); // main URL and alias URL
        $this->assertSiteUrls([1 => ['http://www.example.com'], 2 => ['http://www.example.com', 'http://www.piwik.org']]);

        $this->api->addSiteAliasUrls(2, 'http://piwik.org');
        $this->assertSiteUrls([1 => ['http://www.example.com'], 2 => ['http://www.example.com', 'http://piwik.org', 'http://www.piwik.org']]);

        $this->api->setSiteAliasUrls(2, []);
        $this->assertSiteUrls([1 => ['http://www.example.com'], 2 => ['http://www.example.com']]);
    }

    public function testGetAllCachedSiteUrlsShouldReturnAnEmptyArrayIfThereAreNoSites()
    {
        $this->assertCachedSiteUrls([]);
    }

    public function testGetAllCachedSiteUrlsShouldReturnCorrectResultEvenIfItIsCachedAsWeClearTheCacheOnAnyChange()
    {
        $this->addSite('http://www.example.com'); // only one main URL
        $this->assertCachedSiteUrls([1 => ['http://www.example.com']]);

        $this->addSite('http://www.example.com', 'http://www.piwik.org'); // main URL and alias URL
        $this->assertCachedSiteUrls([1 => ['http://www.example.com'], 2 => ['http://www.example.com', 'http://www.piwik.org']]);

        $this->api->addSiteAliasUrls(2, 'http://piwik.org');
        $this->assertCachedSiteUrls([1 => ['http://www.example.com'], 2 => ['http://www.example.com', 'http://piwik.org', 'http://www.piwik.org']]);

        $this->api->setSiteAliasUrls(2, []);
        $this->assertCachedSiteUrls([1 => ['http://www.example.com'], 2 => ['http://www.example.com']]);

        $this->api->updateSite(1, 'siteName3', ['http://updated.example.com', 'http://2.example.com']);
        $this->assertCachedSiteUrls([1 => ['http://updated.example.com', 'http://2.example.com'], 2 => ['http://www.example.com']]);
    }

    public function testGetAllCachedSiteUrlsShouldWriteACacheFile()
    {
        // make sure cache is empty
        $this->assertValueInCache(false);

        $this->addSite('http://www.example.com');
        $this->siteUrls->getAllCachedSiteUrls();

        // make sure we have a cached result
        $this->assertValueInCache([1 => ['http://www.example.com']]);
    }

    public function testClearSitesCacheShouldActuallyDeleteACache()
    {
        $this->addSite('http://www.example.com');
        $this->siteUrls->getAllCachedSiteUrls();

        // make sure we have a cached result
        $this->assertValueInCache([1 => ['http://www.example.com']]);

        SiteUrls::clearSitesCache();

        // make sure is empty now
        $this->assertValueInCache(false);
    }

    public function testGetAllCachedSiteUrlsShouldReadFromTheCacheFile()
    {
        $urlsToFake = [1 => 'Whatever'];
        $cache      = $this->buildCache();
        $cache->save('allSiteUrlsPerSite', $urlsToFake, 600);

        $actual = $this->siteUrls->getAllCachedSiteUrls();

        $this->assertEquals($urlsToFake, $actual);
    }

    public function testGroupUrlsByHostShouldReturnEmptyArrayWhenNoUrlsGiven()
    {
        $this->assertSame([], $this->siteUrls->groupUrlsByHost([]));
        $this->assertSame([], $this->siteUrls->groupUrlsByHost(null));
    }

    public function testGroupUrlsByHostShouldGroupByHostWithOneSiteAndDifferentDomainsShouldRemoveWwwAndDefaultToPathSlash()
    {
        $idSite = 1;
        $oneSite = [
            $idSite => [
                'http://apache.piwik',
                'http://www.example.com',  // should remove www.
                'https://example.org',     // should handle https or other protocol
                'http://apache.piwik/',    // same as initial one but with slash at the end, should not add idsite twice
                'http://third.www.com'     // should not remove www. in the middle of a domain
            ]
        ];

        $expected = [
            'apache.piwik'  => ['/' => [$idSite]],
            'example.com'   => ['/' => [$idSite]],
            'example.org'   => ['/' => [$idSite]],
            'third.www.com' => ['/' => [$idSite]],
        ];

        $this->assertSame($expected, $this->siteUrls->groupUrlsByHost($oneSite));
    }

    public function testGroupUrlsByHostShouldGroupByHostWithDifferentDomainsAndPathsShouldListPathByNumberOfDirectoriesAndConvertToLowerCase()
    {
        $idSite = 1;
        $idSite2 = 2;
        $idSite3 = 3;
        $idSite4 = 4;
        $idSite5 = 5;

        $urls = [
            $idSite => [
                'http://apache.piwik/test', 'http://apache.piWik', 'http://apache.piwik/foo/bAr/', 'http://apache.piwik/Foo/SECOND'
            ],
            $idSite2 => [
                'http://apache.piwik/test/', 'http://example.oRg', 'http://apache.piwik/foo/secOnd'
            ],
            $idSite3 => [
                'http://apache.piwik/', 'http://apache.piwik/third', 'http://exampLe.com', 'http://example.org/foo/test/two'
            ],
            $idSite4 => [],
            $idSite5 => ['invalidUrl', 'ftp://example.org/'],
        ];

        $expected = [
            'apache.piwik' => [
                '/foo/second/' => [$idSite, $idSite2],
                '/foo/bar/' => [$idSite],
                '/third/' => [$idSite3],
                '/test/' => [$idSite, $idSite2],
                '/' => [$idSite, $idSite3]
            ],
            'example.org' => [
                '/foo/test/two/' => [$idSite3],
                '/' => [$idSite2, $idSite5]
            ],
            'example.com' => [
                '/' => [$idSite3]
            ],
        ];

        $this->assertSame($expected, $this->siteUrls->groupUrlsByHost($urls));
    }

    /**
     * @dataProvider getTestIdSitesMatchingUrl
     */
    public function testGetIdSitesMatchingUrl($expectedMatchSites, $parsedUrl)
    {
        $urlsGroupedByHost = [
            'apache.piwik' => [
                '/foo/second/' => [2],
                '/foo/sec/' => [4],
                '/foo/bar/' => [1],
                '/third/' => [3],
                '/test/' => [1, 2],
                '/' => [1, 3]
            ],
            'example.org' => [
                '/foo/test/two/' => [3],
                '/foo/second/' => [6],
                '/' => [2, 5]
            ],
            'example.com' => [
                '/' => [3]
            ],
            'my.site.com' => [
                '/path/' => [2]
            ],
            '.site.com' => [
                '/' => [3]
            ]
        ];
        $matchedSites = $this->siteUrls->getIdSitesMatchingUrl($parsedUrl, $urlsGroupedByHost);

        $this->assertSame($expectedMatchSites, $matchedSites);
    }

    public function getTestIdSitesMatchingUrl()
    {
        return [
            [[1,3], ['host' => 'apache.piwik']],
            [[1,3], ['host' => 'apache.piwik', 'path' => '/']],
            [[1,3], ['host' => 'apache.piwik', 'path' => 'nomatch']], // no other URL matches a site so we fall back to domain match
            [[1,3], ['host' => 'apache.piwik', 'path' => '/nomatch']],
            [[2], ['host' => 'apache.piwik', 'path' => '/foo/second']],
            [[2], ['host' => 'apache.piwik', 'path' => '/foo/second/']], // it shouldn't matter if slash is at end or not
            [[2], ['host' => 'apache.piwik', 'path' => '/foo/second/test']], // it should find best match
            [[4], ['host' => 'apache.piwik', 'path' => '/foo/sec/test']], // it should not use /foo/second for these
            [[4], ['host' => 'apache.piwik', 'path' => '/foo/sec/']],
            [[4], ['host' => 'apache.piwik', 'path' => '/foo/sec']],
            [[1,3], ['host' => 'apache.piwik', 'path' => '/foo']],
            [[2,5], ['host' => 'example.org']],
            [[2,5], ['host' => 'example.org', 'path' => '/']],
            [[2,5], ['host' => 'example.org', 'path' => 'any/nonmatching/path']],
            [[6], ['host' => 'example.org', 'path' => '/foo/second']],
            [[6], ['host' => 'example.org', 'path' => '/foo/second/test']],
            [[3], ['host' => 'example.com']],
            [null, ['host' => 'example.pro']],
            [null, ['host' => 'example.pro', 'path' => '/any']],
            [[2], ['host' => 'my.site.com', 'path' => '/path/sub']],
            [[3], ['host' => 'my.site.com', 'path' => '/other/path']],
            [[3], ['host' => 'any.site.com', 'path' => '/']],
        ];
    }

    /**
     * @dataProvider getTestPathMatchingUrl
     */
    public function testGetPathMatchingUrl($expectedMatchSites, $parsedUrl)
    {
        $urlsGroupedByHost = [
            'apache.piwik' => [
                '/foo/second/' => [2],
                '/foo/sec/' => [4],
                '/foo/bar/' => [1],
                '/third/' => [3],
                '/test/' => [1, 2],
                '/' => [1, 3]
            ],
            'example.org' => [
                '/foo/test/two/' => [3],
                '/foo/second/' => [6],
                '/' => [2, 5]
            ],
        ];
        $matchedSites = $this->siteUrls->getPathMatchingUrl($parsedUrl, $urlsGroupedByHost);

        $this->assertSame($expectedMatchSites, $matchedSites);
    }

    public function getTestPathMatchingUrl()
    {
        return [
            ['/', ['host' => 'apache.piwik']],
            ['/', ['host' => 'apache.piwik', 'path' => '/']],
            ['/', ['host' => 'apache.piwik', 'path' => '']],
            [null, ['host' => 'test.piwik']],
            [null, ['host' => 'test.apache.piwik']], // we do not match subdomains, only exact domain match

            ['/foo/bar/', ['host' => 'apache.piwik', 'path' => '/foo/bar']],
            ['/foo/bar/', ['host' => 'apache.piwik', 'path' => '/foo/bar/']],
            ['/foo/bar/', ['host' => 'apache.piwik', 'path' => '/foo/bar/baz/']],
            ['/', ['host' => 'apache.piwik', 'path' => '/foo/baz/bar/']],
            ['/third/', ['host' => 'apache.piwik', 'path' => '/third/bar/baz/']],

            ['/foo/second/', ['host' => 'example.org', 'path' => '/foo/second/']],
            ['/', ['host' => 'example.org', 'path' => '/foo/secon']],
            [null, ['host' => 'example.pro', 'path' => '/foo/second/']],
        ];
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

    private function addSite(...$urls)
    {
        $this->api->addSite('siteName', $urls);
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
