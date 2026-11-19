<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\tests\Unit;

use Piwik\Plugins\Goals\GoalsSorter;

/**
 * @group Goals
 * @group GoalsSorterTest
 * @group Plugins
 */
class GoalsSorterTest extends \PHPUnit\Framework\TestCase
{
    public function testStarredGoalsComeFirstAndBothGroupsAreSortedByName(): void
    {
        $sorted = GoalsSorter::sortStarredFirst([
            $this->goal(1, 'Newsletter'),
            $this->goal(2, 'Download', true),
            $this->goal(3, 'Contact'),
            $this->goal(4, 'Checkout', true),
        ]);

        self::assertSame(['Checkout', 'Download', 'Contact', 'Newsletter'], array_column($sorted, 'name'));
    }

    public function testGoalsAreSortedByNameWhenNoGoalIsStarred(): void
    {
        $sorted = GoalsSorter::sortStarredFirst([
            $this->goal(1, 'banana'),
            $this->goal(2, 'Cherry'),
            $this->goal(3, 'apple'),
        ]);

        self::assertSame(['apple', 'banana', 'Cherry'], array_column($sorted, 'name'));
    }

    public function testGoalsAreSortedByNameWhenAllGoalsAreStarred(): void
    {
        $sorted = GoalsSorter::sortStarredFirst([
            $this->goal(1, 'b', true),
            $this->goal(2, 'a', true),
        ]);

        self::assertSame(['a', 'b'], array_column($sorted, 'name'));
    }

    public function testNamesAreComparedIgnoringCaseAndWithNumbersByTheirValue(): void
    {
        $sorted = GoalsSorter::sortStarredFirst([
            $this->goal(1, 'Goal 10'),
            $this->goal(2, 'goal 2'),
            $this->goal(3, 'Zebra'),
            $this->goal(4, 'Goal 1'),
            $this->goal(5, 'apple'),
        ]);

        self::assertSame(['apple', 'Goal 1', 'goal 2', 'Goal 10', 'Zebra'], array_column($sorted, 'name'));
    }

    public function testAccentedNamesAreSortedNextToTheirBaseLetter(): void
    {
        if (!class_exists(\Collator::class)) {
            self::markTestSkipped('Without the intl extension names starting with a non-ASCII character are sorted last.');
        }

        $sorted = GoalsSorter::sortStarredFirst([
            $this->goal(1, 'Zebra'),
            $this->goal(2, 'Écrire'),
            $this->goal(3, 'Download'),
            $this->goal(4, 'Form'),
        ]);

        self::assertSame(['Download', 'Écrire', 'Form', 'Zebra'], array_column($sorted, 'name'));
    }

    public function testGoalsWithAnEqualNameAreOrderedById(): void
    {
        $sorted = GoalsSorter::sortStarredFirst([
            $this->goal(12, 'signup'),
            $this->goal(3, 'Signup'),
            $this->goal(7, 'SIGNUP'),
        ]);

        self::assertSame([3, 7, 12], array_column($sorted, 'idgoal'));
    }

    public function testAnEmptyListStaysEmpty(): void
    {
        self::assertSame([], GoalsSorter::sortStarredFirst([]));
    }

    public function testGoalsIndexedByGoalIdKeepTheirKeys(): void
    {
        // Goals.getGoals returns the goals of a single site indexed by goal ID
        $sorted = GoalsSorter::sortStarredFirst([
            1 => $this->goal(1, 'Zebra'),
            2 => $this->goal(2, 'Apple'),
            5 => $this->goal(5, 'Mango', true),
        ]);

        self::assertSame([5, 2, 1], array_keys($sorted));
        self::assertSame(['Mango', 'Apple', 'Zebra'], array_column($sorted, 'name'));
    }

    public function testAListStaysAList(): void
    {
        // Goals.getGoals returns the goals of several sites as a list
        $sorted = GoalsSorter::sortStarredFirst([
            $this->goal(1, 'Zebra'),
            $this->goal(2, 'Apple'),
        ]);

        self::assertSame([0, 1], array_keys($sorted));
        self::assertSame(['Apple', 'Zebra'], array_column($sorted, 'name'));
    }

    public function testEcommercePseudoGoalsAreNotMoved(): void
    {
        $sorted = GoalsSorter::sortStarredFirst([
            'ecommerceOrder' => ['idgoal' => 'ecommerceOrder', 'name' => 'Ecommerce order'],
            2 => $this->goal(2, 'Zebra'),
            'ecommerceAbandonedCart' => ['idgoal' => 'ecommerceAbandonedCart', 'name' => 'Abandoned cart'],
            1 => $this->goal(1, 'Apple', true),
            3 => $this->goal(3, 'Banana'),
        ]);

        self::assertSame(['ecommerceOrder', 1, 'ecommerceAbandonedCart', 3, 2], array_keys($sorted));
        self::assertSame(
            ['Ecommerce order', 'Apple', 'Abandoned cart', 'Banana', 'Zebra'],
            array_column($sorted, 'name')
        );
    }

    public function testEcommercePseudoGoalsCannotBeStarred(): void
    {
        // a pseudo-goal flagged as starred still does not take part in the sorting
        $sorted = GoalsSorter::sortStarredFirst([
            $this->goal(2, 'Zebra'),
            $this->goal(1, 'Apple'),
            ['idgoal' => 'ecommerceOrder', 'name' => 'Ecommerce order', 'starred' => true],
        ]);

        self::assertSame(['Apple', 'Zebra', 'Ecommerce order'], array_column($sorted, 'name'));
    }

    /**
     * @dataProvider getStarredValues
     * @param mixed $goal
     */
    public function testIsStarred($goal, bool $expected): void
    {
        self::assertSame($expected, GoalsSorter::isStarred($goal));
    }

    /**
     * @return iterable<string, array{mixed, bool}>
     */
    public function getStarredValues(): iterable
    {
        yield 'boolean true, as returned by the API' => [['starred' => true], true];
        yield 'boolean false' => [['starred' => false], false];
        yield 'stored value 1' => [['starred' => '1'], true];
        yield 'stored value 0' => [['starred' => '0'], false];
        yield 'goal without the field' => [['idgoal' => 1], false];
        yield 'no goal' => [null, false];
    }

    /**
     * @return array{idgoal: int, name: string, starred: bool}
     */
    private function goal(int $idGoal, string $name, bool $starred = false): array
    {
        return ['idgoal' => $idGoal, 'name' => $name, 'starred' => $starred];
    }
}
