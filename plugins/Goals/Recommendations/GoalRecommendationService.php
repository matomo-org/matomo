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
use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Development;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\AIProviders\Exception\AIProviderClientException;
use Piwik\Plugins\CorePluginsAdmin\CorePluginsAdmin;
use Psr\Log\LoggerInterface;

/**
 * Orchestrates goal recommendations: analyse the homepage once, then either run
 * the AI recommender (when the user opted in and a provider is available) or the
 * deterministic rules. Scan results are persisted per site so they can be shown
 * again without re-running the scan.
 */
class GoalRecommendationService
{
    private const CONSENT_KEY_PREFIX = 'Goals.aiRecommendationsConsent.';

    private const SCAN_LOCK_TTL_SECONDS = 60;

    private const SITE_SCAN_LOCK_NAMESPACE = 'Goals.recommendationScan.site.';

    private const USER_SCAN_LOCK_NAMESPACE = 'Goals.recommendationScan.user.';

    /** A scan with at least this many goals needs no explanation. */
    private const WARN_BELOW_GOALS = 4;

    /** Bot protection: this many refused pages on a thin result. */
    private const BLOCKED_PAGES_WARNING = 3;

    /** Share of crawled pages whose navigation is rendered in the browser (the homepage alone also counts). */
    private const CLIENT_SIDE_SHARE_WARNING = 0.3;

    /** Time budget ran out before this many pages: the crawl stalled. */
    private const STALLED_BELOW_PAGES = 10;

    /** Share of attempted fetches that failed. */
    private const FAILED_FETCH_SHARE_WARNING = 0.34;

    /** Fewer goals than this on a clean crawl: the site offers few trackable actions. */
    private const FEW_CONVERSIONS_BELOW_GOALS = 3;

    /**
     * @var HomepageAnalyzer
     */
    private $homepageAnalyzer;

    /**
     * @var DeterministicRecommender
     */
    private $deterministicRecommender;

    /**
     * @var ManualSuggestionRecommender
     */
    private $manualRecommender;

    /**
     * @var RecommendationStore
     */
    private $store;

    /**
     * @var LockBackend
     */
    private $lockBackend;

    /**
     * @var AiRecommender|null
     */
    private $aiRecommender = null;
    public function __construct(
        HomepageAnalyzer $homepageAnalyzer,
        DeterministicRecommender $deterministicRecommender,
        ManualSuggestionRecommender $manualRecommender,
        RecommendationStore $store,
        LockBackend $lockBackend,
        ?AiRecommender $aiRecommender = null
    ) {
        $this->homepageAnalyzer = $homepageAnalyzer;
        $this->deterministicRecommender = $deterministicRecommender;
        $this->manualRecommender = $manualRecommender;
        $this->store = $store;
        $this->lockBackend = $lockBackend;
        $this->aiRecommender = $aiRecommender;
    }

    /**
     * Returns fresh goal recommendations for a site and persists them so they can
     * be retrieved again via {@link getSavedRecommendations()}.
     *
     * @param array<int|string, array<string, mixed>> $existingGoals
     * @return array{
     *   mode: string, goals: array<int, array<string, mixed>>,
     *   manualGoals: array<int, array{name: string, howTo: string, category: string}>,
     *   warnings: array<int, array{type: string, severity: string, message: string}>,
     *   aiError: ?string, generatedAt: ?int, remainingAiScans: ?int,
     *   providerName: string, aiAvailability: string, privacyNote: string,
     *   debug: ?array<string, mixed>
     * }
     */
    public function getRecommendations(int $idSite, bool $useAi, array $existingGoals = []): array
    {
        [$userLock, $siteLock] = $this->acquireScanLocks($idSite);

        try {
            return $this->generateRecommendations($idSite, $useAi, $existingGoals);
        } finally {
            try {
                $siteLock->unlock();
            } finally {
                $userLock->unlock();
            }
        }
    }

    /**
     * @param array<int|string, array<string, mixed>> $existingGoals
     * @return array{
     *   mode: string, goals: array<int, array<string, mixed>>,
     *   manualGoals: array<int, array{name: string, howTo: string, category: string}>,
     *   warnings: array<int, array{type: string, severity: string, message: string}>,
     *   aiError: ?string, generatedAt: ?int, remainingAiScans: ?int,
     *   providerName: string, aiAvailability: string, privacyNote: string,
     *   debug: ?array<string, mixed>
     * }
     */
    private function generateRecommendations(int $idSite, bool $useAi, array $existingGoals): array
    {
        $existingGoalSummaries = $this->getExistingGoalSummaries($existingGoals);
        $analysis = $this->homepageAnalyzer->analyze($idSite);
        if ($analysis === null) {
            $this->getLogger()->info(
                'Goals recommendations: could not analyse the homepage for site {idSite}.',
                ['idSite' => $idSite]
            );
            $warnings = $this->buildWarnings(['crawl' => $this->homepageAnalyzer->getLastCrawlStats()], []);

            return [
                'mode' => 'deterministic',
                'goals' => [],
                'manualGoals' => [],
                'warnings' => $warnings,
                // the blocked warning already explains the empty result
                'aiError' => empty($warnings) ? Piwik::translate('Goals_RecommendCouldNotAnalyze') : null,
                'generatedAt' => null,
                'remainingAiScans' => $this->getRemainingAiScans($idSite),
                'providerName' => $this->getConfiguredProviderName(),
                'aiAvailability' => $this->getAiAvailability(),
                'privacyNote' => $this->getPrivacyNote(),
                'debug' => null,
            ];
        }

        $deterministic = $this->assignRecommendationIds($this->filterExistingGoals(
            $this->deterministicRecommender->recommend($analysis),
            $existingGoalSummaries
        ));

        $mode = 'deterministic';
        $goals = $deterministic;
        $aiError = null;

        $dailyLimit = $this->getDailyAiScanLimit();
        if ($useAi && $dailyLimit > 0 && $this->store->countAiScansToday($idSite) >= $dailyLimit) {
            $aiError = Piwik::translate('Goals_RecommendationAiDailyLimitReached', $dailyLimit);
        } elseif ($useAi) {
            if ($this->isAiAvailable()) {
                $this->recordAiConsent();
                $aiRecommender = $this->getAiRecommender();
                try {
                    $aiGoals = $this->filterExistingGoals(
                        $aiRecommender->recommend($analysis, $idSite, $existingGoalSummaries, $deterministic),
                        $existingGoalSummaries
                    );
                    // Only count scans where the provider actually responded, so failures don't burn quota
                    $this->store->recordAiScan($idSite);
                    if (!empty($aiGoals)) {
                        $goals = $aiGoals;
                        $mode = 'ai';
                    }
                } catch (\InvalidArgumentException $e) {
                    // No / unknown provider configured.
                    $this->getLogger()->info(
                        'Goals recommendations: AI provider unavailable: {message}',
                        ['message' => $e->getMessage()]
                    );
                    $aiError = Piwik::translate('Goals_RecommendationAiUnavailable');
                } catch (AIProviderClientException $e) {
                    $this->getLogger()->warning(
                        'Goals recommendations: AI request failed: {message}',
                        ['message' => $e->getMessage()]
                    );
                    $aiError = Piwik::hasUserSuperUserAccess()
                        ? $e->getMessage()
                        : Piwik::translate('Goals_RecommendationAiProviderIssue');
                } catch (\Exception $e) {
                    // provider/network messages can carry raw response excerpts, so only superusers see them
                    $this->getLogger()->warning(
                        'Goals recommendations: AI request failed: {message}',
                        ['message' => $e->getMessage()]
                    );
                    $aiError = Piwik::hasUserSuperUserAccess()
                        ? $e->getMessage()
                        : Piwik::translate('Goals_RecommendationAiRequestFailed');
                }
            } else {
                $aiError = Piwik::translate('Goals_RecommendationAiUnavailable');
            }
        }

        $goals = $this->assignRecommendationIds($goals);
        $manualGoals = $this->filterManualSuggestions($this->manualRecommender->recommend($analysis), $goals);
        $warnings = $this->buildWarnings($analysis, $goals);
        $saved = $this->store->save($idSite, $useAi, $mode, $goals, $manualGoals, $warnings);

        return [
            'mode' => $mode,
            'goals' => $goals,
            'manualGoals' => $manualGoals,
            'warnings' => $warnings,
            'aiError' => $aiError,
            'generatedAt' => $saved['generatedAt'],
            'remainingAiScans' => $this->getRemainingAiScans($idSite),
            'providerName' => $this->getConfiguredProviderName(),
            'aiAvailability' => $this->getAiAvailability(),
            'privacyNote' => $this->getPrivacyNote(),
            'debug' => $this->buildDebugPayload($analysis),
        ];
    }

    /**
     * Explains the result to the user. At most one warning, the most severe cause
     * first: the site refused the crawler, its pages are rendered in the browser, the
     * time budget ran out, or the site simply offers few trackable actions. A scan
     * that found enough goals only keeps the browser-rendering note, since that one
     * says the suggestions may be incomplete rather than that something went wrong.
     *
     * @param array<string, mixed> $analysis
     * @param array<int, array<string, mixed>> $goals
     * @return array<int, array{type: string, severity: string, message: string}>
     */
    private function buildWarnings(array $analysis, array $goals): array
    {
        $crawl = array_merge(
            ['blockedPages' => 0, 'failedFetches' => 0, 'clientSideRenderedPages' => 0, 'homepageClientSideRendered' => false, 'deadlineReached' => false],
            (array) ($analysis['crawl'] ?? [])
        );
        $pagesCrawled = (int) ($analysis['pagesCrawled'] ?? 0);
        $attempted = $pagesCrawled + $crawl['blockedPages'] + $crawl['failedFetches'];

        if ($pagesCrawled === 0 && $crawl['blockedPages'] > 0) {
            return [$this->warning('blocked', 'warning', 'Goals_RecommendScanBlocked')];
        }
        $isClientSideRendered = $crawl['homepageClientSideRendered']
            || ($pagesCrawled > 0 && $crawl['clientSideRenderedPages'] / $pagesCrawled >= self::CLIENT_SIDE_SHARE_WARNING);
        if (count($goals) >= self::WARN_BELOW_GOALS) {
            // a good result needs no excuse, but the owner should still know the crawl saw only part of the site
            return $isClientSideRendered ? [$this->warning('partialRead', 'info', 'Goals_RecommendScanPartialRead')] : [];
        }
        if ($crawl['blockedPages'] >= self::BLOCKED_PAGES_WARNING) {
            return [$this->warning('blocked', 'warning', 'Goals_RecommendScanBlocked')];
        }
        if ($isClientSideRendered) {
            return [$this->warning('partialRead', 'info', 'Goals_RecommendScanPartialRead')];
        }
        if (
            ($crawl['deadlineReached'] && $pagesCrawled < self::STALLED_BELOW_PAGES)
            || ($attempted > 0 && $crawl['failedFetches'] / $attempted > self::FAILED_FETCH_SHARE_WARNING)
        ) {
            return [$this->warning('stoppedEarly', 'info', 'Goals_RecommendScanStoppedEarly', [$pagesCrawled])];
        }
        if ($pagesCrawled > 0 && count($goals) < self::FEW_CONVERSIONS_BELOW_GOALS) {
            return [$this->warning('fewConversions', 'info', 'Goals_RecommendScanFewConversions', [$pagesCrawled])];
        }

        return [];
    }

    /**
     * @param array<int, string|int> $args
     * @return array{type: string, severity: string, message: string}
     */
    private function warning(string $type, string $severity, string $translationKey, array $args = []): array
    {
        return [
            'type' => $type,
            'severity' => $severity,
            'message' => Piwik::translate($translationKey, array_map('strval', $args)),
        ];
    }

    /**
     * TEMPORARY (ID-277 debugging): what the crawl found and every candidate the
     * recommenders could choose from. Development mode only, never persisted.
     *
     * @param array<string, mixed> $analysis
     * @return array<string, mixed>|null
     */
    private function buildDebugPayload(array $analysis): ?array
    {
        if (!Development::isEnabled()) {
            return null;
        }

        $links = array_map(function (array $link): array {
            return [
                'path' => (string) ($link['linkTarget'] ?? ''),
                'label' => (string) ($link['labelSamples'][0] ?? $link['linkText'] ?? ''),
                'pages' => (int) ($link['pageCount'] ?? 0),
                'button' => (int) ($link['buttonLikeCount'] ?? 0),
                'hero' => (int) ($link['heroCount'] ?? 0),
            ];
        }, array_values(array_filter($analysis['links'] ?? [], 'is_array')));

        $forms = array_map(function (array $form): array {
            return [
                'fieldTypes' => array_values($form['fieldTypes'] ?? []),
                'submit' => (string) ($form['submitTexts'][0] ?? ''),
                'pages' => count($form['sourcePages'] ?? []),
                'firstPage' => (string) ($form['sourcePages'][0] ?? ''),
            ];
        }, array_values(array_filter($analysis['forms'] ?? [], 'is_array')));

        $hosts = array_map(function (array $host): array {
            return [
                'host' => (string) ($host['host'] ?? ''),
                'label' => (string) ($host['labels'][0] ?? ''),
                'example' => (string) ($host['examples'][0] ?? $host['href'] ?? ''),
                'pages' => count($host['sourcePages'] ?? []),
            ];
        }, array_values(array_filter($analysis['externalLinks'] ?? [], 'is_array')));

        return [
            'url' => (string) ($analysis['url'] ?? ''),
            'platform' => $analysis['platform'] ?? null,
            'technologies' => array_values($analysis['technologies'] ?? []),
            'pagesCrawled' => (int) ($analysis['pagesCrawled'] ?? 0),
            'crawl' => $analysis['crawl'] ?? [],
            'pages' => array_values($analysis['pages'] ?? []),
            'links' => $links,
            'forms' => $forms,
            'externalHosts' => $hosts,
            'downloads' => array_map(function (array $download): array {
                return ['href' => (string) ($download['href'] ?? ''), 'label' => (string) ($download['labels'][0] ?? '')];
            }, array_values(array_filter($analysis['downloads'] ?? [], 'is_array'))),
            'candidates' => $this->deterministicRecommender->getLastCandidates(),
        ];
    }

    /**
     * @return array{0: Lock, 1: Lock}
     */
    private function acquireScanLocks(int $idSite): array
    {
        $userLock = new Lock(
            $this->lockBackend,
            self::USER_SCAN_LOCK_NAMESPACE,
            self::SCAN_LOCK_TTL_SECONDS
        );
        $userLockId = hash('sha256', Piwik::getCurrentUserLogin());
        if (!$userLock->acquireLock($userLockId, self::SCAN_LOCK_TTL_SECONDS)) {
            throw new ScanAlreadyRunningException();
        }

        $siteLock = new Lock(
            $this->lockBackend,
            self::SITE_SCAN_LOCK_NAMESPACE,
            self::SCAN_LOCK_TTL_SECONDS
        );

        try {
            if (!$siteLock->acquireLock((string) $idSite, self::SCAN_LOCK_TTL_SECONDS)) {
                throw new ScanAlreadyRunningException();
            }
        } catch (\Throwable $e) {
            $userLock->unlock();
            throw $e;
        }

        return [$userLock, $siteLock];
    }

    /**
     * Returns the recommendations persisted by the last scan. Individually dismissed
     * recommendations are excluded. Returns an empty result with a null `generatedAt`
     * when no scan was saved.
     *
     * @return array{
     *   mode: ?string, goals: array<int, array<string, mixed>>, manualGoals: array<int, array<string, mixed>>,
     *   warnings: array<int, array{type: string, severity: string, message: string}>,
     *   useAi: bool, generatedAt: ?int, remainingAiScans: ?int,
     *   providerName: string, aiAvailability: string, privacyNote: string
     * }
     */
    public function getSavedRecommendations(int $idSite): array
    {
        $saved = $this->store->get($idSite);
        if ($saved === null) {
            return [
                'mode' => null,
                'goals' => [],
                'manualGoals' => [],
                'warnings' => [],
                'useAi' => false,
                'generatedAt' => null,
                'remainingAiScans' => $this->getRemainingAiScans($idSite),
                'providerName' => $this->getConfiguredProviderName(),
                'aiAvailability' => $this->getAiAvailability(),
                'privacyNote' => $this->getPrivacyNote(),
            ];
        }

        $goals = array_values(array_filter($saved['goals'], function (array $goal) use ($saved): bool {
            $recommendationId = (string) ($goal['id'] ?? '');

            return $recommendationId === '' || !isset($saved['dismissed'][$recommendationId]);
        }));

        return [
            'mode' => $saved['mode'],
            'goals' => $goals,
            'manualGoals' => $saved['manualGoals'],
            'warnings' => $saved['warnings'],
            'useAi' => $saved['useAi'],
            'generatedAt' => $saved['generatedAt'],
            'remainingAiScans' => $this->getRemainingAiScans($idSite),
            'providerName' => $this->getConfiguredProviderName(),
            'aiAvailability' => $this->getAiAvailability(),
            'privacyNote' => $this->getPrivacyNote(),
        ];
    }

    /**
     * Removes the persisted recommendations for a site.
     */
    public function dismiss(int $idSite): void
    {
        $this->store->delete($idSite);
    }

    /**
     * Dismisses a single saved recommendation so it is no longer shown. The
     * dismissal lasts until the next scan replaces the saved recommendations.
     */
    public function dismissRecommendation(int $idSite, string $recommendationId): bool
    {
        return $this->store->markDismissed($idSite, $recommendationId);
    }

    private function assignRecommendationIds(array $goals): array
    {
        foreach ($goals as &$goal) {
            $goal['id'] = RecommendationMatcher::buildKey(
                (string) ($goal['matchAttribute'] ?? 'url'),
                (string) ($goal['pattern'] ?? '')
            );
        }

        return $goals;
    }

    /**
     * Maximum number of AI-assisted scans allowed per site and day. 0 means unlimited.
     */
    private function getDailyAiScanLimit(): int
    {
        return (int) (Config::getInstance()->Goals['recommendation_ai_daily_scan_limit'] ?? 0);
    }

    /**
     * How many AI-assisted scans the site has left today, or null when unlimited.
     */
    private function getRemainingAiScans(int $idSite): ?int
    {
        $dailyLimit = $this->getDailyAiScanLimit();
        if ($dailyLimit <= 0) {
            return null;
        }

        return max(0, $dailyLimit - $this->store->countAiScansToday($idSite));
    }

    /**
     * Explains which site data the AI recommendation leaves the instance with.
     *
     * Plugins can replace the note to describe an environment specific setup, for
     * example a managed provider with its own privacy terms.
     */
    public function getPrivacyNote(): string
    {
        $privacyNote = Piwik::translate('Goals_RecommendAiToggleHelp', $this->getConfiguredProviderName());

        /**
         * Lets an environment replace the note describing what site data is sent to
         * the AI provider, e.g. to name a managed provider and its privacy terms.
         *
         * @param string &$privacyNote The note shown next to the AI toggle.
         * @ignore
         */
        Piwik::postEvent('Goals.customizeRecommendationPrivacyNote', [&$privacyNote]);

        return $privacyNote;
    }

    /**
     * How usable the AI feature currently is, so the UI can adapt the AI toggle:
     * 'available' (a configured default provider exists), 'notConfigured' (the
     * AIProviders plugin is active but has no configured provider), 'notActivated'
     * (the plugin is not active but could be) or 'disabled' (not active and plugin
     * administration is off, so nobody on this instance can enable it).
     */
    public function getAiAvailability(): string
    {
        if ($this->isAiAvailable()) {
            return 'available';
        }

        if (Manager::getInstance()->isPluginActivated('AIProviders')) {
            // plugin is there, so what is missing is a configured provider
            return 'notConfigured';
        }

        // nobody can activate the plugin here, so the feature is out of reach
        return CorePluginsAdmin::isPluginsAdminEnabled() ? 'notActivated' : 'disabled';
    }

    private function isAiAvailable(): bool
    {
        $service = $this->getAiProviderService();
        if ($service === null) {
            return false;
        }

        try {
            foreach ($service->getAvailableProviderStatuses() as $provider) {
                if (!empty($provider['isDefault']) && !empty($provider['isConfigured'])) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            $this->getLogger()->debug(
                'Goals recommendations: could not read AI provider status: {message}',
                ['message' => $e->getMessage()]
            );
        }

        return false;
    }

    /**
     * Display name of the configured AI provider,
     * or a generic fallback label when none is resolvable. Shown in the privacy
     * note, so the user knows where their site signals are sent.
     */
    private function getConfiguredProviderName(): string
    {
        $service = $this->getAiProviderService();
        if ($service !== null) {
            try {
                return $service->getDefaultProvider()->getName();
            } catch (\Exception $e) {
                $this->getLogger()->debug(
                    'Goals recommendations: could not read AI provider name: {message}',
                    ['message' => $e->getMessage()]
                );
            }
        }

        return Piwik::translate('Goals_RecommendAiProviderFallback');
    }

    private function getAiProviderService(): ?\Piwik\Plugins\AIProviders\AIProviderService
    {
        if (
            !Manager::getInstance()->isPluginActivated('AIProviders')
            || !class_exists('Piwik\\Plugins\\AIProviders\\AIProviderService')
        ) {
            return null;
        }

        return StaticContainer::get('Piwik\\Plugins\\AIProviders\\AIProviderService');
    }

    private function getLogger(): LoggerInterface
    {
        return StaticContainer::get(LoggerInterface::class);
    }

    private function recordAiConsent(): void
    {
        $login = Piwik::getCurrentUserLogin();
        if (!empty($login)) {
            \Piwik\Option::set(self::CONSENT_KEY_PREFIX . $login, (string) Date::now()->getTimestamp());
        }
    }

    private function getAiRecommender(): AiRecommender
    {
        if ($this->aiRecommender === null) {
            $this->aiRecommender = new AiRecommender();
        }

        return $this->aiRecommender;
    }

    /**
     * @param array<int|string, array<string, mixed>> $existingGoals
     * @return array<int, array{name: string, matchAttribute: string, pattern: string, patternType: string}>
     */
    private function getExistingGoalSummaries(array $existingGoals): array
    {
        $goalSummaries = [];

        foreach ($existingGoals as $goal) {
            if (empty($goal['pattern'])) {
                continue;
            }

            $goalSummaries[] = [
                'name' => (string) ($goal['name'] ?? ''),
                'matchAttribute' => (string) ($goal['match_attribute'] ?? 'url'),
                'pattern' => (string) $goal['pattern'],
                'patternType' => (string) ($goal['pattern_type'] ?? ''),
            ];
        }

        return $goalSummaries;
    }

    /**
     * @param array<int, array<string, mixed>> $recommendations
     * @param array<int, array{
     *   name: string, matchAttribute: string, pattern: string, patternType: string
     * }> $existingGoals
     * @return array<int, array<string, mixed>>
     */
    private function filterExistingGoals(array $recommendations, array $existingGoals): array
    {
        if (empty($existingGoals)) {
            return $recommendations;
        }

        $filtered = [];
        foreach ($recommendations as $recommendation) {
            if (!$this->matchesExistingGoal($recommendation, $existingGoals)) {
                $filtered[] = $recommendation;
            }
        }

        return $filtered;
    }

    /**
     * @param array<string, mixed> $recommendation
     * @param array<int, array{
     *   name: string, matchAttribute: string, pattern: string, patternType: string
     * }> $existingGoals
     */
    private function matchesExistingGoal(array $recommendation, array $existingGoals): bool
    {
        $candidateAttribute = (string) ($recommendation['matchAttribute'] ?? 'url');
        $candidatePattern = (string) ($recommendation['pattern'] ?? '');

        foreach ($existingGoals as $goal) {
            $covers = RecommendationMatcher::covers(
                $candidateAttribute,
                $candidatePattern,
                $goal['matchAttribute'],
                $goal['pattern'],
                $goal['patternType']
            );
            if ($covers) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, array{name: string, howTo: string, category: string}> $manualSuggestions
     * @param array<int, array<string, mixed>> $directGoals
     * @return array<int, array{name: string, howTo: string, category: string}>
     */
    private function filterManualSuggestions(array $manualSuggestions, array $directGoals): array
    {
        $coveredCategories = [];
        foreach ($directGoals as $goal) {
            $matchAttribute = (string) ($goal['matchAttribute'] ?? '');
            if (strpos($matchAttribute, 'event_') === 0) {
                $coveredCategories['event'] = true;
            } elseif ($matchAttribute === 'file') {
                $coveredCategories['file'] = true;
            } elseif ($matchAttribute === 'external_website') {
                $coveredCategories['outlink'] = true;
            } elseif ($matchAttribute === 'visit_duration') {
                $coveredCategories['visit_duration'] = true;
            }
        }

        $filter = function (array $suggestion) use ($coveredCategories): bool {
            return empty($coveredCategories[$suggestion['category']]);
        };

        return array_values(array_filter($manualSuggestions, $filter));
    }
}
