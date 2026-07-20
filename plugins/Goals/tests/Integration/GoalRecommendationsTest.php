<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\Integration;

use Piwik\Config;
use Piwik\Date;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\Goals\API;
use Piwik\Plugins\Goals\Recommendations\AiRecommender;
use Piwik\Plugins\Goals\Recommendations\DeterministicRecommender;
use Piwik\Plugins\Goals\Recommendations\GoalRecommendationService;
use Piwik\Plugins\Goals\Recommendations\HomepageAnalyzer;
use Piwik\Plugins\Goals\Recommendations\ManualSuggestionRecommender;
use Piwik\Plugins\Goals\Recommendations\RecommendationStore;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Goals
 * @group Plugins
 * @group GoalRecommendationsTest
 */
class GoalRecommendationsTest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    /**
     * @var int
     */
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();
        $this->api = API::getInstance();

        $this->idSite = Fixture::createWebsite('2024-01-01 00:00:00');
    }

    public function testGetSavedRecommendedGoalsReturnsEmptyResultWhenNothingSaved()
    {
        $result = $this->api->getSavedRecommendedGoals($this->idSite);

        $this->assertSame([
            'mode' => null,
            'goals' => [],
            'manualGoals' => [],
            'useAi' => false,
            'generatedAt' => null,
            'remainingAiScans' => null,
            'providerName' => Piwik::translate('Goals_RecommendAiProviderFallback'),
        ], $result);
    }

    public function testGetSavedRecommendedGoalsReturnsPersistedScan()
    {
        $this->saveScan([$this->makeRecommendation()]);

        $result = $this->api->getSavedRecommendedGoals($this->idSite);

        $this->assertSame('ai', $result['mode']);
        $this->assertTrue($result['useAi']);
        $this->assertIsInt($result['generatedAt']);
        $this->assertGreaterThan(0, $result['generatedAt']);
        $this->assertCount(1, $result['goals']);
        $this->assertSame('Visited contact page', $result['goals'][0]['name']);
        $this->assertCount(1, $result['manualGoals']);
    }

    public function testDismissRecommendedGoalsRemovesSavedScan()
    {
        $this->saveScan([$this->makeRecommendation()]);

        $response = $this->api->dismissRecommendedGoals($this->idSite);

        $this->assertSame(['success' => true], $response);
        $this->assertNull($this->api->getSavedRecommendedGoals($this->idSite)['generatedAt']);
    }

    public function testDismissRecommendedGoalHidesOnlyThatRecommendation()
    {
        $this->saveScan([
            $this->makeRecommendation(),
            $this->makeRecommendation([
                'id' => 'url:pricing',
                'name' => 'Visited pricing page',
                'pattern' => '/pricing',
            ]),
        ]);

        $response = $this->api->dismissRecommendedGoal($this->idSite, 'url:contact');

        $result = $this->api->getSavedRecommendedGoals($this->idSite);

        $this->assertSame(['success' => true], $response);
        $this->assertIsInt($result['generatedAt']);
        $this->assertCount(1, $result['goals']);
        $this->assertSame('Visited pricing page', $result['goals'][0]['name']);
    }

    public function testDismissRecommendedGoalWithUnknownIdChangesNothing()
    {
        $this->saveScan([$this->makeRecommendation()]);

        $response = $this->api->dismissRecommendedGoal($this->idSite, 'url:unknown');

        $this->assertSame(['success' => false], $response);
        $this->assertCount(1, $this->api->getSavedRecommendedGoals($this->idSite)['goals']);
    }

    public function testDismissedRecommendationIsResetByNextScan()
    {
        $this->saveScan([$this->makeRecommendation()]);
        $this->api->dismissRecommendedGoal($this->idSite, 'url:contact');

        // a new scan replaces the saved result, including dismissals
        $this->saveScan([$this->makeRecommendation()]);

        $this->assertCount(1, $this->api->getSavedRecommendedGoals($this->idSite)['goals']);
    }

    public function testDismissRecommendedGoalRequiresWriteAccess()
    {
        $this->setViewOnlyUser();

        $this->expectException(\Exception::class);

        $this->api->dismissRecommendedGoal($this->idSite, 'url:contact');
    }

    public function testGetSavedRecommendedGoalsRequiresWriteAccess()
    {
        $this->setViewOnlyUser();

        $this->expectException(\Exception::class);

        $this->api->getSavedRecommendedGoals($this->idSite);
    }

    public function testGetRecommendedGoalsRequiresWriteAccess()
    {
        $this->setViewOnlyUser();

        $this->expectException(\Exception::class);

        $this->api->getRecommendedGoals($this->idSite);
    }

    public function testAiScanQuotaCountsScansPerSiteAndDay()
    {
        $store = new RecommendationStore();

        $this->assertSame(0, $store->countAiScansToday($this->idSite));

        $store->recordAiScan($this->idSite);
        $store->recordAiScan($this->idSite);

        $this->assertSame(2, $store->countAiScansToday($this->idSite));
        $this->assertSame(0, $store->countAiScansToday($this->idSite + 1));
    }

    public function testAiScanQuotaResetsOnANewDay()
    {
        Option::set('Goals.aiScanQuota.' . $this->idSite, json_encode([
            'date' => Date::now()->subDay(1)->toString(),
            'count' => 3,
        ]));

        $this->assertSame(0, (new RecommendationStore())->countAiScansToday($this->idSite));
    }

    public function testGetRecommendationsSkipsAiWhenDailyScanLimitIsReached()
    {
        Config::getInstance()->Goals = ['ai_recommendation_daily_scan_limit' => 2];

        $store = new RecommendationStore();
        $store->recordAiScan($this->idSite);
        $store->recordAiScan($this->idSite);

        $aiRecommender = $this->createMock(AiRecommender::class);
        $aiRecommender->expects($this->never())->method('recommend');

        $result = $this->makeRecommendationService($aiRecommender)->getRecommendations($this->idSite, true);

        $this->assertSame('deterministic', $result['mode']);
        $this->assertSame(Piwik::translate('Goals_RecommendationAiDailyLimitReached', 2), $result['aiError']);
        $this->assertSame(0, $result['remainingAiScans']);
        $this->assertSame(2, $store->countAiScansToday($this->idSite));
    }

    public function testGetRecommendationsDoesNotConsumeQuotaWhenAiIsUnavailable()
    {
        Config::getInstance()->Goals = ['ai_recommendation_daily_scan_limit' => 2];

        $result = $this->makeRecommendationService($this->createMock(AiRecommender::class))
            ->getRecommendations($this->idSite, true);

        // no AI provider is configured in tests, so the scan falls back without spending quota
        $this->assertSame(Piwik::translate('Goals_RecommendationAiUnavailable'), $result['aiError']);
        $this->assertSame(2, $result['remainingAiScans']);
        $this->assertSame(0, (new RecommendationStore())->countAiScansToday($this->idSite));
    }

    private function makeRecommendationService(AiRecommender $aiRecommender): GoalRecommendationService
    {
        $analyzer = $this->createMock(HomepageAnalyzer::class);
        $analyzer->method('analyze')->willReturn(['url' => 'https://example.org']);

        $deterministic = $this->createMock(DeterministicRecommender::class);
        $deterministic->method('recommend')->willReturn([]);

        $manual = $this->createMock(ManualSuggestionRecommender::class);
        $manual->method('recommend')->willReturn([]);

        return new GoalRecommendationService($analyzer, $deterministic, $manual, new RecommendationStore(), $aiRecommender);
    }

    /**
     * @param array<int, array<string, mixed>> $goals
     */
    private function saveScan(array $goals): void
    {
        (new RecommendationStore())->save(
            $this->idSite,
            true,
            'ai',
            $goals,
            [['name' => 'Submitted a form', 'howTo' => 'Track a form submit event.', 'category' => 'event']]
        );
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function makeRecommendation(array $overrides = []): array
    {
        return array_merge([
            'id' => 'url:contact',
            'name' => 'Visited contact page',
            'matchAttribute' => 'url',
            'pattern' => '/contact',
            'patternType' => 'contains',
            'caseSensitive' => false,
            'allowMultipleConversionsPerVisit' => false,
            'revenue' => 0,
            'useEventValueAsRevenue' => false,
            'reason' => 'Visitors reaching the contact page indicate high intent.',
            'description' => 'Contact page goal',
            'source' => 'ai',
        ], $overrides);
    }

    private function setViewOnlyUser(): void
    {
        FakeAccess::$superUser = false;
        FakeAccess::$idSitesView = [$this->idSite];
        FakeAccess::$idSitesWrite = [];
        FakeAccess::$idSitesAdmin = [];
        FakeAccess::$identity = 'aViewUser';
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
