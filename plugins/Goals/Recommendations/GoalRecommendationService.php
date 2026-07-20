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
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
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
     * @var AiRecommender|null
     */
    private $aiRecommender = null;
    public function __construct(
        HomepageAnalyzer $homepageAnalyzer,
        DeterministicRecommender $deterministicRecommender,
        ManualSuggestionRecommender $manualRecommender,
        RecommendationStore $store,
        ?AiRecommender $aiRecommender = null
    ) {
        $this->homepageAnalyzer = $homepageAnalyzer;
        $this->deterministicRecommender = $deterministicRecommender;
        $this->manualRecommender = $manualRecommender;
        $this->store = $store;
        $this->aiRecommender = $aiRecommender;
    }

    /**
     * Returns fresh goal recommendations for a site and persists them so they can
     * be retrieved again via {@link getSavedRecommendations()}.
     *
     * @param array<int|string, array<string, mixed>> $existingGoals
     * @return array{mode: string, goals: array<int, array<string, mixed>>, manualGoals: array<int, array{name: string, howTo: string, category: string}>, aiError: ?string, generatedAt: ?int, remainingAiScans: ?int, providerName: string}
     */
    public function getRecommendations(int $idSite, bool $useAi, array $existingGoals = []): array
    {
        $existingGoalSummaries = $this->getExistingGoalSummaries($existingGoals);
        $analysis = $this->homepageAnalyzer->analyze($idSite);
        if ($analysis === null) {
            $this->getLogger()->info('Goals recommendations: could not analyse the homepage for site {idSite}.', ['idSite' => $idSite]);

            return [
                'mode' => 'deterministic',
                'goals' => [],
                'manualGoals' => [],
                'aiError' => Piwik::translate('Goals_RecommendCouldNotAnalyze'),
                'generatedAt' => null,
                'remainingAiScans' => $this->getRemainingAiScans($idSite),
                'providerName' => $this->getConfiguredProviderName(),
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
                    $this->getLogger()->info('Goals recommendations: AI provider unavailable: {message}', ['message' => $e->getMessage()]);
                    $aiError = Piwik::translate('Goals_RecommendationAiUnavailable');
                } catch (\Exception $e) {
                    // Surface the provider's own (user-safe) message, e.g. "rejected the API key".
                    $this->getLogger()->warning('Goals recommendations: AI request failed: {message}', ['message' => $e->getMessage()]);
                    $aiError = $e->getMessage();
                }
            } else {
                $aiError = Piwik::translate('Goals_RecommendationAiUnavailable');
            }
        }

        $goals = $this->assignRecommendationIds($goals);
        $manualGoals = $this->filterManualSuggestions($this->manualRecommender->recommend($analysis), $goals);
        $saved = $this->store->save($idSite, $useAi, $mode, $goals, $manualGoals);

        return [
            'mode' => $mode,
            'goals' => $goals,
            'manualGoals' => $manualGoals,
            'aiError' => $aiError,
            'generatedAt' => $saved['generatedAt'],
            'remainingAiScans' => $this->getRemainingAiScans($idSite),
            'providerName' => $this->getConfiguredProviderName(),
        ];
    }

    /**
     * Returns the recommendations persisted by the last scan. Individually dismissed
     * recommendations are excluded. Returns an empty result with a null `generatedAt`
     * when no scan was saved.
     *
     * @return array{mode: ?string, goals: array<int, array<string, mixed>>, manualGoals: array<int, array<string, mixed>>, useAi: bool, generatedAt: ?int, remainingAiScans: ?int, providerName: string}
     */
    public function getSavedRecommendations(int $idSite): array
    {
        $saved = $this->store->get($idSite);
        if ($saved === null) {
            return [
                'mode' => null,
                'goals' => [],
                'manualGoals' => [],
                'useAi' => false,
                'generatedAt' => null,
                'remainingAiScans' => $this->getRemainingAiScans($idSite),
                'providerName' => $this->getConfiguredProviderName(),
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
            'useAi' => $saved['useAi'],
            'generatedAt' => $saved['generatedAt'],
            'remainingAiScans' => $this->getRemainingAiScans($idSite),
            'providerName' => $this->getConfiguredProviderName(),
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
        return (int) (Config::getInstance()->Goals['ai_recommendation_daily_scan_limit'] ?? 0);
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
            $this->getLogger()->debug('Goals recommendations: could not read AI provider status: {message}', ['message' => $e->getMessage()]);
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
                $this->getLogger()->debug('Goals recommendations: could not read AI provider name: {message}', ['message' => $e->getMessage()]);
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
     * @param array<int, array{name: string, matchAttribute: string, pattern: string, patternType: string}> $existingGoals
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
     * @param array<int, array{name: string, matchAttribute: string, pattern: string, patternType: string}> $existingGoals
     */
    private function matchesExistingGoal(array $recommendation, array $existingGoals): bool
    {
        $candidateAttribute = (string) ($recommendation['matchAttribute'] ?? 'url');
        $candidatePattern = (string) ($recommendation['pattern'] ?? '');

        foreach ($existingGoals as $goal) {
            if (RecommendationMatcher::covers($candidateAttribute, $candidatePattern, $goal['matchAttribute'], $goal['pattern'])) {
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

        return array_values(array_filter($manualSuggestions, function (array $suggestion) use ($coveredCategories): bool {
            return empty($coveredCategories[$suggestion['category']]);
        }));
    }
}
