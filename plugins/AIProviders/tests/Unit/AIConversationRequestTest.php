<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders\tests\Unit;

use Piwik\Plugins\AIProviders\AIConversationRequest;
use Piwik\Plugins\AIProviders\AIRequest;
use PHPUnit\Framework\TestCase;

/**
 * @group AIProviders
 */
class AIConversationRequestTest extends TestCase
{
    public function testProvidesRequiredValuesAndSensibleDefaults(): void
    {
        $messages = [
            ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Hello']]],
        ];

        $request = new AIConversationRequest($messages, 'AskMatomo');

        $this->assertSame($messages, $request->getMessages());
        $this->assertSame('AskMatomo', $request->getCallerPluginName());
        $this->assertNull($request->getSystemPrompt());
        $this->assertNull($request->getProviderId());
        $this->assertNull($request->getModel());
        $this->assertNull($request->getFeatureKey());
        $this->assertSame([], $request->getTools());
        $this->assertSame(AIConversationRequest::DEFAULT_MAX_TOKENS, $request->getMaxTokens());
        $this->assertSame(2048, $request->getMaxTokens());
        $this->assertSame(AIRequest::DEFAULT_TEMPERATURE, $request->getTemperature());
        $this->assertSame(0.2, $request->getTemperature());
        $this->assertNull($request->getCapabilityLevel());
        $this->assertSame(AIConversationRequest::DEFAULT_TIMEOUT_SECONDS, $request->getTimeoutSeconds());
        $this->assertSame(60, $request->getTimeoutSeconds());
    }

    public function testWithMethodsReturnImmutableCopies(): void
    {
        $request = new AIConversationRequest(
            [['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Hello']]]],
            'AskMatomo'
        );

        $tools = [
            [
                'name' => 'matomo_site_list',
                'description' => 'Lists sites',
                'inputSchema' => ['type' => 'object'],
            ],
        ];

        $modified = $request
            ->withSystemPrompt('System')
            ->withTools($tools)
            ->withProviderId('anthropic')
            ->withModel('claude-haiku-4-5')
            ->withFeatureKey('chat')
            ->withMaxTokens(4096)
            ->withTemperature(0.7)
            ->withCapabilityLevel('thinking')
            ->withTimeoutSeconds(120);

        // The original request is unchanged.
        $this->assertNull($request->getSystemPrompt());
        $this->assertSame([], $request->getTools());
        $this->assertNull($request->getProviderId());
        $this->assertNull($request->getModel());
        $this->assertNull($request->getFeatureKey());
        $this->assertSame(AIConversationRequest::DEFAULT_MAX_TOKENS, $request->getMaxTokens());
        $this->assertSame(AIRequest::DEFAULT_TEMPERATURE, $request->getTemperature());
        $this->assertNull($request->getCapabilityLevel());
        $this->assertSame(AIConversationRequest::DEFAULT_TIMEOUT_SECONDS, $request->getTimeoutSeconds());

        // The derived request carries the new values.
        $this->assertSame('System', $modified->getSystemPrompt());
        $this->assertSame($tools, $modified->getTools());
        $this->assertSame('anthropic', $modified->getProviderId());
        $this->assertSame('claude-haiku-4-5', $modified->getModel());
        $this->assertSame('chat', $modified->getFeatureKey());
        $this->assertSame(4096, $modified->getMaxTokens());
        $this->assertSame(0.7, $modified->getTemperature());
        $this->assertSame('thinking', $modified->getCapabilityLevel());
        $this->assertSame(120, $modified->getTimeoutSeconds());

        // The messages and caller plugin carry over to derived requests.
        $this->assertSame($request->getMessages(), $modified->getMessages());
        $this->assertSame('AskMatomo', $modified->getCallerPluginName());
    }

    public function testEachWithMethodReturnsANewInstance(): void
    {
        $request = new AIConversationRequest([], 'AskMatomo');

        $this->assertNotSame($request, $request->withSystemPrompt('System'));
        $this->assertNotSame($request, $request->withTools([]));
        $this->assertNotSame($request, $request->withProviderId('anthropic'));
        $this->assertNotSame($request, $request->withModel('claude-haiku-4-5'));
        $this->assertNotSame($request, $request->withFeatureKey('chat'));
        $this->assertNotSame($request, $request->withMaxTokens(1));
        $this->assertNotSame($request, $request->withTemperature(0.5));
        $this->assertNotSame($request, $request->withCapabilityLevel('thinking'));
        $this->assertNotSame($request, $request->withTimeoutSeconds(10));
    }

    public function testWithTimeoutSecondsClampsToAtLeastOneSecond(): void
    {
        $request = new AIConversationRequest([], 'AskMatomo');

        $this->assertSame(1, $request->withTimeoutSeconds(0)->getTimeoutSeconds());
        $this->assertSame(1, $request->withTimeoutSeconds(-5)->getTimeoutSeconds());
        $this->assertSame(1, $request->withTimeoutSeconds(1)->getTimeoutSeconds());
        $this->assertSame(30, $request->withTimeoutSeconds(30)->getTimeoutSeconds());
    }
}
