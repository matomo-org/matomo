<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\Integration;

use Piwik\Config;
use Piwik\Concurrency\LockBackend;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\AIProviders\Exception\AIProviderClientException;
use Piwik\Plugins\Goals\API;
use Piwik\Plugins\Goals\Recommendations\AiRecommender;
use Piwik\Plugins\Goals\Recommendations\DeterministicRecommender;
use Piwik\Plugins\Goals\Recommendations\GoalRecommendationService;
use Piwik\Plugins\Goals\Recommendations\HomepageAnalyzer;
use Piwik\Plugins\Goals\Recommendations\ManualSuggestionRecommender;
use Piwik\Plugins\Goals\Recommendations\RecommendationStore;
use Piwik\Plugins\Goals\Recommendations\ScanAlreadyRunningException;
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

    /**
     * Read lazily by the mocked AIProviderService, so tests can make a provider
     * appear configured from within the test body.
     *
     * @var array<int, array<string, mixed>>
     */
    private static $aiProviderStatuses = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->api = API::getInstance();

        self::$aiProviderStatuses = [];
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
            'aiAvailability' => 'notConfigured',
            'privacyNote' => Piwik::translate('Goals_RecommendAiToggleHelp', Piwik::translate('Goals_RecommendAiProviderFallback')),
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

    public function testRunGoalRecommendationScanRequiresWriteAccess()
    {
        $this->setViewOnlyUser();

        $this->expectException(\Exception::class);

        $this->api->runGoalRecommendationScan($this->idSite);
    }

    public function testGetRecommendationsRejectsConcurrentScansForSameUser()
    {
        $lockBackend = $this->createMock(LockBackend::class);
        $lockBackend->expects($this->once())
            ->method('setIfNotExists')
            ->with($this->anything(), $this->anything(), 60)
            ->willReturn(false);

        $this->expectException(ScanAlreadyRunningException::class);
        $this->expectExceptionCode(429);
        $this->expectExceptionMessage(Piwik::translate('Goals_RecommendScanAlreadyRunning'));

        $this->makeRecommendationService($this->createMock(AiRecommender::class), $lockBackend)
            ->getRecommendations($this->idSite, false);
    }

    public function testGetRecommendationsRejectsConcurrentScansForSameSiteAndReleasesUserLock()
    {
        $lockBackend = $this->createMock(LockBackend::class);
        $lockBackend->expects($this->exactly(2))
            ->method('setIfNotExists')
            ->with($this->anything(), $this->anything(), 60)
            ->willReturnOnConsecutiveCalls(true, false);
        $lockBackend->expects($this->once())
            ->method('deleteIfKeyHasValue')
            ->willReturn(true);

        $this->expectException(ScanAlreadyRunningException::class);

        $this->makeRecommendationService($this->createMock(AiRecommender::class), $lockBackend)
            ->getRecommendations($this->idSite, false);
    }

    public function testGetRecommendationsReleasesBothLocksAfterScan()
    {
        $lockBackend = $this->createMock(LockBackend::class);
        $lockBackend->expects($this->exactly(2))
            ->method('setIfNotExists')
            ->with($this->anything(), $this->anything(), 60)
            ->willReturn(true);
        $lockBackend->expects($this->exactly(2))
            ->method('deleteIfKeyHasValue')
            ->willReturn(true);

        $this->makeRecommendationService($this->createMock(AiRecommender::class), $lockBackend)
            ->getRecommendations($this->idSite, false);
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
        Config::getInstance()->Goals = ['recommendation_ai_daily_scan_limit' => 2];

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
        Config::getInstance()->Goals = ['recommendation_ai_daily_scan_limit' => 2];

        $result = $this->makeRecommendationService($this->createMock(AiRecommender::class))
            ->getRecommendations($this->idSite, true);

        // no AI provider is configured in tests, so the scan falls back without spending quota
        $this->assertSame(Piwik::translate('Goals_RecommendationAiUnavailable'), $result['aiError']);
        $this->assertSame(2, $result['remainingAiScans']);
        $this->assertSame(0, (new RecommendationStore())->countAiScansToday($this->idSite));
    }

    public function testAiSetupErrorShowsProviderMessageToSuperusersAndConsumesNoQuota()
    {
        self::$aiProviderStatuses = [['isDefault' => true, 'isConfigured' => true]];

        $aiRecommender = $this->createMock(AiRecommender::class);
        $aiRecommender->method('recommend')
            ->willThrowException(new AIProviderClientException('OpenAI rejected the API key. Check the key and try again.'));

        $result = $this->makeRecommendationService($aiRecommender)->getRecommendations($this->idSite, true);

        $this->assertSame('deterministic', $result['mode']);
        $this->assertSame('OpenAI rejected the API key. Check the key and try again.', $result['aiError']);
        $this->assertSame(0, (new RecommendationStore())->countAiScansToday($this->idSite));
    }

    public function testAiSetupErrorShowsGenericMessageToNonSuperusers()
    {
        self::$aiProviderStatuses = [['isDefault' => true, 'isConfigured' => true]];
        $this->setWriteUser();

        $aiRecommender = $this->createMock(AiRecommender::class);
        $aiRecommender->method('recommend')
            ->willThrowException(new AIProviderClientException('OpenAI rejected the API key. Check the key and try again.'));

        $result = $this->makeRecommendationService($aiRecommender)->getRecommendations($this->idSite, true);

        $this->assertSame('deterministic', $result['mode']);
        $this->assertSame(Piwik::translate('Goals_RecommendationAiProviderIssue'), $result['aiError']);
    }

    public function testAiTransientErrorMessageIsShownToAnyRole()
    {
        self::$aiProviderStatuses = [['isDefault' => true, 'isConfigured' => true]];
        $this->setWriteUser();

        $aiRecommender = $this->createMock(AiRecommender::class);
        $aiRecommender->method('recommend')
            ->willThrowException(new \RuntimeException('Could not connect to OpenAI.'));

        $result = $this->makeRecommendationService($aiRecommender)->getRecommendations($this->idSite, true);

        $this->assertSame('Could not connect to OpenAI.', $result['aiError']);
    }

    private function makeRecommendationService(
        AiRecommender $aiRecommender,
        ?LockBackend $lockBackend = null
    ): GoalRecommendationService {
        $analyzer = $this->createMock(HomepageAnalyzer::class);
        $analyzer->method('analyze')->willReturn(['url' => 'https://example.org']);

        $deterministic = $this->createMock(DeterministicRecommender::class);
        $deterministic->method('recommend')->willReturn([]);

        $manual = $this->createMock(ManualSuggestionRecommender::class);
        $manual->method('recommend')->willReturn([]);

        return new GoalRecommendationService(
            $analyzer,
            $deterministic,
            $manual,
            new RecommendationStore(),
            $lockBackend ?? StaticContainer::get(LockBackend::class),
            $aiRecommender
        );
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

    private function setWriteUser(): void
    {
        FakeAccess::$superUser = false;
        FakeAccess::$idSitesView = [];
        FakeAccess::$idSitesWrite = [$this->idSite];
        FakeAccess::$idSitesAdmin = [];
        FakeAccess::$identity = 'aWriteUser';
    }

    public function provideContainerConfig()
    {
        $providerService = $this->getMockBuilder(\Piwik\Plugins\AIProviders\AIProviderService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $providerService->method('getAvailableProviderStatuses')
            ->willReturnCallback(function () {
                return self::$aiProviderStatuses;
            });
        // like the real service when no provider is configured, so the
        // provider name falls back to the translated placeholder
        $providerService->method('getDefaultProvider')
            ->willThrowException(new \InvalidArgumentException('No provider configured.'));

        return [
            'Piwik\Access' => new FakeAccess(),
            'Piwik\Plugins\AIProviders\AIProviderService' => $providerService,
        ];
    }
}
