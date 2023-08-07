<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\tests\Integration\Columns;

use Piwik\Common;
use Piwik\Plugins\Referrers\Columns\ReferrerType;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Tracker\Visitor;

/**
 * @group Referrers
 * @group ReferrerTypeTest
 * @group ReferrerType
 * @group Plugins
 */
class ReferrerTypeTest extends IntegrationTestCase
{
    /**
     * @var ReferrerType
     */
    private $referrerType;
    private $idSite1 = 1;
    private $idSite2 = 2;
    private $idSite3 = 3;
    private $idSite4 = 4;
    private $idSite5 = 5;
    private $idSite6 = 6;

    public function setUp(): void
    {
        parent::setUp();

        Cache::clearCacheGeneral();

        $this->referrerType = new ReferrerType();
    }

    protected static function beforeTableDataCached()
    {
        $date = '2012-01-01 00:00:00';
        $ecommerce = false;

        Fixture::createWebsite($date, $ecommerce, 'test1', 'http://piwik.org/foo/bar');
        Fixture::createWebsite($date, $ecommerce, 'test2', 'http://piwik.org/');
        Fixture::createWebsite($date, $ecommerce, 'test3', 'http://piwik.xyz/');
        Fixture::createWebsite($date, $ecommerce, 'test4', 'http://google.com/subdir/', 1, null, null, null, null, $excludeUnknownUrls = 1);
        Fixture::createWebsite($date, $ecommerce, 'test5', null);
        Fixture::createWebsite($date, $ecommerce, 'test6', 'http://matomo.org/', 1, null, null, null, null, null, null, 'http://paypal.com,http://payments.amazon.com/proceed/,.payment.provider');
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
    public function testOnNewVisitShouldDetectCorrectReferrerType($expectedType, $idSite, $url, $referrerUrl, $additionalTrackingParams = [])
    {
        $request = $this->getRequest(['idsite' => $idSite, 'url' => $url, 'urlref' => $referrerUrl] + $additionalTrackingParams);
        $type = $this->referrerType->onNewVisit($request, $this->getNewVisitor(), $action = null);

        $this->assertSame($expectedType, $type);
    }

    public function getReferrerUrls()
    {
        $url = 'http://piwik.org/foo/bar';
        $referrer = 'http://piwik.org';

        // $expectedType,                             $idSite,        $url, $referrerUrl
        return [
            // domain matches but path does not match for idsite1
            [Common::REFERRER_TYPE_DIRECT_ENTRY,      $this->idSite1, $url, $referrer],
            [Common::REFERRER_TYPE_DIRECT_ENTRY,      $this->idSite1, $url, $referrer . '/'],
            // idSite2 matches any piwik.org path, so this is a direct entry for it
            [Common::REFERRER_TYPE_DIRECT_ENTRY, $this->idSite2, $url, $referrer],
            [Common::REFERRER_TYPE_DIRECT_ENTRY, $this->idSite2, $url, $referrer . '/'],
            // idSite3 has different domain, so it is coming from different website
            [Common::REFERRER_TYPE_DIRECT_ENTRY,      $this->idSite3, $url, $referrer],
            [Common::REFERRER_TYPE_DIRECT_ENTRY,      $this->idSite3, $url, $referrer . '/'],

            [Common::REFERRER_TYPE_DIRECT_ENTRY, $this->idSite1, $url, $referrer . '/foo/bar/baz'],
            [Common::REFERRER_TYPE_DIRECT_ENTRY, $this->idSite1, $url, $referrer . '/foo/bar/baz/'],
            [Common::REFERRER_TYPE_DIRECT_ENTRY, $this->idSite1, $url, $referrer . '/foo/bar/baz?x=5'],
            // /not/xyz belongs to different website
            [Common::REFERRER_TYPE_DIRECT_ENTRY,      $this->idSite1, $url, $referrer . '/not/xyz'],
            [Common::REFERRER_TYPE_DIRECT_ENTRY, $this->idSite2, $url, $referrer . '/not/xyz'],

            // /foo/bar/baz belongs to different website
            [Common::REFERRER_TYPE_WEBSITE,      $this->idSite2, $url, $referrer . '/foo/bar/baz'],

            // website as it is from different domain anyway
            [Common::REFERRER_TYPE_WEBSITE,      $this->idSite3, $url, $referrer . '/foo/bar/baz'],

            // should detect campaign independent of domain / path
            [Common::REFERRER_TYPE_CAMPAIGN,     $this->idSite1, $url . '?pk_campaign=test', $referrer],
            [Common::REFERRER_TYPE_CAMPAIGN,     $this->idSite2, $url . '?pk_campaign=test', $referrer],
            [Common::REFERRER_TYPE_CAMPAIGN,     $this->idSite3, $url . '?pk_campaign=test', $referrer],

            // should detect campaign, when campaign parameter directly provided as tracking parameter
            [Common::REFERRER_TYPE_CAMPAIGN,     $this->idSite1, $url, $referrer, ['mtm_campaign' => 'test']],

            // campaign parameters provided as array should simply be ignored (and not produce an error)
            [Common::REFERRER_TYPE_DIRECT_ENTRY,     $this->idSite1, $url . '?pk_campaign[]=test', $referrer],

            [Common::REFERRER_TYPE_SEARCH_ENGINE, $this->idSite3, $url, 'http://google.com/search?q=piwik'],

            [Common::REFERRER_TYPE_SOCIAL_NETWORK, $this->idSite3, $url, 'https://twitter.com/matomo_org'],

            // testing case for backwards compatibility where url has same domain as urlref but the domain is not known to any website
            [Common::REFERRER_TYPE_DIRECT_ENTRY, $this->idSite3, 'http://example.com/foo', 'http://example.com/bar'],
            [Common::REFERRER_TYPE_DIRECT_ENTRY, $this->idSite3, 'http://example.com/foo', 'http://example.com'],
            [Common::REFERRER_TYPE_DIRECT_ENTRY, $this->idSite3, 'http://example.com', 'http://example.com/bar'],

            // testing case where domain of referrer is not known to any site but neither is the URL, url != urlref
            [Common::REFERRER_TYPE_WEBSITE,      $this->idSite3, 'http://example.org', 'http://example.com/bar'],

            ####### testing specific case:
            ## - ignore unknown urls is activated for idSite4

            // referrer comes from another subdir, but same host   => direct entry
            [Common::REFERRER_TYPE_DIRECT_ENTRY,      $this->idSite4, 'http://google.com/subdir/site', 'http://google.com/base'],
            // referrer comes from same subdir and host   => direct entry
            [Common::REFERRER_TYPE_DIRECT_ENTRY, $this->idSite4, 'http://google.com/subdir/page', 'http://google.com/subdir/x'],
            [Common::REFERRER_TYPE_DIRECT_ENTRY, $this->idSite4, 'http://google.com/subdir/', 'http://google.com/subdir/?q=test'],
            // referrer comes from another subdir, but same host, query matches search engine definition  => search engine
            [Common::REFERRER_TYPE_DIRECT_ENTRY, $this->idSite4, 'http://google.com/subdir/index.html', 'http://google.com/search?q=test'],
            // referrer comes from search engine not matching site
            [Common::REFERRER_TYPE_SEARCH_ENGINE, $this->idSite4, 'http://google.com/subdir/index.html', 'http://google.fr/search?q=test'],

            // site w/o url
            [Common::REFERRER_TYPE_DIRECT_ENTRY, $this->idSite5, $url, $referrer . '/'],

            ##### testing referrer exclusion
            [Common::REFERRER_TYPE_DIRECT_ENTRY, $this->idSite6, 'https://matomo.org/faq', 'http://www.paypal.com/subdir/site'],
            [Common::REFERRER_TYPE_DIRECT_ENTRY, $this->idSite6, 'https://matomo.org/faq', 'https://paypal.com/subdir/site'],
            [Common::REFERRER_TYPE_DIRECT_ENTRY, $this->idSite6, 'https://matomo.org/faq', 'https://payment.provider/subdir/site'],
            [Common::REFERRER_TYPE_DIRECT_ENTRY, $this->idSite6, 'https://matomo.org/faq', 'https://custom.payment.provider/'],
            [Common::REFERRER_TYPE_WEBSITE, $this->idSite6, 'https://matomo.org/faq', 'http://shop.paypal.com/subdir/site'],
            [Common::REFERRER_TYPE_WEBSITE, $this->idSite6, 'https://matomo.org/faq', 'http://payments.amazon.com/'],
            [Common::REFERRER_TYPE_DIRECT_ENTRY, $this->idSite6, 'https://matomo.org/faq', 'https://payments.amazon.com/proceed/with/payment'],
        ];
    }

    /**
     * @dataProvider getTestDataForOnExistingVisit
     */
    public function testOnExistingVisitShouldSometimesOverwriteReferrerInfo($expectedType, $idSite, $url, $referrerUrl, $existingType)
    {
        $request = $this->getRequest(['idsite' => $idSite, 'url' => $url, 'urlref' => $referrerUrl]);
        $visitor = $this->getNewVisitor();
        $visitor->initializeVisitorProperty('referer_type', $existingType);
        $type = $this->referrerType->onExistingVisit($request, $visitor, $action = null);

        $this->assertSame($expectedType, $type);
    }

    public function getTestDataForOnExistingVisit()
    {
        return [
            // direct entry => campaign
            [Common::REFERRER_TYPE_CAMPAIGN, $this->idSite3, 'http://piwik.xyz/abc?pk_campaign=testfoobar', 'http://piwik.org', Common::REFERRER_TYPE_DIRECT_ENTRY],

            // direct entry => website
            [Common::REFERRER_TYPE_WEBSITE, $this->idSite3, 'http://piwik.xyz/abc', 'http://piwik.org', Common::REFERRER_TYPE_DIRECT_ENTRY],

            // direct entry => direct entry
            [false, $this->idSite3, 'http://piwik.xyz/abc', 'http://piwik.xyz/def', Common::REFERRER_TYPE_DIRECT_ENTRY],

            // website => direct entry
            [false, $this->idSite3, 'http://piwik.xyz/abc', 'http://piwik.xyz/def', Common::REFERRER_TYPE_WEBSITE],

            // campaign => direct entry
            [false, $this->idSite3, 'http://piwik.xyz/abc', 'http://piwik.xyz/def', Common::REFERRER_TYPE_CAMPAIGN],

            // direct entry => website (site w/o url)
            [Common::REFERRER_TYPE_WEBSITE, $this->idSite5, 'http://piwik.xyz/abc', 'http://piwik.org/', Common::REFERRER_TYPE_DIRECT_ENTRY],

            // direct entry => direct entry (site w/o url)
            [false, $this->idSite5, 'http://piwik.xyz/abc', 'http://piwik.xyz/def', Common::REFERRER_TYPE_DIRECT_ENTRY],

            // website => direct entry (site w/o url)
            [false, $this->idSite5, 'http://piwik.xyz/abc', 'http://piwik.xyz/def', Common::REFERRER_TYPE_WEBSITE],
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
