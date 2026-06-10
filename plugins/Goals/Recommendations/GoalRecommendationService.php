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
 * deterministic rules.
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
     * @var AiRecommender|null
     */
    private $aiRecommender = null;

    public function __construct(
        ?HomepageAnalyzer $homepageAnalyzer = null,
        ?DeterministicRecommender $deterministicRecommender = null,
        ?AiRecommender $aiRecommender = null,
        ?ManualSuggestionRecommender $manualRecommender = null
    ) {
        $this->homepageAnalyzer = $homepageAnalyzer ?? new HomepageAnalyzer();
        $this->deterministicRecommender = $deterministicRecommender ?? new DeterministicRecommender();
        $this->aiRecommender = $aiRecommender;
        $this->manualRecommender = $manualRecommender ?? new ManualSuggestionRecommender();
    }

    /**
     * Returns fresh goal recommendations for a site.
     *
     * @param array<int|string, array<string, mixed>> $existingGoals
     * @return array{mode: string, goals: array<int, array<string, mixed>>, manualGoals: array<int, array{name: string, howTo: string, category: string}>, aiError: ?string, debug: array<string, mixed>}
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
                'debug' => $this->buildDebug($idSite, $useAi, null, 0, false, 0),
            ];
        }

        $deterministic = $this->filterExistingGoals(
            $this->deterministicRecommender->recommend($analysis),
            $existingGoalSummaries
        );

        $mode = 'deterministic';
        $goals = $deterministic;
        $aiError = null;
        $aiAvailable = false;
        $aiDebug = null;

        if ($useAi) {
            $aiAvailable = $this->isAiAvailable();
            if ($aiAvailable) {
                $this->recordAiConsent();
                $aiRecommender = $this->getAiRecommender();
                try {
                    $aiResult = $aiRecommender->recommendWithDebug($analysis, $idSite, $existingGoalSummaries, $deterministic);
                    $aiDebug = $aiResult['debug'];
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
                    $aiDebug = $aiRecommender->getLastDebug();
                    $this->getLogger()->info('Goals recommendations: AI provider unavailable: {message}', ['message' => $e->getMessage()]);
                    $aiError = Piwik::translate('Goals_RecommendationAiUnavailable');
                } catch (\Exception $e) {
                    // Surface the provider's own (user-safe) message, e.g. "rejected the API key".
                    $aiDebug = $aiRecommender->getLastDebug();
                    $this->getLogger()->warning('Goals recommendations: AI request failed: {message}', ['message' => $e->getMessage()]);
                    $aiError = $e->getMessage();
                }
            } else {
                $aiError = Piwik::translate('Goals_RecommendationAiUnavailable');
            }
        }

        $result = [
            'mode' => $mode,
            'goals' => $goals,
            'manualGoals' => $this->filterManualSuggestions($this->manualRecommender->recommend($analysis), $goals),
            'aiError' => $aiError,
            // TODO: remove debug payload before release.
            'debug' => $this->buildDebug($idSite, $useAi, $analysis, count($deterministic), $aiAvailable, count($existingGoalSummaries), $aiDebug),
        ];

        return $result;
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
     * @param array<string, mixed>|null $analysis
     * @return array<string, mixed>
     */
    private function buildDebug(int $idSite, bool $useAi, ?array $analysis, int $deterministicCount, bool $aiAvailable, int $existingGoalCount, ?array $aiDebug = null): array
    {
        return [
            'idSite' => $idSite,
            'useAi' => $useAi,
            'aiAvailable' => $aiAvailable,
            'existingGoalCount' => $existingGoalCount,
            'deterministicCount' => $deterministicCount,
            'pagesCrawled' => $analysis['pagesCrawled'] ?? 0,
            'linkCount' => isset($analysis['links']) ? count($analysis['links']) : 0,
            'formCount' => isset($analysis['forms']) ? count($analysis['forms']) : 0,
            'downloadCount' => isset($analysis['downloads']) ? count($analysis['downloads']) : 0,
            'contactLinkCount' => isset($analysis['contactLinks']) ? count($analysis['contactLinks']) : 0,
            'externalLinkCount' => isset($analysis['externalLinks']) ? count($analysis['externalLinks']) : 0,
            'technologies' => $analysis['technologies'] ?? [],
            'manualSignals' => $analysis['manualSignals'] ?? null,
            'errors' => $analysis['errors'] ?? [],
            'topUrlSignals' => isset($analysis['links']) ? array_slice($analysis['links'], 0, 12) : [],
            'topFormSignals' => isset($analysis['forms']) ? array_slice($analysis['forms'], 0, 6) : [],
            'topDownloadSignals' => isset($analysis['downloads']) ? array_slice($analysis['downloads'], 0, 6) : [],
            'topContactSignals' => isset($analysis['contactLinks']) ? array_slice($analysis['contactLinks'], 0, 6) : [],
            'topExternalSignals' => isset($analysis['externalLinks']) ? array_slice($analysis['externalLinks'], 0, 6) : [],
            'crawl' => [
                'homepageUrl' => $analysis['url'] ?? null,
                'pages' => $analysis['crawledPages'] ?? [],
                'rankedLinks' => $analysis['links'] ?? [],
                'rankedForms' => $analysis['forms'] ?? [],
                'rankedDownloads' => $analysis['downloads'] ?? [],
                'rankedContactLinks' => $analysis['contactLinks'] ?? [],
                'rankedExternalLinks' => $analysis['externalLinks'] ?? [],
            ],
            'ai' => $aiDebug,
        ];
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
        $candidate = $this->normalizeGoalPatternForComparison((string) ($recommendation['pattern'] ?? ''), $candidateAttribute);
        if ($candidate === '') {
            return false;
        }

        foreach ($existingGoals as $goal) {
            $existingAttribute = $goal['matchAttribute'];
            if ($candidateAttribute !== $existingAttribute) {
                continue;
            }

            $existing = $this->normalizeGoalPatternForComparison($goal['pattern'], $existingAttribute);
            if ($existing === '') {
                continue;
            }

            if (
                $candidate === $existing
                || strpos($candidate, $existing) !== false
                || strpos($existing, $candidate) !== false
            ) {
                return true;
            }
        }

        return false;
    }

    private function normalizeGoalPatternForComparison(string $pattern, string $matchAttribute): string
    {
        $pattern = strtolower(trim($pattern));

        if ($matchAttribute === 'url' && preg_match('#^https?://#', $pattern)) {
            $path = parse_url($pattern, PHP_URL_PATH);
            $pattern = is_string($path) ? $path : $pattern;
        }

        if ($matchAttribute === 'file' && preg_match('#^https?://#', $pattern)) {
            $path = parse_url($pattern, PHP_URL_PATH);
            $pattern = is_string($path) ? basename($path) : $pattern;
        }

        return trim(rtrim($pattern, '/'), '/');
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
