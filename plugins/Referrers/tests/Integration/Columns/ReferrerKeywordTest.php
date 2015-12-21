<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\tests\Integration\Columns;

use Piwik\Plugins\Referrers\Columns\Keyword;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Tracker\Visitor;

/**
 * @group Referrers
 * @group ReferrerTypeTest
 * @group ReferrerType
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

    public function setUp()
    {
        parent::setUp();

        $date = '2012-01-01 00:00:00';
        $ecommerce = false;

        Fixture::createWebsite($date, $ecommerce, $name = 'test1', $url = 'http://piwik.org/');
        Fixture::createWebsite($date, $ecommerce, $name = 'test3', $url = 'http://piwik.pro/');

        $this->keyword = new Keyword();
    }

    /**
     * @dataProvider getReferrerUrls
     */
    public function test_onNewVisit_shouldDetectCorrectReferrerType($expectedType, $idSite, $url, $referrerUrl)
    {
        $request = $this->getRequest(array('idsite' => $idSite, 'url' => $url, 'urlref' => $referrerUrl));
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

        return array(
            // website referrer types usually do not have a keyword
            array($noReferrerKeyword, $this->idSite1, $url, $externalReferrer),
            // direct entries do usually not have a referrer keyowrd
            array($noReferrerKeyword, $this->idSite1, $url, $directReferrer),

            // it is a campaign but there is no referrer url and no keyword set specifically, we cannot detect a keyword
            // it does not return null as it is converted to strlower(null)
            array($emptyReferrerKeyword, $this->idSite1, $url . '?pk_campaign=test', $noReferrer),

            // campaigns, coming from same domain should have a keyword
            array('piwik.org',     $this->idSite1, $url . '?pk_campaign=test', $directReferrer),
            // campaigns, coming from different domain should have a keyword
            array('example.org',     $this->idSite2, $url . '?pk_campaign=test', $externalReferrer),
            // campaign keyword is specifically set
            array('campaignkey1',     $this->idSite2, $url . '?pk_campaign=test&pk_keyword=campaignkey1', $externalReferrer),
            array('campaignkey2',     $this->idSite2, $url . '?pk_campaign=test&utm_term=campaignkey2', $externalReferrer),

            // search engine should have keyword the search term
            array('piwik', $this->idSite2, $url, 'http://google.com/search?q=piwik'),
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
