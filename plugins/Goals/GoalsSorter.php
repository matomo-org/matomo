<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals;

/**
 * Orders goals the way they are displayed: starred goals first, then by name.
 *
 * `Goals.getGoals` keeps its order for API consumers, so everything that lists goals to a user sorts them
 * with this class. The Goals Vue package has a matching `sortGoalsStarredFirst()` helper.
 */
class GoalsSorter
{
    /**
     * Sorts goals so starred goals come first. Within the starred and the not starred goals, goals are
     * ordered by name, ignoring case and comparing numbers by their value ("Goal 2" before "Goal 10").
     * Goals with an equal name are ordered by their ID.
     *
     * Entries without a numeric `idgoal`, such as the ecommerce order and abandoned cart pseudo-goals,
     * cannot be starred and keep their position.
     *
     * @template T of array<string, mixed>
     * @param array<int|string, T> $goals Goals as returned by `Goals.getGoals`.
     * @return array<int|string, T> The sorted goals. String keys and goal ID keys are kept, a list stays a list.
     */
    public static function sortStarredFirst(array $goals): array
    {
        $isList = array_is_list($goals);

        $sortable = array_filter($goals, [self::class, 'isSortable']);
        uasort($sortable, [self::class, 'compare']);

        // put the sorted goals into the slots the sortable goals had, so other entries do not move
        $sortedKeys = array_keys($sortable);
        $sorted = [];
        foreach ($goals as $key => $goal) {
            if (!self::isSortable($goal)) {
                $sorted[$key] = $goal;
                continue;
            }

            $nextKey = array_shift($sortedKeys);
            $sorted[$isList ? $key : $nextKey] = $sortable[$nextKey];
        }

        return $sorted;
    }

    /**
     * Whether the given goal, as returned by `Goals.getGoals`, is starred.
     *
     * @param mixed $goal
     */
    public static function isStarred($goal): bool
    {
        return is_array($goal) && !empty($goal['starred']);
    }

    /**
     * @param mixed $goal
     */
    private static function isSortable($goal): bool
    {
        return is_array($goal)
            && isset($goal['idgoal'])
            && is_numeric($goal['idgoal'])
            && (int) $goal['idgoal'] > 0;
    }

    /**
     * @param array<string, mixed> $a
     * @param array<string, mixed> $b
     */
    private static function compare(array $a, array $b): int
    {
        $byStarred = (int) self::isStarred($b) <=> (int) self::isStarred($a);
        if (0 !== $byStarred) {
            return $byStarred;
        }

        $byName = self::compareNames((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        if (0 !== $byName) {
            return $byName;
        }

        return (int) $a['idgoal'] <=> (int) $b['idgoal'];
    }

    private static function compareNames(string $a, string $b): int
    {
        $collator = self::getCollator();
        if (null !== $collator) {
            $result = $collator->compare($a, $b);
            if (false !== $result) {
                return $result;
            }
        }

        return strnatcasecmp($a, $b);
    }

    /**
     * The intl extension is optional. With it, names are compared like the Vue helper compares them
     * (`localeCompare` with `sensitivity: 'base'` and `numeric: true`), so accented names such as "Écrire"
     * are sorted next to "Ecrire". Without it, `strnatcasecmp` puts names starting with a non-ASCII
     * character last.
     */
    private static function getCollator(): ?\Collator
    {
        static $collator = false;

        if (false === $collator) {
            $collator = null;

            if (class_exists(\Collator::class)) {
                try {
                    $collator = new \Collator('root');
                    $collator->setStrength(\Collator::PRIMARY);
                    $collator->setAttribute(\Collator::NUMERIC_COLLATION, \Collator::ON);
                } catch (\Throwable $e) {
                    $collator = null;
                }
            }
        }

        return $collator;
    }
}
