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
 * Providers report grounding in three incompatible shapes, so this class owns
 * the translation and a caller reads one shape whichever provider ran. What the
 * flattening hides:
 *
 * - Anthropic reports both cited sources and every returned search result, so
 *   citations list the cited ones first. `requestCount` is its own counter.
 * - Google reports every retrieved grounding chunk and draws no cited/uncited
 *   distinction. Its `url` is the redirect Google returns, *not* the publisher
 *   URL, and the publisher host is in `domain`. It has no search counter, so
 *   `requestCount` is derived from the deduplicated query count. Two chunks from
 *   one page therefore do not collapse: count distinct `domain`, not `url`.
 * - OpenAI reports cited sources only. `requestCount` counts its `search`
 *   actions, and `queries` can be empty even when searches ran.
 *
 * URLs are guaranteed to be http(s) and are length-capped. Titles and queries
 * are untrusted model output, capped in length but otherwise verbatim: escape
 * them at the point of rendering.
 *
 * @phpstan-type WebSearchCitationArray array{url: string, title: string, domain: string}
 */
final class WebSearchUsage
{
    /**
     * Length caps for model-controlled strings. Providers have no documented
     * limit, and these values are stored and rendered by callers, so a runaway
     * title cannot bloat a row or a page.
     */
    private const MAX_URL_LENGTH = 2048;
    private const MAX_TITLE_LENGTH = 300;
    private const MAX_QUERY_LENGTH = 300;

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
     * first occurrence, since providers list the most relevant first.
     *
     * A `domain` key that is present is trusted verbatim, even when empty, and
     * an absent one is derived from the URL host. Google needs that: its URL is
     * its own redirect, so deriving there would label every source
     * `vertexaisearch.cloud.google.com`, and an empty string is its honest "no
     * publisher domain for this chunk".
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
                'title' => self::cap(is_string($citation['title'] ?? null) ? trim($citation['title']) : '', self::MAX_TITLE_LENGTH),
                'domain' => $domain,
            ];
        }

        return new self($normalized, $requestCount, self::normalizeQueries($queries));
    }

    /**
     * Trims, drops empties and deduplicates a provider's reported queries.
     *
     * Public because Google and Anthropic derive their search count from the
     * number of queries, and counting the raw list would report more searches
     * than {@link getQueries()} lists back.
     *
     * @param list<string> $queries
     * @return list<string>
     */
    public static function normalizeQueries(array $queries): array
    {
        $normalized = [];

        foreach ($queries as $query) {
            $query = self::cap(trim($query), self::MAX_QUERY_LENGTH);
            if ($query !== '' && !in_array($query, $normalized, true)) {
                $normalized[] = $query;
            }
        }

        return $normalized;
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
     * Rejects anything that is not an http(s) URL of sane length. Citation URLs
     * are model-controlled data that callers will render as links, so a
     * `javascript:` or `data:` scheme must never leave this plugin.
     */
    private static function isUsableUrl(string $url): bool
    {
        return strlen($url) <= self::MAX_URL_LENGTH
            && preg_match('~^https?://~i', $url) === 1
            && self::hostFromUrl($url) !== '';
    }

    private static function cap(string $value, int $maxLength): string
    {
        return mb_strlen($value) > $maxLength ? mb_substr($value, 0, $maxLength) : $value;
    }

    private static function hostFromUrl(string $url): string
    {
        $host = UrlHelper::getHostFromUrl($url);

        return is_string($host) ? self::normalizeHost($host) : '';
    }

    /**
     * Normalises a hostname for grouping and display: lowercased, trailing dot
     * and leading `www.` stripped.
     */
    private static function normalizeHost(string $host): string
    {
        $host = strtolower(rtrim(trim($host), '.'));

        // Only when a dotted name remains, so the registered domain `www.com`
        // does not collapse to the meaningless `com`.
        if (strpos($host, 'www.') === 0 && substr_count($host, '.') > 1) {
            $host = substr($host, 4);
        }

        return $host;
    }
}
