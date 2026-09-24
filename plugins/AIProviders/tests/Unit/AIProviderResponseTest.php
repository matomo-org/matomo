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
use Piwik\Plugins\AIProviders\AIProviderResponse;
use Piwik\Plugins\AIProviders\AIRequest;
use Piwik\Plugins\AIProviders\WebSearchUsage;

/**
 * @group AIProviders
 * @group Plugins
 */
class AIProviderResponseTest extends TestCase
{
    /**
     * Pins the positional shape, argument for argument, so a reordering binds the
     * execution time and stop reason to the wrong parameters loudly rather than
     * silently.
     */
    public function testThePositionalConstructorBindsCorrectly(): void
    {
        $response = new AIProviderResponse(
            'openai',
            'OpenAI',
            'gpt-5.4-mini',
            'Blue light scatters most.',
            12,
            7,
            AIRequest::REASONING_NONE,
            1234,
            'stop'
        );

        $this->assertSame(1234, $response->getExecutionTimeMs());
        $this->assertSame('stop', $response->getStopReason());
        // Without a WebSearchUsage there is no search to report, and no detail.
        $this->assertFalse($response->wasWebSearchUsed());
        $this->assertNull($response->getWebSearchRequestCount());
        $this->assertSame([], $response->getWebSearchCitations());
        $this->assertSame([], $response->getWebSearchQueries());
    }

    public function testAResponseWithoutAWebSearchUsageReportsNoSearch(): void
    {
        $this->assertTrue($this->groundedResponse()->wasWebSearchUsed());
        $this->assertFalse((new AIProviderResponse('openai', 'OpenAI', 'm', 'hi'))->wasWebSearchUsed());
    }

    /**
     * Pins the array the README documents.
     */
    public function testToArrayExposesTheDocumentedShape(): void
    {
        $this->assertSame([
            'providerId' => 'openai',
            'providerName' => 'OpenAI',
            'model' => 'gpt-5.4-mini',
            'text' => 'Matomo is open source.',
            'inputTokens' => 900,
            'outputTokens' => 20,
            'reasoningLevel' => AIRequest::REASONING_NONE,
            'webSearchUsed' => true,
            'webSearchRequestCount' => 2,
            'webSearchQueries' => ['best analytics'],
            'webSearchCitations' => [
                ['url' => 'https://matomo.org/a', 'title' => 'Matomo', 'domain' => 'matomo.org'],
            ],
            'executionTimeMs' => 5,
            'stopReason' => 'stop',
        ], $this->groundedResponse()->toArray());
    }

    private function groundedResponse(): AIProviderResponse
    {
        return new AIProviderResponse(
            'openai',
            'OpenAI',
            'gpt-5.4-mini',
            'Matomo is open source.',
            900,
            20,
            AIRequest::REASONING_NONE,
            5,
            'stop',
            WebSearchUsage::fromProviderData(
                [['url' => 'https://matomo.org/a', 'title' => 'Matomo']],
                2,
                ['best analytics']
            )
        );
    }
}
