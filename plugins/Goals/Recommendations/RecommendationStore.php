<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\Recommendations;

use Piwik\Date;
use Piwik\Option;

/**
 * Persists the latest goal recommendation scan per site so results survive page
 * reloads without re-running the crawl or spending AI requests, and records which
 * recommendations were dismissed individually.
 */
class RecommendationStore
{
    private const OPTION_PREFIX = 'Goals.recommendedGoals.';

    /**
     * Saves a scan result, replacing any previous dismissed marks.
     *
     * @param array<int, array<string, mixed>> $goals
     * @param array<int, array{name: string, howTo: string, category: string}> $manualGoals
     * @return array<string, mixed> The saved payload.
     */
    public function save(int $idSite, bool $useAi, string $mode, array $goals, array $manualGoals): array
    {
        $data = [
            'generatedAt' => Date::now()->getTimestamp(),
            'useAi' => $useAi,
            'mode' => $mode,
            'goals' => array_values($goals),
            'manualGoals' => array_values($manualGoals),
            'dismissed' => [],
        ];

        $this->write($idSite, $data);

        return $data;
    }

    /**
     * Returns the last saved scan result, or null when none exists.
     *
     * @return array{generatedAt: int, useAi: bool, mode: string, goals: array<int, array<string, mixed>>, manualGoals: array<int, array<string, mixed>>, dismissed: array<string, int>}|null
     */
    public function get(int $idSite): ?array
    {
        $value = Option::get(self::OPTION_PREFIX . $idSite);
        if (!is_string($value) || $value === '') {
            return null;
        }

        $data = json_decode($value, true);
        if (!is_array($data) || empty($data['generatedAt'])) {
            return null;
        }

        return [
            'generatedAt' => (int) $data['generatedAt'],
            'useAi' => !empty($data['useAi']),
            'mode' => (string) ($data['mode'] ?? 'deterministic'),
            'goals' => is_array($data['goals'] ?? null) ? array_values($data['goals']) : [],
            'manualGoals' => is_array($data['manualGoals'] ?? null) ? array_values($data['manualGoals']) : [],
            'dismissed' => is_array($data['dismissed'] ?? null) ? $data['dismissed'] : [],
        ];
    }

    /**
     * Records that a single saved recommendation was dismissed so it is no longer
     * shown. Does nothing when no saved recommendation has the given identifier.
     * Dismissals are reset by the next scan.
     */
    public function markDismissed(int $idSite, string $recommendationId): bool
    {
        $data = $this->get($idSite);
        if ($data === null || $recommendationId === '') {
            return false;
        }

        foreach ($data['goals'] as $goal) {
            if ((string) ($goal['id'] ?? '') === $recommendationId) {
                $data['dismissed'][$recommendationId] = Date::now()->getTimestamp();
                $this->write($idSite, $data);

                return true;
            }
        }

        return false;
    }

    public function delete(int $idSite): void
    {
        Option::delete(self::OPTION_PREFIX . $idSite);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function write(int $idSite, array $data): void
    {
        $encoded = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (is_string($encoded)) {
            Option::set(self::OPTION_PREFIX . $idSite, $encoded);
        }
    }
}
