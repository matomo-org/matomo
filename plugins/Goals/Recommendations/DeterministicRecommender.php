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
 * Rule-based goal recommender. Turns aggregated crawl signals into
 * Matomo-compatible goal definitions: URL goals from high-intent destinations,
 * and form/download/contact/outlink goals only when the crawl has concrete
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
     * @var array<int, array{category: string, needles: string[], nameKey: string, reasonKey: string}>
     */
    private const RULES = [
        [
            'category' => 'checkout',
            'needles' => ['checkout', '/cart', 'basket', 'warenkorb', '/panier', 'panier', 'kasse'],
            'nameKey' => 'Goals_RecommendationCheckoutName',
            'reasonKey' => 'Goals_RecommendationCheckoutReason',
        ],
        [
            'category' => 'signup',
            'needles' => [
                'sign-up', 'signup', 'sign_up', 'register', '/join', 'create-account', 'get-started',
                'free-trial', '/trial', '/apply', 'application',
            ],
            'nameKey' => 'Goals_RecommendationSignupName',
            'reasonKey' => 'Goals_RecommendationSignupReason',
        ],
        [
            'category' => 'contact',
            'needles' => ['contact', 'kontakt', '/contacto'],
            'nameKey' => 'Goals_RecommendationContactName',
            'reasonKey' => 'Goals_RecommendationContactReason',
        ],
        [
            'category' => 'demo',
            'needles' => [
                '/demo', 'request-demo', 'book-a-demo', '/booking', 'book-now', 'appointment',
                'schedule', 'get-a-quote', '/quote', 'consultation',
            ],
            'nameKey' => 'Goals_RecommendationDemoName',
            'reasonKey' => 'Goals_RecommendationDemoReason',
        ],
        [
            'category' => 'pricing',
            'needles' => ['pricing', '/plans', '/preise', '/tarife', 'donate', '/donation', '/spenden'],
            'nameKey' => 'Goals_RecommendationPricingName',
            'reasonKey' => 'Goals_RecommendationPricingReason',
        ],
        [
            'category' => 'newsletter',
            'needles' => ['newsletter', 'subscribe', '/abonnieren'],
            'nameKey' => 'Goals_RecommendationNewsletterName',
            'reasonKey' => 'Goals_RecommendationNewsletterReason',
        ],
        [
            'category' => 'download',
            'needles' => ['/download', '/downloads', '.pdf'],
            'nameKey' => 'Goals_RecommendationDownloadName',
            'reasonKey' => 'Goals_RecommendationDownloadReason',
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
            $pattern = $this->formGoalPattern($form);
            $label = $this->formGoalLabel($form, $pattern);
            $goals[] = $this->buildGoal([
                'name' => Piwik::translate('Goals_RecommendationFormName', [$label]),
                'matchAttribute' => 'event_name',
                'pattern' => $pattern,
                'reason' => Piwik::translate('Goals_RecommendationFormReason'),
                'source' => 'rule-form',
                'allowMultipleConversionsPerVisit' => true,
                'implementationNote' => Piwik::translate('Goals_RecommendationFormSetupNote', [$pattern]),
                'evidence' => array_filter([
                    Piwik::translate('Goals_RecommendationEvidenceFormSightings', [
                        (string) ($form['count'] ?? 1),
                        (string) count($form['sourcePages'] ?? []),
                    ]),
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
                'allowMultipleConversionsPerVisit' => true,
                'implementationNote' => Piwik::translate('Goals_RecommendationDownloadSetupNote', [$pattern]),
                'evidence' => array_filter([
                    Piwik::translate('Goals_RecommendationEvidenceDownloadSightings', [
                        (string) ($download['count'] ?? 1),
                        (string) count($download['sourcePages'] ?? []),
                    ]),
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
                'allowMultipleConversionsPerVisit' => true,
                'implementationNote' => Piwik::translate('Goals_RecommendationOutlinkSetupNote', [$href]),
                'evidence' => array_filter([
                    Piwik::translate('Goals_RecommendationEvidenceContactSightings', [
                        (string) ($link['count'] ?? 1),
                        (string) count($link['sourcePages'] ?? []),
                    ]),
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
            if (!$this->hasHighIntentText([$link['host'] ?? '', $link['labels'] ?? []])) {
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
                'allowMultipleConversionsPerVisit' => true,
                'implementationNote' => Piwik::translate('Goals_RecommendationOutlinkSetupNote', [$host]),
                'evidence' => array_filter([
                    Piwik::translate('Goals_RecommendationEvidenceExternalLinks', [
                        (string) ($link['count'] ?? 1),
                        (string) count($link['sourcePages'] ?? []),
                    ]),
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

        $weakPathPattern = '#/(blog|news|about|login|sign-in|privacy|terms|legal|cookie'
            . '|careers?|jobs?|press|imprint|sitemap)(/|$)#';
        if (preg_match($weakPathPattern, $pattern)) {
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
    private function formGoalLabel(array $form, string $pattern): string
    {
        $labels = strtolower(implode(' ', array_merge(
            $form['submitTexts'] ?? [],
            $form['contexts'] ?? [],
            $form['fields'] ?? [],
            $form['sourcePages'] ?? []
        )));

        if (strpos($labels, 'demo') !== false) {
            return Piwik::translate('Goals_RecommendationDemoRequestLabel');
        }
        if (strpos($labels, 'contact') !== false && strpos($labels, 'sales') !== false) {
            return Piwik::translate('Goals_RecommendationContactSalesLabel');
        }
        if (
            strpos($labels, 'trial') !== false
            || strpos($labels, 'signup') !== false
            || strpos($labels, 'sign up') !== false
        ) {
            return Piwik::translate('Goals_RecommendationFreeTrialLabel');
        }
        if (strpos($labels, 'newsletter') !== false || strpos($labels, 'subscribe') !== false) {
            return Piwik::translate('Goals_RecommendationNewsletterName');
        }
        if (strpos($labels, 'quote') !== false) {
            return Piwik::translate('Goals_RecommendationQuoteRequestLabel');
        }
        if (strpos($labels, 'booking') !== false || strpos($labels, 'book ') !== false) {
            return Piwik::translate('Goals_RecommendationBookingRequestLabel');
        }

        return $pattern === 'form'
            ? Piwik::translate('Goals_RecommendationFormFallbackLabel')
            : $pattern;
    }

    /**
     * @param array<string, mixed> $form
     */
    private function formGoalPattern(array $form): string
    {
        $submitText = $this->titleFromText((string) ($form['submitTexts'][0] ?? ''));
        if ($submitText !== '' && strcasecmp($submitText, 'Submit') !== 0) {
            return $submitText;
        }

        $source = (string) (($form['sourcePages'][0] ?? '') ?: ($form['action'] ?? ''));

        return $source !== ''
            ? $this->readablePathName($source)
            : 'form';
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

        return $this->titleFromText($label ?: $path);
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
            'implementationNote' => (string) ($values['implementationNote']
                ?? Piwik::translate('Goals_RecommendationDefaultSetupNote')),
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

        return strtolower(
            (string) ($goal['matchAttribute'] ?? '')
                . ':' . (string) ($goal['patternType'] ?? '')
                . ':' . trim(rtrim($pattern, '/'), '/')
        );
    }

    private function truncate(string $value, int $maxLength): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $maxLength);
        }

        return substr($value, 0, $maxLength);
    }
}
