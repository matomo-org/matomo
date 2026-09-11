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
use Piwik\Plugins\AIProviders\AIConversationResponse;
use Piwik\Plugins\AIProviders\AIProviderResponse;
use Piwik\Plugins\AIProviders\AIRequest;
use Piwik\Plugins\AIProviders\Exception\AIProviderClientException;
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
        // No tool_choice: Anthropic defaults it to "auto" when tools are present,
        // so Claude decides whether the prompt needs fresh sources.
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

        // A space is inserted because the search blocks stood between the two, and
        // Anthropic pads neither side. Without it the sentences ran together.
        $this->assertSame("I'll search for that. Matomo is a leading open source option.", $response->getText());
    }

    /**
     * Regression guard for the real defect a separator caused: a grounded answer
     * splits mid-sentence, so any character inserted between the fragments lands
     * inside the JSON string value and makes the response undecodable.
     */
    public function testGroundedJsonModeSurvivesACitationSplitInsideAStringValue(): void
    {
        $claude = new WebSearchRecordingAnthropic();
        $claude->mockResponse = [
            'content' => [
                ['type' => 'text', 'text' => '{"verdict": "Matomo is '],
                [
                    'type' => 'text',
                    'text' => 'the leading option"}',
                    'citations' => [[
                        'type' => 'web_search_result_location',
                        'url' => 'https://matomo.org/',
                        'title' => 'Matomo',
                    ]],
                ],
            ],
            'stop_reason' => 'end_turn',
        ];

        $response = $claude->complete($this->groundedRequest()->withJsonResponse(), self::CLAUDE_CONFIG);

        $this->assertSame(['verdict' => 'Matomo is the leading option'], $response->getJsonData());
    }

    /**
     * The multi-block read also changed the ungrounded path, where extended
     * thinking puts a thinking block before the answer.
     */
    public function testAnthropicUngroundedThinkingResponseReturnsOnlyTheAnswer(): void
    {
        $claude = new WebSearchRecordingAnthropic();
        $claude->mockResponse = [
            'content' => [
                ['type' => 'thinking', 'thinking' => 'Let me consider the options.'],
                ['type' => 'text', 'text' => 'Matomo.'],
            ],
            'stop_reason' => 'end_turn',
        ];

        $response = $claude->complete($this->plainRequest(), self::CLAUDE_CONFIG);

        $this->assertSame('Matomo.', $response->getText());
        $this->assertFalse($response->wasWebSearchUsed());
    }

    /**
     * Anthropic's own documented grounded shape: the preamble ends with a full
     * stop and no trailing space, and the post-search block does not start with
     * one. Joining these with nothing produced "born.Based on the search".
     */
    public function testAnthropicSpacesSentencesSeparatedByTheSearchBlocks(): void
    {
        $claude = new WebSearchRecordingAnthropic();
        $claude->mockResponse = [
            'content' => [
                ['type' => 'text', 'text' => "I'll search for when Claude Shannon was born."],
                [
                    'type' => 'server_tool_use',
                    'id' => 'srvtoolu_1',
                    'name' => 'web_search',
                    'input' => ['query' => 'Claude Shannon birth date'],
                ],
                [
                    'type' => 'web_search_tool_result',
                    'tool_use_id' => 'srvtoolu_1',
                    'content' => [
                        ['type' => 'web_search_result', 'url' => 'https://example.org/s', 'title' => 'Shannon'],
                    ],
                ],
                ['type' => 'text', 'text' => 'Based on the search results, '],
                ['type' => 'text', 'text' => 'he was born on April 30, 1916.'],
            ],
            'stop_reason' => 'end_turn',
        ];

        $response = $claude->complete($this->groundedRequest(), self::CLAUDE_CONFIG);

        $this->assertSame(
            "I'll search for when Claude Shannon was born. Based on the search results, he was born on April 30, 1916.",
            $response->getText()
        );
    }

    /**
     * A thinking block is a boundary too, but it precedes the whole answer, so
     * nothing may be prepended to it.
     */
    public function testAnthropicDoesNotPrependASpaceAfterAThinkingBlock(): void
    {
        $claude = new WebSearchRecordingAnthropic();
        $claude->mockResponse = [
            'content' => [
                ['type' => 'thinking', 'thinking' => 'Considering the options.'],
                ['type' => 'text', 'text' => 'Matomo.'],
            ],
            'stop_reason' => 'end_turn',
        ];

        $this->assertSame('Matomo.', $claude->complete($this->plainRequest(), self::CLAUDE_CONFIG)->getText());
    }

    /**
     * An empty text block must not swallow the boundary the search blocks
     * created, or the sentences either side of it run together again.
     */
    public function testAnthropicKeepsAPendingBoundaryAcrossAnEmptyTextBlock(): void
    {
        $claude = new WebSearchRecordingAnthropic();
        $claude->mockResponse = [
            'content' => [
                ['type' => 'text', 'text' => 'Let me check.'],
                ['type' => 'server_tool_use', 'id' => 's1', 'name' => 'web_search', 'input' => ['query' => 'q']],
                ['type' => 'text', 'text' => ''],
                ['type' => 'text', 'text' => 'Matomo leads.'],
            ],
            'stop_reason' => 'end_turn',
        ];

        $response = $claude->complete($this->groundedRequest(), self::CLAUDE_CONFIG);

        $this->assertSame('Let me check. Matomo leads.', $response->getText());
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

        $this->assertTrue($response->wasWebSearchUsed());
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
        $this->assertTrue($response->wasWebSearchUsed(), 'the search was billed even though it failed');
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

        $this->assertFalse($response->wasWebSearchUsed());
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

    /**
     * Verified against gemini-3.1-flash-lite: Gemini accepts the tool alongside a
     * JSON request (HTTP 200) but then answers without searching, and asking for
     * JSON suppresses grounding through the prompt instruction too — so dropping
     * responseMimeType would not restore the search and would only lose the native
     * format as well. The combination is therefore refused before anything is
     * spent, for the same reason a provider with no web search at all is refused.
     */
    public function testGoogleRejectsWebSearchCombinedWithJsonMode(): void
    {
        $gemini = new WebSearchRecordingGoogle();

        $this->expectException(AIProviderClientException::class);
        $this->expectExceptionMessage('cannot combine web search with JSON mode');

        $gemini->complete($this->groundedRequest()->withJsonResponse(), self::GEMINI_CONFIG);
    }

    /**
     * Only the combination is refused: either option on its own is untouched.
     */
    public function testGoogleAcceptsJsonModeAndWebSearchSeparately(): void
    {
        $json = new WebSearchRecordingGoogle();
        $json->mockResponse = [
            'candidates' => [[
                'content' => ['parts' => [['text' => '{"winner": "Spain"}']]],
                'finishReason' => 'STOP',
            ]],
        ];
        $jsonResponse = $json->complete($this->plainRequest()->withJsonResponse(), self::GEMINI_CONFIG);

        $this->assertSame(['winner' => 'Spain'], $jsonResponse->getJsonData());
        $this->assertArrayNotHasKey('tools', $json->sentPayload);

        $grounded = new WebSearchRecordingGoogle();
        $grounded->complete($this->groundedRequest(), self::GEMINI_CONFIG);

        $this->assertArrayNotHasKey('responseMimeType', $grounded->sentPayload['generationConfig']);
        $this->assertStringContainsString(
            '"tools":[{"google_search":{}}]',
            (string) json_encode($grounded->sentPayload)
        );
    }

    /**
     * Google's finishReason is mapped onto the same canonical vocabulary the
     * conversation path uses, so `end_turn` means the same thing whichever
     * provider ran instead of Google alone shouting `STOP`.
     *
     * @dataProvider getGoogleFinishReasons
     */
    public function testGoogleMapsTheFinishReasonOntoTheCanonicalStopReason(
        ?string $finishReason,
        ?string $expected
    ): void {
        $gemini = new WebSearchRecordingGoogle();
        $candidate = ['content' => ['parts' => [['text' => 'Answer.']]]];
        if ($finishReason !== null) {
            $candidate['finishReason'] = $finishReason;
        }
        $gemini->mockResponse = ['candidates' => [$candidate]];

        $response = $gemini->complete($this->plainRequest(), self::GEMINI_CONFIG);

        $this->assertSame($expected, $response->getStopReason());
    }

    /**
     * @return iterable<string, array{string|null, string|null}>
     */
    public function getGoogleFinishReasons(): iterable
    {
        yield 'end of turn' => ['STOP', AIConversationResponse::STOP_END_TURN];
        yield 'truncated' => ['MAX_TOKENS', AIConversationResponse::STOP_MAX_TOKENS];
        yield 'safety' => ['SAFETY', AIConversationResponse::STOP_GUARDRAIL_INTERVENED];
        yield 'recitation' => ['RECITATION', AIConversationResponse::STOP_GUARDRAIL_INTERVENED];
        yield 'unrecognised passes through' => ['OTHER', 'OTHER'];
        yield 'absent stays null' => [null, null];
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
                    ['text' => 'First fragment. '],
                    ['text' => 'Second fragment.'],
                ]],
            ]],
        ];

        $response = $gemini->complete($this->groundedRequest(), self::GEMINI_CONFIG);

        $this->assertSame('First fragment. Second fragment.', $response->getText());
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

    /**
     * `web.domain` wins, then the URI host unless it is Google's redirect, then
     * the title when it looks like a hostname. Google in practice sends only the
     * redirect plus a bare-host title, and a prose title yields '' rather than a
     * guess a caller could not tell from a real value.
     *
     * @dataProvider getGroundingChunkDomains
     * @param array<string, mixed> $web
     */
    public function testGoogleResolvesTheCitationDomain(array $web, string $expected): void
    {
        $response = $this->completeGoogleWithChunks([['web' => $web]]);

        $this->assertSame($expected, $response->getWebSearchCitations()[0]['domain']);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public function getGroundingChunkDomains(): iterable
    {
        yield 'explicit web.domain wins over redirect uri and title' => [
            ['uri' => 'https://vertexaisearch.cloud.google.com/grounding-api-redirect/abc', 'title' => 'Some Page Title', 'domain' => 'uefa.com'],
            'uefa.com',
        ];
        yield 'bare-host title behind a redirect uri' => [
            ['uri' => 'https://vertexaisearch.cloud.google.com/grounding-api-redirect/abc', 'title' => 'Matomo.org'],
            'matomo.org',
        ];
        yield 'prose title behind a redirect uri yields nothing' => [
            ['uri' => 'https://vertexaisearch.cloud.google.com/grounding-api-redirect/abc', 'title' => 'Who won Euro 2024 - full report'],
            '',
        ];
        yield 'filename title is not a hostname' => [
            ['uri' => 'https://vertexaisearch.cloud.google.com/grounding-api-redirect/abc', 'title' => 'report.pdf'],
            '',
        ];
        yield 'host derived from a non-redirect uri' => [
            ['uri' => 'https://www.example.org/a', 'title' => 'Example'],
            'example.org',
        ];
    }

    public function testGoogleKeepsTheRedirectUrlAsTheCitationUrl(): void
    {
        $response = $this->completeGoogleWithChunks([
            ['web' => ['uri' => 'https://vertexaisearch.cloud.google.com/grounding-api-redirect/abc', 'title' => 'Matomo.org']],
        ]);

        $this->assertSame('https://vertexaisearch.cloud.google.com/grounding-api-redirect/abc', $response->getWebSearchCitations()[0]['url']);
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

        $this->assertFalse($response->wasWebSearchUsed());
        $this->assertSame(0, $response->getWebSearchRequestCount());
    }

    // -- OpenAI: payload ------------------------------------------------------

    /**
     * The grounded request switches API, offers the tool without forcing it, and
     * disables the Responses API's server-side prompt storage, which chat
     * completions does not do and which Matomo prompts must not be subject to.
     */
    public function testOpenAiGroundedCompleteSendsTheResponsesRequest(): void
    {
        $openAI = new WebSearchRecordingOpenAI();

        $openAI->complete($this->groundedRequest()->withMaxTokens(64), self::OPENAI_CONFIG);

        $this->assertSame('https://api.openai.com/v1/responses', $openAI->sentUrl);
        $this->assertSame(120, $openAI->sentTimeout);
        $this->assertSame(
            [['type' => 'web_search', 'search_context_size' => 'medium']],
            $openAI->sentPayload['tools']
        );
        $this->assertArrayNotHasKey('tool_choice', $openAI->sentPayload);
        $this->assertFalse($openAI->sentPayload['store'], 'prompts must not be retained by OpenAI');
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

    public function testOpenAiUngroundedCompleteStaysOnChatCompletions(): void
    {
        $openAI = new WebSearchRecordingOpenAI();

        $openAI->complete($this->plainRequest(), self::OPENAI_CONFIG);

        $this->assertSame('https://api.openai.com/v1/chat/completions', $openAI->sentUrl);
        $this->assertArrayNotHasKey('tools', $openAI->sentPayload);
        $this->assertSame(30, $openAI->sentTimeout);
    }

    /**
     * The system prompt is an input message with the `developer` role, not the
     * top-level `instructions` field, because OpenAI does not count
     * `instructions` when it looks for the word "json" that native JSON mode
     * requires.
     */
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

        $this->assertSame('First part. Second part.', $response->getText());
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

        $this->assertTrue($response->wasWebSearchUsed());
        $this->assertSame(2, $response->getWebSearchRequestCount());
        $this->assertSame(['best web analytics'], $response->getWebSearchQueries());
        $this->assertSame([
            ['url' => 'https://matomo.org/blog/x', 'title' => 'Matomo Blog', 'domain' => 'matomo.org'],
        ], $response->getWebSearchCitations());
        $this->assertSame(4050, $response->getInputTokens());
        $this->assertSame(800, $response->getOutputTokens());
    }

    /**
     * Reasoning models emit open_page and find_in_page actions on the same
     * web_search_call item type. Counting those as searches would overstate the
     * per-search fee a caller is billed for.
     */
    public function testOpenAiCountsOnlySearchActionsNotPageFollowUps(): void
    {
        $openAI = new WebSearchRecordingOpenAI();
        $openAI->mockResponse = [
            'output' => [
                ['type' => 'web_search_call', 'action' => ['type' => 'search', 'query' => 'best analytics']],
                ['type' => 'web_search_call', 'action' => ['type' => 'open_page', 'url' => 'https://matomo.org/']],
                ['type' => 'web_search_call', 'action' => ['type' => 'find_in_page', 'pattern' => 'privacy']],
                ['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'Matomo.']]],
            ],
            'status' => 'completed',
        ];

        $response = $openAI->complete($this->groundedRequest(), self::OPENAI_CONFIG);

        $this->assertSame(1, $response->getWebSearchRequestCount());
        $this->assertSame(['best analytics'], $response->getWebSearchQueries());
    }

    /**
     * Parts of one message are a sentence split at a citation boundary and carry
     * their own spacing, so nothing is inserted. A separate message starts new
     * prose and OpenAI pads neither side, so one space is inserted. Never a
     * newline: a boundary can fall inside a JSON string value.
     */
    public function testOpenAiJoinsPartsWithinAMessageAndSpacesBetweenMessages(): void
    {
        $openAI = new WebSearchRecordingOpenAI();
        $openAI->mockResponse = [
            'output' => [
                ['type' => 'message', 'content' => [
                    ['type' => 'output_text', 'text' => 'Matomo is '],
                    ['type' => 'output_text', 'text' => 'open source.'],
                ]],
                ['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'It self-hosts.']]],
            ],
            'status' => 'completed',
        ];

        $response = $openAI->complete($this->groundedRequest(), self::OPENAI_CONFIG);

        $this->assertSame('Matomo is open source. It self-hosts.', $response->getText());
    }

    /**
     * The boundary between two output messages must not be a newline: a grounded
     * JSON answer can be split across them, and a raw newline inside a string
     * value makes the response undecodable.
     */
    public function testOpenAiGroundedJsonSurvivesAMessageBoundary(): void
    {
        $openAI = new WebSearchRecordingOpenAI();
        $openAI->mockResponse = [
            'output' => [
                ['type' => 'web_search_call', 'action' => ['type' => 'search', 'query' => 'analytics']],
                ['type' => 'message', 'content' => [['type' => 'output_text', 'text' => '{"verdict": "Matomo is ']]],
                ['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'open source"}']]],
            ],
            'status' => 'completed',
        ];

        $response = $openAI->complete($this->groundedRequest()->withJsonResponse(), self::OPENAI_CONFIG);

        $this->assertSame(['verdict' => 'Matomo is open source'], $response->getJsonData());
    }

    /**
     * `incomplete_details.reason` is the specific outcome and wins over the
     * generic `status`, and both are reported in the same vocabulary the
     * ungrounded chat-completions path uses, so getStopReason() does not depend
     * on whether the completion happened to be grounded.
     */
    public function testOpenAiReportsGroundedStopReasonsInTheChatCompletionsVocabulary(): void
    {
        $truncated = new WebSearchRecordingOpenAI();
        $truncated->mockResponse = [
            'output' => [['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'Cut short']]]],
            'status' => 'incomplete',
            'incomplete_details' => ['reason' => 'max_output_tokens'],
        ];

        $completed = new WebSearchRecordingOpenAI();

        // Same values the ungrounded path reports as `finish_reason`.
        $this->assertSame(
            'length',
            $truncated->complete($this->groundedRequest(), self::OPENAI_CONFIG)->getStopReason()
        );
        $this->assertSame(
            'stop',
            $completed->complete($this->groundedRequest(), self::OPENAI_CONFIG)->getStopReason()
        );
    }

    /**
     * An outcome with no chat-completions equivalent is passed through, because
     * only the raw value says what went wrong.
     */
    public function testOpenAiPassesThroughAnUnrecognisedResponsesStatus(): void
    {
        $failed = new WebSearchRecordingOpenAI();
        $failed->mockResponse = ['output' => [], 'status' => 'failed'];

        $this->assertSame(
            'failed',
            $failed->complete($this->groundedRequest(), self::OPENAI_CONFIG)->getStopReason()
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
        $this->assertFalse($response->wasWebSearchUsed());
        $this->assertSame([], $response->getWebSearchCitations());
        $this->assertNull(
            $response->getWebSearchRequestCount(),
            'null means "cannot report", which is different from a reported zero'
        );
    }

    // -- deprecated provider surface -----------------------------------------

    /**
     * A provider written against Matomo 5.13.0 could only report a search by
     * overriding the now-deprecated isWebSearchUsed(). Dropping that override on
     * the floor would silently downgrade its answers to "ungrounded", so it is
     * still consulted until Matomo 6.
     */
    public function testALegacyProviderOverridingIsWebSearchUsedIsStillBelieved(): void
    {
        $legacy = new WebSearchLegacyOverrideProvider();

        $response = $legacy->complete($this->groundedRequest(), self::CUSTOM_CONFIG);

        $this->assertTrue($response->wasWebSearchUsed());
        $this->assertTrue($response->isWebSearchEnabled());
        // The flag carries no detail; only a WebSearchUsage does.
        $this->assertSame([], $response->getWebSearchCitations());
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

/**
 * Mimics a third-party provider written against Matomo 5.13.0: it reports a search
 * through the deprecated override and never passes a WebSearchUsage.
 */
class WebSearchLegacyOverrideProvider extends CustomProvider
{
    protected function isWebSearchUsed(AIRequest $request): bool
    {
        return $request->isWebSearchEnabled();
    }

    protected function sendJsonRequest(string $url, array $headers, array $payload, int $timeoutSeconds = self::COMPLETE_TIMEOUT_SECONDS): array
    {
        return ['choices' => [['message' => ['content' => 'ok'], 'finish_reason' => 'stop']]];
    }
}
