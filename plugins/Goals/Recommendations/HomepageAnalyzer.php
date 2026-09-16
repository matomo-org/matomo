<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\Recommendations;

use Piwik\Config;
use Piwik\Config\GeneralConfig;
use Piwik\Container\StaticContainer;
use Piwik\Http;
use Piwik\Http\EgressBlockedException;
use Piwik\Site;
use Piwik\SiteContentDetector;
use Piwik\UrlHelper;
use Piwik\Plugins\SitesManager\SiteContentDetection\SiteContentDetectionAbstract;
use Psr\Log\LoggerInterface;

/**
 * Fetches a small same-origin slice of a site and turns it into the minimal,
 * safe signal set used to recommend URL goals. Only reduced path/link metadata
 * (never raw HTML) leaves this class.
 */
class HomepageAnalyzer
{
    /** Ranked same-origin destinations kept for the recommenders. */
    public const MAX_LINKS = 500;

    private const MAX_PAGES = 50;
    /** Same-origin links queued for crawling per page (best discovery score first). */
    private const MAX_QUEUED_LINKS_PER_PAGE = 40;

    /** Hard bound on distinct links extracted per page. */
    private const MAX_EXTRACTED_LINKS_PER_PAGE = 250;

    /** Links at the start of the main content that count as hero calls to action. */
    private const HERO_LINKS_PER_PAGE = 6;

    /** Links taken from embedded JSON payloads per page. */
    private const MAX_EMBEDDED_LINKS_PER_PAGE = 60;

    /** Characters searched after an embedded link for its label. */
    private const EMBEDDED_LABEL_WINDOW = 400;

    /** A page with fewer anchors than this and a large body renders its navigation in the browser. */
    private const CLIENT_SIDE_MAX_ANCHORS = 25;
    private const CLIENT_SIDE_MIN_BYTES = 50000;

    /**
     * Looser cutoff for the homepage alone: a start page this large with this few links
     * is an application shell. Measured on 24 server-rendered sites without a false
     * positive, while catching udemy.com in both of its homepage variants.
     */
    private const HOMEPAGE_CLIENT_SIDE_MAX_ANCHORS = 40;
    private const HOMEPAGE_CLIENT_SIDE_MIN_BYTES = 150000;
    private const MAX_LINK_TEXT_LENGTH = 120;

    /** Hard cap on bytes kept per fetched page (Range hint plus post-fetch enforcement). */
    private const MAX_RESPONSE_BYTES = 2000000;

    /** Wall-clock budget for the whole crawl; once exceeded, analysis uses what was collected. */
    private const MAX_CRAWL_SECONDS = 25;

    // Some sites reject requests with an empty or obviously non-browser user agent.
    private const USER_AGENT = 'Mozilla/5.0 (compatible; MatomoGoalRecommendations/1.0)';

    /**
     * @var SiteContentDetector
     */
    private $siteContentDetector;

    /**
     * Counters of the last crawl that explain a thin result: pages the site refused
     * (bot protection), pages that could not be fetched, pages whose navigation is
     * rendered in the browser, and whether the time budget ran out.
     *
     * @var array{blockedPages: int, failedFetches: int, clientSideRenderedPages: int, homepageClientSideRendered: bool, deadlineReached: bool}
     */
    private $crawlStats = self::EMPTY_CRAWL_STATS;

    private const EMPTY_CRAWL_STATS = [
        'blockedPages' => 0,
        'failedFetches' => 0,
        'clientSideRenderedPages' => 0,
        'homepageClientSideRendered' => false,
        'deadlineReached' => false,
    ];

    /** HTTP statuses that mean the site refused the request rather than failed. */
    private const BLOCKED_STATUSES = [401, 403, 429, 503];

    public function __construct(?SiteContentDetector $siteContentDetector = null)
    {
        $this->siteContentDetector = $siteContentDetector ?? new SiteContentDetector();
    }

    /**
     * @return array<string, mixed>|null Null when the homepage cannot be fetched.
     */
    public function analyze(int $idSite, int $timeout = 5): ?array
    {
        $url = Site::getMainUrlFor($idSite);

        if (empty($url)) {
            return null;
        }

        return $this->analyzeUrl($url, $idSite, $timeout);
    }

    /**
     * @return array<string, mixed>|null Null when the homepage cannot be fetched.
     */
    public function analyzeUrl(string $url, ?int $idSite = null, int $timeout = 5): ?array
    {
        $this->crawlStats = self::EMPTY_CRAWL_STATS;
        $startUrl = $this->normalizeCrawlUrl($url);
        if ($startUrl === null) {
            return null;
        }

        $response = $this->usableResponse($this->fetchHomepage($startUrl, $timeout), $startUrl);

        if ($response === null) {
            return null;
        }

        $status = $response['status'] ?? null;
        $html = $response['data'] ?? '';

        if ($html === '') {
            $this->getLogger()->debug(
                'Goals recommendations: empty homepage body for {url} (HTTP status {status}).',
                ['url' => $startUrl, 'status' => $status]
            );
            return null;
        }

        // Re-anchor on the URL the actual request url (e.g. an apex -> www canonical redirect),
        // so absolute same-origin links are kept and crawled pages aren't redirected again.
        $effectiveUrl = $this->normalizeCrawlUrl((string) ($response['effectiveUrl'] ?? ''));
        if ($effectiveUrl !== null) {
            $startUrl = $effectiveUrl;
        }

        $host = $this->getUrlAuthority($startUrl);
        if ($host === '') {
            return null;
        }

        $pages = $this->crawlSameOriginPages($startUrl, $host, $html, $timeout);
        $links = $this->rankLinks($pages);

        $this->getLogger()->debug(
            'Goals recommendations: analysed {url} '
                . '(HTTP status {status}, {bytes} bytes, {pages} pages, {links} ranked links).',
            [
                'url' => $startUrl,
                'status' => $status,
                'bytes' => strlen($html),
                'pages' => count($pages),
                'links' => count($links),
            ]
        );

        return [
            'url' => $startUrl,
            'links' => $links,
            'forms' => $this->rankForms($pages),
            'downloads' => $this->rankDownloads($pages),
            'contactLinks' => $this->rankContactLinks($pages),
            'externalLinks' => $this->rankExternalLinks($pages),
            'technologies' => $this->detectTechnologies($idSite, $html, $response['headers'] ?? []),
            'platform' => $this->detectEcommercePlatform($html),
            'pages' => $this->summarizePages($pages),
            'pagesCrawled' => count($pages),
            'crawl' => $this->crawlStats,
            'manualSignals' => $this->aggregateManualSignals($pages),
        ];
    }

    /**
     * Raw fetch, any status. Null when internet features are off or the request
     * itself failed; see usableResponse() for the status and content type checks.
     *
     * @return array{status?: ?int, headers?: ?array, data?: ?string, effectiveUrl?: string}|null
     */
    protected function fetchHomepage(string $url, int $timeout): ?array
    {
        // Respect the same internet-features kill switch SiteContentDetector honours.
        if (0 === GeneralConfig::getIntegerConfigValue('enable_internet_features', 0)) {
            $this->getLogger()->debug(
                'Goals recommendations: internet features are disabled; skipping homepage fetch.'
            );
            return null;
        }

        try {
            // @todo PHP 8.1 min (Matomo 6): use named arguments to drop the positional null filler.
            $response = Http::sendHttpRequest(
                $url,
                $timeout,
                self::USER_AGENT,
                null,
                0,
                false,
                [0, self::MAX_RESPONSE_BYTES], // $byteRange: Range hint to keep responses small
                true, // $getExtendedInfo: returns ['status', 'headers', 'data']
                'GET',
                null,
                null,
                true, // $checkHostIsAllowed
                true // $validateEgressIp: SSRF-safe fetch (public-IP only, per-redirect revalidation, pinned)
            );
        } catch (EgressBlockedException $e) {
            // admin-fixable rejection, not a transient network error, so it must clear the default WARN level
            $this->getLogger()->warning(
                'Goals recommendations: homepage fetch for {url} was refused: {message}',
                // host only, so a configured URL carrying userinfo keeps credentials out of the log
                ['url' => UrlHelper::getHostFromUrl($url), 'message' => $e->getMessage()]
            );
            return null;
        } catch (\Exception $e) {
            ++$this->crawlStats['failedFetches'];
            $this->getLogger()->debug(
                'Goals recommendations: homepage fetch failed for {url}: {message}',
                ['url' => $url, 'message' => $e->getMessage()]
            );
            return null;
        }

        return $response;
    }

    /**
     * Keeps only successful HTML responses and counts the rest as refused (bot
     * protection statuses) or failed, so a thin result can be explained.
     *
     * @param array{status?: ?int, headers?: ?array, data?: ?string, effectiveUrl?: string}|null $response
     * @return array{status?: ?int, headers?: ?array, data?: ?string, effectiveUrl?: string}|null
     */
    private function usableResponse(?array $response, string $url): ?array
    {
        if ($response === null) {
            return null;
        }

        $status = $response['status'] ?? null;
        $contentType = (string) ($response['headers']['Content-Type'] ?? '');

        // reject error pages and non-html bodies
        if (
            !is_int($status) || $status < 200 || $status >= 300
            || (
                $contentType !== ''
                && stripos($contentType, 'text/html') === false
                && stripos($contentType, 'application/xhtml+xml') === false
            )
        ) {
            if (in_array($status, self::BLOCKED_STATUSES, true)) {
                ++$this->crawlStats['blockedPages'];
            } else {
                ++$this->crawlStats['failedFetches'];
            }
            $this->getLogger()->debug(
                'Goals recommendations: skipping {url} (HTTP status {status}, content type {contentType}).',
                ['url' => $url, 'status' => $status, 'contentType' => $contentType]
            );
            return null;
        }

        if (is_string($response['data'] ?? null) && strlen($response['data']) > self::MAX_RESPONSE_BYTES) {
            // Enforce the cap for servers that ignore the Range header.
            $response['data'] = substr($response['data'], 0, self::MAX_RESPONSE_BYTES);
        }

        return $response;
    }

    /**
     * Counters of the last analyze() call, also available when it returned null
     * because the homepage itself was refused.
     *
     * @return array{blockedPages: int, failedFetches: int, clientSideRenderedPages: int, homepageClientSideRendered: bool, deadlineReached: bool}
     */
    public function getLastCrawlStats(): array
    {
        return $this->crawlStats;
    }

    protected function getCrawlDeadlineSeconds(): int
    {
        return self::MAX_CRAWL_SECONDS;
    }

    protected function getMaxPages(): int
    {
        return max(1, (int) (Config::getInstance()->Goals['recommendation_max_crawl_pages'] ?? self::MAX_PAGES));
    }

    private function extractFirstText(\DOMXPath $xpath, string $query): string
    {
        $nodes = $xpath->query($query);
        if ($nodes === false || $nodes->length === 0) {
            return '';
        }

        return $this->truncateText((string) $nodes->item(0)->textContent, 120);
    }

    /**
     * Compact per-page summary for the recommenders.
     *
     * @param array<int, array{url: string, title?: string, heading?: string, signals?: array<string, mixed>}> $pages
     * @return array<int, array{path: string, title: string, heading: string, types: string[], hasAddToCart: bool}>
     */
    private function summarizePages(array $pages): array
    {
        $summary = [];
        foreach ($pages as $page) {
            $path = rtrim((string) parse_url($page['url'], PHP_URL_PATH), '/');
            $summary[] = [
                'path' => $path === '' ? '/' : $path,
                'title' => (string) ($page['title'] ?? ''),
                'heading' => (string) ($page['heading'] ?? ''),
                'types' => $page['signals']['structuredTypes'] ?? [],
                'hasAddToCart' => !empty($page['signals']['hasAddToCart']),
            ];
        }

        return $summary;
    }

    /**
     * Shop platform from homepage markup; its checkout and confirmation URLs are
     * fixed and never reachable by the crawl.
     */
    private function detectEcommercePlatform(string $html): ?string
    {
        // asset paths and template markers only: plain product names appear in copy text too
        $markers = [
            'shopify' => ['cdn.shopify.com', 'shopify.theme', 'myshopify.com'],
            'woocommerce' => ['plugins/woocommerce/', 'wc-add-to-cart', 'wc_add_to_cart_params', 'class="woocommerce'],
            'edd' => ['easy-digital-downloads/', 'edd-blocks', 'edd_ajax', 'edd_scripts'],
            'shopware' => ['/widgets/listing/', '/widgets/checkout/', 'shopware.min.js', 'class="is--'],
            'magento' => ['mage/cookies', 'Magento_Theme', 'data-mage-init', 'mage-init'],
            'prestashop' => ['prestashop = ', 'themes/classic/assets', 'modules/ps_'],
            'sfcc' => ['/on/demandware.store/', 'demandware.static', 'dwanalytics'],
        ];
        $haystack = substr($html, 0, self::MAX_RESPONSE_BYTES);

        foreach ($markers as $platform => $needles) {
            foreach ($needles as $needle) {
                if (stripos($haystack, $needle) !== false) {
                    return $platform;
                }
            }
        }

        return null;
    }

    private function getLogger(): LoggerInterface
    {
        return StaticContainer::get(LoggerInterface::class);
    }

    /**
     * @return array<int, array{
     *   url: string,
     *   links: array<int, array{
     *     linkText: string, linkTarget: string, url: string, area: string, isButtonLike: bool, isHero: bool, weight: int
     *   }>,
     *   signals: array<string, mixed>
     * }>
     */
    private function crawlSameOriginPages(string $startUrl, string $host, string $homepageHtml, int $timeout): array
    {
        $queue = [$startUrl];
        $queued = [rtrim($startUrl, '/') => true];
        $visited = [];
        $pages = [];
        $htmlByUrl = [$startUrl => $homepageHtml];
        $deadline = microtime(true) + $this->getCrawlDeadlineSeconds();
        $maxPages = $this->getMaxPages();

        while (!empty($queue) && count($pages) < $maxPages && microtime(true) < $deadline) {
            $currentUrl = (string) array_shift($queue);
            $urlKey = rtrim($currentUrl, '/');
            if ($urlKey === '' || isset($visited[$urlKey])) {
                continue;
            }

            // /x and /x/ are one page: fetched with the slash the site uses, tracked without it
            $visited[$urlKey] = true;
            $html = $htmlByUrl[$currentUrl] ?? null;

            if ($html === null) {
                $response = $this->usableResponse($this->fetchHomepage($currentUrl, $timeout), $currentUrl);
                $html = is_array($response) ? (string) ($response['data'] ?? '') : '';

                $pageHost = is_array($response)
                    ? $this->getUrlAuthority((string) ($response['effectiveUrl'] ?? ''))
                    : '';
                if ($pageHost !== '' && strcasecmp($pageHost, $host) !== 0) {
                    $this->getLogger()->debug(
                        'Goals recommendations: skipping {url}, it redirected off-origin to {host}.',
                        ['url' => $currentUrl, 'host' => $pageHost]
                    );
                    continue;
                }
            }

            if ($html === '') {
                $this->getLogger()->debug('Goals recommendations: could not fetch {url}.', ['url' => $currentUrl]);
                continue;
            }

            $xpath = $this->loadXpath($html);
            $links = $xpath !== null ? $this->extractLinks($xpath, $currentUrl, $host) : [];
            $anchorCount = count($links);
            $embedded = $this->extractEmbeddedLinks($html, $currentUrl, $host, $links);
            $links = array_merge($links, $embedded);
            if ($this->isClientSideRendered($html, $anchorCount, count($embedded))) {
                ++$this->crawlStats['clientSideRenderedPages'];
            }
            // the homepage alone decides a lot: a big start page with hardly any links means the
            // navigation is built in the browser, whatever the subpages look like
            if (
                $currentUrl === $startUrl
                && $anchorCount < self::HOMEPAGE_CLIENT_SIDE_MAX_ANCHORS
                && strlen($html) > self::HOMEPAGE_CLIENT_SIDE_MIN_BYTES
            ) {
                $this->crawlStats['homepageClientSideRendered'] = true;
            }
            $signals = $xpath !== null
                ? $this->extractManualSignals($xpath, $currentUrl, $host)
                : $this->emptyManualSignals();
            $pages[] = [
                'url' => $currentUrl,
                'links' => $links,
                'signals' => $signals,
                'title' => $xpath !== null ? $this->extractFirstText($xpath, '//title') : '',
                'heading' => $xpath !== null ? $this->extractFirstText($xpath, '//h1') : '',
            ];

            usort($links, function (array $a, array $b): int {
                return $this->discoveryScore($b) <=> $this->discoveryScore($a);
            });

            // only new links use the per-page budget, so repeated navigation cannot fill it
            $queuedFromPage = 0;
            foreach ($links as $link) {
                if ($queuedFromPage >= self::MAX_QUEUED_LINKS_PER_PAGE) {
                    break;
                }
                $url = $link['url'];
                $urlKey = rtrim($url, '/');
                if (isset($queued[$urlKey]) || isset($visited[$urlKey])) {
                    continue;
                }

                $queue[] = $url;
                $queued[$urlKey] = true;
                ++$queuedFromPage;
            }
        }

        $this->crawlStats['deadlineReached'] = !empty($queue) && count($pages) < $maxPages && microtime(true) >= $deadline;

        return $pages;
    }

    /**
     * Aggregates repeated same-origin links into ranked destination signals.
     *
     * @param array<int, array{
     *   url: string,
     *   links: array<int, array{
     *     linkText: string, linkTarget: string, url: string, area: string, isButtonLike: bool, isHero: bool, weight: int
     *   }>
     * }> $pages
     * @return array<int, array{
     *   linkText: string, linkTarget: string, score: int, pageCount: int, occurrenceCount: int, areas: string[],
     *   labelSamples: string[], exampleUrls: string[], buttonLikeCount: int
     * }>
     */
    private function rankLinks(array $pages): array
    {
        $buckets = [];

        foreach ($pages as $page) {
            foreach ($page['links'] as $link) {
                $target = $link['linkTarget'];
                if (!isset($buckets[$target])) {
                    $buckets[$target] = [
                        'linkTarget' => $target,
                        'labels' => [],
                        'areas' => [],
                        'areaCounts' => [],
                        'pageUrls' => [],
                        'exampleUrls' => [],
                        'occurrenceCount' => 0,
                        'buttonLikeCount' => 0,
                        'heroCount' => 0,
                    ];
                }

                $labelKey = strtolower($link['linkText']);
                if ($labelKey !== '') {
                    $buckets[$target]['labels'][$labelKey] = $link['linkText'];
                }

                $buckets[$target]['areas'][$link['area']] = true;
                if (!isset($buckets[$target]['areaCounts'][$link['area']])) {
                    $buckets[$target]['areaCounts'][$link['area']] = 0;
                }
                ++$buckets[$target]['areaCounts'][$link['area']];
                $buckets[$target]['pageUrls'][$page['url']] = true;
                $buckets[$target]['exampleUrls'][$link['url']] = true;
                ++$buckets[$target]['occurrenceCount'];

                if ($link['isButtonLike']) {
                    ++$buckets[$target]['buttonLikeCount'];
                }
                if (!empty($link['isHero'])) {
                    ++$buckets[$target]['heroCount'];
                }
            }
        }

        $ranked = [];
        foreach ($buckets as $bucket) {
            $pageCount = count($bucket['pageUrls']);
            $labelSamples = array_values($bucket['labels']);
            $ranked[] = [
                'linkText' => implode(' / ', array_slice($labelSamples, 0, 3)),
                'linkTarget' => $bucket['linkTarget'],
                'score' => $this->recommendationScore($bucket),
                'pageCount' => $pageCount,
                'occurrenceCount' => $bucket['occurrenceCount'],
                'areas' => array_keys($bucket['areas']),
                'labelSamples' => array_slice($labelSamples, 0, 5),
                'exampleUrls' => array_slice(array_keys($bucket['exampleUrls']), 0, 3),
                'buttonLikeCount' => $bucket['buttonLikeCount'],
                'heroCount' => $bucket['heroCount'],
            ];
        }

        usort($ranked, function (array $a, array $b): int {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($ranked, 0, self::MAX_LINKS);
    }

    /**
     * Returns null when the HTML cannot be parsed.
     */
    private function loadXpath(string $html): ?\DOMXPath
    {
        $document = new \DOMDocument();

        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded) {
            return null;
        }

        return new \DOMXPath($document);
    }

    /**
     * Extracts same-origin links from a parsed document and reduces each to compact
     * link metadata. External links, anchors, and non-http schemes are dropped.
     *
     * @return array<int, array{
     *   linkText: string, linkTarget: string, url: string, area: string, isButtonLike: bool, isHero: bool, weight: int
     * }>
     */
    private function extractLinks(\DOMXPath $xpath, string $baseUrl, string $host): array
    {
        $anchors = $xpath->query('//a[@href]');

        if ($anchors === false) {
            return [];
        }

        $links = [];
        $mainLinks = 0;

        foreach ($anchors as $anchor) {
            if (!$anchor instanceof \DOMElement) {
                continue;
            }

            $url = $this->resolveSameOriginUrl($anchor->getAttribute('href'), $baseUrl, $host);
            if ($url === null) {
                continue;
            }

            $target = rtrim((string) parse_url($url, PHP_URL_PATH), '/');
            if ($target === '') {
                continue;
            }

            $area = $this->detectArea($anchor);
            $isButtonLike = $this->isButtonLike($anchor);
            $linkText = $this->normalizeLinkText($anchor->textContent);
            $key = strtolower($target . '|' . $linkText . '|' . $area);
            if (isset($links[$key])) {
                continue;
            }

            // the first links of the main content are the page's primary calls to action
            $isHero = in_array($area, ['main', 'section'], true) && $mainLinks < self::HERO_LINKS_PER_PAGE;
            if (in_array($area, ['main', 'section'], true)) {
                ++$mainLinks;
            }

            $links[$key] = [
                'linkText' => $linkText,
                'linkTarget' => $target,
                'url' => $url,
                'area' => $area,
                'isButtonLike' => $isButtonLike,
                'isHero' => $isHero,
                'weight' => $this->areaWeight($area) + ($isButtonLike ? 2 : 0),
            ];

            if (count($links) >= self::MAX_EXTRACTED_LINKS_PER_PAGE) {
                break;
            }
        }

        return array_values($links);
    }

    /**
     * Same-origin links that only exist inside embedded JSON, which single page
     * applications use for navigation they render in the browser. Their labels come
     * from the neighbouring text property when the payload carries one.
     *
     * @param array<int, array{linkTarget: string}> $anchorLinks links already found in the markup
     * @return array<int, array{
     *   linkText: string, linkTarget: string, url: string, area: string, isButtonLike: bool, isHero: bool, weight: int
     * }>
     */
    private function extractEmbeddedLinks(string $html, string $baseUrl, string $host, array $anchorLinks): array
    {
        if (!$this->isEmbeddedLinkMiningEnabled()) {
            return [];
        }

        $found = preg_match_all(
            '#"(?:href|url|link|permalink|path)"\s*:\s*"(/[^"\\\s]{1,200}|https?://[^"\\\s]{1,200})"#i',
            $html,
            $matches,
            PREG_OFFSET_CAPTURE
        );
        if (!$found) {
            return [];
        }

        $seen = array_fill_keys(array_column($anchorLinks, 'linkTarget'), true);
        $links = [];
        foreach ($matches[1] as $match) {
            [$href, $offset] = $match;
            $url = $this->resolveSameOriginUrl(str_replace('\\/', '/', $href), $baseUrl, $host);
            if ($url === null) {
                continue;
            }
            $target = rtrim((string) parse_url($url, PHP_URL_PATH), '/');
            if ($target === '' || isset($seen[$target]) || $this->isNonContentPath($target)) {
                continue;
            }

            $seen[$target] = true;
            $links[] = [
                'linkText' => $this->extractNeighbouringText($html, $offset),
                'linkTarget' => $target,
                'url' => $url,
                'area' => 'script',
                'isButtonLike' => false,
                'isHero' => false,
                'weight' => 1,
            ];
            if (count($links) >= self::MAX_EMBEDDED_LINKS_PER_PAGE) {
                break;
            }
        }

        return $links;
    }

    /**
     * Experimental: links inside embedded JSON are not rendered markup, so they are
     * only mined when the instance opts in.
     */
    protected function isEmbeddedLinkMiningEnabled(): bool
    {
        return 1 === (int) (Config::getInstance()->Goals['recommendation_embedded_links'] ?? 0);
    }

    /**
     * The text property that follows an embedded link, e.g. {"href":"/x","text":"Pricing"}.
     */
    private function extractNeighbouringText(string $html, int $offset): string
    {
        $window = substr($html, $offset, self::EMBEDDED_LABEL_WINDOW);
        if (preg_match('#"(?:text|label|title|name|anchor)"\s*:\s*"([^"\\\\]{1,80})"#i', $window, $match)) {
            return $this->normalizeLinkText($match[1]);
        }

        return '';
    }

    /**
     * Assets, APIs and tracking endpoints that appear in embedded payloads next to
     * the navigation links.
     */
    private function isNonContentPath(string $path): bool
    {
        return preg_match('#^/(api|graphql|_next|_nuxt|static|assets?|cdn|media|sitemap|wp-json|feed|rss)(/|$)#i', $path) === 1
            || preg_match('#\.(js|css|json|xml|txt|png|jpe?g|gif|svg|webp|ico|woff2?|ttf|mp4|mp3)$#i', $path) === 1;
    }

    /**
     * A large page with almost no anchors but many links inside embedded payloads
     * renders its navigation in the browser, so a crawl sees only a fraction of it.
     */
    private function isClientSideRendered(string $html, int $anchorCount, int $embeddedCount): bool
    {
        return $anchorCount < self::CLIENT_SIDE_MAX_ANCHORS
            && $embeddedCount > $anchorCount
            && strlen($html) > self::CLIENT_SIDE_MIN_BYTES;
    }

    /**
     * Extracts non-URL signals from a parsed page: content forms, file downloads,
     * outbound link hosts, and mailto/tel contact links. Only compact, aggregated
     * metadata leaves here, never raw HTML.
     *
     * @return array{
     *   downloadExtensions: array<string, int>, outlinkHosts: array<string, int>, hasContactLinks: bool,
     *   formCount: int, forms: array<int, array<string, mixed>>, downloads: array<int, array<string, mixed>>,
     *   contactLinks: array<int, array<string, mixed>>, externalLinks: array<int, array<string, mixed>>
     * }
     */
    private function extractManualSignals(\DOMXPath $xpath, string $baseUrl, string $host): array
    {
        $downloadExtensions = [];
        $outlinkHosts = [];
        $hasContactLinks = false;
        $downloads = [];
        $contactLinks = [];
        $externalLinks = [];
        // outlink hosts carry no port, so compare without it
        $bareHost = (string) preg_replace('/^www\./', '', strtolower((string) preg_replace('/:\d+$/', '', $host)));

        $anchors = $xpath->query('//a[@href]');
        if ($anchors !== false) {
            foreach ($anchors as $anchor) {
                if (!$anchor instanceof \DOMElement) {
                    continue;
                }

                $href = trim($anchor->getAttribute('href'));
                if ($href === '') {
                    continue;
                }

                $scheme = strtolower((string) parse_url($href, PHP_URL_SCHEME));

                if (in_array($scheme, ['mailto', 'tel'], true)) {
                    $hasContactLinks = true;
                    $contactLinks[] = [
                        'href' => $href,
                        'label' => $this->normalizeLinkText($anchor->textContent ?: $anchor->getAttribute('title')),
                    ];
                    continue;
                }

                if (preg_match('/\.(pdf|docx?|xlsx?|pptx?|csv|zip)(?:[?#]|$)/i', $href, $matches)) {
                    $extension = strtolower($matches[1]);
                    $downloadExtensions[$extension] = ($downloadExtensions[$extension] ?? 0) + 1;
                    $downloads[] = [
                        'href' => $this->resolveUrl($href, $baseUrl) ?? $href,
                        'label' => $this->normalizeLinkText($anchor->textContent ?: $anchor->getAttribute('title')),
                    ];
                    continue;
                }

                if (in_array($scheme, ['http', 'https'], true)) {
                    $linkHost = (string) preg_replace(
                        '/^www\./',
                        '',
                        strtolower((string) parse_url($href, PHP_URL_HOST))
                    );
                    if ($linkHost !== '' && $linkHost !== $bareHost) {
                        $outlinkHosts[$linkHost] = ($outlinkHosts[$linkHost] ?? 0) + 1;
                        $externalLinks[] = [
                            'href' => $href,
                            'host' => $linkHost,
                            'label' => $this->normalizeLinkText($anchor->textContent ?: $anchor->getAttribute('title')),
                            'area' => $this->detectArea($anchor),
                        ];
                    }
                }
            }
        }

        $forms = $this->extractForms($xpath, $baseUrl);

        return [
            'hasAddToCart' => $this->hasAddToCartControl($xpath),
            'structuredTypes' => $this->extractStructuredTypes($xpath),
            'downloadExtensions' => $downloadExtensions,
            'outlinkHosts' => $outlinkHosts,
            'hasContactLinks' => $hasContactLinks,
            'formCount' => count($forms),
            'forms' => $forms,
            'downloads' => $this->uniqueSignalItems($downloads, 'href', 10),
            'contactLinks' => $this->uniqueSignalItems($contactLinks, 'href', 8),
            'externalLinks' => $this->uniqueSignalItems($externalLinks, 'href', 12),
        ];
    }

    /**
     * @return array<int, array{
     *   action: string, method: string, submitText: string, fields: string[], area: string, context: string
     * }>
     */
    private function extractForms(\DOMXPath $xpath, string $baseUrl): array
    {
        $forms = $xpath->query('//form');
        if ($forms === false) {
            return [];
        }

        $result = [];
        foreach ($forms as $form) {
            if (!$form instanceof \DOMElement || !$this->isContentForm($xpath, $form)) {
                continue;
            }

            $fields = $this->extractFieldSignature($xpath, $form);
            if (empty($fields)) {
                continue;
            }

            $action = $this->resolveUrl($form->getAttribute('action'), $baseUrl) ?? $baseUrl;
            $result[] = [
                'action' => (string) parse_url($action, PHP_URL_PATH) ?: '/',
                'method' => strtolower($form->getAttribute('method') ?: 'post'),
                'submitText' => $this->extractSubmitText($xpath, $form),
                'fields' => $fields,
                'fieldTypes' => $this->extractFieldTypes($xpath, $form),
                'area' => $this->detectArea($form),
                'context' => $this->truncateText($form->textContent, 140),
            ];
        }

        return array_slice($result, 0, 6);
    }

    /**
     * Normalised input types of a form, with autocomplete and name hints mapped on.
     *
     * @return string[]
     */
    private function extractFieldTypes(\DOMXPath $xpath, \DOMElement $form): array
    {
        $fields = $xpath->query('.//input | .//textarea | .//select', $form);
        if ($fields === false) {
            return [];
        }

        $types = [];
        foreach ($fields as $field) {
            if (!$field instanceof \DOMElement) {
                continue;
            }
            $type = strtolower($field->getAttribute('type') ?: $field->tagName);
            if (in_array($type, ['hidden', 'submit', 'button', 'image', 'reset'], true)) {
                continue;
            }
            $autocomplete = strtolower($field->getAttribute('autocomplete'));
            $name = strtolower($field->getAttribute('name') . ' ' . $field->getAttribute('id'));
            if ($autocomplete === 'email' || preg_match('/e-?mail/', $name)) {
                $type = 'email';
            } elseif ($autocomplete === 'tel' || preg_match('/\b(tel|phone|telefon|telephone)\b/', $name)) {
                $type = 'tel';
            } elseif (preg_match('/password|passwort|mot_de_passe|contrase/', $name)) {
                $type = 'password';
            } elseif (preg_match('/\b(date|datum|checkin|check-in|arrival|arrivo|fecha)\b/', $name)) {
                $type = 'date';
            }
            $types[] = $type;
        }

        return array_slice($types, 0, 12);
    }

    /**
     * @return string[]
     */
    private function extractFieldSignature(\DOMXPath $xpath, \DOMElement $form): array
    {
        $fields = $xpath->query('.//input | .//textarea | .//select', $form);
        if ($fields === false) {
            return [];
        }

        $result = [];
        foreach ($fields as $field) {
            if (!$field instanceof \DOMElement) {
                continue;
            }

            $type = strtolower($field->getAttribute('type') ?: $field->tagName);
            if (in_array($type, ['hidden', 'submit', 'button', 'image', 'reset'], true)) {
                continue;
            }

            $signature = $this->normalizeLinkText(implode(' ', array_filter([
                $field->getAttribute('name'),
                $field->getAttribute('placeholder'),
                $field->getAttribute('aria-label'),
                $type,
            ])));

            if ($signature !== '') {
                $result[strtolower($signature)] = $signature;
            }
        }

        return array_slice(array_values($result), 0, 8);
    }

    private function extractSubmitText(\DOMXPath $xpath, \DOMElement $form): string
    {
        $submitControls = $xpath->query(
            './/button[@type="submit"] | .//button[not(@type)] | .//input[@type="submit"]',
            $form
        );
        if ($submitControls !== false && $submitControls->length > 0) {
            $control = $submitControls->item(0);
            if ($control instanceof \DOMElement) {
                $text = $this->normalizeLinkText($control->textContent ?: $control->getAttribute('value'));
                if ($text !== '') {
                    return $text;
                }
            }
        }

        return 'Submit';
    }

    private function resolveUrl(string $href, string $baseUrl): ?string
    {
        $href = trim($href);
        if ($href === '') {
            return $baseUrl;
        }

        $scheme = strtolower((string) parse_url($href, PHP_URL_SCHEME));
        if (in_array($scheme, ['mailto', 'tel'], true)) {
            return $href;
        }
        if ($scheme !== '') {
            return $href;
        }

        $base = parse_url($baseUrl);
        if (!is_array($base) || empty($base['host'])) {
            return null;
        }

        $baseScheme = (string) ($base['scheme'] ?? 'https');
        $host = (string) $base['host'] . (isset($base['port']) ? ':' . $base['port'] : '');
        $basePath = (string) ($base['path'] ?? '/');

        if (strpos($href, '/') === 0) {
            $path = $href;
        } else {
            $directory = preg_replace('#/[^/]*$#', '/', $basePath);
            $path = ($directory ?: '/') . $href;
        }

        return $baseScheme . '://' . $host . $this->normalizePath($path);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function uniqueSignalItems(array $items, string $key, int $limit): array
    {
        $seen = [];
        $result = [];

        foreach ($items as $item) {
            $value = (string) ($item[$key] ?? '');
            if ($value === '' || isset($seen[$value])) {
                continue;
            }

            $seen[$value] = true;
            $result[] = $item;
            if (count($result) >= $limit) {
                break;
            }
        }

        return $result;
    }

    /**
     * Add-to-cart control: a form posting to a cart endpoint, or a platform marker
     * class or attribute. Never copy text.
     */
    private function hasAddToCartControl(\DOMXPath $xpath): bool
    {
        $forms = $xpath->query('//form[@action]');
        if ($forms !== false) {
            foreach ($forms as $form) {
                if (
                    $form instanceof \DOMElement
                    && preg_match('#(cart/add|/cart\b|add-to-cart|addarticle|checkout/cart/add|panier|warenkorb)#i', $form->getAttribute('action'))
                    && $xpath->query('.//button | .//input[@type="submit"]', $form)->length > 0
                ) {
                    return true;
                }
            }
        }

        $markers = $xpath->query(
            '//*[contains(@class, "add-to-cart") or contains(@class, "add_to_cart") or contains(@class, "addtocart")'
            . ' or contains(@class, "product-form__submit") or contains(@class, "buy-widget") or @data-add-to-cart'
            . ' or contains(@class, "tocart") or contains(@name, "add-to-cart") or contains(@id, "add-to-cart")'
            . ' or contains(@class, "btn-add-to-cart") or contains(@class, "cart-add")]'
        );

        return $markers !== false && $markers->length > 0;
    }

    /**
     * schema.org types from JSON-LD and microdata, e.g. Product, Event, Restaurant, Course.
     *
     * @return string[]
     */
    private function extractStructuredTypes(\DOMXPath $xpath): array
    {
        $types = [];
        $scripts = $xpath->query('//script[@type="application/ld+json"]');
        if ($scripts !== false) {
            foreach ($scripts as $script) {
                if (preg_match_all('/"@type"\s*:\s*"([A-Za-z]+)"/', $script->textContent, $matches)) {
                    foreach ($matches[1] as $type) {
                        $types[$type] = true;
                    }
                }
            }
        }
        $items = $xpath->query('//*[@itemtype]');
        if ($items !== false) {
            foreach ($items as $item) {
                if ($item instanceof \DOMElement && preg_match('#schema\.org/([A-Za-z]+)#', $item->getAttribute('itemtype'), $m)) {
                    $types[$m[1]] = true;
                }
            }
        }

        return array_slice(array_keys($types), 0, 12);
    }

    private function isContentForm(\DOMXPath $xpath, \DOMElement $form): bool
    {
        if (strtolower($form->getAttribute('role')) === 'search') {
            return false;
        }

        $searchInputs = $xpath->query('.//input[@type="search"]', $form);
        if ($searchInputs !== false && $searchInputs->length > 0) {
            return false;
        }

        $fields = $xpath->query('.//input | .//textarea | .//select', $form);
        if ($fields === false) {
            return false;
        }

        foreach ($fields as $field) {
            if (!$field instanceof \DOMElement) {
                continue;
            }
            $type = strtolower($field->getAttribute('type'));
            if (!in_array($type, ['hidden', 'submit', 'button', 'image', 'reset'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{
     *   downloadExtensions: array<string, int>, outlinkHosts: array<string, int>, hasContactLinks: bool,
     *   formCount: int, forms: array<int, array<string, mixed>>, downloads: array<int, array<string, mixed>>,
     *   contactLinks: array<int, array<string, mixed>>, externalLinks: array<int, array<string, mixed>>
     * }
     */
    private function emptyManualSignals(): array
    {
        return [
            'hasAddToCart' => false,
            'structuredTypes' => [],
            'downloadExtensions' => [],
            'outlinkHosts' => [],
            'hasContactLinks' => false,
            'formCount' => 0,
            'forms' => [],
            'downloads' => [],
            'contactLinks' => [],
            'externalLinks' => [],
        ];
    }

    /**
     * Merges the per-page manual signals collected during the crawl into a single
     * ranked set used by {@see ManualSuggestionRecommender}.
     *
     * @param array<int, array{
     *   signals?: array{
     *     downloadExtensions?: array<string, int>, outlinkHosts?: array<string, int>,
     *     hasContactLinks?: bool, formCount?: int
     *   }
     * }> $pages
     * @return array{
     *   downloadExtensions: array<string, int>, outlinkHosts: array<string, int>,
     *   hasContactLinks: bool, formCount: int
     * }
     */
    private function aggregateManualSignals(array $pages): array
    {
        $signals = [
            'downloadExtensions' => [],
            'outlinkHosts' => [],
            'hasContactLinks' => false,
            'formCount' => 0,
        ];

        foreach ($pages as $page) {
            $pageSignals = $page['signals'] ?? [];

            foreach (($pageSignals['downloadExtensions'] ?? []) as $extension => $count) {
                $signals['downloadExtensions'][$extension] = ($signals['downloadExtensions'][$extension] ?? 0) + $count;
            }
            foreach (($pageSignals['outlinkHosts'] ?? []) as $linkHost => $count) {
                $signals['outlinkHosts'][$linkHost] = ($signals['outlinkHosts'][$linkHost] ?? 0) + $count;
            }
            $signals['hasContactLinks'] = $signals['hasContactLinks'] || !empty($pageSignals['hasContactLinks']);
            $signals['formCount'] += (int) ($pageSignals['formCount'] ?? 0);
        }

        arsort($signals['downloadExtensions']);
        arsort($signals['outlinkHosts']);
        $signals['outlinkHosts'] = array_slice($signals['outlinkHosts'], 0, 5, true);

        return $signals;
    }

    /**
     * @param array<int, array{url: string, signals?: array<string, mixed>}> $pages
     * @return array<int, array<string, mixed>>
     */
    private function rankForms(array $pages): array
    {
        $buckets = [];
        foreach ($pages as $page) {
            foreach (($page['signals']['forms'] ?? []) as $form) {
                if (!is_array($form)) {
                    continue;
                }
                $key = strtolower((string) ($form['action'] ?? '') . '|' . implode('|', $form['fields'] ?? []));
                if ($key === '|') {
                    continue;
                }
                if (!isset($buckets[$key])) {
                    $buckets[$key] = [
                        'action' => (string) ($form['action'] ?? ''),
                        'fields' => $form['fields'] ?? [],
                        'fieldTypes' => $form['fieldTypes'] ?? [],
                        'area' => (string) ($form['area'] ?? ''),
                        'submitTexts' => [],
                        'contexts' => [],
                        'sourcePages' => [],
                        'count' => 0,
                    ];
                }
                ++$buckets[$key]['count'];
                $buckets[$key]['sourcePages'][$page['url']] = true;
                $this->addUniqueSample($buckets[$key]['submitTexts'], (string) ($form['submitText'] ?? ''), 4);
                $this->addUniqueSample($buckets[$key]['contexts'], (string) ($form['context'] ?? ''), 3);
            }
        }

        return $this->finalizeRankedBuckets($buckets, function (array $bucket): int {
            return (int) $bucket['count'] * 4 + count($bucket['sourcePages']) * 3 + min(count($bucket['fields']), 4);
        });
    }

    /**
     * @param array<int, array{url: string, signals?: array<string, mixed>}> $pages
     * @return array<int, array<string, mixed>>
     */
    private function rankDownloads(array $pages): array
    {
        return $this->rankSignalItems($pages, 'downloads', 'href', function (array $bucket): int {
            return (int) $bucket['count'] * 4 + count($bucket['sourcePages']) * 2;
        });
    }

    /**
     * @param array<int, array{url: string, signals?: array<string, mixed>}> $pages
     * @return array<int, array<string, mixed>>
     */
    private function rankContactLinks(array $pages): array
    {
        return $this->rankSignalItems($pages, 'contactLinks', 'href', function (array $bucket): int {
            return (int) $bucket['count'] * 5 + count($bucket['sourcePages']) * 3;
        });
    }

    /**
     * @param array<int, array{url: string, signals?: array<string, mixed>}> $pages
     * @return array<int, array<string, mixed>>
     */
    private function rankExternalLinks(array $pages): array
    {
        return $this->rankSignalItems($pages, 'externalLinks', 'host', function (array $bucket): int {
            return (int) $bucket['count'] * 4 + count($bucket['sourcePages']) * 3;
        });
    }

    /**
     * @param array<int, array{url: string, signals?: array<string, mixed>}> $pages
     * @return array<int, array<string, mixed>>
     */
    private function rankSignalItems(array $pages, string $signalKey, string $bucketKey, callable $scoreCallback): array
    {
        $buckets = [];
        foreach ($pages as $page) {
            foreach (($page['signals'][$signalKey] ?? []) as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $key = strtolower((string) ($item[$bucketKey] ?? ''));
                if ($key === '') {
                    continue;
                }
                if (!isset($buckets[$key])) {
                    $buckets[$key] = [
                        $bucketKey => (string) ($item[$bucketKey] ?? ''),
                        'href' => (string) ($item['href'] ?? ''),
                        'host' => (string) ($item['host'] ?? ''),
                        'labels' => [],
                        'examples' => [],
                        'sourcePages' => [],
                        'count' => 0,
                    ];
                }
                ++$buckets[$key]['count'];
                $buckets[$key]['sourcePages'][$page['url']] = true;
                $this->addUniqueSample($buckets[$key]['labels'], (string) ($item['label'] ?? ''), 4);
                $this->addUniqueSample($buckets[$key]['examples'], (string) ($item['href'] ?? ''), 4);
            }
        }

        return $this->finalizeRankedBuckets($buckets, $scoreCallback);
    }

    /**
     * @param array<string, array<string, mixed>> $buckets
     * @return array<int, array<string, mixed>>
     */
    private function finalizeRankedBuckets(array $buckets, callable $scoreCallback): array
    {
        $ranked = [];
        foreach ($buckets as $bucket) {
            $bucket['sourcePages'] = array_slice(array_keys($bucket['sourcePages'] ?? []), 0, 6);
            $bucket['score'] = $scoreCallback($bucket);
            $ranked[] = $bucket;
        }

        usort($ranked, function (array $a, array $b): int {
            return (int) $b['score'] <=> (int) $a['score'];
        });

        return array_slice($ranked, 0, 20);
    }

    /**
     * @param string[] $samples
     */
    private function addUniqueSample(array &$samples, string $value, int $limit): void
    {
        $value = $this->truncateText($value, 160);
        if ($value === '') {
            return;
        }

        foreach ($samples as $sample) {
            if (strcasecmp($sample, $value) === 0) {
                return;
            }
        }

        if (count($samples) < $limit) {
            $samples[] = $value;
        }
    }

    /**
     * @param array{
     *   linkTarget: string, areaCounts: array<string, int>, occurrenceCount: int,
     *   buttonLikeCount: int, pageUrls: array<string, bool>
     * } $bucket
     */
    private function recommendationScore(array $bucket): int
    {
        $areaCounts = $bucket['areaCounts'];
        $score = min($bucket['occurrenceCount'], 20);
        $score += min(count($bucket['pageUrls']), 10);
        $score += $bucket['buttonLikeCount'] * 8;
        $score += ($areaCounts['main'] ?? 0) * 4;
        $score += ($areaCounts['section'] ?? 0) * 3;
        $score += ($areaCounts['form'] ?? 0) * 4;
        $score += min(($areaCounts['nav'] ?? 0), 5);
        $score += min(($areaCounts['footer'] ?? 0), 2);

        $path = $bucket['linkTarget'];
        $highIntentPattern = '/pricing|contact|signup|sign-up|register|demo|quote|checkout|cart'
            . '|subscribe|donat|enterprise|trial|get-started|installation/i';
        if (preg_match($highIntentPattern, $path)) {
            $score += 12;
        }
        if (preg_match('/privacy|terms|legal|cookie|login|signin|sign-in|blog$/i', $path)) {
            $score -= 10;
        }

        return $score;
    }

    /**
     * Resolves an href to a same-origin URL, or null when it should be skipped
     * (external host, anchor, mailto/tel/javascript/data scheme, or the bare root).
     */
    private function resolveSameOriginUrl(string $href, string $baseUrl, string $host): ?string
    {
        $href = trim($href);

        if ($href === '' || $href === '/' || strpos($href, '#') === 0) {
            return null;
        }

        $scheme = strtolower((string) parse_url($href, PHP_URL_SCHEME));
        if ($scheme !== '' && !in_array($scheme, ['http', 'https'], true)) {
            return null; // mailto:, tel:, javascript:, data:, …
        }

        if ($scheme !== '') {
            $linkHost = $this->getUrlAuthority($href);
            if (strcasecmp($linkHost, $host) !== 0) {
                return null; // external site
            }
            return $this->normalizeCrawlUrl($href);
        } elseif (strpos($href, '//') === 0) {
            // Protocol-relative URL: compare host explicitly.
            $linkHost = $this->getUrlAuthority('https:' . $href);
            if ($linkHost === '' || strcasecmp($linkHost, $host) !== 0) {
                return null;
            }
            return $this->normalizeCrawlUrl('https:' . $href);
        }

        $base = parse_url($baseUrl);
        $baseScheme = (string) ($base['scheme'] ?? 'https');
        $basePath = (string) ($base['path'] ?? '/');

        if (strpos($href, '/') === 0) {
            $path = $href;
        } else {
            $directory = preg_replace('#/[^/]*$#', '/', $basePath);
            $path = ($directory ?: '/') . $href;
        }

        $hadTrailingSlash = substr((string) strtok($path, '?#'), -1) === '/';
        $path = $this->normalizePath($path);
        if ($path === '/' || $path === '') {
            return null;
        }
        if ($hadTrailingSlash) {
            $path .= '/';
        }

        return $this->normalizeCrawlUrl($baseScheme . '://' . $host . $path);
    }

    private function normalizeCrawlUrl(string $url): ?string
    {
        $parts = parse_url($url);
        if (!is_array($parts) || empty($parts['host'])) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $rawPath = (string) ($parts['path'] ?? '/');
        $path = $this->normalizePath($rawPath);
        // keep a trailing slash for fetching: some servers answer 404 without it
        if ($path !== '/' && substr($rawPath, -1) === '/') {
            $path .= '/';
        }

        return $scheme . '://' . $this->getUrlAuthority($url) . $path;
    }

    /**
     * Lowercased host plus non-default port (e.g. "example.com:8443"), used as
     * the crawl's origin identity.
     */
    private function getUrlAuthority(string $url): string
    {
        $parts = parse_url($url);
        if (!is_array($parts) || empty($parts['host'])) {
            return '';
        }

        $host = strtolower((string) $parts['host']);
        $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
        $port = isset($parts['port']) ? (int) $parts['port'] : null;
        if ($port === null || ($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443)) {
            return $host;
        }

        return $host . ':' . $port;
    }

    private function normalizePath(string $path): string
    {
        $path = explode('?', $path, 2)[0];
        $path = explode('#', $path, 2)[0];
        $segments = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($segments);
                continue;
            }
            $segments[] = $segment;
        }

        $normalized = '/' . implode('/', $segments);

        return rtrim($normalized, '/') ?: '/';
    }

    /**
     * @param array{linkTarget: string, weight: int} $link
     */
    private function discoveryScore(array $link): int
    {
        $score = $link['weight'] + 1;
        $path = $link['linkTarget'];

        if (count(array_filter(explode('/', $path))) <= 2) {
            $score += 2;
        }
        if (preg_match('/privacy|terms|legal|cookie/i', $path)) {
            $score -= 4;
        }
        if (preg_match('#/20\d{2}/#', $path)) {
            --$score;
        }

        return $score;
    }

    private function detectArea(\DOMElement $element): string
    {
        $current = $element;
        while ($current->parentNode instanceof \DOMElement) {
            $name = strtolower($current->tagName);
            if (in_array($name, ['header', 'nav'], true)) {
                return 'nav';
            }
            if ($name === 'footer') {
                return 'footer';
            }
            if ($name === 'form') {
                return 'form';
            }
            if (in_array($name, ['main', 'article'], true)) {
                return 'main';
            }
            if ($name === 'section') {
                return 'section';
            }
            $current = $current->parentNode;
        }

        return 'other';
    }

    private function areaWeight(string $area): int
    {
        $weights = [
            'nav' => 5,
            'form' => 4,
            'main' => 3,
            'section' => 2,
            'footer' => 1,
        ];

        return $weights[$area] ?? 1;
    }

    private function isButtonLike(\DOMElement $element): bool
    {
        $role = strtolower($element->getAttribute('role'));
        $class = strtolower($element->getAttribute('class'));

        return $role === 'button' || preg_match('/\b(btn|button|cta)\b/', $class) === 1;
    }

    private function normalizeLinkText(string $text): string
    {
        $text = trim((string) preg_replace('/\s+/', ' ', $text));

        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, self::MAX_LINK_TEXT_LENGTH);
        }

        return substr($text, 0, self::MAX_LINK_TEXT_LENGTH);
    }

    private function truncateText(string $text, int $maxLength): string
    {
        $text = trim((string) preg_replace('/\s+/', ' ', $text));
        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $maxLength);
        }

        return substr($text, 0, $maxLength);
    }

    /**
     * Runs site-content detection against the already-fetched homepage response
     * (no extra HTTP request) and returns the display names of detected CMS /
     * technologies that are useful for goal recommendations.
     *
     * @param array<string, string>|array $headers
     * @return string[]
     */
    private function detectTechnologies(?int $idSite, string $html, array $headers): array
    {
        try {
            $this->siteContentDetector->detectContent(
                [SiteContentDetectionAbstract::TYPE_CMS],
                $idSite,
                ['data' => $html, 'headers' => $headers]
            );
        } catch (\Exception $e) {
            return [];
        }

        $technologies = [];

        foreach ($this->siteContentDetector->getDetectsByType(SiteContentDetectionAbstract::TYPE_CMS) as $detectionId) {
            $detection = $this->siteContentDetector->getSiteContentDetectionById($detectionId);
            if ($detection !== null) {
                $technologies[] = $detection::getName();
            }
        }

        return $technologies;
    }
}
