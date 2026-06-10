<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\AIProviders\AIProviderResponse;
use Piwik\Plugins\AIProviders\AIProviderService;
use Piwik\Plugins\AIProviders\AIRequest;
use Piwik\Plugins\Goals\Recommendations\AiRecommender;

/**
 * @group Goals
 * @group GoalRecommendations
 */
class AiRecommenderTest extends TestCase
{
    public function testRecommendUsesAiProvidersJsonRequestAndKeepsOnlySafeUrlGoals(): void
    {
        $service = $this->getMockBuilder(AIProviderService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['complete'])
            ->getMock();

        $service->expects($this->once())
            ->method('complete')
            ->with($this->callback(function (AIRequest $request): bool {
                return $request->getCallerPluginName() === 'Goals'
                    && $request->getFeatureKey() === 'goal-recommendation'
                    && $request->getIdSite() === 1
                    && $request->isJsonResponse()
                    && strpos($request->getUserPrompt(), '"signals"') !== false
                    && strpos($request->getUserPrompt(), '"baselineGoals"') !== false
                    && strpos($request->getUserPrompt(), '"existingGoals"') !== false
                    && strpos($request->getUserPrompt(), 'internal_destination') !== false
                    && strpos($request->getUserPrompt(), '"form"') !== false
                    && strpos($request->getUserPrompt(), '/contact') !== false;
            }))
            ->willReturn(new AIProviderResponse(
                'test',
                'Test provider',
                'test-model',
                json_encode([
                    'goals' => [
                        [
                            'id' => 'url-contact',
                            'name' => 'Contact us',
                            'matomoGoal' => ['matchAttribute' => 'url', 'pattern' => '/contact'],
                            'display' => ['whyItMatters' => 'Shows lead intent.'],
                        ],
                        [
                            'name' => 'External',
                            'matomoGoal' => ['matchAttribute' => 'url', 'pattern' => 'https://other.example/contact'],
                            'display' => ['whyItMatters' => 'Invalid.'],
                        ],
                        [
                            'name' => '<b>Pricing</b>',
                            'matomoGoal' => ['matchAttribute' => 'url', 'pattern' => 'https://example.com/pricing'],
                            'display' => ['whyItMatters' => '<i>Buying intent.</i>', 'category' => 'High-intent page'],
                        ],
                        [
                            'name' => 'Guide download',
                            'matomoGoal' => [
                                'matchAttribute' => 'file',
                                'pattern' => 'https://example.com/files/guide.pdf',
                                'allowMultipleConversionsPerVisit' => true,
                            ],
                            'display' => ['whyItMatters' => 'Shows evaluation intent.'],
                        ],
                    ],
                ])
            ));

        $goals = (new AiRecommender($service))->recommend([
            'url' => 'https://example.com',
            'pagesCrawled' => 3,
            'errors' => [],
            'technologies' => ['WordPress'],
            'links' => [
                [
                    'linkText' => 'Contact',
                    'linkTarget' => '/contact',
                    'score' => 12,
                    'pageCount' => 2,
                    'occurrenceCount' => 3,
                    'areas' => ['nav'],
                    'labelSamples' => ['Contact', 'Talk to sales'],
                    'exampleUrls' => ['https://example.com/contact'],
                    'buttonLikeCount' => 1,
                ],
            ],
            'forms' => [
                [
                    'action' => '/contact',
                    'fields' => ['email', 'message'],
                    'submitTexts' => ['Contact sales'],
                    'contexts' => ['Talk to sales'],
                    'sourcePages' => ['https://example.com/contact'],
                    'count' => 1,
                ],
            ],
            'downloads' => [],
            'contactLinks' => [],
            'externalLinks' => [],
        ], 1, [
            [
                'name' => 'Existing contact goal',
                'pattern' => '/contact',
                'matchAttribute' => 'url',
                'patternType' => 'contains',
            ],
        ], [
            [
                'id' => 'url-contact',
                'name' => 'Visited contact page',
                'matchAttribute' => 'url',
                'patternType' => 'contains',
                'pattern' => '/contact',
                'reason' => 'Visitors reaching the contact page indicate high intent.',
            ],
        ]);

        $this->assertCount(2, $goals);
        $this->assertSame(['Pricing', 'Guide download'], array_column($goals, 'name'));
        $this->assertSame(['url', 'file'], array_column($goals, 'matchAttribute'));
        $this->assertSame(['/pricing', 'guide.pdf'], array_column($goals, 'pattern'));
        $this->assertSame(['contains', 'contains'], array_column($goals, 'patternType'));
        $this->assertSame('Buying intent.', $goals[0]['reason']);
        $this->assertSame('ai', $goals[0]['source']);
        $this->assertTrue($goals[1]['allowMultipleConversionsPerVisit']);
    }
}
