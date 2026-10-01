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
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugins\AIProviders\AIConversationRequest;
use Piwik\Plugins\AIProviders\AIConversationResponse;
use Piwik\Plugins\AIProviders\AIProviderResponse;
use Piwik\Plugins\AIProviders\AIProviderService;
use Piwik\Plugins\AIProviders\AIProvidersList;
use Piwik\Plugins\AIProviders\AIRequest;
use Piwik\Plugins\AIProviders\API;
use Piwik\Plugins\AIProviders\Exception\AIProviderClientException;
use Piwik\Plugins\AIProviders\Model\Configuration;
use Piwik\Plugins\AIProviders\Provider\AIProvider;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Service-level coverage for {@link AIProviderService::converse()} and
 * {@link AIProviderService::canConverse()}: provider resolution, the
 * conversation-capability guard, and the managed (forced provider) flow.
 *
 * @group AIProviders
 * @group AIProvidersConverse
 * @group Plugins
 */
class ConverseServiceTest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    public function setUp(): void
    {
        parent::setUp();

        // Ensure no forced configuration leaks between tests.
        Config::getInstance()->AIProviders = [];

        Fixture::createSuperUser();
        FakeAccess::clearAccess(true);

        $this->api = API::getInstance();

        // Reset stored settings to their defaults between tests.
        $this->api->saveSettings('', Configuration::CAPABILITY_INSTANT, '{}');
    }

    public function tearDown(): void
    {
        Config::getInstance()->AIProviders = [];

        parent::tearDown();
    }

    public function testConverseRoutesToConfiguredDefaultProvider(): void
    {
        $this->configureClaudeAsDefaultProvider();

        $capturedUrl = null;
        $capturedBody = null;
        $this->mockClaudeConverseResponse(
            [
                'content' => [['type' => 'text', 'text' => 'Hello back.']],
                'stop_reason' => 'end_turn',
                'usage' => ['input_tokens' => 5, 'output_tokens' => 2],
            ],
            $capturedUrl,
            $capturedBody
        );

        $request = (new AIConversationRequest(
            [['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Hello']]]],
            'Test'
        ))->withSystemPrompt('You are concise.');

        $response = StaticContainer::get(AIProviderService::class)->converse($request);

        $this->assertSame('anthropic', $response->getProviderId());
        $this->assertSame([['type' => 'text', 'text' => 'Hello back.']], $response->getContent());
        $this->assertSame('Hello back.', $response->getText());
        $this->assertSame(AIConversationResponse::STOP_END_TURN, $response->getStopReason());
        $this->assertSame(5, $response->getInputTokens());
        $this->assertSame(2, $response->getOutputTokens());
        $this->assertIsInt($response->getExecutionTimeMs());

        $this->assertSame('https://api.anthropic.com/v1/messages', $capturedUrl);
        $this->assertIsArray($capturedBody);
        $this->assertSame('claude-haiku-4-5', $capturedBody['model']);
        $this->assertSame('You are concise.', $capturedBody['system']);
        $this->assertSame(
            [['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Hello']]]],
            $capturedBody['messages']
        );
    }

    public function testConverseAllowsToolOnlyTurnsWithEmptyText(): void
    {
        // Unlike complete(), an empty text response is valid for converse():
        // a turn may consist solely of tool_use blocks.
        $this->configureClaudeAsDefaultProvider();

        $capturedUrl = null;
        $capturedBody = null;
        $this->mockClaudeConverseResponse(
            [
                'content' => [
                    ['type' => 'tool_use', 'id' => 'tu_1', 'name' => 'matomo_site_list', 'input' => []],
                ],
                'stop_reason' => 'tool_use',
                'usage' => ['input_tokens' => 5, 'output_tokens' => 2],
            ],
            $capturedUrl,
            $capturedBody
        );

        $response = StaticContainer::get(AIProviderService::class)->converse(
            new AIConversationRequest(
                [['role' => 'user', 'content' => [['type' => 'text', 'text' => 'List my sites']]]],
                'Test'
            )
        );

        $this->assertSame('', $response->getText());
        $this->assertSame(AIConversationResponse::STOP_TOOL_USE, $response->getStopReason());
        $this->assertSame(
            [['type' => 'tool_use', 'id' => 'tu_1', 'name' => 'matomo_site_list', 'input' => []]],
            $response->getContent()
        );
    }

    public function testConverseAppliesConfiguredDefaultCapabilityLevel(): void
    {
        $this->api->saveSettings('', Configuration::CAPABILITY_THINKING, '{}');
        $this->forceCapabilityRecordingDefaultProvider();

        $response = StaticContainer::get(AIProviderService::class)->converse(
            new AIConversationRequest([], 'Test')
        );

        $this->assertSame(Configuration::CAPABILITY_THINKING, $response->getModel());
    }

    public function testConversePreservesRequestCapabilityOverride(): void
    {
        $this->api->saveSettings('', Configuration::CAPABILITY_THINKING, '{}');
        $this->forceCapabilityRecordingDefaultProvider();

        $response = StaticContainer::get(AIProviderService::class)->converse(
            (new AIConversationRequest([], 'Test'))
                ->withCapabilityLevel(Configuration::CAPABILITY_INSTANT)
        );

        $this->assertSame(Configuration::CAPABILITY_INSTANT, $response->getModel());
    }

    public function testForcedProviderOverridesRequestedProviderAndStripsModel(): void
    {
        // Simulate a managed environment forcing a conversation-capable
        // provider with config-file credentials.
        Config::getInstance()->AIProviders = [
            'defaultProvider' => 'anthropic',
            'anthropicApiKey' => 'config-claude-key',
        ];

        $capturedUrl = null;
        $capturedBody = null;
        $this->mockClaudeConverseResponse(
            [
                'content' => [['type' => 'text', 'text' => 'Forced provider answered.']],
                'stop_reason' => 'end_turn',
                'usage' => ['input_tokens' => 5, 'output_tokens' => 2],
            ],
            $capturedUrl,
            $capturedBody
        );

        // The caller is not on the providerSelectionAllowlist, so both the
        // requested provider and the requested model are overridden.
        $response = StaticContainer::get(AIProviderService::class)->converse(
            (new AIConversationRequest(
                [['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Hello']]]],
                'Goals'
            ))
                ->withProviderId('openai')
                ->withModel('gpt-4o')
        );

        $this->assertSame('anthropic', $response->getProviderId());
        $this->assertSame('https://api.anthropic.com/v1/messages', $capturedUrl);
        $this->assertIsArray($capturedBody);
        // The requested model was stripped, so the forced provider's default is used.
        $this->assertSame('claude-haiku-4-5', $capturedBody['model']);
    }

    public function testAllowlistedCallerMaySelectProviderAndModelWhenProviderIsForced(): void
    {
        Config::getInstance()->AIProviders = [
            'defaultProvider' => 'openai',
            'openaiApiKey' => 'config-openai-key',
            'anthropicApiKey' => 'config-claude-key',
            'providerSelectionAllowlist' => ['ExamplePlugin'],
        ];

        $capturedUrl = null;
        $capturedBody = null;
        $this->mockClaudeConverseResponse(
            [
                'content' => [['type' => 'text', 'text' => 'Allowlisted answer.']],
                'stop_reason' => 'end_turn',
                'usage' => ['input_tokens' => 5, 'output_tokens' => 2],
            ],
            $capturedUrl,
            $capturedBody
        );

        $response = StaticContainer::get(AIProviderService::class)->converse(
            (new AIConversationRequest(
                [['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Hello']]]],
                'ExamplePlugin'
            ))
                ->withProviderId('anthropic')
                ->withModel('claude-sonnet-4-5')
        );

        $this->assertSame('anthropic', $response->getProviderId());
        $this->assertSame('https://api.anthropic.com/v1/messages', $capturedUrl);
        $this->assertIsArray($capturedBody);
        $this->assertSame('claude-sonnet-4-5', $capturedBody['model']);
    }

    public function testConverseThrowsForProviderWithoutConversationSupport(): void
    {
        // All built-in providers now implement converse(); register a
        // completion-only provider and force it as default to exercise the
        // capability guard.
        $this->forceCompletionOnlyDefaultProvider();

        $this->expectException(AIProviderClientException::class);
        $this->expectExceptionMessage('Completion Only does not support multi-turn conversations.');

        StaticContainer::get(AIProviderService::class)->converse(
            new AIConversationRequest(
                [['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Hello']]]],
                'Test'
            )
        );
    }

    public function testCanConverseIsFalseWithoutConfiguredDefaultProvider(): void
    {
        $this->assertFalse(StaticContainer::get(AIProviderService::class)->canConverse());
    }

    public function testCanConverseIsFalseWhenDefaultProviderDoesNotSupportConversations(): void
    {
        $this->forceCompletionOnlyDefaultProvider();

        $service = StaticContainer::get(AIProviderService::class);
        $this->assertFalse($service->canConverse());
        $this->assertSame(
            AIProviderService::CONVERSATION_PROVIDER_UNSUPPORTED,
            $service->getConversationAvailability()['status']
        );
    }

    public function testCanConverseIsFalseWhenConversationCapableProviderIsNotConfigured(): void
    {
        // A managed environment forces Anthropic, but no credentials are available.
        Config::getInstance()->AIProviders = ['defaultProvider' => 'anthropic'];

        $this->assertFalse(StaticContainer::get(AIProviderService::class)->canConverse());
    }

    public function testCanConverseIsTrueWhenDefaultProviderIsConfiguredAndConversationCapable(): void
    {
        $this->configureClaudeAsDefaultProvider();

        $this->assertTrue(StaticContainer::get(AIProviderService::class)->canConverse());
    }

    private function configureClaudeAsDefaultProvider(): void
    {
        $this->api->saveSettings(
            'anthropic',
            Configuration::CAPABILITY_INSTANT,
            (string) json_encode([
                'anthropic' => ['apiKey' => 'secret-claude-key', 'endpointUrl' => ''],
            ])
        );
    }

    /**
     * Registers a completion-only provider and forces it as the default, so
     * the conversation-capability guard can be exercised even though every
     * built-in provider now supports conversations.
     */
    private function forceCompletionOnlyDefaultProvider(): void
    {
        Piwik::addAction('AIProviders.addAIProviders', function (AIProvidersList $providers): void {
            $providers->addProvider(new CompletionOnlyTestProvider());
        });

        Config::getInstance()->AIProviders = ['defaultProvider' => CompletionOnlyTestProvider::ID];
    }

    private function forceCapabilityRecordingDefaultProvider(): void
    {
        Piwik::addAction('AIProviders.addAIProviders', function (AIProvidersList $providers): void {
            $providers->addProvider(new CapabilityRecordingTestProvider());
        });

        Config::getInstance()->AIProviders = ['defaultProvider' => CapabilityRecordingTestProvider::ID];
    }

    /**
     * Mocks the Anthropic Messages endpoint for converse() calls.
     *
     * @param array<string, mixed> $responseBody Decoded provider response to return.
     * @param string|null $capturedUrl Set to the requested URL.
     * @param array<string, mixed>|null $capturedBody Set to the decoded request body.
     */
    private function mockClaudeConverseResponse(
        array $responseBody,
        ?string &$capturedUrl,
        ?array &$capturedBody
    ): void {
        Piwik::addAction('Http.sendHttpRequest', function (
            string $url,
            array $httpEventParams,
            ?string &$response,
            ?int &$status,
            array &$headers
        ) use (
            $responseBody,
            &$capturedUrl,
            &$capturedBody
        ): void {
            $capturedUrl = $url;
            $capturedBody = json_decode((string) $httpEventParams['body'], true);

            $response = (string) json_encode($responseBody);
            $status = 200;
            $headers = ['Content-Type' => 'application/json'];
        });
    }

    public function provideContainerConfig(): array
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}

/**
 * A provider that only implements completions, used to exercise the
 * conversation-capability guard now that every built-in provider supports
 * conversations.
 */
class CompletionOnlyTestProvider extends AIProvider
{
    public const ID = 'completion-only';

    public function __construct()
    {
        parent::__construct(self::ID, 'Completion Only', 'Completion-only test provider.');
    }

    public function isConfigured(array $configuration): bool
    {
        return true;
    }

    public function complete(AIRequest $request, array $configuration): AIProviderResponse
    {
        return new AIProviderResponse($this->getId(), $this->getName(), 'test-model', 'ok');
    }
}

class CapabilityRecordingTestProvider extends AIProvider
{
    public const ID = 'capability-recording';

    public function __construct()
    {
        parent::__construct(self::ID, 'Capability Recording', 'Capability recording test provider.');
    }

    public function isConfigured(array $configuration): bool
    {
        return true;
    }

    public function complete(AIRequest $request, array $configuration): AIProviderResponse
    {
        return new AIProviderResponse($this->getId(), $this->getName(), 'test-model', 'ok');
    }

    public function supportsConversations(): bool
    {
        return true;
    }

    public function converse(AIConversationRequest $request, array $configuration): AIConversationResponse
    {
        return new AIConversationResponse(
            $this->getId(),
            $this->getName(),
            (string) $request->getCapabilityLevel(),
            [['type' => 'text', 'text' => 'ok']],
            AIConversationResponse::STOP_END_TURN
        );
    }
}
