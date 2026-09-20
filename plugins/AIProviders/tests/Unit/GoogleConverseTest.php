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
use Piwik\Plugins\AIProviders\AIRequest;
use Piwik\Plugins\AIProviders\Provider\Google;

/**
 * Tests the full converse() translation between the canonical message shape
 * and the Google generateContent API wire format, with HTTP replaced by a
 * recording sendJsonRequest() override.
 *
 * @group AIProviders
 * @group Plugins
 */
class GoogleConverseTest extends TestCase
{
    private const CONFIGURATION = ['apiKey' => 'secret-gemini-key', 'endpointUrl' => ''];

    public function testPayloadUsesDefaultModelEndpointHeadersAndRequestDefaults(): void
    {
        $gemini = new RecordingGoogle();

        $gemini->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertStringContainsString('gemini-3.1-flash-lite', $gemini->sentUrl);
        $this->assertStringContainsString(':generateContent', $gemini->sentUrl);
        $this->assertSame(['x-goog-api-key' => 'secret-gemini-key'], $gemini->sentHeaders);
        $this->assertSame(
            AIConversationRequest::DEFAULT_MAX_TOKENS,
            $gemini->sentPayload['generationConfig']['maxOutputTokens']
        );
        $this->assertSame(
            AIRequest::DEFAULT_TEMPERATURE,
            $gemini->sentPayload['generationConfig']['temperature']
        );
        $this->assertSame(AIConversationRequest::DEFAULT_TIMEOUT_SECONDS, $gemini->sentTimeoutSeconds);
        // No system prompt was set, so the payload carries no systemInstruction.
        $this->assertArrayNotHasKey('systemInstruction', $gemini->sentPayload);
        $this->assertSame(
            [['role' => 'user', 'parts' => [['text' => 'Hello']]]],
            $gemini->sentPayload['contents']
        );
    }

    public function testPayloadHonoursRequestedModelOptionsSystemPromptAndTimeout(): void
    {
        $gemini = new RecordingGoogle();

        $request = $this->simpleRequest()
            ->withModel('gemini-2.5-pro')
            ->withSystemPrompt('You are concise.')
            ->withMaxTokens(512)
            ->withTemperature(0.9)
            ->withTimeoutSeconds(120);

        $gemini->converse($request, self::CONFIGURATION);

        $this->assertStringContainsString('gemini-2.5-pro', $gemini->sentUrl);
        $this->assertSame(
            ['parts' => [['text' => 'You are concise.']]],
            $gemini->sentPayload['systemInstruction']
        );
        $this->assertSame(512, $gemini->sentPayload['generationConfig']['maxOutputTokens']);
        $this->assertSame(0.9, $gemini->sentPayload['generationConfig']['temperature']);
        $this->assertSame(120, $gemini->sentTimeoutSeconds);
    }

    public function testMessagesTranslateToGeminiRolesAndParts(): void
    {
        $gemini = new RecordingGoogle();

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

        $gemini->converse($request, self::CONFIGURATION);

        $contents = $gemini->sentPayload['contents'];

        $this->assertCount(3, $contents);
        // Canonical 'assistant' becomes Google 'model'; the 'tool' result folds
        // into a 'user' message carrying a functionResponse part.
        $this->assertSame(['user', 'model', 'user'], array_column($contents, 'role'));

        $this->assertSame([['text' => 'delete site 1']], $contents[0]['parts']);

        $this->assertSame(['text' => 'Deleting it now.'], $contents[1]['parts'][0]);
        $this->assertSame('del', $contents[1]['parts'][1]['functionCall']['name']);

        // Empty tool_use input must encode as a JSON object, not an array.
        $this->assertStringContainsString('"args":{}', (string) json_encode($gemini->sentPayload));

        // The tool_result resolves its function name from the prior tool_use.
        $this->assertSame(
            [[
                'functionResponse' => [
                    'name' => 'del',
                    'response' => ['content' => 'deleted'],
                ],
            ]],
            $contents[2]['parts']
        );
    }

    public function testNonEmptyToolUseInputIsForwardedAsArgs(): void
    {
        $gemini = new RecordingGoogle();

        $request = new AIConversationRequest(
            [[
                'role' => 'assistant',
                'content' => [['type' => 'tool_use', 'id' => 'tu_1', 'name' => 'del', 'input' => ['idSite' => 1]]],
            ]],
            'AskMatomo'
        );

        $gemini->converse($request, self::CONFIGURATION);

        $this->assertSame(
            ['idSite' => 1],
            $gemini->sentPayload['contents'][0]['parts'][0]['functionCall']['args']
        );
    }

    public function testToolResultNameFallsBackToUnknownWhenUnresolvable(): void
    {
        $gemini = new RecordingGoogle();

        $request = new AIConversationRequest(
            [[
                'role' => 'tool',
                'content' => [[
                    'type' => 'tool_result',
                    'tool_use_id' => 'tu_missing',
                    'content' => [['type' => 'text', 'text' => 'done']],
                    'structuredContent' => null,
                    'is_error' => false,
                ]],
            ]],
            'AskMatomo'
        );

        $gemini->converse($request, self::CONFIGURATION);

        $this->assertSame(
            'unknown',
            $gemini->sentPayload['contents'][0]['parts'][0]['functionResponse']['name']
        );
    }

    public function testToolResultStructuredContentBecomesResponseObject(): void
    {
        $gemini = new RecordingGoogle();

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

        $gemini->converse($request, self::CONFIGURATION);

        // structuredContent is used verbatim as the response object.
        $this->assertSame(
            ['sites' => [['idsite' => 1]]],
            $gemini->sentPayload['contents'][0]['parts'][0]['functionResponse']['response']
        );
    }

    public function testToolResultMcpTextFallbackIsWrappedInObjectAndErrorReflected(): void
    {
        $gemini = new RecordingGoogle();

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
                    'is_error' => true,
                ]],
            ]],
            'AskMatomo'
        );

        $gemini->converse($request, self::CONFIGURATION);

        $this->assertSame(
            [
                'content' => "plain text payload\n{\"type\":\"resource_link\",\"uri\":\"https:\/\/example.org\/report\"}",
                'error' => true,
            ],
            $gemini->sentPayload['contents'][0]['parts'][0]['functionResponse']['response']
        );
    }

    public function testToolsAreOmittedWhenCatalogueIsEmpty(): void
    {
        $gemini = new RecordingGoogle();

        $gemini->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertArrayNotHasKey('tools', $gemini->sentPayload);
    }

    public function testToolCatalogueTranslatesToFunctionDeclarationsWithoutMcpHints(): void
    {
        $gemini = new RecordingGoogle();

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

        $gemini->converse($request, self::CONFIGURATION);

        $this->assertSame(
            [[
                'functionDeclarations' => [[
                    'name' => 'matomo_site_list',
                    'description' => 'Lists sites',
                    'parameters' => ['type' => 'object', 'properties' => ['idSite' => ['type' => 'integer']]],
                ]],
            ]],
            $gemini->sentPayload['tools']
        );
        // The MCP annotation hints must not reach the provider.
        $this->assertSame(
            ['name', 'description', 'parameters'],
            array_keys($gemini->sentPayload['tools'][0]['functionDeclarations'][0])
        );
    }

    public function testToolSchemaUnsupportedKeywordsAreStrippedForGemini(): void
    {
        // Google's function declaration schema accepts only a restricted subset
        // of OpenAPI 3.0 and rejects standard JSON Schema keywords such as
        // top-level oneOf/anyOf/allOf/not/enum/const AND additionalProperties at
        // every nesting depth (the real API errors on
        // parameters.properties[...].additionalProperties). They are
        // validation-only constraints re-checked by the tool server on call, so
        // they are dropped rather than failing the turn.
        $gemini = new RecordingGoogle();

        $request = $this->simpleRequest()->withTools([
            [
                'name' => 'matomo_api_call_create',
                'title' => null,
                'description' => 'Creates an API call',
                'inputSchema' => [
                    '$schema' => 'http://json-schema.org/draft-07/schema#',
                    'type' => 'object',
                    'properties' => [
                        'method' => ['type' => 'string'],
                        'filter' => [
                            'type' => 'object',
                            'properties' => ['name' => ['type' => 'string']],
                            'additionalProperties' => false,
                        ],
                    ],
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

        $gemini->converse($request, self::CONFIGURATION);

        $this->assertSame(
            [
                'type' => 'object',
                'properties' => [
                    'method' => ['type' => 'string'],
                    'filter' => [
                        'type' => 'object',
                        'properties' => ['name' => ['type' => 'string']],
                    ],
                ],
            ],
            $gemini->sentPayload['tools'][0]['functionDeclarations'][0]['parameters']
        );
    }

    public function testResponsePartsAreParsedIntoCanonicalBlocks(): void
    {
        $gemini = new RecordingGoogle();
        $gemini->cannedResponse = [
            'candidates' => [[
                'content' => [
                    'parts' => [
                        ['text' => 'Checking your sites now.'],
                        ['functionCall' => ['name' => 'matomo_site_list', 'args' => ['idSite' => 1]]],
                    ],
                ],
                'finishReason' => 'STOP',
            ]],
            'usageMetadata' => ['promptTokenCount' => 12, 'candidatesTokenCount' => 7],
        ];

        $response = $gemini->converse($this->simpleRequest(), self::CONFIGURATION);

        $content = $response->getContent();

        $this->assertSame(['type' => 'text', 'text' => 'Checking your sites now.'], $content[0]);
        $this->assertSame('tool_use', $content[1]['type']);
        $this->assertSame('matomo_site_list', $content[1]['name']);
        $this->assertSame(['idSite' => 1], $content[1]['input']);
        // The synthesized id is present and non-empty.
        $this->assertNotSame('', $content[1]['id']);
        $this->assertIsString($content[1]['id']);

        // A functionCall forces tool_use even though finishReason was STOP.
        $this->assertSame('tool_use', $response->getStopReason());
        $this->assertSame(12, $response->getInputTokens());
        $this->assertSame(7, $response->getOutputTokens());
        $this->assertSame('google', $response->getProviderId());
        $this->assertSame('gemini-3.1-flash-lite', $response->getModel());
    }

    public function testThoughtSignatureSurvivesTheToolUseRoundTrip(): void
    {
        $gemini = new RecordingGoogle();
        $gemini->cannedResponse = [
            'candidates' => [[
                'content' => ['parts' => [
                    [
                        'functionCall' => ['name' => 'matomo_site_list', 'args' => ['idSite' => 1]],
                        'thoughtSignature' => 'sig-abc123',
                    ],
                ]],
                'finishReason' => 'STOP',
            ]],
        ];

        $first = $gemini->converse($this->simpleRequest(), self::CONFIGURATION);
        $toolUse = $first->getContent()[0];

        $this->assertSame('sig-abc123', $toolUse['thoughtSignature']);

        // Replay must put the signature back as a sibling of functionCall.
        $followUp = new AIConversationRequest(
            [['role' => 'assistant', 'content' => $first->getContent()]],
            'AskMatomo'
        );
        $gemini->converse($followUp, self::CONFIGURATION);

        $sentPart = $gemini->sentPayload['contents'][0]['parts'][0];
        $this->assertSame('sig-abc123', $sentPart['thoughtSignature']);
        $this->assertSame('matomo_site_list', $sentPart['functionCall']['name']);
    }

    public function testToolUseWithoutThoughtSignatureOmitsItOnBothSides(): void
    {
        $gemini = new RecordingGoogle();
        $gemini->cannedResponse = [
            'candidates' => [[
                'content' => ['parts' => [
                    ['functionCall' => ['name' => 'matomo_site_list', 'args' => ['idSite' => 1]]],
                ]],
                'finishReason' => 'STOP',
            ]],
        ];

        $first = $gemini->converse($this->simpleRequest(), self::CONFIGURATION);
        $this->assertArrayNotHasKey('thoughtSignature', $first->getContent()[0]);

        $followUp = new AIConversationRequest(
            [['role' => 'assistant', 'content' => $first->getContent()]],
            'AskMatomo'
        );
        $gemini->converse($followUp, self::CONFIGURATION);

        $this->assertArrayNotHasKey('thoughtSignature', $gemini->sentPayload['contents'][0]['parts'][0]);
    }

    public function testSynthesizedIdResolvesBackToFunctionNameOnFollowUpTurn(): void
    {
        $gemini = new RecordingGoogle();
        $gemini->cannedResponse = [
            'candidates' => [[
                'content' => ['parts' => [
                    ['functionCall' => ['name' => 'matomo_site_list', 'args' => ['idSite' => 1]]],
                ]],
                'finishReason' => 'STOP',
            ]],
        ];

        $first = $gemini->converse($this->simpleRequest(), self::CONFIGURATION);
        $synthesizedId = $first->getContent()[0]['id'];

        // Feed the assistant tool_use plus a matching tool_result back in.
        $followUp = new AIConversationRequest(
            [
                ['role' => 'assistant', 'content' => $first->getContent()],
                ['role' => 'tool', 'content' => [[
                    'type' => 'tool_result',
                    'tool_use_id' => $synthesizedId,
                    'content' => [['type' => 'text', 'text' => 'ok']],
                    'structuredContent' => null,
                    'is_error' => false,
                ]]],
            ],
            'AskMatomo'
        );

        $gemini->converse($followUp, self::CONFIGURATION);

        // The result correlates back to the original function name.
        $this->assertSame(
            'matomo_site_list',
            $gemini->sentPayload['contents'][1]['parts'][0]['functionResponse']['name']
        );
    }

    public function testFinishReasonStopWithoutCallsYieldsEndTurn(): void
    {
        $gemini = new RecordingGoogle();
        $gemini->cannedResponse = [
            'candidates' => [[
                'content' => ['parts' => [['text' => 'All done.']]],
                'finishReason' => 'STOP',
            ]],
        ];

        $response = $gemini->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertSame('end_turn', $response->getStopReason());
    }

    public function testFinishReasonMaxTokensMapsToMaxTokens(): void
    {
        $gemini = new RecordingGoogle();
        $gemini->cannedResponse = [
            'candidates' => [[
                'content' => ['parts' => [['text' => 'Truncated']]],
                'finishReason' => 'MAX_TOKENS',
            ]],
        ];

        $response = $gemini->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertSame('max_tokens', $response->getStopReason());
    }

    public function testUnknownFinishReasonPassesThrough(): void
    {
        $gemini = new RecordingGoogle();
        $gemini->cannedResponse = [
            'candidates' => [[
                'content' => ['parts' => [['text' => 'Hi.']]],
                'finishReason' => 'OTHER',
            ]],
        ];

        $response = $gemini->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertSame('OTHER', $response->getStopReason());
    }

    public function testMissingUsageYieldsNullTokenCounts(): void
    {
        $gemini = new RecordingGoogle();
        $gemini->cannedResponse = [
            'candidates' => [[
                'content' => ['parts' => [['text' => 'Hi.']]],
                'finishReason' => 'STOP',
            ]],
        ];

        $response = $gemini->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertNull($response->getInputTokens());
        $this->assertNull($response->getOutputTokens());
    }

    public function testGeminiSupportsConversations(): void
    {
        $this->assertTrue((new Google())->supportsConversations());
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
class RecordingGoogle extends Google
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
        'candidates' => [[
            'content' => ['parts' => [['text' => 'Hi there.']]],
            'finishReason' => 'STOP',
        ]],
        'usageMetadata' => ['promptTokenCount' => 3, 'candidatesTokenCount' => 4],
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
