<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders\tests\Integration;

use Piwik\Config;
use Piwik\Log\LoggerInterface;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugins\AIProviders\AIConversationRequest;
use Piwik\Plugins\AIProviders\AIConversationResponse;
use Piwik\Plugins\AIProviders\AIProviderResponse;
use Piwik\Plugins\AIProviders\AIProviderService;
use Piwik\Plugins\AIProviders\AIProvidersList;
use Piwik\Plugins\AIProviders\AIRequest;
use Piwik\Plugins\AIProviders\AIRequestContext;
use Piwik\Plugins\AIProviders\AIRequestDecision;
use Piwik\Plugins\AIProviders\AIUsage;
use Piwik\Plugins\AIProviders\Exception\AIProviderServerException;
use Piwik\Plugins\AIProviders\Exception\AIQuotaExceededException;
use Piwik\Plugins\AIProviders\Provider\AIProvider;
use Piwik\Plugins\AIProviders\WebSearchUsage;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Covers the events {@link AIProviderService} posts around provider calls:
 * `AIProviders.beforeRequest`, `AIProviders.usage`,
 * `AIProviders.getRemainingBudget` and `AIProviders.checkFeatureAllowed`.
 *
 * @group AIProviders
 * @group Plugins
 */
class UsageEventsTest extends IntegrationTestCase
{
    /**
     * @var EventsTestProvider
     */
    private $provider;

    /**
     * @var list<AIRequestContext>
     */
    private $beforeRequestContexts = [];

    /**
     * @var list<AIUsage>
     */
    private $usages = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->provider = new EventsTestProvider();
        $provider = $this->provider;
        Piwik::addAction('AIProviders.addAIProviders', function (AIProvidersList $providers) use ($provider): void {
            $providers->addProvider($provider);
        });
        Config::getInstance()->AIProviders = ['defaultProvider' => EventsTestProvider::ID];

        Piwik::addAction('AIProviders.beforeRequest', function (AIRequestContext $context): void {
            $this->beforeRequestContexts[] = $context;
        });
        Piwik::addAction('AIProviders.usage', function (AIUsage $usage): void {
            $this->usages[] = $usage;
        });
    }

    public function tearDown(): void
    {
        Config::getInstance()->AIProviders = [];
        Fixture::resetTranslations();

        parent::tearDown();
    }

    public function testCompletionReportsContextAndUsageWithTheSameRequestId(): void
    {
        // Allowlisted, so the requested model is kept although the provider is forced.
        Config::getInstance()->AIProviders = [
            'defaultProvider' => EventsTestProvider::ID,
            'providerSelectionAllowlist' => ['AIBrandInsights'],
        ];

        $this->provider->response = new AIProviderResponse(
            EventsTestProvider::ID,
            'Events Test',
            'reported-model',
            'Answer.',
            100,
            20,
            AIRequest::REASONING_NONE,
            250,
            'stop',
            WebSearchUsage::fromProviderData([], 2, []),
            0.004,
            300,
            40,
            1,
            ['endpoint' => 'ai_mode']
        );

        $this->service()->complete(
            (new AIRequest('Prompt', 'AIBrandInsights'))
                ->withFeatureKey('AIBrandInsights.promptQuery')
                ->withIdSite(3)
                ->withUsageReference('query-42')
                ->withMeta(['source' => 'scheduled'])
                ->withProviderId(EventsTestProvider::ID)
                ->withModel('requested-model')
                ->withMaxTokens(512)
                ->withWebSearchEnabled(true)
        );

        $this->assertCount(1, $this->beforeRequestContexts);
        $this->assertCount(1, $this->usages);

        $context = $this->beforeRequestContexts[0];
        $usage = $this->usages[0];
        $this->assertSame($context, $usage->getContext());
        $this->assertFalse($context->isProbe());
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $context->getRequestId());
        $this->assertSame(AIRequestContext::TYPE_COMPLETE, $context->getRequestType());
        $this->assertSame('AIBrandInsights.promptQuery', $context->getFeatureKey());
        $this->assertSame('AIBrandInsights', $context->getCallerPluginName());
        $this->assertSame(3, $context->getIdSite());
        $this->assertSame('query-42', $context->getUsageReference());
        $this->assertSame(EventsTestProvider::ID, $context->getProviderId());
        $this->assertSame('requested-model', $context->getModel());
        $this->assertSame(512, $context->getMaxOutputTokens());
        $this->assertTrue($context->isWebSearchEnabled());
        $this->assertSame(['source' => 'scheduled'], $context->getMeta());

        $this->assertSame(AIUsage::OUTCOME_SUCCESS, $usage->getOutcome());
        $this->assertTrue($usage->isSuccess());
        $this->assertSame('reported-model', $usage->getModel());
        $this->assertSame(100, $usage->getInputTokens());
        $this->assertSame(20, $usage->getOutputTokens());
        $this->assertSame(300, $usage->getCacheReadTokens());
        $this->assertSame(40, $usage->getCacheWriteTokens());
        $this->assertSame(2, $usage->getWebSearchCalls());
        $this->assertSame(1, $usage->getFlatFeeCalls());
        $this->assertSame(0.004, $usage->getProviderCost());
        $this->assertSame('stop', $usage->getStopReason());
        $this->assertSame(250, $usage->getDurationMs());
        $this->assertNull($usage->getErrorClass());
        $this->assertSame(['endpoint' => 'ai_mode'], $usage->getProviderMeta());
    }

    public function testFeatureKeyDefaultsToTheCallerPlugin(): void
    {
        $this->service()->complete(new AIRequest('Prompt', 'Goals'));
        $this->service()->complete((new AIRequest('Prompt', 'Goals'))->withFeatureKey(''));

        $this->assertSame('Goals.default', $this->beforeRequestContexts[0]->getFeatureKey());
        $this->assertSame('Goals.default', $this->beforeRequestContexts[1]->getFeatureKey());
        $this->assertNull($this->beforeRequestContexts[0]->getModel());
        $this->assertSame(0, $this->usages[0]->getWebSearchCalls());
    }

    public function testDeniedRequestThrowsWithTheDecisionAndIsNeverSent(): void
    {
        Piwik::addAction('AIProviders.beforeRequest', function (AIRequestContext $context, AIRequestDecision $decision): void {
            $decision->deny('limit_reached', 'Limit reached.', 1000, 1000, 0);
        });

        try {
            $this->service()->complete(new AIRequest('Prompt', 'Goals'));
            $this->fail('Expected the request to be denied.');
        } catch (AIQuotaExceededException $e) {
            $this->assertSame('Limit reached.', $e->getMessage());
            $this->assertSame('limit_reached', $e->getDecision()->getReason());
            $this->assertSame(1000, $e->getDecision()->getUsed());
            $this->assertSame(1000, $e->getDecision()->getLimit());
            $this->assertSame(0, $e->getDecision()->getRemaining());
        }

        $this->assertSame(0, $this->provider->calls);
        $this->assertSame([], $this->usages);
    }

    public function testFirstDenialStandsWhenAnotherListenerDeniesToo(): void
    {
        Piwik::addAction('AIProviders.beforeRequest', function (AIRequestContext $context, AIRequestDecision $decision): void {
            $decision->deny('disabled');
        });
        Piwik::addAction('AIProviders.beforeRequest', function (AIRequestContext $context, AIRequestDecision $decision): void {
            $decision->deny('limit_reached', 'Limit reached.');
        });

        Fixture::loadAllTranslations();

        try {
            $this->service()->complete(new AIRequest('Prompt', 'Goals'));
            $this->fail('Expected the request to be denied.');
        } catch (AIQuotaExceededException $e) {
            $this->assertSame('disabled', $e->getDecision()->getReason());
            $this->assertSame('The AI usage limit has been reached.', $e->getMessage());
        }
    }

    public function testEmptyCompletionIsReportedBeforeItThrows(): void
    {
        $this->provider->response = new AIProviderResponse(EventsTestProvider::ID, 'Events Test', 'm', '  ', 50, 0);

        try {
            $this->service()->complete(new AIRequest('Prompt', 'Goals'));
            $this->fail('Expected an empty response error.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('empty response', $e->getMessage());
        }

        $this->assertSame(AIUsage::OUTCOME_EMPTY, $this->usages[0]->getOutcome());
        $this->assertSame(50, $this->usages[0]->getInputTokens());
    }

    public function testProviderFailureIsReportedAndRethrown(): void
    {
        $this->provider->error = new AIProviderServerException('Provider is down.');

        try {
            $this->service()->complete(new AIRequest('Prompt', 'Goals'));
            $this->fail('Expected the provider error.');
        } catch (AIProviderServerException $e) {
            $this->assertSame($this->provider->error, $e);
        }

        $usage = $this->usages[0];
        $this->assertSame(AIUsage::OUTCOME_ERROR, $usage->getOutcome());
        $this->assertSame(AIProviderServerException::class, $usage->getErrorClass());
        $this->assertNull($usage->getInputTokens());
        $this->assertIsInt($usage->getDurationMs());
        $this->assertSame([], $usage->getProviderMeta());
        $this->assertSame($this->beforeRequestContexts[0], $usage->getContext());
    }

    public function testFailingUsageListenerDoesNotFailTheCallAndIsLogged(): void
    {
        Piwik::addAction('AIProviders.usage', function (): void {
            throw new \RuntimeException('Metering is down.');
        });

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('AIProviders.usage listener failed'),
                $this->callback(function (array $context): bool {
                    return $context['requestId'] === $this->beforeRequestContexts[0]->getRequestId()
                        && $context['exception']->getMessage() === 'Metering is down.';
                })
            );
        StaticContainer::getContainer()->set(LoggerInterface::class, $logger);

        $response = $this->service()->complete(new AIRequest('Prompt', 'Goals'));

        $this->assertSame('ok', $response->getText());
    }

    public function testConversationFiresBothEvents(): void
    {
        $this->service()->converse(
            (new AIConversationRequest(
                [['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Hello']]]],
                'AskMatomo'
            ))->withFeatureKey('AskMatomo.chat')->withIdSite(1)->withMeta(['turn' => 7])
        );

        $context = $this->beforeRequestContexts[0];
        $usage = $this->usages[0];
        $this->assertSame(AIRequestContext::TYPE_CONVERSE, $context->getRequestType());
        $this->assertSame('AskMatomo.chat', $context->getFeatureKey());
        $this->assertSame(1, $context->getIdSite());
        $this->assertFalse($context->isWebSearchEnabled());
        $this->assertSame(['turn' => 7], $context->getMeta());
        $this->assertSame($context, $usage->getContext());
        $this->assertSame(AIUsage::OUTCOME_SUCCESS, $usage->getOutcome());
        $this->assertSame(9, $usage->getInputTokens());
        $this->assertSame(['region' => 'eu'], $usage->getProviderMeta());
    }

    public function testAssertRequestAllowedIsAProbeAndPassesWhenAllowed(): void
    {
        $this->service()->assertRequestAllowed(new AIRequest('Prompt', 'Goals'));

        $this->assertTrue($this->beforeRequestContexts[0]->isProbe());
        $this->assertSame(AIRequestContext::TYPE_COMPLETE, $this->beforeRequestContexts[0]->getRequestType());
        $this->assertSame(0, $this->provider->calls);
        $this->assertSame([], $this->usages);
    }

    public function testAssertRequestAllowedStripsTheModelForACallerThatIsNotAllowlisted(): void
    {
        $this->service()->assertRequestAllowed((new AIRequest('Prompt', 'Goals'))->withModel('requested-model'));

        $this->assertNull($this->beforeRequestContexts[0]->getModel());
    }

    public function testAssertRequestAllowedThrowsWithoutCallingTheProvider(): void
    {
        Piwik::addAction('AIProviders.beforeRequest', function (AIRequestContext $context, AIRequestDecision $decision): void {
            $decision->deny('limit_reached');
        });

        try {
            $this->service()->assertRequestAllowed(new AIConversationRequest([], 'AskMatomo'));
            $this->fail('Expected the probe to be denied.');
        } catch (AIQuotaExceededException $e) {
            $this->assertSame('limit_reached', $e->getDecision()->getReason());
        }

        $this->assertSame(0, $this->provider->calls);
        $this->assertSame(AIRequestContext::TYPE_CONVERSE, $this->beforeRequestContexts[0]->getRequestType());
        $this->assertTrue($this->beforeRequestContexts[0]->isProbe());
        $this->assertSame([], $this->usages);
    }

    public function testDeniedConversationIsNeverSent(): void
    {
        Piwik::addAction('AIProviders.beforeRequest', function (AIRequestContext $context, AIRequestDecision $decision): void {
            $decision->deny('limit_reached');
        });

        try {
            $this->service()->converse($this->conversationRequest());
            $this->fail('Expected the conversation to be denied.');
        } catch (AIQuotaExceededException $e) {
            $this->assertSame('limit_reached', $e->getDecision()->getReason());
        }

        $this->assertSame(0, $this->provider->calls);
        $this->assertSame([], $this->usages);
    }

    public function testConversationFailureIsReportedAndRethrown(): void
    {
        $this->provider->error = new AIProviderServerException('Provider is down.');

        try {
            $this->service()->converse($this->conversationRequest());
            $this->fail('Expected the provider error.');
        } catch (AIProviderServerException $e) {
            $this->assertSame($this->provider->error, $e);
        }

        $this->assertSame(AIUsage::OUTCOME_ERROR, $this->usages[0]->getOutcome());
        $this->assertSame(AIRequestContext::TYPE_CONVERSE, $this->usages[0]->getContext()->getRequestType());
        $this->assertSame($this->beforeRequestContexts[0], $this->usages[0]->getContext());
    }

    public function testRemainingBudgetIsUnlimitedWithoutListenersAndTheStrictestOtherwise(): void
    {
        $this->assertNull($this->service()->getRemainingBudget('AIBrandInsights.promptQuery'));

        foreach ([40, 12] as $remaining) {
            Piwik::addAction('AIProviders.getRemainingBudget', function (string $featureKey, ?int &$budget) use ($remaining): void {
                if ($budget === null || $remaining < $budget) {
                    $budget = $remaining;
                }
            });
        }

        $this->assertSame(12, $this->service()->getRemainingBudget('AIBrandInsights.promptQuery'));
    }

    public function testCheckFeatureAllowedPassesThePayloadAndReturnsTheDecision(): void
    {
        $this->assertTrue($this->service()->checkFeatureAllowed('AIBrandInsights.addPrompt')->isAllowed());

        Piwik::addAction(
            'AIProviders.checkFeatureAllowed',
            function (string $featureKey, array $payload, AIRequestDecision $decision): void {
                if ($featureKey === 'AIBrandInsights.addPrompt' && $payload['currentCount'] >= 1) {
                    $decision->deny('limit_reached', 'Prompt limit reached.');
                }
            }
        );

        $decision = $this->service()->checkFeatureAllowed('AIBrandInsights.addPrompt', ['currentCount' => 1]);

        $this->assertFalse($decision->isAllowed());
        $this->assertSame('Prompt limit reached.', $decision->getMessage());
    }

    private function conversationRequest(): AIConversationRequest
    {
        return new AIConversationRequest(
            [['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Hello']]]],
            'AskMatomo'
        );
    }

    private function service(): AIProviderService
    {
        return StaticContainer::get(AIProviderService::class);
    }
}

/**
 * Returns a canned response or throws a canned error, and counts its calls.
 */
class EventsTestProvider extends AIProvider
{
    public const ID = 'events-test';

    /**
     * @var AIProviderResponse|null
     */
    public $response = null;

    /**
     * @var \Throwable|null
     */
    public $error = null;

    /**
     * @var int
     */
    public $calls = 0;

    public function __construct()
    {
        parent::__construct(self::ID, 'Events Test', 'Events test provider.');
    }

    public function isConfigured(array $configuration): bool
    {
        return true;
    }

    public function supportsWebSearch(): bool
    {
        return true;
    }

    public function supportsConversations(): bool
    {
        return true;
    }

    public function complete(AIRequest $request, array $configuration): AIProviderResponse
    {
        $this->calls++;

        if ($this->error !== null) {
            throw $this->error;
        }

        return $this->response ?? new AIProviderResponse($this->getId(), $this->getName(), 'm', 'ok');
    }

    public function converse(AIConversationRequest $request, array $configuration): AIConversationResponse
    {
        $this->calls++;

        if ($this->error !== null) {
            throw $this->error;
        }

        return new AIConversationResponse(
            $this->getId(),
            $this->getName(),
            'm',
            [['type' => 'text', 'text' => 'ok']],
            AIConversationResponse::STOP_END_TURN,
            9,
            3,
            100,
            null,
            null,
            null,
            0,
            ['region' => 'eu']
        );
    }
}
