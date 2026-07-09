<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\Recommendations;

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
        ?HomepageAnalyzer $homepageAnalyzer = null,
        ?DeterministicRecommender $deterministicRecommender = null,
        ?AiRecommender $aiRecommender = null,
        ?ManualSuggestionRecommender $manualRecommender = null,
        ?RecommendationStore $store = null
    ) {
        $this->homepageAnalyzer = $homepageAnalyzer ?? new HomepageAnalyzer();
        $this->deterministicRecommender = $deterministicRecommender ?? new DeterministicRecommender();
        $this->aiRecommender = $aiRecommender;
        $this->manualRecommender = $manualRecommender ?? new ManualSuggestionRecommender();
        $this->store = $store ?? new RecommendationStore();
    }

    /**
     * Returns fresh goal recommendations for a site and persists them so they can
     * be retrieved again via {@link getSavedRecommendations()}.
     *
     * @param array<int|string, array<string, mixed>> $existingGoals
     * @return array{mode: string, goals: array<int, array<string, mixed>>, manualGoals: array<int, array{name: string, howTo: string, category: string}>, aiError: ?string, generatedAt: ?int}
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
            ];
        }

        $deterministic = $this->assignRecommendationIds($this->filterExistingGoals(
            $this->deterministicRecommender->recommend($analysis),
            $existingGoalSummaries
        ));

        $mode = 'deterministic';
        $goals = $deterministic;
        $aiError = null;
        $aiAvailable = false;

        if ($useAi) {
            $aiAvailable = $this->isAiAvailable();
            if ($aiAvailable) {
                $this->recordAiConsent();
                $aiRecommender = $this->getAiRecommender();
                try {
                    $aiResult = $aiRecommender->recommendWithDebug($analysis, $idSite, $existingGoalSummaries, $deterministic);
                    $aiGoals = $this->filterExistingGoals(
                        $aiResult['goals'],
                        $existingGoalSummaries
                    );
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
        ];
    }

    /**
     * Returns the recommendations persisted by the last scan. Individually dismissed
     * recommendations are excluded. Returns an empty result with a null `generatedAt`
     * when no scan was saved.
     *
     * @return array{mode: ?string, goals: array<int, array<string, mixed>>, manualGoals: array<int, array<string, mixed>>, useAi: bool, generatedAt: ?int}
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

    private function isAiAvailable(): bool
    {
        if (
            !Manager::getInstance()->isPluginActivated('AIProviders')
            || !class_exists('Piwik\\Plugins\\AIProviders\\AIProviderService')
        ) {
            return false;
        }

        try {
            /** @var \Piwik\Plugins\AIProviders\AIProviderService $service */
            $service = StaticContainer::get('Piwik\\Plugins\\AIProviders\\AIProviderService');
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
