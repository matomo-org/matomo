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
use Piwik\Plugins\AIProviders\Provider\Anthropic;
use Piwik\Plugins\AIProviders\Provider\Bedrock;

/**
 * Covers which models get a `temperature` in the request payload.
 *
 * Both providers allowlist: a model has to be recognised as accepting the
 * field before it is sent, because a model that rejects it fails the whole
 * request with a 400 while a model that would have accepted it merely falls
 * back to its own default. The unlisted-model cases below are therefore the
 * point of the design, not an edge case.
 *
 * @group AIProviders
 * @group Plugins
 */
class TemperatureSupportTest extends TestCase
{
    private const CONFIGURATION = ['apiKey' => 'key', 'endpointUrl' => '', 'model' => ''];

    /**
     * @dataProvider bedrockModels
     */
    public function testBedrockSendsTemperatureOnlyForModelsThatAcceptIt(string $model, bool $expected): void
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
     * @dataProvider bedrockModels
     */
    public function testBedrockCompletionMatchesConversationBehaviour(string $model, bool $expected): void
    {
        $bedrock = new TemperatureRecordingBedrock();
        $bedrock->complete((new AIRequest('hello', 'AskMatomo'))->withModel($model), self::CONFIGURATION);

        $this->assertSame(
            $expected,
            array_key_exists('temperature', $bedrock->sentPayload['inferenceConfig']),
            $model
        );
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public function bedrockModels(): iterable
    {
        // Accepted: the default model and one model per allowlisted vendor.
        yield 'gpt-oss default' => ['openai.gpt-oss-120b-1:0', true];
        yield 'gpt-oss safeguard' => ['openai.gpt-oss-safeguard-20b', true];
        yield 'nova gen1' => ['amazon.nova-lite-v1:0', true];
        yield 'nova 2' => ['amazon.nova-2-lite-v1:0', true];
        yield 'llama' => ['meta.llama3-3-70b-instruct-v1:0', true];
        yield 'mistral' => ['mistral.mistral-large-2407-v1:0', true];
        yield 'qwen' => ['qwen.qwen3-32b-v1:0', true];
        yield 'deepseek' => ['deepseek.v3-v1:0', true];
        yield 'gemma' => ['google.gemma-3-12b-it-v1:0', true];
        yield 'grok 4.3' => ['xai.grok-4.3', true];

        // Accepted: Claude up to and including Opus 4.6.
        yield 'claude 3 haiku' => ['anthropic.claude-3-haiku-20240307-v1:0', true];
        yield 'claude sonnet 4.6' => ['anthropic.claude-sonnet-4-6', true];
        yield 'claude opus 4.6' => ['anthropic.claude-opus-4-6-v1', true];
        yield 'claude haiku 4.5' => ['anthropic.claude-haiku-4-5-20251001-v1:0', true];

        // Rejected: OpenAI frontier.
        yield 'gpt-5.5' => ['openai.gpt-5.5', false];
        yield 'gpt-5.6 luna' => ['openai.gpt-5.6-luna', false];
        yield 'gpt-6 astra' => ['openai.gpt-6-astra', false];

        // Rejected: Claude released after Opus 4.6.
        yield 'claude opus 4.7' => ['anthropic.claude-opus-4-7', false];
        yield 'claude opus 4.8' => ['anthropic.claude-opus-4-8', false];
        yield 'claude opus 5' => ['anthropic.claude-opus-5', false];
        yield 'claude sonnet 5' => ['anthropic.claude-sonnet-5', false];
        yield 'claude fable 5.1' => ['anthropic.claude-fable-5-1', false];

        // Disputed upstream, so deliberately not allowlisted.
        yield 'gpt-5.4' => ['openai.gpt-5.4', false];
        yield 'grok 4.6' => ['xai.grok-4.6', false];

        // Unlisted entirely: models absent from the catalogue snapshot and
        // anything AWS adds later must degrade rather than fail.
        yield 'gpt-5.6 cyber' => ['openai.gpt-5.6-cyber', false];
        yield 'claude mythos preview' => ['anthropic.claude-mythos-preview', false];
        yield 'unknown vendor' => ['acme.some-future-model-v9:0', false];
        yield 'empty model falls back to default' => ['', true];
    }

    /**
     * Cross-region inference profiles and ARNs prefix the vendor-qualified ID,
     * so the decision has to survive the prefix in both directions.
     *
     * @dataProvider prefixedModels
     */
    public function testBedrockDecisionSurvivesRegionalPrefixes(string $model, bool $expected): void
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
        foreach (['us', 'eu', 'apac', 'au', 'jp', 'in', 'ca', 'global', 'us-gov'] as $prefix) {
            yield $prefix . ' accepts' => [$prefix . '.amazon.nova-lite-v1:0', true];
            yield $prefix . ' rejects' => [$prefix . '.openai.gpt-5.6-luna', false];
            yield $prefix . ' claude accepts' => [$prefix . '.anthropic.claude-opus-4-6-v1', true];
            yield $prefix . ' claude rejects' => [$prefix . '.anthropic.claude-opus-4-8', false];
        }

        yield 'inference profile ARN' => [
            'arn:aws:bedrock:eu-west-1:123456789012:inference-profile/eu.openai.gpt-5.6-luna',
            false,
        ];
        yield 'foundation model ARN' => [
            'arn:aws:bedrock:eu-west-1::foundation-model/amazon.nova-lite-v1:0',
            true,
        ];
    }

    /**
     * @dataProvider anthropicModels
     */
    public function testAnthropicSendsTemperatureOnlyForModelsThatAcceptIt(string $model, bool $expected): void
    {
        $claude = new TemperatureRecordingAnthropic();
        $claude->converse($this->conversationRequest()->withModel($model), self::CONFIGURATION);

        $this->assertSame($expected, array_key_exists('temperature', $claude->sentPayload), $model);
    }

    /**
     * @dataProvider anthropicModels
     */
    public function testAnthropicCompletionMatchesConversationBehaviour(string $model, bool $expected): void
    {
        $claude = new TemperatureRecordingAnthropic();
        $claude->complete((new AIRequest('hello', 'AskMatomo'))->withModel($model), self::CONFIGURATION);

        $this->assertSame($expected, array_key_exists('temperature', $claude->sentPayload), $model);
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public function anthropicModels(): iterable
    {
        yield 'default model' => ['', true];
        yield 'haiku 4.5' => ['claude-haiku-4-5', true];
        yield 'sonnet 4.5' => ['claude-sonnet-4-5', true];
        yield 'sonnet 4.6' => ['claude-sonnet-4-6', true];
        yield 'opus 4.1' => ['claude-opus-4-1', true];
        yield 'opus 4.6' => ['claude-opus-4-6', true];
        yield '3.5 haiku' => ['claude-3-5-haiku-latest', true];

        yield 'opus 4.7' => ['claude-opus-4-7', false];
        yield 'opus 4.8' => ['claude-opus-4-8', false];
        yield 'opus 5' => ['claude-opus-5', false];
        yield 'sonnet 5' => ['claude-sonnet-5', false];
        yield 'fable 5.1' => ['claude-fable-5-1', false];
        yield 'mythos 5.1' => ['claude-mythos-5-1', false];
        yield 'unknown future model' => ['claude-something-7', false];
    }

    public function testConfigurationOverrideForcesTemperatureOff(): void
    {
        $bedrock = new TemperatureRecordingBedrock();
        $bedrock->converse(
            $this->conversationRequest()->withModel('amazon.nova-lite-v1:0'),
            self::CONFIGURATION + ['sendTemperature' => false]
        );

        $this->assertArrayNotHasKey('temperature', $bedrock->sentPayload['inferenceConfig']);
    }

    public function testConfigurationOverrideForcesTemperatureOn(): void
    {
        $bedrock = new TemperatureRecordingBedrock();
        $bedrock->converse(
            $this->conversationRequest()->withModel('openai.gpt-5.6-luna'),
            self::CONFIGURATION + ['sendTemperature' => true]
        );

        $this->assertSame(0.2, $bedrock->sentPayload['inferenceConfig']['temperature']);
    }

    public function testConfigurationOverrideAppliesToAnthropicToo(): void
    {
        $claude = new TemperatureRecordingAnthropic();
        $claude->converse(
            $this->conversationRequest()->withModel('claude-opus-4-8'),
            self::CONFIGURATION + ['sendTemperature' => true]
        );

        $this->assertSame(0.2, $claude->sentPayload['temperature']);
    }

    /**
     * An unset override must not be mistaken for "off" — absent means detect.
     */
    public function testAbsentOverrideFallsBackToDetection(): void
    {
        $bedrock = new TemperatureRecordingBedrock();
        $bedrock->converse(
            $this->conversationRequest()->withModel('amazon.nova-lite-v1:0'),
            self::CONFIGURATION + ['sendTemperature' => null]
        );

        $this->assertSame(0.2, $bedrock->sentPayload['inferenceConfig']['temperature']);
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

class TemperatureRecordingAnthropic extends Anthropic
{
    /** @var array<string, mixed> */
    public $sentPayload = [];

    protected function sendJsonRequest(string $url, array $headers, array $payload, int $timeoutSeconds = 30): array
    {
        $this->sentPayload = $payload;

        return [
            'content' => [['type' => 'text', 'text' => 'ok']],
            'stop_reason' => 'end_turn',
            'usage' => ['input_tokens' => 1, 'output_tokens' => 1],
        ];
    }
}
