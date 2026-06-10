<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\Recommendations;

use Piwik\Piwik;

/**
 * Rule-based goal recommender inspired by the ID-6 simple-new prototype.
 *
 * It turns aggregated crawl signals into Matomo-compatible goal definitions.
 * URL goals are created directly from high-intent destinations; form, download,
 * contact, and outlink goals are included only when the crawl contains concrete
 * matching evidence.
 */
class DeterministicRecommender
{
    private const MAX_RECOMMENDATIONS = 5;
    private const MAX_GENERIC_RECOMMENDATIONS = 5;

    /**
     * @var array<int, string>
     */
    private const HIGH_INTENT_WORDS = [
        'contact',
        'quote',
        'demo',
        'trial',
        'signup',
        'sign-up',
        'register',
        'pricing',
        'plans',
        'checkout',
        'booking',
        'book',
        'apply',
        'donate',
        'enterprise',
        'download',
        'subscribe',
        'newsletter',
        'partner',
        'marketplace',
        'certification',
        'get-started',
        'appointment',
        'consultation',
    ];

    /**
     * Ordered by priority (highest business value first).
     *
     * @var array<int, array{category: string, needles: string[], nameKey: string, reasonKey: string, displayCategoryKey: string}>
     */
    private const RULES = [
        [
            'category' => 'checkout',
            'needles' => ['checkout', '/cart', 'basket', 'warenkorb', '/panier', 'panier', 'kasse'],
            'nameKey' => 'Goals_RecommendationCheckoutName',
            'reasonKey' => 'Goals_RecommendationCheckoutReason',
            'displayCategoryKey' => 'Goals_RecommendationCategoryPurchase',
        ],
        [
            'category' => 'signup',
            'needles' => ['sign-up', 'signup', 'sign_up', 'register', '/join', 'create-account', 'get-started', 'free-trial', '/trial', '/apply', 'application'],
            'nameKey' => 'Goals_RecommendationSignupName',
            'reasonKey' => 'Goals_RecommendationSignupReason',
            'displayCategoryKey' => 'Goals_RecommendationCategoryLeadGeneration',
        ],
        [
            'category' => 'contact',
            'needles' => ['contact', 'kontakt', '/contacto'],
            'nameKey' => 'Goals_RecommendationContactName',
            'reasonKey' => 'Goals_RecommendationContactReason',
            'displayCategoryKey' => 'Goals_RecommendationCategoryContact',
        ],
        [
            'category' => 'demo',
            'needles' => ['/demo', 'request-demo', 'book-a-demo', '/booking', 'book-now', 'appointment', 'schedule', 'get-a-quote', '/quote', 'consultation'],
            'nameKey' => 'Goals_RecommendationDemoName',
            'reasonKey' => 'Goals_RecommendationDemoReason',
            'displayCategoryKey' => 'Goals_RecommendationCategoryLeadGeneration',
        ],
        [
            'category' => 'pricing',
            'needles' => ['pricing', '/plans', '/preise', '/tarife', 'donate', '/donation', '/spenden'],
            'nameKey' => 'Goals_RecommendationPricingName',
            'reasonKey' => 'Goals_RecommendationPricingReason',
            'displayCategoryKey' => 'Goals_RecommendationCategoryHighIntentPage',
        ],
        [
            'category' => 'newsletter',
            'needles' => ['newsletter', 'subscribe', '/abonnieren'],
            'nameKey' => 'Goals_RecommendationNewsletterName',
            'reasonKey' => 'Goals_RecommendationNewsletterReason',
            'displayCategoryKey' => 'Goals_RecommendationCategoryLeadGeneration',
        ],
        [
            'category' => 'download',
            'needles' => ['/download', '/downloads', '.pdf'],
            'nameKey' => 'Goals_RecommendationDownloadName',
            'reasonKey' => 'Goals_RecommendationDownloadReason',
            'displayCategoryKey' => 'Goals_RecommendationCategoryDownload',
        ],
    ];

    /**
     * @param array<string, mixed> $analysis
     * @return array<int, array<string, mixed>>
     */
    public function recommend(array $analysis): array
    {
        $destinationGoals = $this->buildDestinationGoals($analysis);
        $formGoals = $this->buildFormGoals($analysis['forms'] ?? []);
        $downloadGoals = $this->buildDownloadGoals($analysis['downloads'] ?? []);
        $contactGoals = $this->buildContactGoals($analysis['contactLinks'] ?? []);
        $externalGoals = $this->buildExternalGoals($analysis['externalLinks'] ?? []);

        return $this->selectDiverseGoals([
            $destinationGoals,
            $formGoals,
            $downloadGoals,
            $contactGoals,
            $externalGoals,
        ]);
    }

    /**
     * @param array<string, mixed> $analysis
     * @return array<int, array<string, mixed>>
     */
    private function buildDestinationGoals(array $analysis): array
    {
        $links = $analysis['links'] ?? [];
        $technologies = $analysis['technologies'] ?? [];

        /** @var array<string, array<string, mixed>> $byCategory */
        $byCategory = [];

        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }

            $haystack = strtolower((string) ($link['linkTarget'] ?? '') . ' ' . (string) ($link['linkText'] ?? ''));

            foreach (self::RULES as $rule) {
                if (isset($byCategory[$rule['category']])) {
                    continue;
                }

                if ($this->matchesAnyNeedle($haystack, $rule['needles'])) {
                    $pattern = (string) ($link['linkTarget'] ?? '');
                    $byCategory[$rule['category']] = $this->buildGoal([
                        'name' => Piwik::translate($rule['nameKey']),
                        'matchAttribute' => 'url',
                        'pattern' => $pattern,
                        'reason' => Piwik::translate($rule['reasonKey']),
                        'source' => 'rule',
                        'category' => Piwik::translate($rule['displayCategoryKey']),
                        'exampleMatches' => [$pattern],
                        'sourcePages' => $link['exampleUrls'] ?? [],
                    ]);
                }
            }
        }

        $this->addEcommercePurchaseGoal($byCategory, $technologies);

        $ordered = [];
        foreach (self::RULES as $rule) {
            if (isset($byCategory[$rule['category']])) {
                $ordered[] = $byCategory[$rule['category']];
                unset($byCategory[$rule['category']]);
            }
        }
        foreach ($byCategory as $goal) {
            $ordered[] = $goal;
        }

        if (count($ordered) < self::MAX_RECOMMENDATIONS) {
            $ordered = array_merge($ordered, $this->buildGenericUrlGoals($links, array_column($ordered, 'pattern')));
        }

        return $ordered;
    }

    /**
     * @param array<int, array<string, mixed>> $links
     * @param string[] $patternsToSkip
     * @return array<int, array<string, mixed>>
     */
    private function buildGenericUrlGoals(array $links, array $patternsToSkip = []): array
    {
        $goals = [];
        $seen = array_fill_keys($patternsToSkip, true);

        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }

            $pattern = (string) ($link['linkTarget'] ?? '');
            if ($pattern === '' || isset($seen[$pattern]) || $this->isWeakGenericCandidate($link)) {
                continue;
            }

            $label = $this->getGenericGoalLabel($link, $pattern);
            $goals[] = $this->buildGoal([
                'name' => Piwik::translate('Goals_RecommendationKeyPageName', [$label]),
                'matchAttribute' => 'url',
                'pattern' => $pattern,
                'reason' => Piwik::translate('Goals_RecommendationKeyPageReason'),
                'source' => 'rule-generic',
                'category' => Piwik::translate('Goals_RecommendationCategoryHighIntentPage'),
                'exampleMatches' => [$pattern],
                'sourcePages' => $link['exampleUrls'] ?? [],
            ]);

            $seen[$pattern] = true;
            if (count($goals) >= self::MAX_GENERIC_RECOMMENDATIONS) {
                break;
            }
        }

        return $goals;
    }

    /**
     * @param array<int, array<string, mixed>> $forms
     * @return array<int, array<string, mixed>>
     */
    private function buildFormGoals(array $forms): array
    {
        $goals = [];

        foreach ($forms as $form) {
            if (!is_array($form)) {
                continue;
            }

            $label = $this->formGoalLabel($form);
            $goals[] = $this->buildGoal([
                'name' => Piwik::translate('Goals_RecommendationFormName', [$label]),
                'matchAttribute' => 'event_name',
                'pattern' => $label,
                'reason' => Piwik::translate('Goals_RecommendationFormReason'),
                'source' => 'rule-form',
                'category' => Piwik::translate('Goals_RecommendationCategoryLeadGeneration'),
                'allowMultipleConversionsPerVisit' => true,
                'implementationNote' => Piwik::translate('Goals_RecommendationFormSetupNote', [$label]),
                'evidence' => array_filter([
                    sprintf('%s form sightings across %s pages.', (string) ($form['count'] ?? 1), (string) count($form['sourcePages'] ?? [])),
                    (string) (($form['contexts'][0] ?? '') ?: ($form['submitTexts'][0] ?? '')),
                ]),
                'sourcePages' => $form['sourcePages'] ?? [],
                'exampleMatches' => $form['sourcePages'] ?? [],
            ]);
        }

        return $goals;
    }

    /**
     * @param array<int, array<string, mixed>> $downloads
     * @return array<int, array<string, mixed>>
     */
    private function buildDownloadGoals(array $downloads): array
    {
        $goals = [];

        foreach ($downloads as $download) {
            if (!is_array($download)) {
                continue;
            }

            $href = (string) ($download['href'] ?? '');
            $pattern = $this->downloadGoalPattern($href);
            if ($pattern === '') {
                continue;
            }

            $label = $this->titleFromText((string) (($download['labels'][0] ?? '') ?: $pattern));
            $goals[] = $this->buildGoal([
                'name' => Piwik::translate('Goals_RecommendationDownloadFileName', [$label]),
                'matchAttribute' => 'file',
                'pattern' => $pattern,
                'reason' => Piwik::translate('Goals_RecommendationDownloadReason'),
                'source' => 'rule-download',
                'category' => Piwik::translate('Goals_RecommendationCategoryDownload'),
                'allowMultipleConversionsPerVisit' => true,
                'implementationNote' => Piwik::translate('Goals_RecommendationDownloadSetupNote', [$pattern]),
                'evidence' => array_filter([
                    sprintf('%s download link sightings across %s pages.', (string) ($download['count'] ?? 1), (string) count($download['sourcePages'] ?? [])),
                    (string) ($download['labels'][0] ?? $href),
                ]),
                'sourcePages' => $download['sourcePages'] ?? [],
                'exampleMatches' => $download['examples'] ?? [$href],
            ]);
        }

        return $goals;
    }

    /**
     * @param array<int, array<string, mixed>> $contactLinks
     * @return array<int, array<string, mixed>>
     */
    private function buildContactGoals(array $contactLinks): array
    {
        $goals = [];

        foreach ($contactLinks as $link) {
            if (!is_array($link)) {
                continue;
            }

            $href = (string) ($link['href'] ?? '');
            if ($href === '') {
                continue;
            }

            $goals[] = $this->buildGoal([
                'name' => Piwik::translate('Goals_RecommendationContactLinkName'),
                'matchAttribute' => 'external_website',
                'pattern' => $href,
                'reason' => Piwik::translate('Goals_RecommendationContactLinkReason'),
                'source' => 'rule-contact-link',
                'category' => Piwik::translate('Goals_RecommendationCategoryContact'),
                'allowMultipleConversionsPerVisit' => true,
                'implementationNote' => Piwik::translate('Goals_RecommendationOutlinkSetupNote', [$href]),
                'evidence' => array_filter([
                    sprintf('%s contact link sightings across %s pages.', (string) ($link['count'] ?? 1), (string) count($link['sourcePages'] ?? [])),
                    (string) ($link['labels'][0] ?? $href),
                ]),
                'sourcePages' => $link['sourcePages'] ?? [],
                'exampleMatches' => [$href],
            ]);
        }

        return $goals;
    }

    /**
     * @param array<int, array<string, mixed>> $externalLinks
     * @return array<int, array<string, mixed>>
     */
    private function buildExternalGoals(array $externalLinks): array
    {
        $goals = [];

        foreach ($externalLinks as $link) {
            if (!is_array($link) || !$this->hasHighIntentText([$link['host'] ?? '', $link['labels'] ?? []])) {
                continue;
            }

            $host = (string) ($link['host'] ?? '');
            if ($host === '') {
                continue;
            }

            $label = $this->titleFromText((string) (($link['labels'][0] ?? '') ?: $host));
            $goals[] = $this->buildGoal([
                'name' => Piwik::translate('Goals_RecommendationExternalLinkName', [$label]),
                'matchAttribute' => 'external_website',
                'pattern' => $host,
                'reason' => Piwik::translate('Goals_RecommendationExternalLinkReason'),
                'source' => 'rule-external-link',
                'category' => Piwik::translate('Goals_RecommendationCategoryExternalConversion'),
                'allowMultipleConversionsPerVisit' => true,
                'implementationNote' => Piwik::translate('Goals_RecommendationOutlinkSetupNote', [$host]),
                'evidence' => array_filter([
                    sprintf('%s external links across %s pages.', (string) ($link['count'] ?? 1), (string) count($link['sourcePages'] ?? [])),
                    (string) ($link['labels'][0] ?? $host),
                ]),
                'sourcePages' => $link['sourcePages'] ?? [],
                'exampleMatches' => $link['examples'] ?? [],
            ]);
        }

        return $goals;
    }

    /**
     * @param array<int, array<int, array<string, mixed>>> $goalGroups
     * @return array<int, array<string, mixed>>
     */
    private function selectDiverseGoals(array $goalGroups): array
    {
        $result = [];
        $seen = [];
        $groups = array_values(array_filter($goalGroups));
        $index = 0;

        while (count($result) < self::MAX_RECOMMENDATIONS && $this->hasGroupItemAtIndex($groups, $index)) {
            foreach ($groups as $group) {
                if (count($result) >= self::MAX_RECOMMENDATIONS) {
                    break;
                }

                if (!isset($group[$index])) {
                    continue;
                }

                $goal = $group[$index];
                $key = $this->goalKey($goal);
                if ($key === '' || isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $result[] = $goal;
            }

            ++$index;
        }

        foreach ($result as $position => &$goal) {
            $goal['priority'] = $position + 1;
        }
        unset($goal);

        return $result;
    }

    /**
     * @param array<int, array<int, array<string, mixed>>> $groups
     */
    private function hasGroupItemAtIndex(array $groups, int $index): bool
    {
        foreach ($groups as $group) {
            if (isset($group[$index])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $link
     */
    private function isWeakGenericCandidate(array $link): bool
    {
        $pattern = strtolower((string) ($link['linkTarget'] ?? ''));
        $label = strtolower((string) ($link['linkText'] ?? ''));
        $score = (int) ($link['score'] ?? 0);
        $pageCount = (int) ($link['pageCount'] ?? 0);
        $buttonLikeCount = (int) ($link['buttonLikeCount'] ?? 0);

        if ($pattern === '' || $pattern === '/') {
            return true;
        }

        if (preg_match('#/(blog|news|about|login|sign-in|privacy|terms|legal|cookie|careers?|jobs?|press|imprint|sitemap)(/|$)#', $pattern)) {
            return true;
        }

        if (preg_match('/\b(blog|news|about|login|privacy|terms|legal|careers?|jobs?)\b/', $label)) {
            return true;
        }

        return $score < 12 && $pageCount < 2 && $buttonLikeCount < 1;
    }

    /**
     * @param array<string, mixed> $link
     */
    private function getGenericGoalLabel(array $link, string $pattern): string
    {
        $label = trim((string) ($link['linkText'] ?? ''));

        if ($label !== '') {
            $label = preg_split('/\s*\/\s*/', $label)[0] ?? $label;
            $label = trim($label);
        }

        if ($label === '') {
            $label = trim(str_replace(['-', '_'], ' ', basename($pattern)));
        }

        return $this->truncate($label, 50) ?: Piwik::translate('Goals_RecommendationKeyPageFallbackLabel');
    }

    /**
     * @param array<string, array<string, mixed>> $byCategory
     * @param string[] $technologies
     */
    private function addEcommercePurchaseGoal(array &$byCategory, array $technologies): void
    {
        $techHaystack = strtolower(implode(' ', $technologies));
        $isShopify = strpos($techHaystack, 'shopify') !== false;
        $isWordPress = strpos($techHaystack, 'wordpress') !== false;

        if (!$isShopify && !($isWordPress && isset($byCategory['checkout']))) {
            return;
        }

        $pattern = $isShopify ? '/thank_you' : '/order-received';

        $byCategory['purchase'] = $this->buildGoal([
            'name' => Piwik::translate('Goals_RecommendationPurchaseName'),
            'matchAttribute' => 'url',
            'pattern' => $pattern,
            'reason' => Piwik::translate('Goals_RecommendationPurchaseReason'),
            'source' => 'rule',
            'category' => Piwik::translate('Goals_RecommendationCategoryPurchase'),
            'exampleMatches' => [$pattern],
        ]);
    }

    /**
     * @param string[] $needles
     */
    private function matchesAnyNeedle(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $form
     */
    private function formGoalLabel(array $form): string
    {
        $labels = strtolower(implode(' ', array_merge(
            $form['submitTexts'] ?? [],
            $form['contexts'] ?? [],
            $form['fields'] ?? [],
            $form['sourcePages'] ?? []
        )));

        if (strpos($labels, 'demo') !== false) {
            return 'Demo request';
        }
        if (strpos($labels, 'contact') !== false && strpos($labels, 'sales') !== false) {
            return 'Contact sales';
        }
        if (strpos($labels, 'trial') !== false || strpos($labels, 'signup') !== false || strpos($labels, 'sign up') !== false) {
            return 'Free trial';
        }
        if (strpos($labels, 'newsletter') !== false || strpos($labels, 'subscribe') !== false) {
            return 'Newsletter signup';
        }
        if (strpos($labels, 'quote') !== false) {
            return 'Quote request';
        }
        if (strpos($labels, 'booking') !== false || strpos($labels, 'book ') !== false) {
            return 'Booking request';
        }

        $submitText = $this->titleFromText((string) ($form['submitTexts'][0] ?? ''));
        if ($submitText !== '' && strcasecmp($submitText, 'Submit') !== 0) {
            return $submitText;
        }

        return $this->readablePathName((string) (($form['sourcePages'][0] ?? '') ?: ($form['action'] ?? 'form')));
    }

    private function downloadGoalPattern(string $href): string
    {
        $path = (string) parse_url($href, PHP_URL_PATH);
        if ($path !== '') {
            return basename($path);
        }

        return basename(strtok($href, '?') ?: $href);
    }

    /**
     * @param array<int, mixed> $values
     */
    private function hasHighIntentText(array $values): bool
    {
        $haystack = strtolower(implode(' ', array_map(function ($value): string {
            return is_array($value) ? implode(' ', $value) : (string) $value;
        }, $values)));

        foreach (self::HIGH_INTENT_WORDS as $word) {
            if (strpos($haystack, $word) !== false) {
                return true;
            }
        }

        return false;
    }

    private function readablePathName(string $path): string
    {
        $parts = array_filter(explode('/', (string) parse_url($path, PHP_URL_PATH)));
        $label = implode(' ', array_slice($parts, -2));

        return $this->titleFromText($label ?: $path ?: 'Page');
    }

    private function titleFromText(string $value): string
    {
        $cleaned = preg_replace('/[-_]+/', ' ', $value);
        $cleaned = preg_replace('/\.[a-z0-9]+$/i', '', (string) $cleaned);
        $parts = array_slice(array_filter(explode(' ', trim((string) $cleaned))), 0, 6);
        $title = array_map(function (string $part): string {
            return ucfirst(strtolower($part));
        }, $parts);

        return implode(' ', $title);
    }

    /**
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    private function buildGoal(array $values): array
    {
        $matchAttribute = (string) ($values['matchAttribute'] ?? 'url');

        return [
            'name' => (string) ($values['name'] ?? ''),
            'matchAttribute' => $matchAttribute,
            'pattern' => (string) ($values['pattern'] ?? ''),
            'patternType' => $this->defaultPatternType($matchAttribute),
            'caseSensitive' => false,
            'allowMultipleConversionsPerVisit' => !empty($values['allowMultipleConversionsPerVisit']),
            'revenue' => 0,
            'useEventValueAsRevenue' => false,
            'reason' => (string) ($values['reason'] ?? ''),
            'description' => (string) ($values['reason'] ?? ''),
            'source' => (string) ($values['source'] ?? 'rule'),
            'category' => (string) ($values['category'] ?? Piwik::translate('Goals_RecommendationCategoryGoal')),
            'implementationNote' => (string) ($values['implementationNote'] ?? Piwik::translate('Goals_RecommendationDefaultSetupNote')),
            'evidence' => array_values($values['evidence'] ?? []),
            'sourcePages' => array_values($values['sourcePages'] ?? []),
            'exampleMatches' => array_values($values['exampleMatches'] ?? []),
        ];
    }

    private function defaultPatternType(string $matchAttribute): string
    {
        if (in_array($matchAttribute, ['visit_duration', 'visit_total_actions', 'visit_total_pageviews'], true)) {
            return 'greater_than';
        }

        return 'contains';
    }

    /**
     * @param array<string, mixed> $goal
     */
    private function goalKey(array $goal): string
    {
        $pattern = trim((string) ($goal['pattern'] ?? ''));
        if ($pattern === '') {
            return '';
        }

        if (($goal['matchAttribute'] ?? '') === 'url' && preg_match('#^https?://#i', $pattern)) {
            $path = parse_url($pattern, PHP_URL_PATH);
            $pattern = is_string($path) ? $path : $pattern;
        }

        return strtolower((string) ($goal['matchAttribute'] ?? '') . ':' . (string) ($goal['patternType'] ?? '') . ':' . trim(rtrim($pattern, '/'), '/'));
    }

    private function truncate(string $value, int $maxLength): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $maxLength);
        }

        return substr($value, 0, $maxLength);
    }
}
