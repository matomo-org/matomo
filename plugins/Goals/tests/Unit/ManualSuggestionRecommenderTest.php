<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\tests\Unit;

use Piwik\Plugins\Goals\Recommendations\ManualSuggestionRecommender;
use PHPUnit\Framework\TestCase;

/**
 * @group Goals
 * @group GoalRecommendations
 */
class ManualSuggestionRecommenderTest extends TestCase
{
    /**
     * @var ManualSuggestionRecommender
     */
    private $recommender;

    public function setUp(): void
    {
        parent::setUp();
        $this->recommender = new ManualSuggestionRecommender();
    }

    public function testSuggestsFormDownloadOutlinkAndDurationFromSignals(): void
    {
        $analysis = [
            'manualSignals' => [
                'downloadExtensions' => ['pdf' => 4, 'zip' => 1],
                'outlinkHosts' => ['github.com' => 6],
                'hasContactLinks' => true,
                'formCount' => 2,
            ],
        ];

        $suggestions = $this->recommender->recommend($analysis);

        $this->assertSame(
            ['event', 'file', 'outlink', 'visit_duration'],
            array_column($suggestions, 'category')
        );

        foreach ($suggestions as $suggestion) {
            $this->assertNotSame('', $suggestion['name']);
            $this->assertNotSame('', $suggestion['howTo']);
        }
    }

    public function testAlwaysIncludesVisitDurationFallbackWhenNoSignals(): void
    {
        $suggestions = $this->recommender->recommend(['manualSignals' => []]);

        $this->assertSame(['visit_duration'], array_column($suggestions, 'category'));
    }

    public function testOutlinkSuggestedFromContactLinksOnly(): void
    {
        $analysis = [
            'manualSignals' => [
                'downloadExtensions' => [],
                'outlinkHosts' => [],
                'hasContactLinks' => true,
                'formCount' => 0,
            ],
        ];

        $this->assertSame(
            ['outlink', 'visit_duration'],
            array_column($this->recommender->recommend($analysis), 'category')
        );
    }

    public function testHandlesMissingManualSignalsKey(): void
    {
        $this->assertSame(
            ['visit_duration'],
            array_column($this->recommender->recommend([]), 'category')
        );
    }
}
