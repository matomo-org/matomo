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
use Piwik\Plugins\AIProviders\Provider\Bedrock;

/**
 * Covers which Bedrock models get an `inferenceConfig.temperature`.
 *
 * @group AIProviders
 * @group Plugins
 */
class BedrockTemperatureTest extends TestCase
{
    private const CONFIGURATION = ['apiKey' => 'bedrock-api-key', 'endpointUrl' => '', 'model' => ''];

    /**
     * @dataProvider models
     */
    public function testSendsTemperatureOnlyForModelsThatAcceptIt(string $model, bool $expected): void
    {
        $bedrock = new TemperatureRecordingBedrock();
        $bedrock->converse($this->conversationRequest()->withModel($model), self::CONFIGURATION);

        $this->assertSame(
            $expected,
            array_key_exists('temperature', $bedrock->sentPayload['inferenceConfig']),
            $model
        );
        $this->assertSame(32, $bedrock->sentPayload['inferenceConfig']['maxTokens'], $model);
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public function models(): iterable
    {
        // Accepted: the default model and one model per allowlisted vendor.
        yield 'gpt-oss default' => ['openai.gpt-oss-120b-1:0', true];
        yield 'nova' => ['amazon.nova-lite-v1:0', true];
        yield 'titan text' => ['amazon.titan-text-express-v1', true];
        yield 'llama' => ['meta.llama3-3-70b-instruct-v1:0', true];
        yield 'mistral' => ['mistral.mistral-large-2407-v1:0', true];
        yield 'qwen' => ['qwen.qwen3-32b-v1:0', true];
        yield 'deepseek' => ['deepseek.v3-v1:0', true];
        yield 'gemma' => ['google.gemma-3-12b-it-v1:0', true];

        // Accepted: Claude up to and including Opus 4.6.
        yield 'claude 3 haiku' => ['anthropic.claude-3-haiku-20240307-v1:0', true];
        yield 'claude opus 4.6' => ['anthropic.claude-opus-4-6-v1', true];
        yield 'claude opus 4, dated' => ['us.anthropic.claude-opus-4-20250514-v1:0', true];
        yield 'claude sonnet 4, aliased' => ['us.anthropic.claude-sonnet-4-0', true];

        // Rejected: OpenAI frontier, and Claude released after Opus 4.6.
        yield 'gpt-5.5' => ['openai.gpt-5.5', false];
        yield 'claude opus 4.7' => ['anthropic.claude-opus-4-7', false];
        yield 'claude opus 5' => ['anthropic.claude-opus-5', false];

        // The point-release lookahead: a longer minor must not match a shorter
        // allowlisted one.
        yield 'claude opus 4.1' => ['anthropic.claude-opus-4-1', true];
        yield 'claude opus 4.10' => ['anthropic.claude-opus-4-10', false];

        // Unlisted entirely: models absent from the catalogue snapshot and
        // anything AWS adds later must degrade rather than fail.
        yield 'unknown vendor' => ['acme.some-future-model-v9:0', false];
        yield 'empty model falls back to default' => ['', true];
    }

    /**
     * Cross-region inference profiles and ARNs prefix the vendor-qualified ID,
     * so the decision has to survive the prefix in both directions. Grok is
     * only reachable this way: Converse serves it through its cross-Region
     * inference IDs, never the bare model ID.
     *
     * @dataProvider prefixedModels
     */
    public function testDecisionSurvivesRegionalPrefixes(string $model, bool $expected): void
    {
        $bedrock = new TemperatureRecordingBedrock();
        $bedrock->converse($this->conversationRequest()->withModel($model), self::CONFIGURATION);

        $this->assertSame(
            $expected,
            array_key_exists('temperature', $bedrock->sentPayload['inferenceConfig']),
            $model
        );
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public function prefixedModels(): iterable
    {
        yield 'region prefix accepts' => ['eu.amazon.nova-lite-v1:0', true];
        yield 'region prefix rejects' => ['eu.openai.gpt-5.5', false];
        yield 'geo prefix, grok accepts' => ['us.xai.grok-4.6', true];
        yield 'global prefix, grok accepts' => ['global.xai.grok-4.6', true];
        yield 'global prefix, claude accepts' => ['global.anthropic.claude-opus-4-6-v1', true];
        yield 'us-gov prefix, claude rejects' => ['us-gov.anthropic.claude-opus-4-8', false];
        yield 'inference profile ARN' => [
            'arn:aws:bedrock:eu-west-1:123456789012:inference-profile/eu.openai.gpt-5.5',
            false,
        ];
        yield 'foundation model ARN' => [
            'arn:aws:bedrock:eu-west-1::foundation-model/amazon.nova-lite-v1:0',
            true,
        ];
    }

    /**
     * complete() builds its payload separately from converse(), so it needs its
     * own check that the decision is applied at all — not the whole matrix again.
     */
    public function testCompletionsApplyTheSameDecision(): void
    {
        $rejecting = new TemperatureRecordingBedrock();
        $rejecting->complete(
            (new AIRequest('hello', 'AskMatomo'))->withModel('openai.gpt-5.5'),
            self::CONFIGURATION
        );
        $this->assertArrayNotHasKey('temperature', $rejecting->sentPayload['inferenceConfig']);

        $accepting = new TemperatureRecordingBedrock();
        $accepting->complete(
            (new AIRequest('hello', 'AskMatomo'))->withModel('amazon.nova-lite-v1:0'),
            self::CONFIGURATION
        );
        $this->assertSame(0.2, $accepting->sentPayload['inferenceConfig']['temperature']);
    }

    private function conversationRequest(): AIConversationRequest
    {
        return (new AIConversationRequest(
            [['role' => 'user', 'content' => [['type' => 'text', 'text' => 'hello']]]],
            'AskMatomo'
        ))->withMaxTokens(32);
    }
}

class TemperatureRecordingBedrock extends Bedrock
{
    /** @var array<string, mixed> */
    public $sentPayload = [];

    protected function sendConverseRequest(string $model, array $payload, int $timeoutSeconds, array $configuration): array
    {
        $this->sentPayload = $payload;

        return [
            'output' => ['message' => ['role' => 'assistant', 'content' => [['text' => 'ok']]]],
            'stopReason' => 'end_turn',
            'usage' => ['inputTokens' => 1, 'outputTokens' => 1],
        ];
    }
}
