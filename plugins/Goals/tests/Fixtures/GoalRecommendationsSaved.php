<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\Fixtures;

use Piwik\Plugins\Goals\Recommendations\RecommendationMatcher;
use Piwik\Plugins\Goals\Recommendations\RecommendationStore;
use Piwik\Tests\Fixtures\SomePageGoalVisitsWithConversions;

/**
 * Site with two goals plus a saved goal recommendation scan, so the UI can be tested without crawling.
 */
class GoalRecommendationsSaved extends SomePageGoalVisitsWithConversions
{
    public function setUp(): void
    {
        parent::setUp();

        $goals = [];
        foreach (['/pricing', '/contact', '/signup', '/demo', '/checkout', '/thank-you'] as $path) {
            $goals[] = $this->buildGoal(ucfirst(trim($path, '/')) . ' page visited', 'url', $path);
        }

        $goals[] = $this->buildGoal('Newsletter signup', 'event_action', 'newsletter_submit', [
            'needsSetup' => true,
            'evidence' => ['Found a newsletter form on 3 pages'],
            'implementationNote' => 'Send an event with action newsletter_submit when the form is submitted.',
        ]);

        $manualGoals = [[
            'name' => 'Video watched',
            'howTo' => 'Track a video play event and use it as the trigger.',
            'category' => 'engagement',
        ]];

        (new RecommendationStore())->save($this->idSite, false, 'deterministic', $goals, $manualGoals);
    }

    private function buildGoal(string $name, string $matchAttribute, string $pattern, array $extra = []): array
    {
        return $extra + [
            'id' => RecommendationMatcher::buildKey($matchAttribute, $pattern),
            'name' => $name,
            'matchAttribute' => $matchAttribute,
            'pattern' => $pattern,
            'patternType' => 'contains',
            'caseSensitive' => false,
            'allowMultipleConversionsPerVisit' => false,
            'revenue' => 0,
            'reason' => 'Visitors reach this page when they are ready to convert.',
        ];
    }
}
