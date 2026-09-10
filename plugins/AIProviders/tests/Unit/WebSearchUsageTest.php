<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\AIProviders\WebSearchUsage;

/**
 * Verifies the provider-agnostic normalisation of web search results: which
 * citation URLs are accepted, how domains are derived, and when a search counts
 * as having run.
 *
 * @group AIProviders
 * @group Plugins
 */
class WebSearchUsageTest extends TestCase
{
    public function testNoneReportsUnusedWithEmptyData(): void
    {
        $usage = WebSearchUsage::none();

        $this->assertFalse($usage->wasUsed());
        $this->assertSame([], $usage->getCitations());
        $this->assertSame([], $usage->getQueries());
        $this->assertNull($usage->getRequestCount());
    }

    /**
     * Citation URLs are model-controlled and callers render them as links, so
     * anything that is not http(s) must be dropped rather than passed on.
     */
    public function testDropsCitationsThatAreNotHttpUrls(): void
    {
        $usage = WebSearchUsage::fromProviderData([
            ['url' => 'javascript:alert(1)', 'title' => 'xss'],
            ['url' => 'data:text/html;base64,PHNjcmlwdD4=', 'title' => 'data uri'],
            ['url' => '/relative/path', 'title' => 'relative'],
            ['url' => '', 'title' => 'empty'],
            ['url' => 'https://matomo.org/blog', 'title' => 'kept'],
        ], 1, []);

        $citations = $usage->getCitations();

        $this->assertCount(1, $citations);
        $this->assertSame('https://matomo.org/blog', $citations[0]['url']);
    }

    public function testDeduplicatesCitationsByUrlKeepingTheFirst(): void
    {
        $usage = WebSearchUsage::fromProviderData([
            ['url' => 'https://matomo.org/blog', 'title' => 'cited'],
            ['url' => 'https://matomo.org/blog', 'title' => 'returned again'],
        ], 1, []);

        $citations = $usage->getCitations();

        $this->assertCount(1, $citations);
        $this->assertSame('cited', $citations[0]['title']);
    }

    public function testDerivesTheDomainFromTheUrlHostAndStripsWww(): void
    {
        $usage = WebSearchUsage::fromProviderData([
            ['url' => 'https://WWW.Example.ORG/a/b?c=d', 'title' => 'Example'],
        ], 1, []);

        $this->assertSame('example.org', $usage->getCitations()[0]['domain']);
    }

    /**
     * Google supplies the publisher domain separately because its URL is a
     * redirect, so a provider-supplied domain must win over the URL host.
     */
    public function testKeepsAProviderSuppliedDomain(): void
    {
        $usage = WebSearchUsage::fromProviderData([
            [
                'url' => 'https://vertexaisearch.cloud.google.com/grounding-api-redirect/abc',
                'title' => 'uefa.com',
                'domain' => 'UEFA.com',
            ],
        ], 1, []);

        $citation = $usage->getCitations()[0];

        $this->assertSame('uefa.com', $citation['domain']);
        $this->assertSame(
            'https://vertexaisearch.cloud.google.com/grounding-api-redirect/abc',
            $citation['url'],
            'the redirect URL is preserved rather than replaced with a fabricated publisher URL'
        );
    }

    /**
     * Regression guard. An empty `domain` key is a deliberate "no trustworthy
     * publisher domain", which Google emits when its citation URL is only a
     * redirect. Deriving the domain from that URL anyway would label the source
     * `vertexaisearch.cloud.google.com` — Google's host, not the publisher's.
     */
    public function testRespectsAnExplicitlyEmptyDomainInsteadOfDerivingFromTheUrl(): void
    {
        $usage = WebSearchUsage::fromProviderData([
            [
                'url' => 'https://vertexaisearch.cloud.google.com/grounding-api-redirect/abc',
                'title' => 'Who won Euro 2024 - full report',
                'domain' => '',
            ],
        ], 1, []);

        $this->assertSame('', $usage->getCitations()[0]['domain']);
    }

    /**
     * The counterpart: a citation that omits the key entirely does get the host
     * derived from its URL. This is how Anthropic and OpenAI citations work.
     */
    public function testDerivesTheDomainWhenTheKeyIsAbsent(): void
    {
        $usage = WebSearchUsage::fromProviderData([
            ['url' => 'https://matomo.org/blog/x', 'title' => 'Matomo Blog'],
        ], 1, []);

        $this->assertSame('matomo.org', $usage->getCitations()[0]['domain']);
    }

    public function testNormalisesAProviderSuppliedDomainWithWwwPrefix(): void
    {
        $usage = WebSearchUsage::fromProviderData([
            ['url' => 'https://example.org/a', 'title' => 'Example', 'domain' => 'WWW.Example.ORG.'],
        ], 1, []);

        $this->assertSame('example.org', $usage->getCitations()[0]['domain']);
    }

    public function testKeepsWwwWhenStrippingItWouldLeaveABareSuffix(): void
    {
        $usage = WebSearchUsage::fromProviderData(
            [['url' => 'https://www.com/a']],
            1,
            []
        );

        $this->assertSame('www.com', $usage->getCitations()[0]['domain']);
    }

    public function testCapsUntrustedTitleAndQueryLength(): void
    {
        $usage = WebSearchUsage::fromProviderData(
            [['url' => 'https://example.org/a', 'title' => str_repeat('t', 400)]],
            1,
            [str_repeat('q', 400)]
        );

        $this->assertSame(300, mb_strlen($usage->getCitations()[0]['title']));
        $this->assertSame(300, mb_strlen($usage->getQueries()[0]));
    }

    public function testDropsAnAbsurdlyLongUrl(): void
    {
        $usage = WebSearchUsage::fromProviderData(
            [['url' => 'https://example.org/' . str_repeat('a', 2100)]],
            1,
            []
        );

        $this->assertSame([], $usage->getCitations());
    }

    public function testDeduplicatesAndTrimsQueries(): void
    {
        $usage = WebSearchUsage::fromProviderData([], 2, ['  best analytics  ', 'best analytics', '']);

        $this->assertSame(['best analytics'], $usage->getQueries());
    }

    /**
     * The providers disagree on what they report, so any positive evidence has to
     * count — otherwise spend that really happened would be reported as no search.
     *
     * @dataProvider getUsedEvidence
     * @param list<array{url?: mixed, title?: mixed, domain?: mixed}> $citations
     * @param list<string> $queries
     */
    public function testWasUsedIsTrueOnAnyPositiveEvidence(array $citations, ?int $requestCount, array $queries): void
    {
        $this->assertTrue(WebSearchUsage::fromProviderData($citations, $requestCount, $queries)->wasUsed());
    }

    /**
     * @return iterable<string, array{list<array<string, mixed>>, int|null, list<string>}>
     */
    public function getUsedEvidence(): iterable
    {
        // OpenAI can cite without echoing the query it used.
        yield 'citations only' => [[['url' => 'https://matomo.org']], null, []];
        // Google can report a query whose chunk yielded no usable URL.
        yield 'queries only' => [[], null, ['best analytics']];
        // Anthropic counts a search that returned an error or nothing usable.
        yield 'request count only' => [[], 1, []];
    }

    public function testWasUsedIsFalseWhenTheModelDidNotSearch(): void
    {
        $usage = WebSearchUsage::fromProviderData([], 0, []);

        $this->assertFalse($usage->wasUsed());
        $this->assertSame(0, $usage->getRequestCount(), 'a reported zero is not the same as "cannot report"');
    }
}
