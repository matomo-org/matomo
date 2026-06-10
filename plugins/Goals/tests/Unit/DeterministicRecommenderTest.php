<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\tests\Unit;

use Piwik\Plugins\Goals\Recommendations\DeterministicRecommender;
use PHPUnit\Framework\TestCase;

/**
 * @group Goals
 * @group GoalRecommendations
 */
class DeterministicRecommenderTest extends TestCase
{
    /**
     * @var DeterministicRecommender
     */
    private $recommender;

    public function setUp(): void
    {
        parent::setUp();
        $this->recommender = new DeterministicRecommender();
    }

    public function testRecommendMapsHighIntentLinksToUrlGoalsRankedAndCapped(): void
    {
        $analysis = [
            'url' => 'https://example.com',
            'technologies' => [],
            'links' => [
                ['linkText' => 'Contact us', 'linkTarget' => '/contact'],
                ['linkText' => 'Pricing', 'linkTarget' => '/pricing'],
                ['linkText' => 'Sign up', 'linkTarget' => '/signup'],
                ['linkText' => 'Cart', 'linkTarget' => '/cart'],
                ['linkText' => 'Newsletter', 'linkTarget' => '/newsletter'],
                ['linkText' => 'Book a demo', 'linkTarget' => '/demo'],
                ['linkText' => 'Brochure', 'linkTarget' => '/downloads/guide.pdf'],
            ],
        ];

        $goals = $this->recommender->recommend($analysis);

        // Capped at 5 and ordered by rule priority.
        $this->assertCount(5, $goals);
        $this->assertSame(
            ['/cart', '/signup', '/contact', '/demo', '/pricing'],
            array_column($goals, 'pattern')
        );

        foreach ($goals as $goal) {
            $this->assertSame('rule', $goal['source']);
            $this->assertSame('url', $goal['matchAttribute']);
            $this->assertSame('contains', $goal['patternType']);
            $this->assertNotSame('', $goal['name']);
            $this->assertArrayHasKey('reason', $goal);
        }
    }

    public function testRecommendAddsPurchaseGoalWhenShopifyDetected(): void
    {
        $analysis = [
            'url' => 'https://shop.example.com',
            'technologies' => ['Shopify'],
            'links' => [
                ['linkText' => 'Cart', 'linkTarget' => '/cart'],
            ],
        ];

        $patterns = array_column($this->recommender->recommend($analysis), 'pattern');

        $this->assertContains('/cart', $patterns);
        $this->assertContains('/thank_you', $patterns);
    }

    public function testRecommendReturnsNothingWithoutHighIntentLinks(): void
    {
        $analysis = [
            'url' => 'https://example.com',
            'technologies' => [],
            'links' => [
                ['linkText' => 'Home', 'linkTarget' => '/home'],
                ['linkText' => 'Blog', 'linkTarget' => '/blog'],
            ],
        ];

        $this->assertSame([], $this->recommender->recommend($analysis));
    }

    public function testRecommendCreatesDirectGoalsFromFormDownloadContactAndExternalSignals(): void
    {
        $analysis = [
            'url' => 'https://example.com',
            'technologies' => [],
            'links' => [],
            'forms' => [
                [
                    'action' => '/contact',
                    'fields' => ['email', 'message'],
                    'submitTexts' => ['Request demo'],
                    'contexts' => ['Book a demo with sales'],
                    'sourcePages' => ['https://example.com/contact', 'https://example.com/pricing'],
                    'count' => 2,
                ],
            ],
            'downloads' => [
                [
                    'href' => 'https://example.com/files/brochure.pdf',
                    'labels' => ['Product brochure'],
                    'sourcePages' => ['https://example.com/product'],
                    'examples' => ['https://example.com/files/brochure.pdf'],
                    'count' => 1,
                ],
            ],
            'contactLinks' => [
                [
                    'href' => 'mailto:sales@example.com',
                    'labels' => ['Email sales'],
                    'sourcePages' => ['https://example.com/contact'],
                    'count' => 1,
                ],
            ],
            'externalLinks' => [
                [
                    'host' => 'marketplace.example',
                    'href' => 'https://marketplace.example/listing',
                    'labels' => ['Marketplace listing'],
                    'sourcePages' => ['https://example.com/integrations'],
                    'examples' => ['https://marketplace.example/listing'],
                    'count' => 1,
                ],
            ],
        ];

        $goals = $this->recommender->recommend($analysis);

        $this->assertSame(
            ['event_name', 'file', 'external_website', 'external_website'],
            array_column($goals, 'matchAttribute')
        );
        $this->assertSame('Demo request', $goals[0]['pattern']);
        $this->assertSame('brochure.pdf', $goals[1]['pattern']);
        $this->assertSame('mailto:sales@example.com', $goals[2]['pattern']);
        $this->assertSame('marketplace.example', $goals[3]['pattern']);

        foreach ($goals as $goal) {
            $this->assertTrue($goal['allowMultipleConversionsPerVisit']);
            $this->assertNotSame('', $goal['implementationNote']);
        }
    }

    public function testRecommendFallsBackToStrongRankedUrlDestinations(): void
    {
        $analysis = [
            'url' => 'https://example.com',
            'technologies' => [],
            'links' => [
                [
                    'linkText' => 'Explore services',
                    'linkTarget' => '/services',
                    'score' => 14,
                    'pageCount' => 2,
                    'occurrenceCount' => 3,
                    'areas' => ['nav', 'main'],
                    'buttonLikeCount' => 1,
                ],
                [
                    'linkText' => 'About',
                    'linkTarget' => '/about',
                    'score' => 20,
                    'pageCount' => 4,
                    'occurrenceCount' => 5,
                    'areas' => ['nav'],
                    'buttonLikeCount' => 0,
                ],
            ],
        ];

        $goals = $this->recommender->recommend($analysis);

        $this->assertCount(1, $goals);
        $this->assertSame('/services', $goals[0]['pattern']);
        $this->assertSame('rule-generic', $goals[0]['source']);
    }
}
