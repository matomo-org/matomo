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
use Piwik\Plugins\AIProviders\Provider\CustomProvider;

/**
 * Tests CustomProvider's converse() wiring: the /chat/completions endpoint
 * resolution that is unique to this provider, and that a request is dispatched
 * through the shared OpenAI-compatible chat-completion path. The canonical
 * message/tool translation and response parsing are shared with OpenAI and
 * covered in full by {@see OpenAIConverseTest}.
 *
 * @group AIProviders
 * @group Plugins
 */
class CustomProviderConverseTest extends TestCase
{
    private const CONFIGURATION = [
        'apiKey' => 'secret-custom-key',
        'endpointUrl' => 'https://llm.example.org/v1',
        'model' => 'llama3.2',
    ];

    public function testPayloadUsesResolvedEndpointHeadersConfiguredModelAndRequestDefaults(): void
    {
        $custom = new RecordingCustomProvider();

        $custom->converse($this->simpleRequest(), self::CONFIGURATION);

        // The configured endpoint gets /chat/completions appended.
        $this->assertSame('https://llm.example.org/v1/chat/completions', $custom->sentUrl);
        $this->assertSame(['Authorization' => 'Bearer secret-custom-key'], $custom->sentHeaders);
        $this->assertSame('llama3.2', $custom->sentPayload['model']);
        $this->assertSame(AIConversationRequest::DEFAULT_MAX_TOKENS, $custom->sentPayload['max_tokens']);
        $this->assertSame(AIRequest::DEFAULT_TEMPERATURE, $custom->sentPayload['temperature']);
        $this->assertSame(AIConversationRequest::DEFAULT_TIMEOUT_SECONDS, $custom->sentTimeoutSeconds);
        $this->assertSame(
            [['role' => 'user', 'content' => 'Hello']],
            $custom->sentPayload['messages']
        );
    }

    public function testCompleteDisablesThinkingWhenThinkingBudgetIsZero(): void
    {
        $custom = new RecordingCustomProvider();

        $custom->complete((new AIRequest('Recommend goals', 'Goals'))->withThinkingBudget(0), self::CONFIGURATION);

        $this->assertFalse($custom->sentPayload['think']);
    }

    public function testEndpointWithChatCompletionsSuffixIsLeftIntact(): void
    {
        $custom = new RecordingCustomProvider();

        $custom->converse($this->simpleRequest(), [
            'apiKey' => 'secret-custom-key',
            'endpointUrl' => 'https://llm.example.org/v1/chat/completions',
            'model' => 'llama3.2',
        ]);

        $this->assertSame('https://llm.example.org/v1/chat/completions', $custom->sentUrl);
    }

    public function testConverseThrowsWhenNoModelIsConfiguredOrRequested(): void
    {
        $custom = new RecordingCustomProvider();

        $this->expectException(AIProviderClientException::class);

        $custom->converse($this->simpleRequest(), [
            'apiKey' => 'secret-custom-key',
            'endpointUrl' => 'https://llm.example.org/v1',
        ]);
    }

    public function testCustomProviderSupportsConversations(): void
    {
        $this->assertTrue((new CustomProvider())->supportsConversations());
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
class RecordingCustomProvider extends CustomProvider
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
