<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\AIProviders\AIConversationRequest;
use Piwik\Plugins\AIProviders\AIProviderResponse;
use Piwik\Plugins\AIProviders\AIRequest;
use Piwik\Plugins\AIProviders\Exception\AIProviderClientException;
use Piwik\Plugins\AIProviders\Provider\AIProvider;
use Piwik\Plugins\AIProviders\Provider\Anthropic;

/**
 * Tests the full converse() translation between the canonical message shape
 * and the Anthropic Messages API wire format, with HTTP replaced by a
 * recording sendJsonRequest() override.
 *
 * @group AIProviders
 * @group Plugins
 */
class AnthropicConverseTest extends TestCase
{
    private const CONFIGURATION = ['apiKey' => 'secret-claude-key', 'endpointUrl' => ''];

    public function testPayloadUsesDefaultModelEndpointHeadersAndRequestDefaults(): void
    {
        $claude = new RecordingAnthropic();

        $claude->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertSame('https://api.anthropic.com/v1/messages', $claude->sentUrl);
        $this->assertSame(
            [
                'anthropic-version' => '2023-06-01',
                'x-api-key' => 'secret-claude-key',
            ],
            $claude->sentHeaders
        );
        $this->assertSame('claude-haiku-4-5', $claude->sentPayload['model']);
        $this->assertSame(AIConversationRequest::DEFAULT_MAX_TOKENS, $claude->sentPayload['max_tokens']);
        $this->assertSame(AIRequest::DEFAULT_TEMPERATURE, $claude->sentPayload['temperature']);
        $this->assertSame(AIConversationRequest::DEFAULT_TIMEOUT_SECONDS, $claude->sentTimeoutSeconds);
        // No system prompt was set, so the payload carries no system key.
        $this->assertArrayNotHasKey('system', $claude->sentPayload);
        $this->assertSame(
            [['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Hello']]]],
            $claude->sentPayload['messages']
        );
    }

    public function testPayloadHonoursRequestedModelOptionsSystemPromptAndTimeout(): void
    {
        $claude = new RecordingAnthropic();

        $request = $this->simpleRequest()
            ->withModel('claude-sonnet-4-5')
            ->withSystemPrompt('You are concise.')
            ->withMaxTokens(512)
            ->withTemperature(0.9)
            ->withTimeoutSeconds(120);

        $claude->converse($request, self::CONFIGURATION);

        $this->assertSame('claude-sonnet-4-5', $claude->sentPayload['model']);
        $this->assertSame('You are concise.', $claude->sentPayload['system']);
        $this->assertSame(512, $claude->sentPayload['max_tokens']);
        $this->assertSame(0.9, $claude->sentPayload['temperature']);
        $this->assertSame(120, $claude->sentTimeoutSeconds);
    }

    public function testMessagesTranslateToAnthropicRolesAndBlocks(): void
    {
        $claude = new RecordingAnthropic();

        $request = new AIConversationRequest(
            [
                ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'delete site 1']]],
                [
                    'role' => 'assistant',
                    'content' => [
                        ['type' => 'text', 'text' => 'Deleting it now.'],
                        ['type' => 'tool_use', 'id' => 'tu_1', 'name' => 'del', 'input' => []],
                    ],
                ],
                [
                    'role' => 'tool',
                    'content' => [[
                        'type' => 'tool_result',
                        'tool_use_id' => 'tu_1',
                        'content' => [['type' => 'text', 'text' => 'deleted']],
                        'structuredContent' => null,
                        'is_error' => false,
                    ]],
                ],
            ],
            'AskMatomo'
        );

        $claude->converse($request, self::CONFIGURATION);

        $messages = $claude->sentPayload['messages'];

        $this->assertCount(3, $messages);
        // The canonical 'tool' role folds into a 'user' message so the
        // tool_use/tool_result pairing survives the round trip.
        $this->assertSame(['user', 'assistant', 'user'], array_column($messages, 'role'));

        $this->assertSame([['type' => 'text', 'text' => 'delete site 1']], $messages[0]['content']);

        $this->assertSame('text', $messages[1]['content'][0]['type']);
        $this->assertSame('tool_use', $messages[1]['content'][1]['type']);
        $this->assertSame('tu_1', $messages[1]['content'][1]['id']);
        $this->assertSame('del', $messages[1]['content'][1]['name']);

        // Empty tool_use input must encode as a JSON object, not an array.
        $this->assertStringContainsString('"input":{}', (string) json_encode($claude->sentPayload));

        $this->assertSame(
            [[
                'type' => 'tool_result',
                'tool_use_id' => 'tu_1',
                'content' => [['type' => 'text', 'text' => 'deleted']],
                'is_error' => false,
            ]],
            $messages[2]['content']
        );
    }

    public function testNonEmptyToolUseInputIsForwardedAsIs(): void
    {
        $claude = new RecordingAnthropic();

        $request = new AIConversationRequest(
            [[
                'role' => 'assistant',
                'content' => [['type' => 'tool_use', 'id' => 'tu_1', 'name' => 'del', 'input' => ['idSite' => 1]]],
            ]],
            'AskMatomo'
        );

        $claude->converse($request, self::CONFIGURATION);

        $this->assertSame(
            ['idSite' => 1],
            $claude->sentPayload['messages'][0]['content'][0]['input']
        );
    }

    public function testToolResultStructuredContentBecomesSingleJsonTextBlock(): void
    {
        $claude = new RecordingAnthropic();

        $request = new AIConversationRequest(
            [[
                'role' => 'tool',
                'content' => [[
                    'type' => 'tool_result',
                    'tool_use_id' => 'tu_1',
                    'content' => [['type' => 'text', 'text' => '{"sites":[{"idsite":1}]}']],
                    'structuredContent' => ['sites' => [['idsite' => 1]]],
                    'is_error' => false,
                ]],
            ]],
            'AskMatomo'
        );

        $claude->converse($request, self::CONFIGURATION);

        // structuredContent wins over the MCP text echo and is serialised
        // into a single text block.
        $this->assertSame(
            [['type' => 'text', 'text' => '{"sites":[{"idsite":1}]}']],
            $claude->sentPayload['messages'][0]['content'][0]['content']
        );
    }

    public function testToolResultTranslatesMcpBlocksAndStringifiesNonTextBlocks(): void
    {
        $claude = new RecordingAnthropic();

        $request = new AIConversationRequest(
            [[
                'role' => 'tool',
                'content' => [[
                    'type' => 'tool_result',
                    'tool_use_id' => 'tu_1',
                    'content' => [
                        ['type' => 'text', 'text' => 'plain text payload'],
                        ['type' => 'resource_link', 'uri' => 'https://example.org/report'],
                    ],
                    'structuredContent' => null,
                    'is_error' => false,
                ]],
            ]],
            'AskMatomo'
        );

        $claude->converse($request, self::CONFIGURATION);

        $this->assertSame(
            [
                ['type' => 'text', 'text' => 'plain text payload'],
                ['type' => 'text', 'text' => '{"type":"resource_link","uri":"https:\/\/example.org\/report"}'],
            ],
            $claude->sentPayload['messages'][0]['content'][0]['content']
        );
    }

    public function testEmptyToolResultContentBecomesEmptyTextBlockAndErrorFlagPassesThrough(): void
    {
        $claude = new RecordingAnthropic();

        $request = new AIConversationRequest(
            [[
                'role' => 'tool',
                'content' => [[
                    'type' => 'tool_result',
                    'tool_use_id' => 'tu_1',
                    'content' => [],
                    'structuredContent' => null,
                    'is_error' => true,
                ]],
            ]],
            'AskMatomo'
        );

        $claude->converse($request, self::CONFIGURATION);

        $this->assertSame(
            [
                'type' => 'tool_result',
                'tool_use_id' => 'tu_1',
                'content' => [['type' => 'text', 'text' => '']],
                'is_error' => true,
            ],
            $claude->sentPayload['messages'][0]['content'][0]
        );
    }

    public function testToolsAreOmittedWhenCatalogueIsEmpty(): void
    {
        $claude = new RecordingAnthropic();

        $claude->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertArrayNotHasKey('tools', $claude->sentPayload);
    }

    public function testToolCatalogueTranslatesToAnthropicToolsWithoutMcpHints(): void
    {
        $claude = new RecordingAnthropic();

        $request = $this->simpleRequest()->withTools([
            [
                'name' => 'matomo_site_list',
                'title' => null,
                'description' => 'Lists sites',
                'inputSchema' => ['type' => 'object', 'properties' => ['idSite' => ['type' => 'integer']]],
                'outputSchema' => null,
                'readOnly' => true,
                'destructive' => false,
                'idempotent' => true,
                'openWorld' => null,
            ],
        ]);

        $claude->converse($request, self::CONFIGURATION);

        $this->assertSame(
            [[
                'name' => 'matomo_site_list',
                'description' => 'Lists sites',
                'input_schema' => ['type' => 'object', 'properties' => ['idSite' => ['type' => 'integer']]],
            ]],
            $claude->sentPayload['tools']
        );
        // The MCP annotation hints stay in the catalogue for the caller's
        // approval flow and must not reach the provider.
        $this->assertSame(
            ['name', 'description', 'input_schema'],
            array_keys($claude->sentPayload['tools'][0])
        );
    }

    public function testResponseContentIsParsedIntoCanonicalBlocks(): void
    {
        $claude = new RecordingAnthropic();
        $claude->cannedResponse = [
            'content' => [
                ['type' => 'thinking', 'thinking' => 'Let me check the sites.'],
                ['type' => 'text', 'text' => 'Checking your sites now.'],
                ['type' => 'tool_use', 'id' => 'tu_42', 'name' => 'matomo_site_list', 'input' => ['idSite' => 1]],
            ],
            'stop_reason' => 'tool_use',
            'usage' => ['input_tokens' => 12, 'output_tokens' => 7],
        ];

        $response = $claude->converse($this->simpleRequest(), self::CONFIGURATION);

        // Unknown block types (thinking, …) are dropped; text and tool_use
        // blocks come back in the canonical shape.
        $this->assertSame(
            [
                ['type' => 'text', 'text' => 'Checking your sites now.'],
                ['type' => 'tool_use', 'id' => 'tu_42', 'name' => 'matomo_site_list', 'input' => ['idSite' => 1]],
            ],
            $response->getContent()
        );
        $this->assertSame('tool_use', $response->getStopReason());
        $this->assertSame(12, $response->getInputTokens());
        $this->assertSame(7, $response->getOutputTokens());
        $this->assertSame('anthropic', $response->getProviderId());
        $this->assertSame('Anthropic', $response->getProviderName());
        $this->assertSame('claude-haiku-4-5', $response->getModel());
    }

    public function testUnknownStopReasonPassesThrough(): void
    {
        $claude = new RecordingAnthropic();
        $claude->cannedResponse = [
            'content' => [['type' => 'text', 'text' => 'Hi.']],
            'stop_reason' => 'refusal',
            'usage' => ['input_tokens' => 1, 'output_tokens' => 1],
        ];

        $response = $claude->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertSame('refusal', $response->getStopReason());
    }

    public function testMissingUsageYieldsNullTokenCounts(): void
    {
        $claude = new RecordingAnthropic();
        $claude->cannedResponse = [
            'content' => [['type' => 'text', 'text' => 'Hi.']],
            'stop_reason' => 'end_turn',
        ];

        $response = $claude->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertNull($response->getInputTokens());
        $this->assertNull($response->getOutputTokens());
    }

    public function testClaudeSupportsConversations(): void
    {
        $this->assertTrue((new Anthropic())->supportsConversations());
    }

    public function testProvidersWithoutConverseOverrideRejectConversations(): void
    {
        $provider = new MinimalCompletionOnlyProvider();

        $this->assertFalse($provider->supportsConversations());

        $this->expectException(AIProviderClientException::class);
        $this->expectExceptionMessage('Completion Only does not support multi-turn conversations.');

        $provider->converse($this->simpleRequest(), self::CONFIGURATION);
    }

    private function simpleRequest(): AIConversationRequest
    {
        return new AIConversationRequest(
            [['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Hello']]]],
            'AskMatomo'
        );
    }
}

/**
 * Records the wire request instead of performing HTTP and returns a canned
 * decoded response.
 */
class RecordingAnthropic extends Anthropic
{
    /** @var string|null */
    public $sentUrl = null;

    /** @var array<string, string> */
    public $sentHeaders = [];

    /** @var array<string, mixed> */
    public $sentPayload = [];

    /** @var int|null */
    public $sentTimeoutSeconds = null;

    /** @var array<string, mixed> */
    public $cannedResponse = [
        'content' => [['type' => 'text', 'text' => 'Hi there.']],
        'stop_reason' => 'end_turn',
        'usage' => ['input_tokens' => 3, 'output_tokens' => 4],
    ];

    protected function sendJsonRequest(string $url, array $headers, array $payload, int $timeoutSeconds = 30): array
    {
        $this->sentUrl = $url;
        $this->sentHeaders = $headers;
        $this->sentPayload = $payload;
        $this->sentTimeoutSeconds = $timeoutSeconds;

        return $this->cannedResponse;
    }
}

/**
 * Minimal provider that does not override converse(), to test the base-class
 * default behaviour.
 */
class MinimalCompletionOnlyProvider extends AIProvider
{
    public function __construct()
    {
        parent::__construct('completion-only', 'Completion Only', 'Test-only provider');
    }

    public function complete(AIRequest $request, array $configuration): AIProviderResponse
    {
        return $this->buildResponse($request, 'test-model', 'canned completion');
    }
}
