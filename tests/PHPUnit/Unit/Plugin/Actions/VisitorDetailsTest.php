<?php

declare(strict_types=1);

namespace Piwik\Tests\Core\Plugin\Actions;

use Piwik\Plugins\Actions\VisitorDetails;

/**
 * @group Core
 */
class VisitorDetailsTest extends TestCase
{
    /**
     * @dataProvider provideActionsDataProvider
     */
    public function testProvideActionsForVisitIds(
        array $visitIds,
        array $chunkResults,
        int $expectedCount
    ): void {
        $sut = $this->getMockBuilder(VisitorDetails::class)
            ->onlyMethods(['queryActionsForVisits'])
            ->getMock();

        $sut->expects($this->exactly(count($chunkResults)))
            ->method('queryActionsForVisits')
            ->willReturnOnConsecutiveCalls(...$chunkResults);

        $actions = [];
        $sut->provideActionsForVisitIds($actions, $visitIds);

        $this->assertCount($expectedCount, $actions);
    }

    public static function provideActionsDataProvider(): array
    {
        return [
            'one chunk' => [
                'visitIds'      => range(1, 42),
                'chunkResults'  => [
                    [['idvisit' => 1], ['idvisit' => 2]],
                ],
                'expectedCount' => 2,
            ],
            'two chunks' => [
                'visitIds'      => range(1, 250),
                'chunkResults'  => [
                    [['idvisit' => 1], ['idvisit' => 2]],
                    [['idvisit' => 3], ['idvisit' => 4]],
                ],
                'expectedCount' => 4,
            ],
        ];
    }
}