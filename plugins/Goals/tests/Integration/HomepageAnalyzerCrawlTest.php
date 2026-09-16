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
class HomepageAnalyzerCrawlTest extends IntegrationTestCase
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

    public function testCrawlKeepsTrailingSlashForFetchingButNotForPatterns()
    {
        $analyzer = $this->makeAnalyzer([
            'http://example.com/' => [
                'status' => 200,
                'headers' => [],
                'data' => '<html><body><a href="/lavazza/">Lavazza</a><a href="/lavazza">Lavazza</a></body></html>',
                'effectiveUrl' => 'http://example.com/',
            ],
            // Shopware style servers answer 404 without the slash, so the slashed form must be requested
            'http://example.com/lavazza/' => [
                'status' => 200,
                'headers' => [],
                'data' => '<html><body><a href="/checkout/cart/">Warenkorb</a></body></html>',
                'effectiveUrl' => 'http://example.com/lavazza/',
            ],
        ]);

        $analysis = $analyzer->analyze($this->idSite);

        $this->assertNotNull($analysis);
        $this->assertSame(2, $analysis['pagesCrawled']);
        $targets = array_column($analysis['links'], 'linkTarget');
        sort($targets);
        $this->assertSame(['/checkout/cart', '/lavazza'], $targets);
        $this->assertSame(['/', '/lavazza'], array_column($analysis['pages'], 'path'));
    }

    public function testCrawlExtractsContentLinksBehindLargeMenusAndDetectsThePlatform()
    {
        $menu = '';
        for ($i = 1; $i <= 60; $i++) {
            $menu .= '<a href="/menu-' . $i . '">Menu ' . $i . '</a>';
        }
        $analyzer = $this->makeAnalyzer([
            'http://example.com/' => [
                'status' => 200,
                'headers' => [],
                'data' => '<html><head><title>Shop</title>'
                    . '<script src="https://cdn.shopify.com/s/files/theme.js"></script></head>'
                    . '<script type="application/ld+json">{"@type":"Product","name":"Shoe"}</script></head>'
                    . '<body><nav>' . $menu . '</nav><main><h1>Welcome</h1>'
                    . '<a class="btn" href="/pages/contact">Contact</a>'
                    . '<form action="/cart/add"><input name="id" type="hidden"><button>Add</button></form>'
                    . '</main></body></html>',
                'effectiveUrl' => 'http://example.com/',
            ],
        ]);

        $analysis = $analyzer->analyze($this->idSite);

        $this->assertNotNull($analysis);
        // the 61st anchor in document order is still extracted
        $this->assertContains('/pages/contact', array_column($analysis['links'], 'linkTarget'));
        $this->assertSame('shopify', $analysis['platform']);
        $this->assertSame(
            [['path' => '/', 'title' => 'Shop', 'heading' => 'Welcome', 'types' => ['Product'], 'hasAddToCart' => true]],
            $analysis['pages']
        );
        // the contact link is a hero call to action, the menu links are not
        $contact = array_values(array_filter($analysis['links'], function (array $link): bool {
            return $link['linkTarget'] === '/pages/contact';
        }))[0];
        $this->assertSame(1, $contact['heroCount']);
        $this->assertSame(1, $contact['buttonLikeCount']);
    }

    public function testCrawlQueuesNewLinksPerPageInsteadOfRepeatedNavigation()
    {
        $nav = '';
        for ($i = 1; $i <= 45; $i++) {
            $nav .= '<a href="/nav-' . $i . '">Nav ' . $i . '</a>';
        }
        $responses = [
            'http://example.com/' => [
                'status' => 200,
                'headers' => [],
                'data' => '<html><body><nav>' . $nav . '</nav></body></html>',
                'effectiveUrl' => 'http://example.com/',
            ],
        ];
        for ($i = 1; $i <= 45; $i++) {
            // every nav page repeats the navigation and links one deeper page
            $responses['http://example.com/nav-' . $i] = [
                'status' => 200,
                'headers' => [],
                'data' => '<html><body><nav>' . $nav . '</nav><main><a href="/deep-' . $i . '">Deep</a></main></body></html>',
                'effectiveUrl' => 'http://example.com/nav-' . $i,
            ];
            $responses['http://example.com/deep-' . $i] = [
                'status' => 200,
                'headers' => [],
                'data' => '<html><body><form action="/deep-' . $i . '"><input type="email"><textarea></textarea></form></body></html>',
                'effectiveUrl' => 'http://example.com/deep-' . $i,
            ];
        }
        $analysis = $this->makeAnalyzer($responses)->analyze($this->idSite);

        $this->assertNotNull($analysis);
        // 50 pages: the homepage, the nav pages and deep pages, not 41 nav pages and nothing else
        $this->assertSame(50, $analysis['pagesCrawled']);
        $this->assertContains('/deep-1', array_column($analysis['pages'], 'path'));
        $this->assertSame(['email', 'textarea'], $analysis['forms'][0]['fieldTypes']);
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
