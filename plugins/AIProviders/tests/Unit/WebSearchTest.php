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
use Piwik\Plugins\AIProviders\Provider\Anthropic;
use Piwik\Plugins\AIProviders\Provider\Bedrock;
use Piwik\Plugins\AIProviders\Provider\CustomProvider;
use Piwik\Plugins\AIProviders\Provider\Google;
use Piwik\Plugins\AIProviders\Provider\OpenAI;

/**
 * Verifies how a web search request maps onto each provider's complete() wire
 * format, and how each provider's grounding data is parsed back into the shared
 * citation shape.
 *
 * @group AIProviders
 * @group Plugins
 */
class WebSearchTest extends TestCase
{
    private const OPENAI_CONFIG = ['apiKey' => 'k', 'endpointUrl' => ''];
    private const CLAUDE_CONFIG = ['apiKey' => 'k', 'endpointUrl' => ''];
    private const GEMINI_CONFIG = ['apiKey' => 'k', 'endpointUrl' => ''];
    private const CUSTOM_CONFIG = [
        'apiKey' => '',
        'endpointUrl' => 'http://localhost:1234/v1',
        'model' => 'local-model',
    ];
    private const BEDROCK_CONFIG = [
        'apiKey' => 'k',
        'endpointUrl' => '',
        'region' => 'us-east-1',
        'model' => 'openai.gpt-oss-120b-1:0',
    ];

    public function testSupportsWebSearchFlags(): void
    {
        $this->assertTrue((new Anthropic())->supportsWebSearch());
        $this->assertTrue((new Google())->supportsWebSearch());
        $this->assertTrue((new OpenAI())->supportsWebSearch());
        $this->assertFalse((new Bedrock())->supportsWebSearch());
        $this->assertFalse((new CustomProvider())->supportsWebSearch());
    }

    // -- Anthropic: payload ---------------------------------------------------

    public function testAnthropicSendsNoToolsWhenWebSearchNotRequested(): void
    {
        $claude = new WebSearchRecordingAnthropic();

        $claude->complete($this->plainRequest(), self::CLAUDE_CONFIG);

        $this->assertArrayNotHasKey('tools', $claude->sentPayload);
        $this->assertSame(30, $claude->sentTimeout);
    }

    public function testAnthropicSendsWebSearchToolWithACappedNumberOfSearches(): void
    {
        $claude = new WebSearchRecordingAnthropic();

        $claude->complete($this->groundedRequest(), self::CLAUDE_CONFIG);

        $this->assertSame([
            ['type' => 'web_search_20250305', 'name' => 'web_search', 'max_uses' => 5],
        ], $claude->sentPayload['tools']);
    }

    /**
     * No tool_choice is sent: Anthropic defaults it to "auto" when tools are
     * present, which is the behaviour we want (the model decides whether the
     * prompt needs fresh sources) and an explicit auto would invalidate caching.
     */
    public function testAnthropicSendsNoToolChoiceSoTheModelDecides(): void
    {
        $claude = new WebSearchRecordingAnthropic();

        $claude->complete($this->groundedRequest(), self::CLAUDE_CONFIG);

        $this->assertArrayNotHasKey('tool_choice', $claude->sentPayload);
    }

    public function testAnthropicUsesTheLongerTimeoutOnlyForGroundedRequests(): void
    {
        $claude = new WebSearchRecordingAnthropic();

        $claude->complete($this->groundedRequest(), self::CLAUDE_CONFIG);

        $this->assertSame(120, $claude->sentTimeout);
    }

    public function testARequestTimeoutOverridesTheGroundedDefault(): void
    {
        $claude = new WebSearchRecordingAnthropic();

        $claude->complete($this->groundedRequest()->withTimeoutSeconds(45), self::CLAUDE_CONFIG);

        $this->assertSame(45, $claude->sentTimeout);
    }

    // -- Anthropic: parsing ---------------------------------------------------

    /**
     * Regression guard: with search on, the first text block is only the model's
     * "I'll look that up" preamble, so returning it alone lost the answer.
     */
    public function testAnthropicConcatenatesAllTextBlocksAroundASearch(): void
    {
        $claude = new WebSearchRecordingAnthropic();
        $claude->mockResponse = [
            'content' => [
                ['type' => 'text', 'text' => "I'll search for that."],
                [
                    'type' => 'server_tool_use',
                    'id' => 'srvtoolu_1',
                    'name' => 'web_search',
                    'input' => ['query' => 'best web analytics'],
                ],
                [
                    'type' => 'web_search_tool_result',
                    'tool_use_id' => 'srvtoolu_1',
                    'content' => [
                        ['type' => 'web_search_result', 'url' => 'https://matomo.org/', 'title' => 'Matomo'],
                    ],
                ],
                ['type' => 'text', 'text' => 'Matomo is a leading open source option.'],
            ],
            'stop_reason' => 'end_turn',
        ];

        $response = $claude->complete($this->groundedRequest(), self::CLAUDE_CONFIG);

        $this->assertSame("I'll search for that.\nMatomo is a leading open source option.", $response->getText());
    }

    public function testAnthropicParsesCitationsQueriesAndRequestCount(): void
    {
        $claude = new WebSearchRecordingAnthropic();
        $claude->mockResponse = [
            'content' => [
                [
                    'type' => 'server_tool_use',
                    'id' => 'srvtoolu_1',
                    'name' => 'web_search',
                    'input' => ['query' => 'best web analytics'],
                ],
                [
                    'type' => 'web_search_tool_result',
                    'tool_use_id' => 'srvtoolu_1',
                    'content' => [
                        ['type' => 'web_search_result', 'url' => 'https://g2.com/analytics', 'title' => 'G2'],
                    ],
                ],
                [
                    'type' => 'text',
                    'text' => 'Matomo is a leading option.',
                    'citations' => [
                        [
                            'type' => 'web_search_result_location',
                            'url' => 'https://matomo.org/blog/x',
                            'title' => 'Matomo Blog',
                            'cited_text' => 'Matomo is…',
                        ],
                    ],
                ],
            ],
            'usage' => [
                'input_tokens' => 8412,
                'output_tokens' => 431,
                'server_tool_use' => ['web_search_requests' => 3],
            ],
            'stop_reason' => 'end_turn',
        ];

        $response = $claude->complete($this->groundedRequest(), self::CLAUDE_CONFIG);

        $this->assertTrue($response->isWebSearchEnabled());
        $this->assertSame(3, $response->getWebSearchRequestCount());
        $this->assertSame(['best web analytics'], $response->getWebSearchQueries());
        // Cited sources come before merely returned ones.
        $this->assertSame([
            ['url' => 'https://matomo.org/blog/x', 'title' => 'Matomo Blog', 'domain' => 'matomo.org'],
            ['url' => 'https://g2.com/analytics', 'title' => 'G2', 'domain' => 'g2.com'],
        ], $response->getWebSearchCitations());
    }

    /**
     * A failed search still returns HTTP 200 with `content` as a single error
     * object instead of a list of rows, which must not break parsing — and the
     * attempt still counted, so the search is reported as having run.
     */
    public function testAnthropicSearchToolResultErrorYieldsNoCitationsWithoutThrowing(): void
    {
        $claude = new WebSearchRecordingAnthropic();
        $claude->mockResponse = [
            'content' => [
                [
                    'type' => 'server_tool_use',
                    'id' => 'srvtoolu_1',
                    'name' => 'web_search',
                    'input' => ['query' => 'best web analytics'],
                ],
                [
                    'type' => 'web_search_tool_result',
                    'tool_use_id' => 'srvtoolu_1',
                    'content' => ['type' => 'web_search_tool_result_error', 'error_code' => 'max_uses_exceeded'],
                ],
                ['type' => 'text', 'text' => 'I could not search.'],
            ],
            'usage' => ['server_tool_use' => ['web_search_requests' => 1]],
            'stop_reason' => 'end_turn',
        ];

        $response = $claude->complete($this->groundedRequest(), self::CLAUDE_CONFIG);

        $this->assertSame([], $response->getWebSearchCitations());
        $this->assertSame(1, $response->getWebSearchRequestCount());
        $this->assertTrue($response->isWebSearchEnabled(), 'the search was billed even though it failed');
    }

    public function testAnthropicReportsSearchUnusedWhenTheModelChoseNotToSearch(): void
    {
        $claude = new WebSearchRecordingAnthropic();
        $claude->mockResponse = [
            'content' => [['type' => 'text', 'text' => 'Two plus two is four.']],
            'usage' => ['input_tokens' => 12, 'output_tokens' => 7, 'server_tool_use' => ['web_search_requests' => 0]],
            'stop_reason' => 'end_turn',
        ];

        $response = $claude->complete($this->groundedRequest(), self::CLAUDE_CONFIG);

        $this->assertFalse($response->isWebSearchEnabled());
        $this->assertSame(0, $response->getWebSearchRequestCount());
        $this->assertSame([], $response->getWebSearchCitations());
    }

    public function testAnthropicPauseTurnIsSurfacedAsTheStopReason(): void
    {
        $claude = new WebSearchRecordingAnthropic();
        $claude->mockResponse = [
            'content' => [['type' => 'text', 'text' => 'Partial answer so far.']],
            'stop_reason' => 'pause_turn',
        ];

        $response = $claude->complete($this->groundedRequest(), self::CLAUDE_CONFIG);

        $this->assertSame('pause_turn', $response->getStopReason());
    }

    // -- Google: payload ------------------------------------------------------

    public function testGoogleSendsNoToolsWhenWebSearchNotRequested(): void
    {
        $gemini = new WebSearchRecordingGoogle();

        $gemini->complete($this->plainRequest(), self::GEMINI_CONFIG);

        $this->assertArrayNotHasKey('tools', $gemini->sentPayload);
    }

    /**
     * Asserted on the encoded JSON rather than the PHP array: an empty array
     * would satisfy an array comparison but encodes as `[]`, which Google rejects
     * for this object slot.
     */
    public function testGoogleSendsGoogleSearchToolAsAJsonObject(): void
    {
        $gemini = new WebSearchRecordingGoogle();

        $gemini->complete($this->groundedRequest(), self::GEMINI_CONFIG);

        $this->assertStringContainsString(
            '"tools":[{"google_search":{}}]',
            (string) json_encode($gemini->sentPayload)
        );
    }

    // -- Google: parsing ------------------------------------------------------

    /**
     * Regression guard: a grounded candidate returns several text parts, so
     * reading parts[0] truncated the answer to its first fragment.
     */
    public function testGoogleConcatenatesAllTextParts(): void
    {
        $gemini = new WebSearchRecordingGoogle();
        $gemini->mockResponse = [
            'candidates' => [[
                'content' => ['parts' => [
                    ['text' => 'First fragment.'],
                    ['text' => 'Second fragment.'],
                ]],
            ]],
        ];

        $response = $gemini->complete($this->groundedRequest(), self::GEMINI_CONFIG);

        $this->assertSame("First fragment.\nSecond fragment.", $response->getText());
    }

    public function testGoogleSkipsThoughtParts(): void
    {
        $gemini = new WebSearchRecordingGoogle();
        $gemini->mockResponse = [
            'candidates' => [[
                'content' => ['parts' => [
                    ['text' => 'Internal reasoning.', 'thought' => true],
                    ['text' => 'The answer.'],
                ]],
            ]],
        ];

        $response = $gemini->complete($this->groundedRequest(), self::GEMINI_CONFIG);

        $this->assertSame('The answer.', $response->getText());
    }

    public function testGooglePrefersAnExplicitWebDomain(): void
    {
        $response = $this->completeGoogleWithChunks([
            ['web' => [
                'uri' => 'https://vertexaisearch.cloud.google.com/grounding-api-redirect/abc',
                'title' => 'Some Page Title',
                'domain' => 'uefa.com',
            ]],
        ]);

        $this->assertSame('uefa.com', $response->getWebSearchCitations()[0]['domain']);
    }

    /**
     * The shape Google actually returns: a redirect URI whose host is Google's,
     * no `web.domain`, and the publisher host as the title.
     */
    public function testGoogleFallsBackToTheTitleWhenTheUriIsAGroundingRedirect(): void
    {
        $response = $this->completeGoogleWithChunks([
            ['web' => [
                'uri' => 'https://vertexaisearch.cloud.google.com/grounding-api-redirect/abc',
                'title' => 'Matomo.org',
            ]],
        ]);

        $citation = $response->getWebSearchCitations()[0];

        $this->assertSame('matomo.org', $citation['domain']);
        $this->assertSame('https://vertexaisearch.cloud.google.com/grounding-api-redirect/abc', $citation['url']);
    }

    /**
     * A prose title is not a domain: an empty string is reported rather than a
     * guess a caller could not distinguish from a real value.
     */
    public function testGoogleReportsNoDomainWhenNeitherUriNorTitleYieldsOne(): void
    {
        $response = $this->completeGoogleWithChunks([
            ['web' => [
                'uri' => 'https://vertexaisearch.cloud.google.com/grounding-api-redirect/abc',
                'title' => 'Who won Euro 2024 - full report',
            ]],
        ]);

        $this->assertSame('', $response->getWebSearchCitations()[0]['domain']);
    }

    public function testGoogleDerivesTheDomainFromANonRedirectUri(): void
    {
        $response = $this->completeGoogleWithChunks([
            ['web' => ['uri' => 'https://www.example.org/a', 'title' => 'Example']],
        ]);

        $this->assertSame('example.org', $response->getWebSearchCitations()[0]['domain']);
    }

    public function testGoogleDerivesTheRequestCountFromItsSearchQueries(): void
    {
        $gemini = new WebSearchRecordingGoogle();
        $gemini->mockResponse = [
            'candidates' => [[
                'content' => ['parts' => [['text' => 'Spain won.']]],
                'groundingMetadata' => [
                    'webSearchQueries' => ['UEFA Euro 2024 winner', 'who won euro 2024'],
                    'groundingChunks' => [],
                ],
            ]],
        ];

        $response = $gemini->complete($this->groundedRequest(), self::GEMINI_CONFIG);

        $this->assertSame(2, $response->getWebSearchRequestCount());
        $this->assertSame(['UEFA Euro 2024 winner', 'who won euro 2024'], $response->getWebSearchQueries());
    }

    public function testGoogleReportsSearchUnusedWhenNoGroundingMetadataCameBack(): void
    {
        $gemini = new WebSearchRecordingGoogle();

        $response = $gemini->complete($this->groundedRequest(), self::GEMINI_CONFIG);

        $this->assertFalse($response->isWebSearchEnabled());
        $this->assertSame(0, $response->getWebSearchRequestCount());
    }

    // -- OpenAI: payload ------------------------------------------------------

    public function testOpenAiGroundedCompleteTargetsTheResponsesEndpoint(): void
    {
        $openAI = new WebSearchRecordingOpenAI();

        $openAI->complete($this->groundedRequest(), self::OPENAI_CONFIG);

        $this->assertSame('https://api.openai.com/v1/responses', $openAI->sentUrl);
        $this->assertSame(120, $openAI->sentTimeout);
    }

    public function testOpenAiUngroundedCompleteStaysOnChatCompletions(): void
    {
        $openAI = new WebSearchRecordingOpenAI();

        $openAI->complete($this->plainRequest(), self::OPENAI_CONFIG);

        $this->assertSame('https://api.openai.com/v1/chat/completions', $openAI->sentUrl);
        $this->assertArrayNotHasKey('tools', $openAI->sentPayload);
        $this->assertSame(30, $openAI->sentTimeout);
    }

    public function testOpenAiOffersTheWebSearchToolAndLetsTheModelDecide(): void
    {
        $openAI = new WebSearchRecordingOpenAI();

        $openAI->complete($this->groundedRequest(), self::OPENAI_CONFIG);

        $this->assertSame(
            [['type' => 'web_search', 'search_context_size' => 'medium']],
            $openAI->sentPayload['tools']
        );
        $this->assertArrayNotHasKey('tool_choice', $openAI->sentPayload);
    }

    public function testOpenAiCompleteSendsTheResponsesPayloadShape(): void
    {
        $openAI = new WebSearchRecordingOpenAI();

        $openAI->complete($this->groundedRequest()->withMaxTokens(64), self::OPENAI_CONFIG);

        $this->assertSame(
            [['role' => 'user', 'content' => 'best web analytics tools']],
            $openAI->sentPayload['input']
        );
        $this->assertSame('none', $openAI->sentPayload['reasoning']['effort']);
        $this->assertSame(64, $openAI->sentPayload['max_output_tokens']);
        $this->assertArrayNotHasKey('messages', $openAI->sentPayload);
        $this->assertArrayNotHasKey('max_completion_tokens', $openAI->sentPayload);
        $this->assertArrayNotHasKey('max_tokens', $openAI->sentPayload);
        $this->assertArrayNotHasKey('temperature', $openAI->sentPayload);
    }

    /**
     * The Responses API stores prompts and output server-side by default, which
     * chat completions does not. Matomo prompts carry customer data, so storage
     * must be switched off on every request.
     */
    public function testOpenAiCompleteDisablesServerSideStorage(): void
    {
        $openAI = new WebSearchRecordingOpenAI();

        $openAI->complete($this->groundedRequest(), self::OPENAI_CONFIG);

        $this->assertFalse($openAI->sentPayload['store']);
    }

    public function testOpenAiCompleteSendsTheSystemPromptAsADeveloperInputMessage(): void
    {
        $openAI = new WebSearchRecordingOpenAI();

        $openAI->complete($this->groundedRequest()->withSystemPrompt('You are concise.'), self::OPENAI_CONFIG);

        $this->assertSame([
            ['role' => 'developer', 'content' => 'You are concise.'],
            ['role' => 'user', 'content' => 'best web analytics tools'],
        ], $openAI->sentPayload['input']);
        $this->assertArrayNotHasKey('instructions', $openAI->sentPayload);
    }

    /**
     * OpenAI rejects the combination outright, so the native format is dropped and
     * JSON is requested through the prompt instruction instead of failing.
     */
    public function testOpenAiDropsNativeJsonFormatWhenGroundingIsOn(): void
    {
        $openAI = new WebSearchRecordingOpenAI();

        $openAI->complete($this->groundedRequest()->withJsonResponse(), self::OPENAI_CONFIG);

        $this->assertArrayNotHasKey('text', $openAI->sentPayload);
        $this->assertArrayHasKey('tools', $openAI->sentPayload);
        $this->assertStringContainsString('JSON', $openAI->sentPayload['input'][0]['content']);
    }

    // -- OpenAI: parsing ------------------------------------------------------

    public function testOpenAiConcatenatesOutputTextAcrossMessageItems(): void
    {
        $openAI = new WebSearchRecordingOpenAI();
        $openAI->mockResponse = [
            'output' => [
                ['type' => 'reasoning', 'summary' => []],
                ['type' => 'web_search_call', 'action' => ['type' => 'search', 'query' => 'best web analytics']],
                ['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'First part.']]],
                ['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'Second part.']]],
            ],
            'status' => 'completed',
        ];

        $response = $openAI->complete($this->groundedRequest(), self::OPENAI_CONFIG);

        $this->assertSame("First part.\nSecond part.", $response->getText());
    }

    public function testOpenAiParsesUrlCitationAnnotationsAndCountsSearchCalls(): void
    {
        $openAI = new WebSearchRecordingOpenAI();
        $openAI->mockResponse = [
            'output' => [
                ['type' => 'web_search_call', 'action' => ['type' => 'search', 'query' => 'best web analytics']],
                ['type' => 'web_search_call', 'action' => ['type' => 'search']],
                [
                    'type' => 'message',
                    'content' => [[
                        'type' => 'output_text',
                        'text' => 'Matomo is a leading option.',
                        'annotations' => [
                            [
                                'type' => 'url_citation',
                                'url' => 'https://matomo.org/blog/x',
                                'title' => 'Matomo Blog',
                                'start_index' => 0,
                                'end_index' => 10,
                            ],
                            ['type' => 'file_citation', 'file_id' => 'ignored'],
                        ],
                    ]],
                ],
            ],
            'usage' => ['input_tokens' => 4050, 'output_tokens' => 800],
            'status' => 'completed',
        ];

        $response = $openAI->complete($this->groundedRequest(), self::OPENAI_CONFIG);

        $this->assertTrue($response->isWebSearchEnabled());
        $this->assertSame(2, $response->getWebSearchRequestCount());
        $this->assertSame(['best web analytics'], $response->getWebSearchQueries());
        $this->assertSame([
            ['url' => 'https://matomo.org/blog/x', 'title' => 'Matomo Blog', 'domain' => 'matomo.org'],
        ], $response->getWebSearchCitations());
        $this->assertSame(4050, $response->getInputTokens());
        $this->assertSame(800, $response->getOutputTokens());
    }

    public function testOpenAiIncompleteDetailsReasonWinsOverStatusAsStopReason(): void
    {
        $truncated = new WebSearchRecordingOpenAI();
        $truncated->mockResponse = [
            'output' => [['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'Cut short']]]],
            'status' => 'incomplete',
            'incomplete_details' => ['reason' => 'max_output_tokens'],
        ];

        $completed = new WebSearchRecordingOpenAI();

        $this->assertSame(
            'max_output_tokens',
            $truncated->complete($this->groundedRequest(), self::OPENAI_CONFIG)->getStopReason()
        );
        $this->assertSame(
            'completed',
            $completed->complete($this->groundedRequest(), self::OPENAI_CONFIG)->getStopReason()
        );
    }

    /**
     * Regression guard for the migration: converse() must stay on chat
     * completions, where the canonical tool-calling translation lives.
     */
    public function testOpenAiConverseStillUsesChatCompletions(): void
    {
        $openAI = new WebSearchRecordingOpenAI();

        $openAI->converse(
            new AIConversationRequest(
                [['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Hello']]]],
                'Test'
            ),
            self::OPENAI_CONFIG
        );

        $this->assertSame('https://api.openai.com/v1/chat/completions', $openAI->sentUrl);
        $this->assertArrayHasKey('max_completion_tokens', $openAI->sentPayload);
        $this->assertArrayNotHasKey('reasoning', $openAI->sentPayload);
    }

    // -- Providers without web search ----------------------------------------

    public function testCustomProviderIgnoresAWebSearchRequest(): void
    {
        $custom = new WebSearchRecordingCustomProvider();

        $response = $custom->complete($this->groundedRequest(), self::CUSTOM_CONFIG);

        $this->assertArrayNotHasKey('tools', $custom->sentPayload);
        $this->assertSame(30, $custom->sentTimeout, 'no grounded timeout for a provider that cannot ground');
        $this->assertFalse($response->isWebSearchEnabled());
        $this->assertSame([], $response->getWebSearchCitations());
        $this->assertNull(
            $response->getWebSearchRequestCount(),
            'null means "cannot report", which is different from a reported zero'
        );
    }

    public function testBedrockIgnoresAWebSearchRequest(): void
    {
        $bedrock = new WebSearchRecordingBedrock();

        $response = $bedrock->complete($this->groundedRequest(), self::BEDROCK_CONFIG);

        $this->assertArrayNotHasKey('tools', $bedrock->sentPayload);
        $this->assertArrayNotHasKey('toolConfig', $bedrock->sentPayload);
        $this->assertFalse($response->isWebSearchEnabled());
        $this->assertNull($response->getWebSearchRequestCount());
    }

    // -- helpers --------------------------------------------------------------

    private function plainRequest(): AIRequest
    {
        return new AIRequest('why is the sky blue', 'Test');
    }

    private function groundedRequest(): AIRequest
    {
        return (new AIRequest('best web analytics tools', 'Test'))->withWebSearchEnabled(true);
    }

    /**
     * @param list<array<string, mixed>> $chunks
     */
    private function completeGoogleWithChunks(array $chunks): AIProviderResponse
    {
        $gemini = new WebSearchRecordingGoogle();
        $gemini->mockResponse = [
            'candidates' => [[
                'content' => ['parts' => [['text' => 'Answer.']]],
                'groundingMetadata' => [
                    'webSearchQueries' => ['a query'],
                    'groundingChunks' => $chunks,
                ],
            ]],
        ];

        return $gemini->complete($this->groundedRequest(), self::GEMINI_CONFIG);
    }
}

class WebSearchRecordingAnthropic extends Anthropic
{
    /** @var array<string, mixed> */
    public $sentPayload = [];

    /** @var int|null */
    public $sentTimeout = null;

    /** @var array<string, mixed> */
    public $mockResponse = ['content' => [['type' => 'text', 'text' => 'ok']], 'stop_reason' => 'end_turn'];

    protected function sendJsonRequest(string $url, array $headers, array $payload, int $timeoutSeconds = 30): array
    {
        $this->sentPayload = $payload;
        $this->sentTimeout = $timeoutSeconds;

        return $this->mockResponse;
    }
}

class WebSearchRecordingGoogle extends Google
{
    /** @var array<string, mixed> */
    public $sentPayload = [];

    /** @var int|null */
    public $sentTimeout = null;

    /** @var array<string, mixed> */
    public $mockResponse = ['candidates' => [['content' => ['parts' => [['text' => 'ok']]]]]];

    protected function sendJsonRequest(string $url, array $headers, array $payload, int $timeoutSeconds = 30): array
    {
        $this->sentPayload = $payload;
        $this->sentTimeout = $timeoutSeconds;

        return $this->mockResponse;
    }
}

class WebSearchRecordingOpenAI extends OpenAI
{
    /** @var string */
    public $sentUrl = '';

    /** @var array<string, mixed> */
    public $sentPayload = [];

    /** @var int|null */
    public $sentTimeout = null;

    /** @var array<string, mixed> */
    public $mockResponse = [
        'output' => [['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'ok']]]],
        'status' => 'completed',
    ];

    protected function sendJsonRequest(string $url, array $headers, array $payload, int $timeoutSeconds = 30): array
    {
        $this->sentUrl = $url;
        $this->sentPayload = $payload;
        $this->sentTimeout = $timeoutSeconds;

        // converse() speaks chat completions, so it needs a choices[] body.
        if (strpos($url, '/chat/completions') !== false) {
            return ['choices' => [['message' => ['content' => 'ok'], 'finish_reason' => 'stop']]];
        }

        return $this->mockResponse;
    }
}

class WebSearchRecordingCustomProvider extends CustomProvider
{
    /** @var array<string, mixed> */
    public $sentPayload = [];

    /** @var int|null */
    public $sentTimeout = null;

    protected function sendJsonRequest(string $url, array $headers, array $payload, int $timeoutSeconds = 30): array
    {
        $this->sentPayload = $payload;
        $this->sentTimeout = $timeoutSeconds;

        return ['choices' => [['message' => ['content' => 'ok'], 'finish_reason' => 'stop']]];
    }
}

class WebSearchRecordingBedrock extends Bedrock
{
    /** @var array<string, mixed> */
    public $sentPayload = [];

    /** @var int|null */
    public $sentTimeout = null;

    // Intercepted at sendConverseRequest() rather than sendJsonRequest() so the
    // test does not depend on Bedrock's region/endpoint derivation.
    protected function sendConverseRequest(
        string $model,
        array $payload,
        int $timeoutSeconds,
        array $configuration
    ): array {
        $this->sentPayload = $payload;
        $this->sentTimeout = $timeoutSeconds;

        return [
            'output' => ['message' => ['content' => [['text' => 'ok']]]],
            'stopReason' => 'end_turn',
        ];
    }
}
