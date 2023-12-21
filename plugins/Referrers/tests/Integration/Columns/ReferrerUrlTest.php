<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\tests\Integration\Columns;

use Piwik\Config;
use Piwik\Plugins\Referrers\Columns\ReferrerUrl;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Tracker\Visitor;

/**
 * @group Referrers
 * @group ReferrerUrlTest
 * @group ReferrerUrl
 * @group Plugins
 */
class ReferrerUrlTest extends IntegrationTestCase
{
    /**
     * @var ReferrerUrl
     */
    private $referrerUrl;
    private $idSite1 = 1;

    public function setUp(): void
    {
        parent::setUp();

        Cache::clearCacheGeneral();

        $date = '2012-01-01 00:00:00';
        $ecommerce = false;

        Fixture::createWebsite($date, $ecommerce, $name = 'test1', $url = 'http://piwik.org/foo/bar');

        $this->referrerUrl = new ReferrerUrl();
    }

    public function tearDown(): void
    {
        // clean up your test here if needed
        Cache::clearCacheGeneral();

        parent::tearDown();
    }

    public function test_onNewVisit_shouldTrimReferUrl()
    {
        Config::getInstance()->Tracker['page_maximum_length'] = ReferrerUrl::MAX_LEN + 100;
        $refUrl = 'http://example.org/foo/bar' . str_pad('r', ReferrerUrl::MAX_LEN, 'r');
        $this->assertGreaterThan(ReferrerUrl::MAX_LEN, strlen($refUrl));
        $request = $this->getRequest(['idsite' => $this->idSite1, 'url' => 'http://piwik.org/foo/bar', 'urlref' => $refUrl]);
        $detectedUrl = $this->referrerUrl->onNewVisit($request, $this->getNewVisitor(), $action = null);

        $this->assertSame(ReferrerUrl::MAX_LEN, strlen($detectedUrl));
        $this->assertStringStartsWith('http://example.org/foo/barrrr', $detectedUrl);
    }

    /**
     * @dataProvider getReferrerUrls
     */
    public function test_onNewVisit_shouldDetectCorrectReferrerUrl($referrerUrl, $expectedUrl)
    {
        $request = $this->getRequest(['idsite' => $this->idSite1, 'url' => 'http://piwik.org/foo/bar', 'urlref' => $referrerUrl]);
        $detectedUrl = $this->referrerUrl->onNewVisit($request, $this->getNewVisitor(), $action = null);

        $this->assertSame($expectedUrl, $detectedUrl);
    }

    public function getReferrerUrls()
    {
        // $referrerUrl, $expectedUrl
        return [
            // instagram referrer urls
            ['https://l.instagram.com/?u=https%3A%2F%2Fexample.com%2Fexample.com&amp;e=BTPcuqWixl6Mf5hgYPp6wXIlstuaEdJssdYEvT9s8-6yme_lb275lY2Bwc-YvE-fZNtSKux4QB-v8xNk&amp;s=1',
                  'https://l.instagram.com/?u=https%3A%2F%2Fexample.com%2Fexample.com'],
            ['https://m.instagram.com/?u=https%3A%2F%2Fexample.com%2Fexample.com&e=BTPcuqWixl6Mf5hgYPp6wXIlstuaEdJssdYEvT9s8-6yme_lb275lY2Bwc-YvE-fZNtSKux4QB-v8xNk',
                  'https://m.instagram.com/?u=https%3A%2F%2Fexample.com%2Fexample.com'],

            // facebook referrer urls
            ['http://l.facebook.com/l.php?u=http://www.example.com.com/&h=BL0RXrrUUyk_ZbqijDe_mVGBi3ZsyVxJEvOfIhjlUEiRy4zkKwYMDUWbuoICNzhC6pKm6zbGCPAJQP4s8e2psymaokRV3dhp7FPx4Zk6B4x0fBbYTi54xynmBsoBRFB7f5t',
                'http://l.facebook.com/l.php?u=http://www.example.com.com/'],
            ['http://lm.facebook.com/l.php?u=http://example.com/foobar&h=BT2Dh3r3VDLoabL3Rb1lpmN-_s0lFtReSGzBED3kfUGnaO5fPF-x8LspJAfJN9kkee5ptpybYgyIx68yzgo9kPAN6snSZL_eNcmgu5xhuUcLXJukNKvi0XMOY78Ca9NKexnpJKxKUDeVApPcfB',
                'http://lm.facebook.com/l.php?u=http://example.com/foobar'],

            // google referrer urls
            ['https://www.google.com/url?q=https://example.com/foo&sa=D&ust=1689581471834000&usg=BCQjCNFw5f1S7rLgPNephpTW_4-i2KnAGA',
                'https://www.google.com/url?q=https://example.com/foo'],

            // bing referrer urls
            ['https://www.bing.com/search?q=foo+bar&form=EDGTCK&qs=AB&cvid=ff8399e313a74fb592b0ca1d91c42224&refig=4540178a841b46ce8de1664920449112&cc=BE&setlang=4k-NL&elv=AXXfrEiqqD9r3GuelwApuloWthKnH5oOVtTkjmeLPBeagbGxe4rwyaaV!5HJFcbCTxaO4q5w7QqvI8XbCTXyJKn1N4PzqCvVFSdBSr*sdwlB&plvar=0',
                'https://www.bing.com/search?q=foo+bar'],

            // ensure custom url still keep those parameters
            ['http://www.example.com/index.php?s=test&e=val&h=param&cvid=custom',
                'http://www.example.com/index.php?s=test&e=val&h=param&cvid=custom']
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
