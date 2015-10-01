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
