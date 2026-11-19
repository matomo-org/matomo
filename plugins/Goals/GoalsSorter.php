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
     * ordered by name (see {@link compareNames()}). Goals with an equal name are ordered by their ID.
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

    /**
     * Compares names like `strcasecmp()`, which Matomo used to sort goals by name, except that a run of
     * digits is compared by its value ("Goal 2" before "Goal 10"):
     *
     * - characters are compared by their character code, with A-Z matching a-z, so "<" comes before
     *   letters and "{" after them, and a name starting with a non-ASCII letter such as "Écrire" comes
     *   after all names starting with an ASCII letter;
     * - names that are equal this way, such as "goal 01" and "Goal 1", compare as equal.
     *
     * The Vue helper `sortGoalsStarredFirst()` implements the same comparison. The UTF-8 bytes compared
     * here are in the same order as the code points compared there.
     */
    private static function compareNames(string $a, string $b): int
    {
        $lengthA = strlen($a);
        $lengthB = strlen($b);
        $i = 0;
        $j = 0;

        while ($i < $lengthA && $j < $lengthB) {
            if (self::isDigit($a[$i]) && self::isDigit($b[$j])) {
                $runA = strspn($a, '0123456789', $i);
                $runB = strspn($b, '0123456789', $j);
                $numberA = ltrim(substr($a, $i, $runA), '0');
                $numberB = ltrim(substr($b, $j, $runB), '0');

                // numbers of any length: more digits is the bigger number, else compare digit by digit
                $byNumber = strlen($numberA) <=> strlen($numberB);
                if (0 === $byNumber) {
                    $byNumber = strcmp($numberA, $numberB) <=> 0;
                }
                if (0 !== $byNumber) {
                    return $byNumber;
                }

                $i += $runA;
                $j += $runB;
                continue;
            }

            $byCharacter = self::lowerAscii(ord($a[$i])) <=> self::lowerAscii(ord($b[$j]));
            if (0 !== $byCharacter) {
                return $byCharacter;
            }

            $i++;
            $j++;
        }

        // a name that is the start of the other comes first
        return ($lengthA - $i) <=> ($lengthB - $j);
    }

    private static function isDigit(string $character): bool
    {
        $code = ord($character);
        return $code >= 48 && $code <= 57;
    }

    private static function lowerAscii(int $code): int
    {
        return $code >= 65 && $code <= 90 ? $code + 32 : $code;
    }
}
