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
use Piwik\Plugins\AIProviders\AIRequest;
use Piwik\Plugins\AIProviders\Model\Configuration;
use Piwik\Plugins\AIProviders\Provider\Anthropic;
use Piwik\Plugins\AIProviders\Provider\CustomProvider;
use Piwik\Plugins\AIProviders\Provider\Google;
use Piwik\Plugins\AIProviders\Provider\OpenAI;

/**
 * Verifies how the instant/thinking capability level maps onto each provider's
 * complete() wire format, and that an explicit per-request thinking budget
 * overrides the capability.
 *
 * @group AIProviders
 * @group Plugins
 */
class CapabilityThinkingTest extends TestCase
{
    private const OPENAI_CONFIG = ['apiKey' => 'k', 'endpointUrl' => ''];
    private const CLAUDE_CONFIG = ['apiKey' => 'k', 'endpointUrl' => ''];
    private const GEMINI_CONFIG = ['apiKey' => 'k', 'endpointUrl' => ''];
    private const CUSTOM_CONFIG = ['apiKey' => '', 'endpointUrl' => 'http://localhost:1234/v1', 'model' => 'local-model'];

    public function testOpenAiInstantDisablesReasoning(): void
    {
        $openAI = new RecordingCompleteOpenAI();

        $openAI->complete($this->instantRequest(), self::OPENAI_CONFIG);

        $this->assertSame('none', $openAI->sentPayload['reasoning_effort']);
    }

    public function testOpenAiThinkingEnablesReasoning(): void
    {
        $openAI = new RecordingCompleteOpenAI();

        $openAI->complete($this->thinkingRequest(), self::OPENAI_CONFIG);

        $this->assertSame('medium', $openAI->sentPayload['reasoning_effort']);
    }

    public function testClaudeInstantSendsNoThinkingBlock(): void
    {
        $claude = new RecordingCompleteAnthropic();

        $claude->complete($this->instantRequest(), self::CLAUDE_CONFIG);

        $this->assertArrayNotHasKey('thinking', $claude->sentPayload);
        $this->assertArrayHasKey('temperature', $claude->sentPayload);
    }

    public function testClaudeThinkingEnablesThinkingBumpsMaxTokensAndDropsTemperature(): void
    {
        $claude = new RecordingCompleteAnthropic();

        // Default max_tokens (1024) is below the thinking budget, so it must be
        // bumped above the budget; temperature must be dropped.
        $claude->complete($this->thinkingRequest(), self::CLAUDE_CONFIG);

        // The default thinking budget (2048) is used when none is given.
        $this->assertSame(['type' => 'enabled', 'budget_tokens' => 2048], $claude->sentPayload['thinking']);
        $this->assertGreaterThan(2048, $claude->sentPayload['max_tokens']);
        $this->assertArrayNotHasKey('temperature', $claude->sentPayload);
    }

    public function testClaudeThinkingReadsTextBlockAfterThinkingBlock(): void
    {
        $claude = new RecordingCompleteAnthropic();
        $claude->mockResponse = [
            'content' => [
                ['type' => 'thinking', 'thinking' => 'internal reasoning', 'signature' => 'sig'],
                ['type' => 'text', 'text' => 'visible answer'],
            ],
            'stop_reason' => 'end_turn',
        ];

        $response = $claude->complete($this->thinkingRequest(), self::CLAUDE_CONFIG);

        $this->assertSame('visible answer', $response->getText());
    }

    public function testGeminiInstantDisablesThinkingBudget(): void
    {
        $gemini = new RecordingCompleteGoogle();

        $gemini->complete($this->instantRequest(), self::GEMINI_CONFIG);

        $this->assertSame(0, $gemini->sentPayload['generationConfig']['thinkingConfig']['thinkingBudget']);
    }

    public function testGeminiThinkingSetsPositiveThinkingBudget(): void
    {
        $gemini = new RecordingCompleteGoogle();

        $gemini->complete($this->thinkingRequest(), self::GEMINI_CONFIG);

        $this->assertGreaterThan(0, $gemini->sentPayload['generationConfig']['thinkingConfig']['thinkingBudget']);
    }

    public function testCustomProviderMapsCapabilityOntoThinkFlag(): void
    {
        $custom = new RecordingCompleteCustomProvider();

        $custom->complete($this->instantRequest(), self::CUSTOM_CONFIG);
        $this->assertFalse($custom->sentPayload['think']);

        $custom->complete($this->thinkingRequest(), self::CUSTOM_CONFIG);
        $this->assertTrue($custom->sentPayload['think']);
    }

    public function testExplicitZeroBudgetForcesInstantEvenWhenCapabilityIsThinking(): void
    {
        $openAI = new RecordingCompleteOpenAI();

        // Thinking capability, but the caller pins the budget to 0.
        $request = (new AIRequest('hi', 'Test'))
            ->withCapabilityLevel(Configuration::CAPABILITY_THINKING)
            ->withThinkingBudget(0);

        $openAI->complete($request, self::OPENAI_CONFIG);

        $this->assertSame('none', $openAI->sentPayload['reasoning_effort']);
    }

    private function instantRequest(): AIRequest
    {
        return (new AIRequest('hi', 'Test'))->withCapabilityLevel(Configuration::CAPABILITY_INSTANT);
    }

    private function thinkingRequest(): AIRequest
    {
        return (new AIRequest('hi', 'Test'))->withCapabilityLevel(Configuration::CAPABILITY_THINKING);
    }
}

class RecordingCompleteOpenAI extends OpenAI
{
    /** @var array<string, mixed> */
    public $sentPayload = [];

    protected function sendJsonRequest(string $url, array $headers, array $payload, int $timeoutSeconds = 30): array
    {
        $this->sentPayload = $payload;

        return ['choices' => [['message' => ['content' => 'ok'], 'finish_reason' => 'stop']]];
    }
}

class RecordingCompleteAnthropic extends Anthropic
{
    /** @var array<string, mixed> */
    public $sentPayload = [];

    /** @var array<string, mixed> */
    public $mockResponse = ['content' => [['type' => 'text', 'text' => 'ok']], 'stop_reason' => 'end_turn'];

    protected function sendJsonRequest(string $url, array $headers, array $payload, int $timeoutSeconds = 30): array
    {
        $this->sentPayload = $payload;

        return $this->mockResponse;
    }
}

class RecordingCompleteGoogle extends Google
{
    /** @var array<string, mixed> */
    public $sentPayload = [];

    protected function sendJsonRequest(string $url, array $headers, array $payload, int $timeoutSeconds = 30): array
    {
        $this->sentPayload = $payload;

        return ['candidates' => [['content' => ['parts' => [['text' => 'ok']]]]]];
    }
}

class RecordingCompleteCustomProvider extends CustomProvider
{
    /** @var array<string, mixed> */
    public $sentPayload = [];

    protected function sendJsonRequest(string $url, array $headers, array $payload, int $timeoutSeconds = 30): array
    {
        $this->sentPayload = $payload;

        return ['choices' => [['message' => ['content' => 'ok'], 'finish_reason' => 'stop']]];
    }
}
