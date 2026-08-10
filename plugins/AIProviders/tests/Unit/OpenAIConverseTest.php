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
use Piwik\Plugins\AIProviders\Provider\OpenAI;

/**
 * Tests the full converse() translation between the canonical message shape
 * and the OpenAI Chat Completions API wire format, with HTTP replaced by a
 * recording sendJsonRequest() override.
 *
 * @group AIProviders
 * @group Plugins
 */
class OpenAIConverseTest extends TestCase
{
    private const CONFIGURATION = ['apiKey' => 'secret-openai-key', 'endpointUrl' => ''];

    public function testPayloadUsesDefaultModelEndpointHeadersAndRequestDefaults(): void
    {
        $openAI = new RecordingOpenAI();

        $openAI->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertSame('https://api.openai.com/v1/chat/completions', $openAI->sentUrl);
        $this->assertSame(['Authorization' => 'Bearer secret-openai-key'], $openAI->sentHeaders);
        $this->assertSame('gpt-5.4-mini', $openAI->sentPayload['model']);
        $this->assertSame(AIConversationRequest::DEFAULT_MAX_TOKENS, $openAI->sentPayload['max_completion_tokens']);
        $this->assertArrayNotHasKey('max_tokens', $openAI->sentPayload);
        $this->assertArrayNotHasKey('temperature', $openAI->sentPayload);
        $this->assertSame(AIConversationRequest::DEFAULT_TIMEOUT_SECONDS, $openAI->sentTimeoutSeconds);
        // No system prompt was set, so the messages start with the user turn.
        $this->assertSame(
            [['role' => 'user', 'content' => 'Hello']],
            $openAI->sentPayload['messages']
        );
    }

    public function testPayloadHonoursRequestedModelOptionsSystemPromptAndTimeout(): void
    {
        $openAI = new RecordingOpenAI();

        $request = $this->simpleRequest()
            ->withModel('gpt-4o')
            ->withSystemPrompt('You are concise.')
            ->withMaxTokens(512)
            ->withTemperature(0.9)
            ->withTimeoutSeconds(120);

        $openAI->converse($request, self::CONFIGURATION);

        $this->assertSame('gpt-4o', $openAI->sentPayload['model']);
        $this->assertSame(512, $openAI->sentPayload['max_completion_tokens']);
        $this->assertArrayNotHasKey('temperature', $openAI->sentPayload);
        $this->assertSame(120, $openAI->sentTimeoutSeconds);
        // The system prompt is prepended as the first message.
        $this->assertSame(
            ['role' => 'system', 'content' => 'You are concise.'],
            $openAI->sentPayload['messages'][0]
        );
    }

    public function testSystemMessageOmittedWhenNotSet(): void
    {
        $openAI = new RecordingOpenAI();

        $openAI->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertNotSame('system', $openAI->sentPayload['messages'][0]['role']);
    }

    public function testAssistantTextAndToolCallsTranslateWithJsonStringArguments(): void
    {
        $openAI = new RecordingOpenAI();

        $request = new AIConversationRequest(
            [
                ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'delete site 1']]],
                [
                    'role' => 'assistant',
                    'content' => [
                        ['type' => 'text', 'text' => 'Deleting it now.'],
                        ['type' => 'tool_use', 'id' => 'tu_1', 'name' => 'del', 'input' => ['idSite' => 1]],
                        ['type' => 'tool_use', 'id' => 'tu_2', 'name' => 'noop', 'input' => []],
                    ],
                ],
            ],
            'AskMatomo'
        );

        $openAI->converse($request, self::CONFIGURATION);

        $messages = $openAI->sentPayload['messages'];

        $this->assertSame('user', $messages[0]['role']);
        $this->assertSame('delete site 1', $messages[0]['content']);

        $assistant = $messages[1];
        $this->assertSame('assistant', $assistant['role']);
        $this->assertSame('Deleting it now.', $assistant['content']);

        // arguments must be a JSON string, and empty input encodes as '{}'.
        $this->assertSame(
            [
                [
                    'id' => 'tu_1',
                    'type' => 'function',
                    'function' => ['name' => 'del', 'arguments' => '{"idSite":1}'],
                ],
                [
                    'id' => 'tu_2',
                    'type' => 'function',
                    'function' => ['name' => 'noop', 'arguments' => '{}'],
                ],
            ],
            $assistant['tool_calls']
        );
        $this->assertIsString($assistant['tool_calls'][0]['function']['arguments']);
    }

    public function testAssistantWithNoTextEmitsEmptyStringContent(): void
    {
        $openAI = new RecordingOpenAI();

        $request = new AIConversationRequest(
            [[
                'role' => 'assistant',
                'content' => [['type' => 'tool_use', 'id' => 'tu_1', 'name' => 'del', 'input' => []]],
            ]],
            'AskMatomo'
        );

        $openAI->converse($request, self::CONFIGURATION);

        $assistant = $openAI->sentPayload['messages'][0];
        $this->assertSame('', $assistant['content']);
        $this->assertArrayHasKey('tool_calls', $assistant);
    }

    public function testToolMessageWithTwoResultsExpandsIntoTwoToolMessages(): void
    {
        $openAI = new RecordingOpenAI();

        $request = new AIConversationRequest(
            [[
                'role' => 'tool',
                'content' => [
                    [
                        'type' => 'tool_result',
                        'tool_use_id' => 'tu_1',
                        'content' => [['type' => 'text', 'text' => '{"sites":[{"idsite":1}]}']],
                        'structuredContent' => ['sites' => [['idsite' => 1]]],
                        'is_error' => false,
                    ],
                    [
                        'type' => 'tool_result',
                        'tool_use_id' => 'tu_2',
                        'content' => [
                            ['type' => 'text', 'text' => 'plain text payload'],
                            ['type' => 'resource_link', 'uri' => 'https://example.org/report'],
                        ],
                        'structuredContent' => null,
                        'is_error' => false,
                    ],
                ],
            ]],
            'AskMatomo'
        );

        $openAI->converse($request, self::CONFIGURATION);

        $messages = $openAI->sentPayload['messages'];

        $this->assertCount(2, $messages);
        // structuredContent wins over the MCP text echo and is serialised.
        $this->assertSame(
            ['role' => 'tool', 'tool_call_id' => 'tu_1', 'content' => '{"sites":[{"idsite":1}]}'],
            $messages[0]
        );
        // MCP text fallback: text blocks concatenated, non-text JSON-encoded.
        $this->assertSame(
            [
                'role' => 'tool',
                'tool_call_id' => 'tu_2',
                'content' => "plain text payload\n" . '{"type":"resource_link","uri":"https:\/\/example.org\/report"}',
            ],
            $messages[1]
        );
    }

    public function testEmptyToolResultContentBecomesEmptyString(): void
    {
        $openAI = new RecordingOpenAI();

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

        $openAI->converse($request, self::CONFIGURATION);

        $this->assertSame(
            ['role' => 'tool', 'tool_call_id' => 'tu_1', 'content' => ''],
            $openAI->sentPayload['messages'][0]
        );
    }

    public function testToolsAreOmittedWhenCatalogueIsEmpty(): void
    {
        $openAI = new RecordingOpenAI();

        $openAI->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertArrayNotHasKey('tools', $openAI->sentPayload);
    }

    public function testToolCatalogueTranslatesToOpenAIToolsWithoutMcpHints(): void
    {
        $openAI = new RecordingOpenAI();

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

        $openAI->converse($request, self::CONFIGURATION);

        $this->assertSame(
            [[
                'type' => 'function',
                'function' => [
                    'name' => 'matomo_site_list',
                    'description' => 'Lists sites',
                    'parameters' => ['type' => 'object', 'properties' => ['idSite' => ['type' => 'integer']]],
                ],
            ]],
            $openAI->sentPayload['tools']
        );
        // The MCP annotation hints must not reach the provider.
        $this->assertSame(
            ['name', 'description', 'parameters'],
            array_keys($openAI->sentPayload['tools'][0]['function'])
        );
    }

    public function testToolSchemaTopLevelConstraintsAreStrippedForOpenAI(): void
    {
        // OpenAI rejects top-level oneOf/anyOf/allOf/not/enum/const and requires
        // a top-level object type. These are validation-only constraints (the
        // tool server re-validates on call), so they are dropped rather than
        // failing the whole turn.
        $openAI = new RecordingOpenAI();

        $request = $this->simpleRequest()->withTools([
            [
                'name' => 'matomo_api_call_create',
                'title' => null,
                'description' => 'Creates an API call',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => ['method' => ['type' => 'string']],
                    'not' => ['anyOf' => [['required' => ['method']]]],
                    'additionalProperties' => false,
                ],
                'outputSchema' => null,
                'readOnly' => false,
                'destructive' => false,
                'idempotent' => false,
                'openWorld' => null,
            ],
        ]);

        $openAI->converse($request, self::CONFIGURATION);

        $this->assertSame(
            [
                'type' => 'object',
                'properties' => ['method' => ['type' => 'string']],
                'additionalProperties' => false,
            ],
            $openAI->sentPayload['tools'][0]['function']['parameters']
        );
    }

    public function testResponseTextAndToolCallsAreParsedIntoCanonicalBlocks(): void
    {
        $openAI = new RecordingOpenAI();
        $openAI->cannedResponse = [
            'choices' => [[
                'message' => [
                    'role' => 'assistant',
                    'content' => 'Checking your sites now.',
                    'tool_calls' => [[
                        'id' => 'tu_42',
                        'type' => 'function',
                        'function' => ['name' => 'matomo_site_list', 'arguments' => '{"idSite":1}'],
                    ]],
                ],
                'finish_reason' => 'tool_calls',
            ]],
            'usage' => ['prompt_tokens' => 12, 'completion_tokens' => 7],
        ];

        $response = $openAI->converse($this->simpleRequest(), self::CONFIGURATION);

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
        $this->assertSame('openai', $response->getProviderId());
        $this->assertSame('OpenAI', $response->getProviderName());
        $this->assertSame('gpt-5.4-mini', $response->getModel());
    }

    public function testBlankToolCallArgumentsDecodeToEmptyInput(): void
    {
        $openAI = new RecordingOpenAI();
        $openAI->cannedResponse = [
            'choices' => [[
                'message' => [
                    'role' => 'assistant',
                    'content' => null,
                    'tool_calls' => [[
                        'id' => 'tu_1',
                        'type' => 'function',
                        'function' => ['name' => 'noop', 'arguments' => ''],
                    ]],
                ],
                'finish_reason' => 'tool_calls',
            ]],
        ];

        $response = $openAI->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertSame(
            [['type' => 'tool_use', 'id' => 'tu_1', 'name' => 'noop', 'input' => []]],
            $response->getContent()
        );
    }

    public function testFinishReasonStopMapsToEndTurn(): void
    {
        $openAI = new RecordingOpenAI();
        $openAI->cannedResponse = [
            'choices' => [['message' => ['content' => 'Done.'], 'finish_reason' => 'stop']],
        ];

        $response = $openAI->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertSame('end_turn', $response->getStopReason());
    }

    public function testFinishReasonLengthMapsToMaxTokens(): void
    {
        $openAI = new RecordingOpenAI();
        $openAI->cannedResponse = [
            'choices' => [['message' => ['content' => 'Trunc'], 'finish_reason' => 'length']],
        ];

        $response = $openAI->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertSame('max_tokens', $response->getStopReason());
    }

    public function testUnknownFinishReasonPassesThrough(): void
    {
        $openAI = new RecordingOpenAI();
        $openAI->cannedResponse = [
            'choices' => [['message' => ['content' => 'Hi.'], 'finish_reason' => 'function_call']],
        ];

        $response = $openAI->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertSame('function_call', $response->getStopReason());
    }

    public function testFinishReasonContentFilterMapsToGuardrail(): void
    {
        $openAI = new RecordingOpenAI();
        $openAI->cannedResponse = [
            'choices' => [['message' => ['content' => 'Blocked.'], 'finish_reason' => 'content_filter']],
        ];

        $response = $openAI->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertSame('guardrail_intervened', $response->getStopReason());
    }

    public function testMissingUsageYieldsNullTokenCounts(): void
    {
        $openAI = new RecordingOpenAI();
        $openAI->cannedResponse = [
            'choices' => [['message' => ['content' => 'Hi.'], 'finish_reason' => 'stop']],
        ];

        $response = $openAI->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertNull($response->getInputTokens());
        $this->assertNull($response->getOutputTokens());
    }

    public function testOpenAISupportsConversations(): void
    {
        $this->assertTrue((new OpenAI())->supportsConversations());
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
class RecordingOpenAI extends OpenAI
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
        'choices' => [['message' => ['role' => 'assistant', 'content' => 'Hi there.'], 'finish_reason' => 'stop']],
        'usage' => ['prompt_tokens' => 3, 'completion_tokens' => 4],
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
