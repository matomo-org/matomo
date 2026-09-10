<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders;

use Piwik\UrlHelper;

/**
 * Normalised record of what a provider's server-side web search actually did
 * for one completion: the web sources attached to the answer, how many searches
 * ran, and the queries the model issued.
 *
 * The providers report grounding in three incompatible shapes — Anthropic uses
 * `server_tool_use`/`web_search_tool_result` blocks plus per-text-block
 * citations, Google a `groundingMetadata` object, OpenAI typed output items
 * carrying `url_citation` annotations. This plugin owns the translation so a
 * caller reads one shape and can switch providers without changing its code.
 *
 * What that flattening hides, so callers know what they are reading:
 *
 * - Anthropic reports both cited sources and every returned search result, so
 *   citations list the cited ones first. `requestCount` is Anthropic's own
 *   counter.
 * - Google reports every retrieved grounding chunk and draws no cited/uncited
 *   distinction. Its `url` is the Vertex AI Search redirect Google returns,
 *   *not* the publisher URL — the publisher host is in `domain`. Google has no
 *   search counter, so `requestCount` is derived from the number of queries.
 * - OpenAI reports cited sources only. `requestCount` counts its
 *   `web_search_call` items, and `queries` can be empty even when searches ran,
 *   because OpenAI does not always echo the query it used.
 *
 * A consequence worth knowing before computing shares: "citations" is not the
 * same denominator on all three providers, and because Google's URLs are
 * per-chunk redirects, two chunks from one publisher page do not collapse
 * during deduplication. Count distinct `domain` rather than distinct `url` when
 * the question is "how many sources".
 *
 * @phpstan-type WebSearchCitationArray array{url: string, title: string, domain: string}
 */
final class WebSearchUsage
{
    /**
     * @var list<WebSearchCitationArray>
     */
    private $citations;

    /**
     * @var int|null
     */
    private $requestCount;

    /**
     * @var list<string>
     */
    private $queries;

    /**
     * @param list<WebSearchCitationArray> $citations
     * @param list<string> $queries
     */
    private function __construct(array $citations, ?int $requestCount, array $queries)
    {
        $this->citations = $citations;
        $this->requestCount = $requestCount;
        $this->queries = $queries;
    }

    /**
     * No search ran: it was not requested, or the provider has no web search to
     * offer. This is what every ungrounded completion reports, so callers get a
     * consistent object instead of a null check.
     */
    public static function none(): self
    {
        return new self([], null, []);
    }

    /**
     * Normalises one provider's parsed grounding data.
     *
     * Citations are filtered to http(s) URLs and deduplicated by URL keeping the
     * first occurrence (providers list the most relevant first).
     *
     * The `domain` key decides who owns domain resolution, and the distinction
     * matters: when a citation omits the key entirely the domain is derived from
     * the URL host, but when the key is present it is trusted verbatim — even
     * when empty. Google needs that, because its URL is a redirect whose host is
     * Google's own: deriving from the URL there would label every source
     * `vertexaisearch.cloud.google.com`, and an empty string is Google's honest
     * "no trustworthy publisher domain for this chunk". Anthropic and OpenAI omit
     * the key and get the derived host.
     *
     * @param list<array{url?: mixed, title?: mixed, domain?: mixed}> $citations
     * @param int|null $requestCount Provider-reported search count, or null when it reports none.
     * @param list<string> $queries
     */
    public static function fromProviderData(array $citations, ?int $requestCount, array $queries): self
    {
        $normalized = [];
        $seenUrls = [];

        foreach ($citations as $citation) {
            $url = is_string($citation['url'] ?? null) ? trim($citation['url']) : '';
            if (!self::isUsableUrl($url) || isset($seenUrls[$url])) {
                continue;
            }
            $seenUrls[$url] = true;

            if (array_key_exists('domain', $citation)) {
                $domain = is_string($citation['domain']) ? self::normalizeHost($citation['domain']) : '';
            } else {
                $domain = self::hostFromUrl($url);
            }

            $normalized[] = [
                'url' => $url,
                'title' => is_string($citation['title'] ?? null) ? trim($citation['title']) : '',
                'domain' => $domain,
            ];
        }

        $normalizedQueries = [];
        foreach ($queries as $query) {
            $query = trim($query);
            if ($query !== '' && !in_array($query, $normalizedQueries, true)) {
                $normalizedQueries[] = $query;
            }
        }

        return new self($normalized, $requestCount, $normalizedQueries);
    }

    /**
     * @return list<WebSearchCitationArray>
     */
    public function getCitations(): array
    {
        return $this->citations;
    }

    /**
     * Number of searches the provider ran, or null when it reports none. Google
     * has no counter, so its value is derived from the query count.
     */
    public function getRequestCount(): ?int
    {
        return $this->requestCount;
    }

    /**
     * @return list<string>
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * Whether search demonstrably ran. Any positive evidence counts, because the
     * providers disagree on what they report: a model can search and cite
     * nothing (Anthropic still counts the request), cite without echoing a query
     * (OpenAI), or report a query whose chunk yielded no usable URL (Google).
     * Reporting false in those cases would hide spend that actually happened.
     */
    public function wasUsed(): bool
    {
        return $this->citations !== []
            || $this->queries !== []
            || ($this->requestCount !== null && $this->requestCount > 0);
    }

    /**
     * @return array{citations: list<WebSearchCitationArray>, requestCount: int|null, queries: list<string>}
     */
    public function toArray(): array
    {
        return [
            'citations' => $this->citations,
            'requestCount' => $this->requestCount,
            'queries' => $this->queries,
        ];
    }

    /**
     * Rejects anything that is not an http(s) URL. Citation URLs are
     * model-controlled data that callers will render as links, so a
     * `javascript:` or `data:` scheme must never leave this plugin.
     */
    private static function isUsableUrl(string $url): bool
    {
        return preg_match('~^https?://~i', $url) === 1 && self::hostFromUrl($url) !== '';
    }

    /**
     * Publisher host of a URL, or '' when it carries none.
     */
    private static function hostFromUrl(string $url): string
    {
        $host = UrlHelper::getHostFromUrl($url);

        return is_string($host) ? self::normalizeHost($host) : '';
    }

    /**
     * Normalises a hostname for grouping and display: lowercased, trailing dot
     * and leading `www.` stripped, matching how callers normalise their own
     * configured domains so both sides compare equal.
     */
    private static function normalizeHost(string $host): string
    {
        $host = strtolower(rtrim(trim($host), '.'));

        if (strpos($host, 'www.') === 0) {
            $host = substr($host, 4);
        }

        return $host;
    }
}
