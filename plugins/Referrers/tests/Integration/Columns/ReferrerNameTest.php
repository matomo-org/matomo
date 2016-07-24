<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\tests\Integration\Columns;

use Piwik\Plugins\Referrers\Columns\ReferrerName;
use Piwik\Plugins\Referrers\Columns\ReferrerType;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Tracker\Visitor;

/**
 * @group Referrers
 * @group ReferrerNameTest
 * @group ReferrerName
 * @group Plugins
 */
class ReferrerNameTest extends IntegrationTestCase
{
    /**
     * @var ReferrerType
     */
    private $referrerName;
    private $idSite1 = 1;
    private $idSite2 = 2;
    private $idSite3 = 3;

    public function setUp()
    {
        parent::setUp();

        Cache::clearCacheGeneral();

        $date = '2012-01-01 00:00:00';
        $ecommerce = false;

        Fixture::createWebsite($date, $ecommerce, $name = 'test1', $url = 'http://piwik.org/foo/bar');
        Fixture::createWebsite($date, $ecommerce, $name = 'test2', $url = 'http://piwik.org/');
        Fixture::createWebsite($date, $ecommerce, $name = 'test3', $url = 'http://piwik.xyz/');

        $this->referrerName = new ReferrerName();
    }

    public function tearDown()
    {
        // clean up your test here if needed
        Cache::clearCacheGeneral();

        parent::tearDown();
    }

    /**
     * @dataProvider getReferrerUrls
     */
    public function test_onNewVisit_shouldDetectCorrectReferrerType($expectedType, $idSite, $url, $referrerUrl)
    {
        $request = $this->getRequest(array('idsite' => $idSite, 'url' => $url, 'urlref' => $referrerUrl));
        $type = $this->referrerName->onNewVisit($request, $this->getNewVisitor(), $action = null);

        $this->assertSame($expectedType, $type);
    }

    public function getReferrerUrls()
    {
        $url = 'http://piwik.org/foo/bar';
        $referrer = 'http://piwik.org';

        $directEntryReferrerName = null;

        return array(
            // domain matches but path does not match for idsite1
            array('piwik.org',              $this->idSite1, $url, $referrer),
            array('piwik.org',              $this->idSite1, $url, $referrer . '/'),
            // idSite2 matches any piwik.org path so this is a direct entry for it
            array($directEntryReferrerName, $this->idSite2, $url, $referrer),
            array($directEntryReferrerName, $this->idSite2, $url, $referrer . '/'),
            // idSite3 has different domain so it is coming from different website
            array('piwik.org',              $this->idSite3, $url, $referrer),
            array('piwik.org',              $this->idSite3, $url, $referrer . '/'),

            array($directEntryReferrerName, $this->idSite1, $url, $referrer . '/foo/bar/baz'),
            array($directEntryReferrerName, $this->idSite1, $url, $referrer . '/foo/bar/baz/'),
            array($directEntryReferrerName, $this->idSite1, $url, $referrer . '/foo/bar/baz?x=5'),
            array($directEntryReferrerName, $this->idSite1, $url, $referrer . '/fOo/BaR/baz?x=5'),
            // /not/xyz belongs to different website
            array('piwik.org',              $this->idSite1, $url, $referrer . '/not/xyz'),
            array($directEntryReferrerName, $this->idSite2, $url, $referrer . '/not/xyz'),

            // /foo/bar/baz belongs to different website
            array('piwik.org/foo/bar',      $this->idSite2, $url, $referrer . '/foo/bar/baz'),
            array('piwik.org/foo/bar',      $this->idSite3, $url, $referrer . '/foo/bar'),
            array('piwik.org/foo/bar',      $this->idSite3, $url, $referrer . '/fOo/BaR'),

            // should detect campaign independent of domain / path
            array('test',                   $this->idSite1, $url . '?pk_campaign=test', $referrer),
            array('testfoobar',             $this->idSite2, $url . '?pk_campaign=testfoobar', $referrer),
            array('test',                   $this->idSite3, $url . '?pk_campaign=test', $referrer),

            array('Google',                 $this->idSite3, $url, 'http://google.com/search?q=piwik'),

            // testing case for backwards compatibility where url has same domain as urlref but the domain is not known to any website
            array($directEntryReferrerName, $this->idSite3, 'http://example.com/foo', 'http://example.com/bar'),
            array($directEntryReferrerName, $this->idSite3, 'http://example.com/foo', 'http://example.com'),
            array($directEntryReferrerName, $this->idSite3, 'http://example.com',     'http://example.com/bar'),

            // testing case where domain of referrer is not known to any site but neither is the URL, url != urlref
            array('example.com',            $this->idSite3, 'http://example.org',     'http://example.com/bar'),
        );
    }

    private function getRequest($params)
    {
        return new Request($params);
    }

    private function getNewVisitor()
    {
        return new Visitor(new VisitProperties());
    }

}
