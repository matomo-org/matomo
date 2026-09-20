<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders\tests\Unit;

use Piwik\Plugins\AIProviders\AIRequest;
use PHPUnit\Framework\TestCase;

/**
 * @group AIProviders
 */
class AIRequestTest extends TestCase
{
    public function testProvidesRequiredValuesAndSensibleDefaults(): void
    {
        $request = new AIRequest('Summarise this report.', 'Goals');

        $this->assertSame('Summarise this report.', $request->getUserPrompt());
        $this->assertSame('Goals', $request->getCallerPluginName());
        $this->assertNull($request->getSystemPrompt());
        $this->assertNull($request->getProviderId());
        $this->assertNull($request->getModel());
        $this->assertNull($request->getCapabilityLevel());
        $this->assertNull($request->getFeatureKey());
        $this->assertNull($request->getIdSite());
        $this->assertSame(AIRequest::DEFAULT_MAX_TOKENS, $request->getMaxTokens());
        $this->assertSame(AIRequest::DEFAULT_TEMPERATURE, $request->getTemperature());
        $this->assertSame(AIRequest::REASONING_NONE, $request->getReasoningLevel());
        $this->assertFalse($request->isWebSearchEnabled());
        $this->assertNull($request->getThinkingBudget());
    }

    public function testWithMethodsReturnImmutableCopies(): void
    {
        $request = new AIRequest('Prompt', 'Goals');

        $modified = $request
            ->withSystemPrompt('System')
            ->withProviderId('anthropic')
            ->withModel('claude-haiku-4-5')
            ->withCapabilityLevel('thinking')
            ->withFeatureKey('goal-recommendation')
            ->withIdSite(3)
            ->withMaxTokens(256)
            ->withTemperature(0.7)
            ->withReasoningLevel('low')
            ->withWebSearchEnabled(true)
            ->withThinkingBudget(128);

        // The original request is unchanged.
        $this->assertNull($request->getSystemPrompt());
        $this->assertNull($request->getProviderId());
        $this->assertSame(AIRequest::DEFAULT_MAX_TOKENS, $request->getMaxTokens());

        // The derived request carries the new values.
        $this->assertSame('System', $modified->getSystemPrompt());
        $this->assertSame('anthropic', $modified->getProviderId());
        $this->assertSame('claude-haiku-4-5', $modified->getModel());
        $this->assertSame('thinking', $modified->getCapabilityLevel());
        $this->assertSame('goal-recommendation', $modified->getFeatureKey());
        $this->assertSame(3, $modified->getIdSite());
        $this->assertSame(256, $modified->getMaxTokens());
        $this->assertSame(0.7, $modified->getTemperature());
        $this->assertSame('low', $modified->getReasoningLevel());
        $this->assertTrue($modified->isWebSearchEnabled());
        $this->assertSame(128, $modified->getThinkingBudget());
    }
}
