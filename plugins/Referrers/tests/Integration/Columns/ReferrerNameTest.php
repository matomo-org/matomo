<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\tests\Integration\Columns;

use Piwik\Common;
use Piwik\Plugins\Referrers\Columns\ReferrerName;
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
     * @var ReferrerName
     */
    private $referrerName;
    private $idSite1 = 1;
    private $idSite2 = 2;
    private $idSite3 = 3;
    private $idSite4 = 4;

    public function setUp(): void
    {
        parent::setUp();

        Cache::clearCacheGeneral();

        $date = '2012-01-01 00:00:00';
        $ecommerce = false;

        Fixture::createWebsite($date, $ecommerce, $name = 'test1', $url = 'http://piwik.org/foo/bar');
        Fixture::createWebsite($date, $ecommerce, $name = 'test2', $url = 'http://piwik.org/');
        Fixture::createWebsite($date, $ecommerce, $name = 'test3', $url = 'http://piwik.xyz/');
        Fixture::createWebsite($date, $ecommerce, $name = 'test4', $url = null);

        $this->referrerName = new ReferrerName();
    }

    public function tearDown(): void
    {
        // clean up your test here if needed
        Cache::clearCacheGeneral();

        parent::tearDown();
    }

    /**
     * @dataProvider getReferrerUrls
     */
    public function testOnNewVisitShouldDetectCorrectReferrerType($expectedType, $idSite, $url, $referrerUrl)
    {
        $request = $this->getRequest(['idsite' => $idSite, 'url' => $url, 'urlref' => $referrerUrl]);
        $type = $this->referrerName->onNewVisit($request, $this->getNewVisitor(), $action = null);

        $this->assertSame($expectedType, $type);
    }

    public function getReferrerUrls()
    {
        $url = 'http://piwik.org/foo/bar';
        $referrer = 'http://piwik.org';

        $longString = str_repeat('very_long_', 25);

        $directEntryReferrerName = null;

        return [
            // domain matches but path does not match for idsite1
            [null,              $this->idSite1, $url, $referrer],
            [null,              $this->idSite1, $url, $referrer . '/'],
            // idSite2 matches any piwik.org path so this is a direct entry for it
            [$directEntryReferrerName, $this->idSite2, $url, $referrer],
            [$directEntryReferrerName, $this->idSite2, $url, $referrer . '/'],
            // idSite3 has different domain so it is coming from different website
            [null,              $this->idSite3, $url, $referrer],
            [null,              $this->idSite3, $url, $referrer . '/'],

            [$directEntryReferrerName, $this->idSite1, $url, $referrer . '/foo/bar/baz'],
            [$directEntryReferrerName, $this->idSite1, $url, $referrer . '/foo/bar/baz/'],
            [$directEntryReferrerName, $this->idSite1, $url, $referrer . '/foo/bar/baz?x=5'],
            [$directEntryReferrerName, $this->idSite1, $url, $referrer . '/fOo/BaR/baz?x=5'],
            // /not/xyz belongs to different website
            [null,              $this->idSite1, $url, $referrer . '/not/xyz'],
            [$directEntryReferrerName, $this->idSite2, $url, $referrer . '/not/xyz'],

            // /foo/bar/baz belongs to different website
            ['piwik.org/foo/bar',      $this->idSite2, $url, $referrer . '/foo/bar/baz'],
            ['piwik.org/foo/bar',      $this->idSite3, $url, $referrer . '/foo/bar'],
            ['piwik.org/foo/bar',      $this->idSite3, $url, $referrer . '/fOo/BaR'],

            // should detect campaign independent of domain / path
            ['test',                   $this->idSite1, $url . '?pk_campaign=test', $referrer],
            ['testfoobar',             $this->idSite2, $url . '?pk_campaign=testfoobar', $referrer],
            ['test',                   $this->idSite3, $url . '?pk_campaign=test', $referrer],
            [$longString,              $this->idSite3, $url . '?pk_campaign=' . $longString, $referrer],

            ['Google',                 $this->idSite3, $url, 'http://google.com/search?q=piwik'],

            // testing case for backwards compatibility where url has same domain as urlref but the domain is not known to any website
            [$directEntryReferrerName, $this->idSite3, 'http://example.com/foo', 'http://example.com/bar'],
            [$directEntryReferrerName, $this->idSite3, 'http://example.com/foo', 'http://example.com'],
            [$directEntryReferrerName, $this->idSite3, 'http://example.com',     'http://example.com/bar'],

            // testing case where domain of referrer is not known to any site but neither is the URL, url != urlref
            ['example.com',            $this->idSite3, 'http://example.org',     'http://example.com/bar'],

            // site w/o url
            [$directEntryReferrerName, $this->idSite4, $url, $referrer . '/'],
        ];
    }

    /**
     * @dataProvider getTestDataForOnExistingVisit
     */
    public function testOnExistingVisitShouldSometimesOverwriteReferrerInfo($expectedName, $idSite, $url, $referrerUrl, $existingType)
    {
        $request = $this->getRequest(['idsite' => $idSite, 'url' => $url, 'urlref' => $referrerUrl]);
        $visitor = $this->getNewVisitor();
        $visitor->initializeVisitorProperty('referer_type', $existingType);
        $name = $this->referrerName->onExistingVisit($request, $visitor, $action = null);

        $this->assertSame($expectedName, $name);
    }

    public function getTestDataForOnExistingVisit()
    {
        return [
            // direct entry => campaign
            ['testfoobar', $this->idSite3, 'http://piwik.xyz/abc?pk_campaign=testfoobar', 'http://piwik.org', Common::REFERRER_TYPE_DIRECT_ENTRY],

            // direct entry => website
            ['piwik.org', $this->idSite3, 'http://piwik.xyz/abc', 'http://piwik.org', Common::REFERRER_TYPE_DIRECT_ENTRY],

            // direct entry => direct entry
            [false, $this->idSite3, 'http://piwik.xyz/abc', 'http://piwik.xyz/def', Common::REFERRER_TYPE_DIRECT_ENTRY],

            // website => direct entry
            [false, $this->idSite3, 'http://piwik.xyz/abc', 'http://piwik.xyz/def', Common::REFERRER_TYPE_WEBSITE],

            // campaign => direct entry
            [false, $this->idSite3, 'http://piwik.xyz/abc', 'http://piwik.xyz/def', Common::REFERRER_TYPE_CAMPAIGN],

            // direct entry => website (site w/o url)
            ['piwik.org', $this->idSite4, 'http://piwik.xyz/abc', 'http://piwik.org/', Common::REFERRER_TYPE_DIRECT_ENTRY],

            // direct entry => direct entry (site w/o url)
            [false, $this->idSite4, 'http://piwik.xyz/abc', 'http://piwik.xyz/def', Common::REFERRER_TYPE_DIRECT_ENTRY],

            // website => direct entry (site w/o url)
            [false, $this->idSite4, 'http://piwik.xyz/abc', 'http://piwik.xyz/def', Common::REFERRER_TYPE_WEBSITE],
        ];
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
