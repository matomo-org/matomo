<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\Recommendations;

use Piwik\Config\GeneralConfig;
use Piwik\Container\StaticContainer;
use Piwik\Http;
use Piwik\Site;
use Piwik\SiteContentDetector;
use Piwik\Plugins\SitesManager\SiteContentDetection\SiteContentDetectionAbstract;
use Psr\Log\LoggerInterface;

/**
 * Fetches a small same-origin slice of a site and turns it into the minimal,
 * safe signal set used to recommend URL goals: ranked destination paths plus
 * the names of any detected site technologies (CMS, …).
 *
 * Only same-origin pages under the site's configured main URL are fetched, and
 * only reduced path/link metadata (never raw HTML) leaves this class, in line with the feature's
 * prompt-injection and privacy constraints.
 */
class HomepageAnalyzer
{
    /**
     * Upper bound on the number of links handed downstream, to keep prompt size
     * and processing predictable.
     */
    public const MAX_LINKS = 60;

    private const MAX_PAGES = 50;
    private const MAX_LINKS_PER_PAGE = 40;
    private const MAX_LINK_TEXT_LENGTH = 120;

    /**
     * A descriptive, browser-like user agent: some sites reject requests with an
     * empty or obviously non-browser user agent.
     */
    private const USER_AGENT = 'Mozilla/5.0 (compatible; MatomoGoalRecommendations/1.0)';

    /**
     * @var SiteContentDetector
     */
    private $siteContentDetector;

    public function __construct(?SiteContentDetector $siteContentDetector = null)
    {
        $this->siteContentDetector = $siteContentDetector ?? new SiteContentDetector();
    }

    /**
     * Fetches and analyses the homepage of the given site.
     *
     * @return array<string, mixed>|null
     *         Null when the homepage cannot be fetched (internet features disabled,
     *         no main URL configured, or the site is unreachable).
     */
    public function analyze(int $idSite, int $timeout = 5): ?array
    {
        $url = Site::getMainUrlFor($idSite);

        if (empty($url)) {
            return null;
        }

        $startUrl = $this->normalizeCrawlUrl($url);
        if ($startUrl === null) {
            return null;
        }

        $response = $this->fetchHomepage($startUrl, $timeout);

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

        $host = (string) parse_url($startUrl, PHP_URL_HOST);
        if ($host === '') {
            return null;
        }

        $crawl = $this->crawlSameOriginPages($startUrl, $host, $html, $timeout);
        $links = $this->rankLinks($crawl['pages']);

        $this->getLogger()->debug(
            'Goals recommendations: analysed {url} (HTTP status {status}, {bytes} bytes, {pages} pages, {links} ranked links).',
            ['url' => $startUrl, 'status' => $status, 'bytes' => strlen($html), 'pages' => count($crawl['pages']), 'links' => count($links)]
        );

        return [
            'url' => $startUrl,
            'links' => $links,
            'forms' => $this->rankForms($crawl['pages']),
            'downloads' => $this->rankDownloads($crawl['pages']),
            'contactLinks' => $this->rankContactLinks($crawl['pages']),
            'externalLinks' => $this->rankExternalLinks($crawl['pages']),
            'technologies' => $this->detectTechnologies($idSite, $html, $response['headers'] ?? []),
            'pagesCrawled' => count($crawl['pages']),
            'crawledPages' => $this->buildCrawledPagesDebug($crawl['pages']),
            'manualSignals' => $this->aggregateManualSignals($crawl['pages']),
            'errors' => $crawl['errors'],
        ];
    }

    /**
     * @return array{status?: ?int, headers?: ?array, data?: ?string}|null
     */
    private function fetchHomepage(string $url, int $timeout): ?array
    {
        // Respect the same internet-features kill switch SiteContentDetector honours.
        if (0 === GeneralConfig::getIntegerConfigValue('enable_internet_features', 0)) {
            $this->getLogger()->debug('Goals recommendations: internet features are disabled; skipping homepage fetch.');
            return null;
        }

        try {
            return Http::sendHttpRequest(
                $url,
                $timeout,
                self::USER_AGENT,
                null,
                0,
                false,
                false,
                true // $getExtendedInfo: returns ['status', 'headers', 'data']
            );
        } catch (\Exception $e) {
            $this->getLogger()->debug(
                'Goals recommendations: homepage fetch failed for {url}: {message}',
                ['url' => $url, 'message' => $e->getMessage()]
            );
            return null;
        }
    }

    private function getLogger(): LoggerInterface
    {
        return StaticContainer::get(LoggerInterface::class);
    }

    /**
     * @return array{pages: array<int, array{url: string, links: array<int, array{linkText: string, linkTarget: string, url: string, area: string, isButtonLike: bool, weight: int}>, signals: array<string, mixed>}>, errors: string[]}
     */
    private function crawlSameOriginPages(string $startUrl, string $host, string $homepageHtml, int $timeout): array
    {
        $queue = [$startUrl];
        $queued = [$startUrl => true];
        $visited = [];
        $pages = [];
        $errors = [];
        $htmlByUrl = [$startUrl => $homepageHtml];

        while (!empty($queue) && count($pages) < self::MAX_PAGES) {
            $currentUrl = array_shift($queue);
            if (!is_string($currentUrl) || isset($visited[$currentUrl])) {
                continue;
            }

            $visited[$currentUrl] = true;
            $html = $htmlByUrl[$currentUrl] ?? null;

            if ($html === null) {
                $response = $this->fetchHomepage($currentUrl, $timeout);
                $html = is_array($response) ? (string) ($response['data'] ?? '') : '';
            }

            if ($html === '') {
                $errors[] = sprintf('Could not fetch %s', $currentUrl);
                continue;
            }

            $xpath = $this->loadXpath($html);
            $links = $xpath !== null ? $this->extractLinks($xpath, $currentUrl, $host) : [];
            $signals = $xpath !== null ? $this->extractManualSignals($xpath, $currentUrl, $host) : $this->emptyManualSignals();
            $pages[] = ['url' => $currentUrl, 'links' => $links, 'signals' => $signals];

            usort($links, function (array $a, array $b): int {
                return $this->discoveryScore($b) <=> $this->discoveryScore($a);
            });

            foreach (array_slice($links, 0, self::MAX_LINKS_PER_PAGE) as $link) {
                $url = $link['url'];
                if (isset($queued[$url]) || isset($visited[$url])) {
                    continue;
                }

                $queue[] = $url;
                $queued[$url] = true;
            }
        }

        return ['pages' => $pages, 'errors' => $errors];
    }

    /**
     * Aggregates repeated same-origin links into ranked destination signals.
     *
     * @param array<int, array{url: string, links: array<int, array{linkText: string, linkTarget: string, url: string, area: string, isButtonLike: bool, weight: int}>}> $pages
     * @return array<int, array{linkText: string, linkTarget: string, score: int, pageCount: int, occurrenceCount: int, areas: string[], labelSamples: string[], exampleUrls: string[], buttonLikeCount: int}>
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
            ];
        }

        usort($ranked, function (array $a, array $b): int {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($ranked, 0, self::MAX_LINKS);
    }

    /**
     * Loads HTML into a DOMXPath once so links and manual-goal signals can be
     * extracted from the same parse. Returns null when the HTML cannot be parsed.
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
     * @return array<int, array{linkText: string, linkTarget: string, url: string, area: string, isButtonLike: bool, weight: int}>
     */
    private function extractLinks(\DOMXPath $xpath, string $baseUrl, string $host): array
    {
        $anchors = $xpath->query('//a[@href]');

        if ($anchors === false) {
            return [];
        }

        $links = [];

        foreach ($anchors as $anchor) {
            if (!$anchor instanceof \DOMElement) {
                continue;
            }

            $url = $this->resolveSameOriginUrl($anchor->getAttribute('href'), $baseUrl, $host);
            if ($url === null) {
                continue;
            }

            $target = (string) parse_url($url, PHP_URL_PATH);
            if ($target === '' || $target === '/') {
                continue;
            }

            $area = $this->detectArea($anchor);
            $isButtonLike = $this->isButtonLike($anchor);
            $linkText = $this->normalizeLinkText($anchor->textContent);
            $key = strtolower($target . '|' . $linkText . '|' . $area);
            if (isset($links[$key])) {
                continue;
            }

            $links[$key] = [
                'linkText' => $linkText,
                'linkTarget' => $target,
                'url' => $url,
                'area' => $area,
                'isButtonLike' => $isButtonLike,
                'weight' => $this->areaWeight($area) + ($isButtonLike ? 2 : 0),
            ];

            if (count($links) >= self::MAX_LINKS_PER_PAGE) {
                break;
            }
        }

        return array_values($links);
    }

    /**
     * Extracts non-URL signals from a parsed page: content forms, file downloads,
     * outbound link hosts, and mailto/tel contact links. Only compact, aggregated
     * metadata leaves here, never raw HTML.
     *
     * @return array{downloadExtensions: array<string, int>, outlinkHosts: array<string, int>, hasContactLinks: bool, formCount: int, forms: array<int, array<string, mixed>>, downloads: array<int, array<string, mixed>>, contactLinks: array<int, array<string, mixed>>, externalLinks: array<int, array<string, mixed>>}
     */
    private function extractManualSignals(\DOMXPath $xpath, string $baseUrl, string $host): array
    {
        $downloadExtensions = [];
        $outlinkHosts = [];
        $hasContactLinks = false;
        $downloads = [];
        $contactLinks = [];
        $externalLinks = [];
        $bareHost = (string) preg_replace('/^www\./', '', strtolower($host));

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
                    $linkHost = (string) preg_replace('/^www\./', '', strtolower((string) parse_url($href, PHP_URL_HOST)));
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
     * @return array<int, array{action: string, method: string, submitText: string, fields: string[], area: string, context: string}>
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
                'area' => $this->detectArea($form),
                'context' => $this->truncateText($form->textContent, 140),
            ];
        }

        return array_slice($result, 0, 4);
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
        $submitControls = $xpath->query('.//button[@type="submit"] | .//button[not(@type)] | .//input[@type="submit"]', $form);
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
        $host = (string) $base['host'];
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
     * @return array{downloadExtensions: array<string, int>, outlinkHosts: array<string, int>, hasContactLinks: bool, formCount: int, forms: array<int, array<string, mixed>>, downloads: array<int, array<string, mixed>>, contactLinks: array<int, array<string, mixed>>, externalLinks: array<int, array<string, mixed>>}
     */
    private function emptyManualSignals(): array
    {
        return [
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
     * @param array<int, array{signals?: array{downloadExtensions?: array<string, int>, outlinkHosts?: array<string, int>, hasContactLinks?: bool, formCount?: int}}> $pages
     * @return array{downloadExtensions: array<string, int>, outlinkHosts: array<string, int>, hasContactLinks: bool, formCount: int}
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
     * @param array{linkTarget: string, areaCounts: array<string, int>, occurrenceCount: int, buttonLikeCount: int, pageUrls: array<string, bool>} $bucket
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
        if (preg_match('/pricing|contact|signup|sign-up|register|demo|quote|checkout|cart|subscribe|donat|enterprise|trial|get-started|installation/i', $path)) {
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
            $linkHost = (string) parse_url($href, PHP_URL_HOST);
            if (strcasecmp($linkHost, $host) !== 0) {
                return null; // external site
            }
            return $this->normalizeCrawlUrl($href);
        } elseif (strpos($href, '//') === 0) {
            // Protocol-relative URL: compare host explicitly.
            $linkHost = (string) parse_url('https:' . $href, PHP_URL_HOST);
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

        $path = $this->normalizePath($path);
        if ($path === '/' || $path === '') {
            return null;
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

        $host = strtolower((string) $parts['host']);
        $path = isset($parts['path']) ? $this->normalizePath((string) $parts['path']) : '/';

        return $scheme . '://' . $host . $path;
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
     * @param array<int, array<string, mixed>> $pages
     * @return array<int, array<string, mixed>>
     */
    private function buildCrawledPagesDebug(array $pages): array
    {
        return array_map(function (array $page): array {
            return [
                'url' => $page['url'],
                'linkCount' => count($page['links']),
                'formCount' => count($page['signals']['forms'] ?? []),
                'downloadCount' => count($page['signals']['downloads'] ?? []),
                'contactCount' => count($page['signals']['contactLinks'] ?? []),
                'externalLinkCount' => count($page['signals']['externalLinks'] ?? []),
                'sampleLinks' => array_map(function (array $link): array {
                    return [
                        'linkText' => $link['linkText'],
                        'linkTarget' => $link['linkTarget'],
                        'area' => $link['area'],
                        'isButtonLike' => $link['isButtonLike'],
                    ];
                }, array_slice($page['links'], 0, 10)),
            ];
        }, $pages);
    }

    /**
     * Runs site-content detection against the already-fetched homepage response
     * (no extra HTTP request) and returns the display names of detected CMS /
     * technologies that are useful for goal recommendations.
     *
     * @param array<string, string>|array $headers
     * @return string[]
     */
    private function detectTechnologies(int $idSite, string $html, array $headers): array
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
