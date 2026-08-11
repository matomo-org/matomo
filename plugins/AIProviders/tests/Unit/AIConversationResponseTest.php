<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders\tests\Unit;

use Piwik\Plugins\AIProviders\AIConversationResponse;
use PHPUnit\Framework\TestCase;

/**
 * @group AIProviders
 */
class AIConversationResponseTest extends TestCase
{
    public function testProvidesConstructorValuesThroughGetters(): void
    {
        $content = [
            ['type' => 'text', 'text' => 'Hello!'],
            ['type' => 'tool_use', 'id' => 'tu_1', 'name' => 'matomo_site_list', 'input' => []],
        ];

        $response = new AIConversationResponse(
            'anthropic',
            'Anthropic',
            'claude-haiku-4-5',
            $content,
            AIConversationResponse::STOP_TOOL_USE,
            12,
            7,
            450
        );

        $this->assertSame('anthropic', $response->getProviderId());
        $this->assertSame('Anthropic', $response->getProviderName());
        $this->assertSame('claude-haiku-4-5', $response->getModel());
        $this->assertSame($content, $response->getContent());
        $this->assertSame(AIConversationResponse::STOP_TOOL_USE, $response->getStopReason());
        $this->assertSame(12, $response->getInputTokens());
        $this->assertSame(7, $response->getOutputTokens());
        $this->assertSame(450, $response->getExecutionTimeMs());
    }

    public function testTokensAndExecutionTimeDefaultToNull(): void
    {
        $response = new AIConversationResponse(
            'anthropic',
            'Anthropic',
            'claude-haiku-4-5',
            [],
            AIConversationResponse::STOP_END_TURN
        );

        $this->assertNull($response->getInputTokens());
        $this->assertNull($response->getOutputTokens());
        $this->assertNull($response->getExecutionTimeMs());
    }

    public function testGetTextConcatenatesTextBlocksAndIgnoresOtherBlocks(): void
    {
        $response = new AIConversationResponse(
            'anthropic',
            'Anthropic',
            'claude-haiku-4-5',
            [
                ['type' => 'text', 'text' => 'First paragraph.'],
                ['type' => 'tool_use', 'id' => 'tu_1', 'name' => 'matomo_site_list', 'input' => []],
                ['type' => 'text', 'text' => 12345],
                ['type' => 'text'],
                ['type' => 'text', 'text' => 'Second paragraph.'],
            ],
            AIConversationResponse::STOP_TOOL_USE
        );

        // Text blocks are joined with a newline; tool_use blocks and text
        // blocks without a string text value are ignored.
        $this->assertSame("First paragraph.\nSecond paragraph.", $response->getText());
    }

    public function testGetTextReturnsEmptyStringForToolOnlyTurn(): void
    {
        $response = new AIConversationResponse(
            'anthropic',
            'Anthropic',
            'claude-haiku-4-5',
            [['type' => 'tool_use', 'id' => 'tu_1', 'name' => 'matomo_site_list', 'input' => []]],
            AIConversationResponse::STOP_TOOL_USE
        );

        $this->assertSame('', $response->getText());
    }
}
