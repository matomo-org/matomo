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
        $this->assertSame(AIRequest::DEFAULT_MAX_WEB_SEARCHES, $request->getMaxWebSearches());
        $this->assertNull($request->getTimeoutSeconds());
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
            ->withUsageReference('query-42')
            ->withMeta(['source' => 'scheduled'])
            ->withMaxTokens(256)
            ->withTemperature(0.7)
            ->withReasoningLevel('low')
            ->withWebSearchEnabled(true)
            ->withMaxWebSearches(5)
            ->withTimeoutSeconds(90)
            ->withThinkingBudget(128);

        // The original request is unchanged.
        $this->assertNull($request->getSystemPrompt());
        $this->assertNull($request->getProviderId());
        $this->assertSame(AIRequest::DEFAULT_MAX_TOKENS, $request->getMaxTokens());
        $this->assertNull($request->getUsageReference());
        $this->assertSame([], $request->getMeta());

        // The derived request carries the new values.
        $this->assertSame('System', $modified->getSystemPrompt());
        $this->assertSame('anthropic', $modified->getProviderId());
        $this->assertSame('claude-haiku-4-5', $modified->getModel());
        $this->assertSame('thinking', $modified->getCapabilityLevel());
        $this->assertSame('goal-recommendation', $modified->getFeatureKey());
        $this->assertSame(3, $modified->getIdSite());
        $this->assertSame('query-42', $modified->getUsageReference());
        $this->assertSame(['source' => 'scheduled'], $modified->getMeta());
        $this->assertSame(256, $modified->getMaxTokens());
        $this->assertSame(0.7, $modified->getTemperature());
        $this->assertSame('low', $modified->getReasoningLevel());
        $this->assertTrue($modified->isWebSearchEnabled());
        $this->assertSame(5, $modified->getMaxWebSearches());
        $this->assertSame(90, $modified->getTimeoutSeconds());
        $this->assertSame(128, $modified->getThinkingBudget());
    }

    public function testTimeoutSecondsIsClampedToAtLeastOneSecondAndNullRestoresTheDefault(): void
    {
        $request = new AIRequest('Prompt', 'Goals');

        $this->assertSame(1, $request->withTimeoutSeconds(0)->getTimeoutSeconds());
        $this->assertSame(1, $request->withTimeoutSeconds(-5)->getTimeoutSeconds());
        $this->assertSame(90, $request->withTimeoutSeconds(90)->getTimeoutSeconds());
        // null is not a value but the absence of one: the provider picks its own
        // default, which differs for a grounded request.
        $this->assertNull($request->withTimeoutSeconds(45)->withTimeoutSeconds(null)->getTimeoutSeconds());
    }

    public function testMaxWebSearchesIsClampedToAtLeastOneAndNullRestoresTheDefault(): void
    {
        $request = new AIRequest('Prompt', 'Goals');

        $this->assertSame(1, $request->withMaxWebSearches(0)->getMaxWebSearches());
        $this->assertSame(1, $request->withMaxWebSearches(-3)->getMaxWebSearches());
        $this->assertSame(
            AIRequest::DEFAULT_MAX_WEB_SEARCHES,
            $request->withMaxWebSearches(5)->withMaxWebSearches(null)->getMaxWebSearches()
        );
    }
}
