<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\tests\Unit;

use Piwik\Plugins\BotTracking\BotDetector;
use PHPUnit\Framework\TestCase;

/**
 * @group BotTracking
 * @group BotDetectorTest
 * @group BotDetector
 * @group Plugins
 */
class BotDetectorTest extends TestCase
{
    /**
     * @dataProvider getBotUserAgents
     */
    public function testDetectReturnsCorrectBotInfo(string $userAgent, string $expectedBotName, string $expectedBotType): void
    {
        $botDetector = new BotDetector($userAgent);

        self::assertTrue($botDetector->isBot());

        $result = $botDetector->getDetectionResult();

        self::assertIsArray($result);
        self::assertArrayHasKey('bot_name', $result);
        self::assertArrayHasKey('bot_type', $result);
        self::assertEquals($expectedBotName, $result['bot_name']);
        self::assertEquals($expectedBotType, $result['bot_type']);
    }

    /**
     * @dataProvider getNonBotUserAgents
     */
    public function testDetectReturnsNullForNonBots(string $userAgent): void
    {
        $botDetector = new BotDetector($userAgent);

        self::assertFalse($botDetector->isBot());
        self::assertNull($botDetector->getDetectionResult());
    }

    /**
     * @return array<array{0: string, 1: string, 2: string}>
     */
    public function getBotUserAgents(): array
    {
        return [
            ['ChatGPT-User/1.0', 'ChatGPT-User', 'ai_assistant'],
            ['chatgpt-user/1.0', 'ChatGPT-User', 'ai_assistant'],
            ['CHATGPT-USER/1.0', 'ChatGPT-User', 'ai_assistant'],
            ['Mozilla/5.0 (compatible; ChatGPT-User/1.0; +https://openai.com)', 'ChatGPT-User', 'ai_assistant'],
            ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; MistralAI-User/1.0; +https://docs.mistral.ai/robots)', 'MistralAI-User', 'ai_assistant'],
            ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Gemini-Deep-Research; +https://gemini.google/overview/deep-research/) Chrome/135.0.0.0 Safari/537.36', 'Gemini-Deep-Research', 'ai_assistant'],
            ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Claude-User/1.0; +Claude-User@anthropic.com)', 'Claude-User', 'ai_assistant'],
            ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Perplexity-User/1.0; +https://perplexity.ai/perplexity-user)', 'Perplexity-User', 'ai_assistant'],
            ['Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36; Devin/1.0; +devin.ai', 'Devin', 'ai_assistant'],
            ['Google-NotebookLM', 'Google-NotebookLM', 'ai_assistant'],

        ];
    }

    /**
     * @return array<array{0: string}>
     */
    public function getNonBotUserAgents(): array
    {
        return [
            ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'],
            ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'],
            ['curl/7.68.0'],
            ['Googlebot/2.1'],
            [''],
        ];
    }
}
