<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// No declare(strict_types=1) on purpose: the compatibility test below mimics a caller
// written against Matomo 5.13.0, and coercion of its positional arguments is the
// behaviour under test.

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
     * The positional shape Matomo 5.13.0 shipped, argument for argument. The
     * $webSearchEnabled slot is kept and ignored precisely so this keeps binding
     * the execution time and stop reason to the right parameters; without it
     * `true` coerced into $executionTimeMs and reported a 1ms provider round trip.
     */
    public function testTheMatomo5130PositionalConstructorStillBindsCorrectly(): void
    {
        $response = new AIProviderResponse(
            'openai',
            'OpenAI',
            'gpt-5.4-mini',
            'Blue light scatters most.',
            12,
            7,
            AIRequest::REASONING_NONE,
            true,
            1234,
            'stop'
        );

        $this->assertSame(1234, $response->getExecutionTimeMs());
        $this->assertSame('stop', $response->getStopReason());
        // The deprecated flag is ignored: only a WebSearchUsage reports a search.
        $this->assertFalse($response->wasWebSearchUsed());
        $this->assertNull($response->getWebSearchRequestCount());
        $this->assertSame([], $response->getWebSearchCitations());
    }

    public function testTheDeprecatedReaderAgreesWithTheCurrentOne(): void
    {
        $grounded = $this->groundedResponse();

        $this->assertTrue($grounded->wasWebSearchUsed());
        $this->assertTrue($grounded->isWebSearchEnabled());

        $ungrounded = new AIProviderResponse('openai', 'OpenAI', 'm', 'hi');

        $this->assertFalse($ungrounded->wasWebSearchUsed());
        $this->assertFalse($ungrounded->isWebSearchEnabled());
    }

    /**
     * Pins the array the README documents, including the deprecated
     * `webSearchEnabled` key kept alongside `webSearchUsed`.
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
            'webSearchEnabled' => true,
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
            false,
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
