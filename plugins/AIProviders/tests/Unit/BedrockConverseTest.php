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
use Piwik\Plugins\AIProviders\Exception\AIProviderClientException;
use Piwik\Plugins\AIProviders\Model\Configuration;
use Piwik\Plugins\AIProviders\Provider\Bedrock;

/**
 * Tests the translation between the canonical message shape and the Bedrock
 * Converse wire format via a recording sendConverseRequest() override, plus
 * the bearer-key HTTP transport (endpoint building, authentication, model
 * discovery) via recording sendJsonRequest()/sendGetRequest() overrides.
 *
 * @group AIProviders
 * @group Plugins
 */
class BedrockConverseTest extends TestCase
{
    private const CONFIGURATION = ['apiKey' => 'bedrock-api-key', 'endpointUrl' => '', 'model' => ''];

    public function testConverseTranslatesCanonicalTextRoundTrip(): void
    {
        $bedrock = new RecordingBedrock();
        $bedrock->cannedResponse = [
            'output' => ['message' => ['role' => 'assistant', 'content' => [['text' => 'Hi there.']]]],
            'stopReason' => 'end_turn',
            'usage' => ['inputTokens' => 3, 'outputTokens' => 4],
        ];

        $request = $this->simpleRequest()
            ->withSystemPrompt('system')
            ->withMaxTokens(64);

        $response = $bedrock->converse($request, self::CONFIGURATION);

        $this->assertSame('end_turn', $response->getStopReason());
        $this->assertSame(3, $response->getInputTokens());
        $this->assertSame(4, $response->getOutputTokens());
        $this->assertSame([['type' => 'text', 'text' => 'Hi there.']], $response->getContent());
        $this->assertSame('bedrock', $response->getProviderId());
        $this->assertSame('AWS Bedrock', $response->getProviderName());

        $this->assertSame(
            [['role' => 'user', 'content' => [['text' => 'hello']]]],
            $bedrock->sentPayload['messages']
        );
        $this->assertSame([['text' => 'system']], $bedrock->sentPayload['system']);
        $this->assertSame(
            ['maxTokens' => 64, 'temperature' => 0.2],
            $bedrock->sentPayload['inferenceConfig']
        );
        $this->assertArrayNotHasKey('toolConfig', $bedrock->sentPayload);
    }

    public function testCompleteMapsSimpleResponseMetadata(): void
    {
        $bedrock = new RecordingBedrock();
        $bedrock->cannedResponse = [
            'output' => ['message' => ['role' => 'assistant', 'content' => [['text' => 'It scatters sunlight.']]]],
            'stopReason' => 'end_turn',
            'usage' => ['inputTokens' => 7, 'outputTokens' => 3],
        ];

        $response = $bedrock->complete(
            (new AIRequest('why is the sky blue?', 'FormAnalytics'))
                ->withMaxTokens(32),
            self::CONFIGURATION
        );

        $this->assertSame('It scatters sunlight.', $response->getText());
        $this->assertSame('end_turn', $response->getStopReason());
        $this->assertSame(7, $response->getInputTokens());
        $this->assertSame(3, $response->getOutputTokens());
        $this->assertSame('openai.gpt-oss-120b-1:0', $response->getModel());
        $this->assertSame(
            [['role' => 'user', 'content' => [['text' => 'why is the sky blue?']]]],
            $bedrock->sentPayload['messages']
        );
        $this->assertSame(
            ['maxTokens' => 32, 'temperature' => 0.2],
            $bedrock->sentPayload['inferenceConfig']
        );
        $this->assertSame(
            ['reasoning_effort' => 'low'],
            $bedrock->sentPayload['additionalModelRequestFields']
        );
        $this->assertSame('openai.gpt-oss-120b-1:0', $bedrock->sentModel);
        // Completions run with the provider's fixed timeout.
        $this->assertSame(30, $bedrock->sentTimeoutSeconds);
    }

    public function testSystemIsOmittedWhenNoSystemPromptIsSet(): void
    {
        $bedrock = new RecordingBedrock();

        $bedrock->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertArrayNotHasKey('system', $bedrock->sentPayload);
    }

    public function testModelResolutionPrefersRequestThenConfigurationThenDefault(): void
    {
        $bedrock = new RecordingBedrock();

        // The per-request model wins over everything.
        $bedrock->converse(
            $this->simpleRequest()->withModel('anthropic.claude-3-opus'),
            ['apiKey' => 'k', 'endpointUrl' => '', 'model' => 'eu.amazon.nova-lite-v1:0']
        );
        $this->assertSame('anthropic.claude-3-opus', $bedrock->sentModel);

        // Without a request model, the model saved in the configuration wins.
        $bedrock->converse(
            $this->simpleRequest(),
            ['apiKey' => 'k', 'endpointUrl' => '', 'model' => 'eu.amazon.nova-lite-v1:0']
        );
        $this->assertSame('eu.amazon.nova-lite-v1:0', $bedrock->sentModel);

        // Without either, the provider default applies.
        $bedrock->converse($this->simpleRequest(), self::CONFIGURATION);
        $this->assertSame('openai.gpt-oss-120b-1:0', $bedrock->sentModel);
    }

    public function testTimeoutIsForwardedFromTheRequest(): void
    {
        $bedrock = new RecordingBedrock();

        $bedrock->converse($this->simpleRequest(), self::CONFIGURATION);
        $this->assertSame(AIConversationRequest::DEFAULT_TIMEOUT_SECONDS, $bedrock->sentTimeoutSeconds);

        $bedrock->converse($this->simpleRequest()->withTimeoutSeconds(120), self::CONFIGURATION);
        $this->assertSame(120, $bedrock->sentTimeoutSeconds);
    }

    public function testConverseCoalescesConsecutiveSameRoleMessages(): void
    {
        // Converse requires turns to alternate user/assistant. A canonical
        // 'tool' row folds into a 'user' turn, so a turn that stopped at a tool
        // result followed by the user's next message would otherwise emit two
        // consecutive 'user' turns and Bedrock would reject the whole history.
        // The provider must merge them into one user turn carrying both the
        // tool result and the new text.
        $bedrock = new RecordingBedrock();

        $request = new AIConversationRequest(
            [
                ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'delete site 1']]],
                [
                    'role' => 'assistant',
                    'content' => [['type' => 'tool_use', 'id' => 'tu_1', 'name' => 'del', 'input' => ['idSite' => 1]]],
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
                ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'thanks']]],
            ],
            'AskMatomo'
        );

        $bedrock->converse($request, self::CONFIGURATION);

        $messages = $bedrock->sentPayload['messages'];

        // Roles strictly alternate: the tool_result turn and the following
        // user text turn are merged into a single user turn.
        $this->assertSame(['user', 'assistant', 'user'], array_column($messages, 'role'));

        // The coalescing must keep the messages a list with sequential keys,
        // otherwise json_encode would emit an object instead of an array.
        $this->assertSame(range(0, count($messages) - 1), array_keys($messages));

        // The merged user turn carries the tool result first, then the new text.
        $merged = $messages[2]['content'];
        $this->assertCount(2, $merged);
        $this->assertSame('tu_1', $merged[0]['toolResult']['toolUseId']);
        $this->assertSame(['text' => 'thanks'], $merged[1]);
    }

    public function testConverseCoalescesConsecutiveAssistantMessages(): void
    {
        $bedrock = new RecordingBedrock();

        $request = new AIConversationRequest(
            [
                ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'hi']]],
                ['role' => 'assistant', 'content' => [['type' => 'text', 'text' => 'first']]],
                ['role' => 'assistant', 'content' => [['type' => 'text', 'text' => 'second']]],
            ],
            'AskMatomo'
        );

        $bedrock->converse($request, self::CONFIGURATION);

        $messages = $bedrock->sentPayload['messages'];

        $this->assertSame(['user', 'assistant'], array_column($messages, 'role'));
        $this->assertSame(range(0, count($messages) - 1), array_keys($messages));
        $this->assertSame(
            [['text' => 'first'], ['text' => 'second']],
            $messages[1]['content']
        );
    }

    public function testEmptyToolUseInputEncodesAsJsonObject(): void
    {
        // Bedrock requires toolUse.input to be a JSON object, even when empty.
        // json_decode collapses `{}` to `[]`; round-tripping that back as `[]`
        // makes Bedrock reject the next turn with a 400, so the provider must
        // coerce empty inputs back to an object.
        $bedrock = new RecordingBedrock();

        $request = new AIConversationRequest(
            [
                ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'do it']]],
                [
                    'role' => 'assistant',
                    'content' => [['type' => 'tool_use', 'id' => 'tu_prev', 'name' => 'x', 'input' => []]],
                ],
            ],
            'AskMatomo'
        );

        $bedrock->converse($request, self::CONFIGURATION);

        $encoded = (string) json_encode($bedrock->sentPayload);

        $this->assertStringContainsString('"input":{}', $encoded);
        $this->assertSame(
            'tu_prev',
            $bedrock->sentPayload['messages'][1]['content'][0]['toolUse']['toolUseId']
        );
    }

    public function testToolResultPrefersJsonBlockWhenStructuredContentPresent(): void
    {
        // Tools that declared an outputSchema emit a structuredContent payload
        // alongside the text echo. The provider projects that into Bedrock's
        // native `{json: …}` toolResult block instead of round-tripping the
        // stringified JSON inside a text block.
        $bedrock = new RecordingBedrock();

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

        $bedrock->converse($request, self::CONFIGURATION);

        $message = $bedrock->sentPayload['messages'][0];
        $this->assertSame('user', $message['role']);
        $this->assertSame(
            [
                'toolResult' => [
                    'toolUseId' => 'tu_1',
                    'content' => [['json' => ['sites' => [['idsite' => 1]]]]],
                    'status' => 'success',
                ],
            ],
            $message['content'][0]
        );
    }

    public function testToolResultFallsBackToTranslatedTextWhenNoStructuredContent(): void
    {
        $bedrock = new RecordingBedrock();

        $request = new AIConversationRequest(
            [[
                'role' => 'tool',
                'content' => [[
                    'type' => 'tool_result',
                    'tool_use_id' => 'tu_2',
                    'content' => [['type' => 'text', 'text' => 'plain text payload']],
                    'structuredContent' => null,
                    'is_error' => true,
                ]],
            ]],
            'AskMatomo'
        );

        $bedrock->converse($request, self::CONFIGURATION);

        $this->assertSame(
            [
                'toolResult' => [
                    'toolUseId' => 'tu_2',
                    'content' => [['text' => 'plain text payload']],
                    'status' => 'error',
                ],
            ],
            $bedrock->sentPayload['messages'][0]['content'][0]
        );
    }

    public function testToolResultStringifiesNonTextBlocksAndEmptyContentBecomesEmptyTextBlock(): void
    {
        $bedrock = new RecordingBedrock();

        $request = new AIConversationRequest(
            [[
                'role' => 'tool',
                'content' => [
                    [
                        'type' => 'tool_result',
                        'tool_use_id' => 'tu_1',
                        'content' => [['type' => 'resource_link', 'uri' => 'https://example.org/report']],
                        'structuredContent' => null,
                        'is_error' => false,
                    ],
                    [
                        'type' => 'tool_result',
                        'tool_use_id' => 'tu_2',
                        'content' => [],
                        'structuredContent' => null,
                        'is_error' => false,
                    ],
                ],
            ]],
            'AskMatomo'
        );

        $bedrock->converse($request, self::CONFIGURATION);

        $blocks = $bedrock->sentPayload['messages'][0]['content'];

        // Non-text MCP blocks are JSON-stringified so their data still
        // reaches the model.
        $this->assertSame(
            [['text' => '{"type":"resource_link","uri":"https:\/\/example.org\/report"}']],
            $blocks[0]['toolResult']['content']
        );

        // An empty tool result still needs a content block.
        $this->assertSame([['text' => '']], $blocks[1]['toolResult']['content']);
    }

    public function testConverseEmitsToolConfigFromCatalog(): void
    {
        $bedrock = new RecordingBedrock();

        $request = $this->simpleRequest()->withTools([
            [
                'name' => 'matomo_site_list',
                'title' => null,
                'description' => 'Lists sites',
                'inputSchema' => ['type' => 'object'],
                'outputSchema' => null,
                'readOnly' => true,
                'destructive' => false,
                'idempotent' => true,
                'openWorld' => null,
            ],
            [
                'name' => 'matomo_api_call_create',
                'description' => 'Create something',
                'inputSchema' => ['type' => 'object', 'properties' => ['idSite' => ['type' => 'integer']]],
            ],
        ]);

        $bedrock->converse($request, self::CONFIGURATION);

        $this->assertSame(
            [
                'tools' => [
                    [
                        'toolSpec' => [
                            'name' => 'matomo_site_list',
                            'description' => 'Lists sites',
                            'inputSchema' => ['json' => ['type' => 'object']],
                        ],
                    ],
                    [
                        'toolSpec' => [
                            'name' => 'matomo_api_call_create',
                            'description' => 'Create something',
                            'inputSchema' => ['json' => ['type' => 'object', 'properties' => ['idSite' => ['type' => 'integer']]]],
                        ],
                    ],
                ],
            ],
            $bedrock->sentPayload['toolConfig']
        );
    }

    public function testToolSpecDoesNotLeakReadOnlyOrDestructiveHints(): void
    {
        // Bedrock's toolSpec has no fields for read-only/destructive hints;
        // those metadata stay in the catalogue for the caller's confirmation
        // flow and must not pollute the request to Bedrock.
        $bedrock = new RecordingBedrock();

        $request = $this->simpleRequest()->withTools([
            [
                'name' => 'matomo_api_call_delete',
                'description' => 'Delete',
                'inputSchema' => ['type' => 'object'],
                'readOnly' => false,
                'destructive' => true,
                'idempotent' => false,
            ],
        ]);

        $bedrock->converse($request, self::CONFIGURATION);

        $spec = $bedrock->sentPayload['toolConfig']['tools'][0]['toolSpec'];

        $this->assertSame(['name', 'description', 'inputSchema'], array_keys($spec));
        $this->assertSame(['json'], array_keys($spec['inputSchema']));
    }

    /**
     * Mistral Large only returns a structured toolUse block when a tool call is
     * forced via toolChoice; under the default it leaks the call back as text.
     * The provider forces `toolChoice: {any}` for this family only.
     *
     * @dataProvider getMistralLargeModelData
     */
    public function testMistralLargeForcesToolChoiceAny(string $model): void
    {
        $bedrock = new RecordingBedrock();

        $bedrock->converse($this->toolRequestForModel($model), self::CONFIGURATION);

        $toolConfig = $bedrock->sentPayload['toolConfig'];
        $this->assertArrayHasKey('toolChoice', $toolConfig);
        $this->assertEquals(['any' => new \stdClass()], $toolConfig['toolChoice']);

        // The forced choice must serialise as an object (`{}`), not an empty
        // array (`[]`); Bedrock rejects `"any":[]`.
        $this->assertStringContainsString(
            '"toolChoice":{"any":{}}',
            (string) json_encode($bedrock->sentPayload)
        );
    }

    /**
     * @return array<string, array{string}>
     */
    public function getMistralLargeModelData(): array
    {
        return [
            'Large 2402' => ['mistral.mistral-large-2402-v1:0'],
            'Large 2 (2407)' => ['mistral.mistral-large-2407-v1:0'],
            'Large 3' => ['mistral.mistral-large-3-675b-instruct'],
            'regional profile' => ['us.mistral.mistral-large-2402-v1:0'],
            'inference-profile ARN' => [
                'arn:aws:bedrock:us-east-1:123456789012:inference-profile/eu.mistral.mistral-large-2402-v1:0',
            ],
        ];
    }

    /**
     * Every other family (including Magistral and the other Mistral tiers)
     * returns structured tool use under the default toolChoice, so the provider
     * must not force one on them.
     *
     * @dataProvider getNonMistralLargeModelData
     */
    public function testNonMistralLargeModelsDoNotForceToolChoice(string $model): void
    {
        $bedrock = new RecordingBedrock();

        $bedrock->converse($this->toolRequestForModel($model), self::CONFIGURATION);

        // The catalogue is still emitted; only the forced toolChoice is absent.
        $this->assertArrayHasKey('toolConfig', $bedrock->sentPayload);
        $this->assertArrayNotHasKey('toolChoice', $bedrock->sentPayload['toolConfig']);
    }

    /**
     * @return array<string, array{string}>
     */
    public function getNonMistralLargeModelData(): array
    {
        return [
            'Magistral' => ['mistral.magistral-small-2509'],
            'Ministral 8B' => ['mistral.ministral-3-8b'],
            'Ministral 3B' => ['mistral.ministral-3b'],
            'Mistral Small' => ['mistral.mistral-small-2402-v1:0'],
            'Mistral 7B' => ['mistral.mistral-7b-instruct-v0:2'],
            'Claude' => ['anthropic.claude-3-7-sonnet-20250219-v1:0'],
            'Nova' => ['amazon.nova-lite-v1:0'],
            'gpt-oss' => ['openai.gpt-oss-120b-1:0'],
            'embedded lookalike' => ['mymistral.mistral-large-2402-v1:0'],
        ];
    }

    public function testMistralLargeWithoutToolsSendsNoToolConfig(): void
    {
        // toolChoice only rides along with a tool catalogue; a plain-chat turn
        // to Mistral Large must not gain an empty toolConfig.
        $bedrock = new RecordingBedrock();

        $bedrock->converse(
            $this->simpleRequest()->withModel('mistral.mistral-large-2402-v1:0'),
            self::CONFIGURATION
        );

        $this->assertArrayNotHasKey('toolConfig', $bedrock->sentPayload);
    }

    public function testResponseToolUseAndUnknownBlocksAreMappedToCanonical(): void
    {
        $bedrock = new RecordingBedrock();
        $bedrock->cannedResponse = [
            'output' => [
                'message' => [
                    'role' => 'assistant',
                    'content' => [
                        ['reasoningContent' => ['reasoningText' => ['text' => 'thinking …']]],
                        ['text' => 'Checking your sites now.'],
                        ['toolUse' => ['toolUseId' => 'tu_42', 'name' => 'matomo_demo', 'input' => ['idSite' => 1]]],
                    ],
                ],
            ],
            'stopReason' => 'tool_use',
            'usage' => ['inputTokens' => 7, 'outputTokens' => 5],
        ];

        $response = $bedrock->converse(
            $this->simpleRequest()->withCapabilityLevel(Configuration::CAPABILITY_THINKING),
            self::CONFIGURATION
        );

        $this->assertSame(
            [
                ['type' => 'reasoning', 'text' => 'thinking …'],
                ['type' => 'text', 'text' => 'Checking your sites now.'],
                ['type' => 'tool_use', 'id' => 'tu_42', 'name' => 'matomo_demo', 'input' => ['idSite' => 1]],
            ],
            $response->getContent()
        );
        $this->assertSame('tool_use', $response->getStopReason());
        $this->assertSame(7, $response->getInputTokens());
        $this->assertSame(5, $response->getOutputTokens());
    }

    public function testUnknownStopReasonPassesThroughAndMissingUsageYieldsNullTokens(): void
    {
        $bedrock = new RecordingBedrock();
        $bedrock->cannedResponse = [
            'output' => ['message' => ['role' => 'assistant', 'content' => [['text' => 'Blocked.']]]],
            'stopReason' => 'guardrail_intervened',
        ];

        $response = $bedrock->converse($this->simpleRequest(), self::CONFIGURATION);

        $this->assertSame('guardrail_intervened', $response->getStopReason());
        $this->assertNull($response->getInputTokens());
        $this->assertNull($response->getOutputTokens());
    }

    public function testHttpTransportTargetsRegionalConverseEndpointWithBearerAuth(): void
    {
        $bedrock = new HttpRecordingBedrock();

        $bedrock->converse($this->simpleRequest(), self::CONFIGURATION);

        // The model travels URL-encoded in the path of the default (us-east-1)
        // runtime endpoint; the API key travels as a bearer token.
        $this->assertSame(
            'https://bedrock-runtime.us-east-1.amazonaws.com/model/openai.gpt-oss-120b-1%3A0/converse',
            $bedrock->sentUrl
        );
        $this->assertSame(['Authorization' => 'Bearer bedrock-api-key'], $bedrock->sentHeaders);
        $this->assertSame(AIConversationRequest::DEFAULT_TIMEOUT_SECONDS, $bedrock->sentTimeoutSeconds);
        $this->assertArrayHasKey('messages', $bedrock->sentPayload);
    }

    public function testHttpTransportHonoursConfiguredRegion(): void
    {
        $bedrock = new HttpRecordingBedrock();

        $bedrock->converse($this->simpleRequest(), [
            'apiKey' => 'bedrock-api-key',
            'endpointUrl' => 'eu-central-1',
            'model' => '',
        ]);

        $this->assertSame(
            'https://bedrock-runtime.eu-central-1.amazonaws.com/model/openai.gpt-oss-120b-1%3A0/converse',
            $bedrock->sentUrl
        );
    }

    /**
     * @dataProvider getRegionShorthandData
     */
    public function testConfiguredRegionExpandsToTheRegionalEndpoint(string $endpointValue, string $expectedHost): void
    {
        $bedrock = new HttpRecordingBedrock();

        $bedrock->converse($this->simpleRequest(), [
            'apiKey' => 'bedrock-api-key',
            'endpointUrl' => $endpointValue,
            'model' => '',
        ]);

        $this->assertSame(
            'https://' . $expectedHost . '/model/openai.gpt-oss-120b-1%3A0/converse',
            $bedrock->sentUrl
        );
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function getRegionShorthandData(): array
    {
        return [
            'plain region' => ['eu-central-1', 'bedrock-runtime.eu-central-1.amazonaws.com'],
            'gov-cloud style region' => ['us-gov-west-1', 'bedrock-runtime.us-gov-west-1.amazonaws.com'],
            'whitespace and case are tolerated' => [' US-East-1 ', 'bedrock-runtime.us-east-1.amazonaws.com'],
            'empty value falls back to the default endpoint' => ['', 'bedrock-runtime.us-east-1.amazonaws.com'],
        ];
    }

    public function testFullUrlEndpointIsRejectedBeforeTheBearerTokenCanBeSent(): void
    {
        $bedrock = new HttpRecordingBedrock();

        try {
            $bedrock->converse($this->simpleRequest(), [
                'apiKey' => 'bedrock-api-key',
                'endpointUrl' => 'https://example.com',
                'model' => '',
            ]);
            $this->fail('Expected the invalid Bedrock region to be rejected.');
        } catch (AIProviderClientException $e) {
            $this->assertSame('The AWS region for AWS Bedrock is invalid.', $e->getMessage());
        }

        $this->assertNull($bedrock->sentUrl);
        $this->assertSame([], $bedrock->sentHeaders);
    }

    public function testRegionShorthandAlsoDrivesTheModelListingHost(): void
    {
        $bedrock = new HttpRecordingBedrock();

        $bedrock->listModels([
            'apiKey' => 'bedrock-api-key',
            'endpointUrl' => 'ap-southeast-2',
            'model' => '',
        ]);

        $this->assertSame(
            'https://bedrock.ap-southeast-2.amazonaws.com/foundation-models?byInferenceType=ON_DEMAND',
            $bedrock->sentGetUrl
        );
    }

    public function testCompleteWithoutApiKeyFailsWithClearClientError(): void
    {
        $this->expectException(AIProviderClientException::class);
        $this->expectExceptionMessage('No API key is configured for AWS Bedrock.');

        (new HttpRecordingBedrock())->complete(
            new AIRequest('hello', 'FormAnalytics'),
            ['apiKey' => '', 'endpointUrl' => '', 'model' => '']
        );
    }

    public function testIsConfiguredRequiresOnlyTheApiKey(): void
    {
        $bedrock = new Bedrock();

        // The endpoint has a working default, so unlike other custom-endpoint
        // providers the API key alone decides whether the provider is usable.
        $this->assertFalse($bedrock->isConfigured([]));
        $this->assertFalse($bedrock->isConfigured(['endpointUrl' => 'https://bedrock-runtime.eu-central-1.amazonaws.com']));
        $this->assertTrue($bedrock->isConfigured(['apiKey' => 'bedrock-api-key']));
        $this->assertTrue($bedrock->supportsConversations());
        $this->assertTrue($bedrock->supportsCustomEndpoint());
    }

    public function testListModelsQueriesControlPlaneAndReturnsSortedModelIds(): void
    {
        $bedrock = new HttpRecordingBedrock();
        $bedrock->cannedGetResponse = [
            'modelSummaries' => [
                ['modelId' => 'openai.gpt-oss-120b-1:0'],
                ['modelId' => 'amazon.nova-lite-v1:0'],
                ['modelName' => 'entry without id is skipped'],
                ['modelId' => ''],
            ],
        ];

        $models = $bedrock->listModels([
            'apiKey' => 'bedrock-api-key',
            'endpointUrl' => 'eu-central-1',
            'model' => '',
        ]);

        // The listing lives on the control-plane host (bedrock., not
        // bedrock-runtime.) of the same region; ON_DEMAND keeps out models
        // that are only invokable through inference profiles.
        $this->assertSame(
            'https://bedrock.eu-central-1.amazonaws.com/foundation-models?byInferenceType=ON_DEMAND',
            $bedrock->sentGetUrl
        );
        $this->assertSame(['Authorization' => 'Bearer bedrock-api-key'], $bedrock->sentGetHeaders);
        $this->assertSame(['amazon.nova-lite-v1:0', 'openai.gpt-oss-120b-1:0'], $models);
    }

    public function testFipsEndpointSwitchUsesFipsRuntimeAndControlPlaneHosts(): void
    {
        $bedrock = new HttpRecordingBedrock();
        $configuration = [
            'apiKey' => 'bedrock-api-key',
            'endpointUrl' => 'us-east-1',
            'model' => '',
            'useFipsEndpoint' => true,
        ];

        $bedrock->converse($this->simpleRequest(), $configuration);

        $this->assertSame(
            'https://bedrock-runtime-fips.us-east-1.amazonaws.com/model/openai.gpt-oss-120b-1%3A0/converse',
            $bedrock->sentUrl
        );

        $bedrock->listModels($configuration);

        $this->assertSame(
            'https://bedrock-fips.us-east-1.amazonaws.com/foundation-models?byInferenceType=ON_DEMAND',
            $bedrock->sentGetUrl
        );
    }

    /**
     * @dataProvider getTrustedHostData
     */
    public function testOnlyGenuineBedrockServiceHostsBypassTheHostBlocklist(string $host, bool $expectedTrusted): void
    {
        $bedrock = new class extends Bedrock {
            public function isTrusted(string $host): bool
            {
                return $this->isTrustedRequestHost($host);
            }
        };

        $this->assertSame($expectedTrusted, $bedrock->isTrusted($host));
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public function getTrustedHostData(): array
    {
        return [
            'runtime host' => ['bedrock-runtime.eu-central-1.amazonaws.com', true],
            'control-plane host' => ['bedrock.eu-central-1.amazonaws.com', true],
            'FIPS runtime host' => ['bedrock-runtime-fips.us-east-1.amazonaws.com', true],
            'FIPS control-plane host' => ['bedrock-fips.us-east-1.amazonaws.com', true],
            'other AWS service stays blocked' => ['s3.eu-central-1.amazonaws.com', false],
            'metadata-style host stays blocked' => ['ec2.internal.amazonaws.com', false],
            'lookalike domain stays blocked' => ['bedrock-runtime.eu-central-1.amazonaws.com.evil.example', false],
            'arbitrary custom endpoint stays blocked' => ['my-llm-server.example.org', false],
        ];
    }

    public function testVerifyConnectionDelegatesToTheModelListing(): void
    {
        $bedrock = new HttpRecordingBedrock();

        $bedrock->verifyConnection(self::CONFIGURATION);

        $this->assertSame(
            'https://bedrock.us-east-1.amazonaws.com/foundation-models?byInferenceType=ON_DEMAND',
            $bedrock->sentGetUrl
        );
    }

    /**
     * @dataProvider getGptOssReasoningEffortData
     */
    public function testGptOssMapsCapabilityToReasoningEffort(
        string $method,
        string $model,
        string $capabilityLevel,
        string $expectedEffort
    ): void {
        $bedrock = new RecordingBedrock();

        if ($method === 'complete') {
            $bedrock->complete(
                (new AIRequest('hello', 'AskMatomo'))
                    ->withModel($model)
                    ->withCapabilityLevel($capabilityLevel),
                self::CONFIGURATION
            );
        } else {
            $bedrock->converse(
                $this->simpleRequest()
                    ->withModel($model)
                    ->withCapabilityLevel($capabilityLevel),
                self::CONFIGURATION
            );
        }

        $this->assertSame(
            ['reasoning_effort' => $expectedEffort],
            $bedrock->sentPayload['additionalModelRequestFields']
        );
    }

    public function getGptOssReasoningEffortData(): array
    {
        $cases = [];
        foreach (['complete', 'converse'] as $method) {
            foreach (['openai.gpt-oss-20b-1:0', 'openai.gpt-oss-120b-1:0'] as $model) {
                $cases[$method . ' instant ' . $model] = [
                    $method,
                    $model,
                    Configuration::CAPABILITY_INSTANT,
                    'low',
                ];
                $cases[$method . ' thinking ' . $model] = [
                    $method,
                    $model,
                    Configuration::CAPABILITY_THINKING,
                    'medium',
                ];
            }
        }

        return $cases;
    }

    /**
     * A per-request thinking budget overrides the capability level on the
     * completion path (0 forces instant, >0 forces thinking), matching every
     * other provider's wantsThinking() contract.
     *
     * @dataProvider getCompletionBudgetOverrideData
     */
    public function testCompletionThinkingBudgetOverridesCapabilityLevel(
        string $capabilityLevel,
        int $thinkingBudget,
        string $expectedEffort
    ): void {
        $bedrock = new RecordingBedrock();

        $bedrock->complete(
            (new AIRequest('hello', 'AskMatomo'))
                ->withModel('openai.gpt-oss-120b-1:0')
                ->withCapabilityLevel($capabilityLevel)
                ->withThinkingBudget($thinkingBudget),
            self::CONFIGURATION
        );

        $this->assertSame(
            ['reasoning_effort' => $expectedEffort],
            $bedrock->sentPayload['additionalModelRequestFields']
        );
    }

    public function getCompletionBudgetOverrideData(): array
    {
        return [
            'positive budget forces thinking despite instant' => [
                Configuration::CAPABILITY_INSTANT,
                4096,
                'medium',
            ],
            'zero budget forces instant despite thinking' => [
                Configuration::CAPABILITY_THINKING,
                0,
                'low',
            ],
        ];
    }

    /**
     * @dataProvider getProfileAndArnModelData
     */
    public function testGptOssProfilesAndArnsAreRecognized(string $model): void
    {
        $bedrock = new RecordingBedrock();

        $bedrock->converse(
            $this->simpleRequest()
                ->withModel($model)
                ->withCapabilityLevel(Configuration::CAPABILITY_THINKING),
            self::CONFIGURATION
        );

        $this->assertSame(
            ['reasoning_effort' => 'medium'],
            $bedrock->sentPayload['additionalModelRequestFields']
        );
    }

    public function getProfileAndArnModelData(): array
    {
        return [
            'regional profile' => ['us.openai.gpt-oss-20b-1:0'],
            'foundation model ARN' => [
                'arn:aws:bedrock:us-east-1::foundation-model/openai.gpt-oss-120b-1:0',
            ],
            'regional profile ARN' => [
                'arn:aws:bedrock:eu-west-1:123456789012:inference-profile/eu.openai.gpt-oss-20b-1:0',
            ],
        ];
    }

    /**
     * @dataProvider getNovaReasoningConfigData
     */
    public function testNovaMapsCapabilityToReasoningConfig(
        string $method,
        string $model,
        string $capabilityLevel,
        array $expectedConfig
    ): void {
        $bedrock = new RecordingBedrock();

        if ($method === 'complete') {
            $bedrock->complete(
                (new AIRequest('hello', 'AskMatomo'))
                    ->withModel($model)
                    ->withCapabilityLevel($capabilityLevel),
                self::CONFIGURATION
            );
        } else {
            $bedrock->converse(
                $this->simpleRequest()
                    ->withModel($model)
                    ->withCapabilityLevel($capabilityLevel),
                self::CONFIGURATION
            );
        }

        $this->assertSame(
            ['reasoningConfig' => $expectedConfig],
            $bedrock->sentPayload['additionalModelRequestFields']
        );
    }

    public function getNovaReasoningConfigData(): array
    {
        $cases = [];
        foreach (['complete', 'converse'] as $method) {
            foreach (['amazon.nova-2-lite-v1:0'] as $model) {
                $cases[$method . ' instant ' . $model] = [
                    $method,
                    $model,
                    Configuration::CAPABILITY_INSTANT,
                    ['type' => 'disabled'],
                ];
                $cases[$method . ' thinking ' . $model] = [
                    $method,
                    $model,
                    Configuration::CAPABILITY_THINKING,
                    ['type' => 'enabled', 'maxReasoningEffort' => 'medium'],
                ];
            }
        }

        return $cases;
    }

    /**
     * @dataProvider getNovaReasoningProfileAndArnModelData
     */
    public function testNovaReasoningProfilesAndArnsAreRecognized(string $model): void
    {
        $bedrock = new RecordingBedrock();

        $bedrock->converse(
            $this->simpleRequest()
                ->withModel($model)
                ->withCapabilityLevel(Configuration::CAPABILITY_THINKING),
            self::CONFIGURATION
        );

        $this->assertSame(
            ['reasoningConfig' => ['type' => 'enabled', 'maxReasoningEffort' => 'medium']],
            $bedrock->sentPayload['additionalModelRequestFields']
        );
    }

    public function getNovaReasoningProfileAndArnModelData(): array
    {
        return [
            'Nova 2 regional profile' => ['eu.amazon.nova-2-lite-v1:0'],
            'Nova 2 inference-profile ARN' => [
                'arn:aws:bedrock:us-east-1:123456789012:inference-profile/amazon.nova-2-lite-v1:0',
            ],
        ];
    }

    /**
     * @dataProvider getNonGptOssModelData
     */
    public function testOtherModelsLeaveReasoningFieldsOutOfBothPayloads(string $model): void
    {
        $bedrock = new RecordingBedrock();

        $bedrock->complete(
            (new AIRequest('hello', 'AskMatomo'))
                ->withModel($model)
                ->withCapabilityLevel(Configuration::CAPABILITY_THINKING),
            self::CONFIGURATION
        );
        $this->assertArrayNotHasKey('additionalModelRequestFields', $bedrock->sentPayload);

        $bedrock->converse(
            $this->simpleRequest()
                ->withModel($model)
                ->withCapabilityLevel(Configuration::CAPABILITY_THINKING),
            self::CONFIGURATION
        );
        $this->assertArrayNotHasKey('additionalModelRequestFields', $bedrock->sentPayload);
    }

    public function getNonGptOssModelData(): array
    {
        return [
            'Claude' => ['anthropic.claude-3-7-sonnet-20250219-v1:0'],
            'Nova' => ['amazon.nova-lite-v1:0'],
            'other OpenAI model' => ['openai.gpt-4o'],
            'other gpt-oss size' => ['openai.gpt-oss-7b-1:0'],
            'embedded lookalike' => ['myopenai.gpt-oss-120b-1:0'],
            'suffixed lookalike' => ['openai.gpt-oss-120b-1:0-extra'],
        ];
    }

    public function testStructuredReasoningIsHiddenForInstantRequests(): void
    {
        $bedrock = new RecordingBedrock();
        $bedrock->cannedResponse = [
            'output' => ['message' => ['content' => [
                ['reasoningContent' => ['reasoningText' => ['text' => 'internal thoughts']]],
                ['text' => 'Here is the answer.'],
            ]]],
            'stopReason' => 'end_turn',
        ];

        $response = $bedrock->converse(
            $this->simpleRequest()->withCapabilityLevel(Configuration::CAPABILITY_INSTANT),
            self::CONFIGURATION
        );

        $this->assertSame([['type' => 'text', 'text' => 'Here is the answer.']], $response->getContent());
    }

    public function getRedactedReasoningPlaceholderData(): array
    {
        return [
            'bare' => ['[REDACTED]'],
            'trailing period' => ['[REDACTED].'],
            'surrounding whitespace' => ['  [REDACTED]  '],
        ];
    }

    /**
     * Nova 2 does not expose its reasoning text yet: it returns the literal
     * "[REDACTED]" placeholder, which must never be surfaced as reasoning.
     *
     * @dataProvider getRedactedReasoningPlaceholderData
     */
    public function testRedactedReasoningPlaceholderIsNotSurfaced(string $placeholder): void
    {
        $bedrock = new RecordingBedrock();
        $bedrock->cannedResponse = [
            'output' => ['message' => ['content' => [
                ['reasoningContent' => ['reasoningText' => ['text' => $placeholder]]],
                ['toolUse' => ['toolUseId' => 'tu_1', 'name' => 'matomo_site_get', 'input' => ['idSite' => 1]]],
            ]]],
            'stopReason' => 'tool_use',
        ];

        $response = $bedrock->converse(
            $this->simpleRequest()
                ->withModel('amazon.nova-2-lite-v1:0')
                ->withCapabilityLevel(Configuration::CAPABILITY_THINKING),
            self::CONFIGURATION
        );

        $this->assertSame(
            [['type' => 'tool_use', 'id' => 'tu_1', 'name' => 'matomo_site_get', 'input' => ['idSite' => 1]]],
            $response->getContent()
        );
    }

    public function testGenuineStructuredReasoningTextIsStillSurfaced(): void
    {
        $bedrock = new RecordingBedrock();
        $bedrock->cannedResponse = [
            'output' => ['message' => ['content' => [
                ['reasoningContent' => ['reasoningText' => ['text' => 'Genuine step-by-step reasoning.']]],
                ['text' => 'Answer.'],
            ]]],
            'stopReason' => 'end_turn',
        ];

        $response = $bedrock->converse(
            $this->simpleRequest()
                ->withModel('amazon.nova-2-lite-v1:0')
                ->withCapabilityLevel(Configuration::CAPABILITY_THINKING),
            self::CONFIGURATION
        );

        $this->assertSame(
            [
                ['type' => 'reasoning', 'text' => 'Genuine step-by-step reasoning.'],
                ['type' => 'text', 'text' => 'Answer.'],
            ],
            $response->getContent()
        );
    }

    public function getNovaGen1InlineReasoningTagData(): array
    {
        return [
            'reasoning' => ['reasoning'],
            'think' => ['think'],
            'thinking' => ['thinking'],
        ];
    }

    /**
     * Nova gen-1 models have no structured reasoningContent; they emit their
     * chain-of-thought as inline <thinking> tags at the start of the answer,
     * which must be split into a separate reasoning block.
     *
     * @dataProvider getNovaGen1InlineReasoningTagData
     */
    public function testNovaGen1LeadingReasoningIsExposedForThinkingRequests(string $tag): void
    {
        $bedrock = new RecordingBedrock();
        $bedrock->cannedResponse = [
            'output' => ['message' => ['content' => [[
                'text' => sprintf('<%1$s>internal thoughts</%1$s>Here is the answer.', $tag),
            ]]]],
            'stopReason' => 'end_turn',
        ];

        $response = $bedrock->converse(
            $this->simpleRequest()
                ->withModel('amazon.nova-lite-v1:0')
                ->withCapabilityLevel(Configuration::CAPABILITY_THINKING),
            self::CONFIGURATION
        );

        $this->assertSame(
            [
                ['type' => 'reasoning', 'text' => 'internal thoughts'],
                ['type' => 'text', 'text' => 'Here is the answer.'],
            ],
            $response->getContent()
        );
    }

    public function testNovaGen1LeadingReasoningIsRemovedForInstantRequests(): void
    {
        $bedrock = new RecordingBedrock();
        $bedrock->cannedResponse = [
            'output' => ['message' => ['content' => [[
                'text' => '<thinking>internal thoughts</thinking>Here is the answer.',
            ]]]],
            'stopReason' => 'end_turn',
        ];

        $response = $bedrock->converse(
            $this->simpleRequest()
                ->withModel('amazon.nova-lite-v1:0')
                ->withCapabilityLevel(Configuration::CAPABILITY_INSTANT),
            self::CONFIGURATION
        );

        $this->assertSame([['type' => 'text', 'text' => 'Here is the answer.']], $response->getContent());
    }

    public function testNovaGen1MalformedLeadingReasoningRemainsAnswerText(): void
    {
        $bedrock = new RecordingBedrock();
        $bedrock->cannedResponse = [
            'output' => ['message' => ['content' => [['text' => '<thinking>unfinished answer']]]],
            'stopReason' => 'max_tokens',
        ];

        $response = $bedrock->converse(
            $this->simpleRequest()
                ->withModel('amazon.nova-lite-v1:0')
                ->withCapabilityLevel(Configuration::CAPABILITY_THINKING),
            self::CONFIGURATION
        );

        $this->assertSame(
            [['type' => 'text', 'text' => '<thinking>unfinished answer']],
            $response->getContent()
        );
    }

    public function getStructuredReasoningModelData(): array
    {
        return [
            // gpt-oss and Nova 2 surface reasoning as structured
            // reasoningContent, so a leading <thinking> tag in their text is a
            // genuine answer, not reasoning, and must not be split out.
            'gpt-oss' => ['openai.gpt-oss-120b-1:0'],
            'Nova 2 lite' => ['amazon.nova-2-lite-v1:0'],
            'Claude' => ['anthropic.claude-3-7-sonnet-20250219-v1:0'],
            'gen-1 lookalike' => ['myamazon.nova-lite-v1:0'],
        ];
    }

    /**
     * @dataProvider getStructuredReasoningModelData
     */
    public function testModelsWithoutInlineReasoningKeepTagsUnchanged(string $model): void
    {
        $bedrock = new RecordingBedrock();
        $bedrock->cannedResponse = [
            'output' => ['message' => ['content' => [[
                'text' => '<thinking>ordinary answer text</thinking>Still answer text.',
            ]]]],
            'stopReason' => 'end_turn',
        ];

        $response = $bedrock->converse(
            $this->simpleRequest()
                ->withModel($model)
                ->withCapabilityLevel(Configuration::CAPABILITY_THINKING),
            self::CONFIGURATION
        );

        $this->assertSame(
            [['type' => 'text', 'text' => '<thinking>ordinary answer text</thinking>Still answer text.']],
            $response->getContent()
        );
    }

    public function getNovaGen1ProfileAndArnModelData(): array
    {
        return [
            'plain' => ['amazon.nova-lite-v1:0'],
            'region profile' => ['us.amazon.nova-lite-v1:0'],
            'micro tier' => ['amazon.nova-micro-v1:0'],
            'arn' => ['arn:aws:bedrock:us-east-1:123:inference-profile/amazon.nova-pro-v1:0'],
        ];
    }

    /**
     * @dataProvider getNovaGen1ProfileAndArnModelData
     */
    public function testNovaGen1InlineReasoningIsSplitAcrossProfilesAndArns(string $model): void
    {
        $bedrock = new RecordingBedrock();
        $bedrock->cannedResponse = [
            'output' => ['message' => ['content' => [[
                'text' => '<thinking>internal thoughts</thinking>Here is the answer.',
            ]]]],
            'stopReason' => 'end_turn',
        ];

        $response = $bedrock->converse(
            $this->simpleRequest()
                ->withModel($model)
                ->withCapabilityLevel(Configuration::CAPABILITY_THINKING),
            self::CONFIGURATION
        );

        $this->assertSame(
            [
                ['type' => 'reasoning', 'text' => 'internal thoughts'],
                ['type' => 'text', 'text' => 'Here is the answer.'],
            ],
            $response->getContent()
        );
    }

    public function testCompleteStripsNovaGen1LeadingReasoning(): void
    {
        $bedrock = new RecordingBedrock();
        $bedrock->cannedResponse = [
            'output' => ['message' => ['content' => [[
                'text' => '<reasoning>internal thoughts</reasoning>Visible answer.',
            ]]]],
            'stopReason' => 'end_turn',
        ];

        $response = $bedrock->complete(
            (new AIRequest('hello', 'AskMatomo'))
                ->withModel('amazon.nova-lite-v1:0')
                ->withCapabilityLevel(Configuration::CAPABILITY_INSTANT),
            self::CONFIGURATION
        );

        $this->assertSame('Visible answer.', $response->getText());
    }

    public function testCanonicalReasoningIsNotReplayed(): void
    {
        $bedrock = new RecordingBedrock();
        $request = new AIConversationRequest([
            ['role' => 'assistant', 'content' => [
                ['type' => 'reasoning', 'text' => 'internal thoughts'],
                ['type' => 'text', 'text' => 'Visible answer.'],
            ]],
        ], 'AskMatomo');

        $bedrock->converse($request, self::CONFIGURATION);

        $this->assertSame(
            [['role' => 'assistant', 'content' => [['text' => 'Visible answer.']]]],
            $bedrock->sentPayload['messages']
        );
    }

    private function simpleRequest(): AIConversationRequest
    {
        return new AIConversationRequest(
            [['role' => 'user', 'content' => [['type' => 'text', 'text' => 'hello']]]],
            'AskMatomo'
        );
    }

    private function toolRequestForModel(string $model): AIConversationRequest
    {
        return $this->simpleRequest()
            ->withModel($model)
            ->withTools([
                [
                    'name' => 'matomo_site_list',
                    'description' => 'Lists sites',
                    'inputSchema' => ['type' => 'object'],
                ],
            ]);
    }
}

/**
 * Records the Converse call above the transport seam and returns a canned
 * response.
 */
class RecordingBedrock extends Bedrock
{
    /** @var string|null */
    public $sentModel = null;

    /** @var array<string, mixed> */
    public $sentPayload = [];

    /** @var int|null */
    public $sentTimeoutSeconds = null;

    /** @var array<string, mixed> */
    public $cannedResponse = [
        'output' => ['message' => ['role' => 'assistant', 'content' => [['text' => 'ok']]]],
        'stopReason' => 'end_turn',
        'usage' => ['inputTokens' => 1, 'outputTokens' => 1],
    ];

    protected function sendConverseRequest(string $model, array $payload, int $timeoutSeconds, array $configuration): array
    {
        $this->sentModel = $model;
        $this->sentPayload = $payload;
        $this->sentTimeoutSeconds = $timeoutSeconds;

        return $this->cannedResponse;
    }
}

/**
 * Records below the transport seam, replacing the HTTP exchange itself, so
 * the URL building and authentication headers of the real
 * sendConverseRequest() are covered.
 */
class HttpRecordingBedrock extends Bedrock
{
    /** @var string|null */
    public $sentUrl = null;

    /** @var array<string, string> */
    public $sentHeaders = [];

    /** @var array<string, mixed> */
    public $sentPayload = [];

    /** @var int|null */
    public $sentTimeoutSeconds = null;

    /** @var string|null */
    public $sentGetUrl = null;

    /** @var array<string, string> */
    public $sentGetHeaders = [];

    /** @var array<string, mixed> */
    public $cannedGetResponse = ['modelSummaries' => []];

    protected function sendJsonRequest(string $url, array $headers, array $payload, int $timeoutSeconds = 30): array
    {
        $this->sentUrl = $url;
        $this->sentHeaders = $headers;
        $this->sentPayload = $payload;
        $this->sentTimeoutSeconds = $timeoutSeconds;

        return [
            'output' => ['message' => ['role' => 'assistant', 'content' => [['text' => 'ok']]]],
            'stopReason' => 'end_turn',
            'usage' => ['inputTokens' => 1, 'outputTokens' => 1],
        ];
    }

    protected function sendGetRequest(string $url, array $headers, int $timeoutSeconds = 10): array
    {
        $this->sentGetUrl = $url;
        $this->sentGetHeaders = $headers;

        return $this->cannedGetResponse;
    }
}
