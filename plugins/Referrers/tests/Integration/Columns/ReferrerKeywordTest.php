<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\tests\Integration\Columns;

use Piwik\Common;
use Piwik\Plugins\Referrers\Columns\Keyword;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Tracker\Visitor;

/**
 * @group Referrers
 * @group ReferrerKeywordTest
 * @group ReferrerKeyword
 * @group Plugins
 */
class ReferrerKeywordTest extends IntegrationTestCase
{
    /**
     * @var Keyword
     */
    private $keyword;
    private $idSite1 = 1;
    private $idSite2 = 2;
    private $idSite3 = 3;

    public function setUp(): void
    {
        parent::setUp();

        $date = '2012-01-01 00:00:00';
        $ecommerce = false;

        Fixture::createWebsite($date, $ecommerce, $name = 'test1', $url = 'http://piwik.org/');
        Fixture::createWebsite($date, $ecommerce, $name = 'test2', $url = 'http://piwik.xyz/');
        Fixture::createWebsite($date, $ecommerce, $name = 'test3', $url = null);

        $this->keyword = new Keyword();
    }

    /**
     * @dataProvider getReferrerUrls
     */
    public function test_onNewVisit_shouldDetectCorrectReferrerType($expectedType, $idSite, $url, $referrerUrl)
    {
        $request = $this->getRequest(['idsite' => $idSite, 'url' => $url, 'urlref' => $referrerUrl]);
        $type = $this->keyword->onNewVisit($request, $this->getNewVisitor(), $action = null);

        $this->assertSame($expectedType, $type);
    }

    public function getReferrerUrls()
    {
        $url = 'http://piwik.org/foo/bar';
        $noReferrer = '';
        $directReferrer = 'http://piwik.org';
        $externalReferrer = 'http://example.org';

        $noReferrerKeyword = null;
        $emptyReferrerKeyword = '';

        return [
            // website referrer types usually do not have a keyword
            [$noReferrerKeyword, $this->idSite1, $url, $externalReferrer],
            // direct entries do usually not have a referrer keyowrd
            [$noReferrerKeyword, $this->idSite1, $url, $directReferrer],

            // it is a campaign but there is no referrer url and no keyword set specifically, we cannot detect a keyword
            // it does not return null as it is converted to strlower(null)
            [$emptyReferrerKeyword, $this->idSite1, $url . '?pk_campaign=test', $noReferrer],

            // campaigns, coming from same domain should have a keyword
            ['piwik.org',     $this->idSite1, $url . '?pk_campaign=test', $directReferrer],
            // campaigns, coming from different domain should have a keyword
            ['example.org',     $this->idSite2, $url . '?pk_campaign=test', $externalReferrer],
            // campaign keyword is specifically set
            ['campaignkey1',     $this->idSite2, $url . '?pk_campaign=test&mtm_keyword=campaignkey1', $externalReferrer],
            ['campaignkey2',     $this->idSite2, $url . '?pk_campaign=test&utm_term=campaignkey2', $externalReferrer],

            // search engine should have keyword the search term
            ['piwik', $this->idSite2, $url, 'http://google.com/search?q=piwik'],

            // site w/o url
            [$noReferrerKeyword, $this->idSite3, $url, $directReferrer . '/'],
        ];
    }

    /**
     * @dataProvider getTestDataForOnExistingVisit
     */
    public function test_onExistingVisit_shouldSometimesOverwriteReferrerInfo($expectedKeyword, $idSite, $url, $referrerUrl, $existingType)
    {
        $request = $this->getRequest(['idsite' => $idSite, 'url' => $url, 'urlref' => $referrerUrl]);
        $visitor = $this->getNewVisitor();
        $visitor->initializeVisitorProperty('referer_type', $existingType);
        $keyword = $this->keyword->onExistingVisit($request, $visitor, $action = null);

        $this->assertSame($expectedKeyword, $keyword);
    }

    public function getTestDataForOnExistingVisit()
    {
        return [
            // direct entry => campaign
            ['campaignkey1', $this->idSite2, 'http://piwik.xyz/abc?pk_campaign=testfoobar&mtm_keyword=campaignkey1', 'http://piwik.org', Common::REFERRER_TYPE_DIRECT_ENTRY],

            // direct entry => website
            ['piwik2', $this->idSite2, 'http://piwik.xyz/abc', 'http://google.com/search?q=piwik2', Common::REFERRER_TYPE_DIRECT_ENTRY],

            // direct entry => direct entry
            [false, $this->idSite2, 'http://piwik.xyz/abc', 'http://piwik.xyz/def', Common::REFERRER_TYPE_DIRECT_ENTRY],

            // website => direct entry
            [false, $this->idSite2, 'http://piwik.xyz/abc', 'http://piwik.xyz/def', Common::REFERRER_TYPE_WEBSITE],

            // campaign => direct entry
            [false, $this->idSite2, 'http://piwik.xyz/abc', 'http://piwik.xyz/def', Common::REFERRER_TYPE_CAMPAIGN],

            // direct entry => website (site w/o url)
            ['piwik3', $this->idSite3, 'http://piwik.xyz/abc', 'http://google.com/search?q=piwik3', Common::REFERRER_TYPE_DIRECT_ENTRY],

            // direct entry => direct entry (site w/o url)
            [false, $this->idSite3, 'http://piwik.xyz/abc', 'http://piwik.xyz/def', Common::REFERRER_TYPE_DIRECT_ENTRY],

            // website => direct entry (site w/o url)
            [false, $this->idSite3, 'http://piwik.xyz/abc', 'http://piwik.xyz/def', Common::REFERRER_TYPE_WEBSITE],
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
