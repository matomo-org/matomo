<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\Integration;

use Piwik\Plugins\Goals\Recommendations\HomepageAnalyzer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Goals
 * @group Plugins
 * @group GoalRecommendationsTest
 */
class HomepageAnalyzerRedirectTest extends IntegrationTestCase
{
    /**
     * @var int
     */
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();
        $this->idSite = Fixture::createWebsite('2024-01-01 00:00:00', 0, false, 'http://example.com');
    }

    public function testCrawlFollowsCanonicalRedirectAndKeepsAbsoluteSameOriginLinks()
    {
        $analyzer = $this->makeAnalyzer([
            // configured apex URL redirects to www; links on the page are absolute www URLs
            'http://example.com/' => [
                'status' => 200,
                'headers' => [],
                'data' => '<html><body>'
                    . '<a href="https://www.example.com/pricing">Pricing</a>'
                    . '<a href="https://www.example.com/contact">Contact us</a>'
                    . '</body></html>',
                'effectiveUrl' => 'https://www.example.com/',
            ],
            'https://www.example.com/pricing' => [
                'status' => 200,
                'headers' => [],
                'data' => '<html><body><a href="/contact">Contact</a></body></html>',
                'effectiveUrl' => 'https://www.example.com/pricing',
            ],
            'https://www.example.com/contact' => [
                'status' => 200,
                'headers' => [],
                'data' => '<html><body><form action="/contact"><input type="email"></form></body></html>',
                'effectiveUrl' => 'https://www.example.com/contact',
            ],
        ]);

        $analysis = $analyzer->analyze($this->idSite);

        $this->assertNotNull($analysis);
        // the crawl re-anchored on the effective origin
        $this->assertSame('https://www.example.com/', $analysis['url']);
        // absolute www links were treated as same-origin and crawled
        $this->assertSame(3, $analysis['pagesCrawled']);
        $targets = array_column($analysis['links'], 'linkTarget');
        $this->assertContains('/pricing', $targets);
        $this->assertContains('/contact', $targets);
    }

    public function testCrawlSkipsPagesThatRedirectOffOrigin()
    {
        $analyzer = $this->makeAnalyzer([
            'http://example.com/' => [
                'status' => 200,
                'headers' => [],
                'data' => '<html><body>'
                    . '<a href="/services">Services</a>'
                    . '</body></html>',
                'effectiveUrl' => 'http://example.com/',
            ],
            // /services silently redirects to a different site
            'http://example.com/services' => [
                'status' => 200,
                'headers' => [],
                'data' => '<html><body><a href="/partner-offer">Offer</a></body></html>',
                'effectiveUrl' => 'https://business.partner.example/services',
            ],
        ]);

        $analysis = $analyzer->analyze($this->idSite);

        $this->assertNotNull($analysis);
        // only the homepage was ingested; the off-origin page was dropped
        $this->assertSame(1, $analysis['pagesCrawled']);
        $this->assertNotContains('/partner-offer', array_column($analysis['links'], 'linkTarget'));
    }

    /**
     * @param array<string, array<string, mixed>> $responsesByUrl
     */
    private function makeAnalyzer(array $responsesByUrl): HomepageAnalyzer
    {
        return new class ($responsesByUrl) extends HomepageAnalyzer {
            /** @var array<string, array<string, mixed>> */
            private $responsesByUrl;

            public function __construct(array $responsesByUrl)
            {
                parent::__construct();
                $this->responsesByUrl = $responsesByUrl;
            }

            protected function fetchHomepage(string $url, int $timeout): ?array
            {
                return $this->responsesByUrl[$url] ?? null;
            }
        };
    }
}
