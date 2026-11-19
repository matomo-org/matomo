/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export interface SortableGoal {
  idgoal: string|number;
  name?: string;
  starred?: string|number|boolean;
}

/**
 * Whether a goal as returned by `Goals.getGoals` is starred. The API returns a boolean, values that
 * went through other formats may be `1` or `'1'`.
 */
export function isGoalStarred(goal?: Pick<SortableGoal, 'starred'>|null): boolean {
  const starred = goal?.starred;
  return starred === true || starred === 1 || starred === '1' || starred === 'true';
}

function isSortable(goal: SortableGoal): boolean {
  const idGoal = Number(goal?.idgoal);
  return Number.isInteger(idGoal) && idGoal > 0;
}

function isDigit(code: number): boolean {
  return code >= 48 && code <= 57;
}

function lowerAscii(code: number): number {
  return code >= 65 && code <= 90 ? code + 32 : code;
}

function codePoints(text: string): number[] {
  return Array.from(text, (character) => character.codePointAt(0)!);
}

function digitRunLength(codes: number[], start: number): number {
  let end = start;
  while (end < codes.length && isDigit(codes[end])) {
    end += 1;
  }
  return end - start;
}

// the digits of a run without leading zeros, as a string so numbers of any length can be compared
function digitRunValue(codes: number[], start: number, length: number): string {
  return String.fromCodePoint(...codes.slice(start, start + length)).replace(/^0+/, '');
}

/**
 * Compares names like PHP's `strcasecmp()`, which Matomo used to sort goals by name, except that a
 * run of digits is compared by its value ("Goal 2" before "Goal 10"). Characters are compared by
 * their code point with A-Z matching a-z, so "<" comes before letters, "{" after them, and a name
 * starting with a non-ASCII letter such as "Écrire" after all names starting with an ASCII letter.
 * Same comparison as `GoalsSorter::compareNames()` in PHP.
 */
export function compareGoalNames(nameA: string, nameB: string): number {
  const a = codePoints(nameA);
  const b = codePoints(nameB);
  let i = 0;
  let j = 0;

  while (i < a.length && j < b.length) {
    if (isDigit(a[i]) && isDigit(b[j])) {
      const runA = digitRunLength(a, i);
      const runB = digitRunLength(b, j);
      const numberA = digitRunValue(a, i, runA);
      const numberB = digitRunValue(b, j, runB);

      // more digits is the bigger number, else compare digit by digit
      if (numberA.length !== numberB.length) {
        return numberA.length < numberB.length ? -1 : 1;
      }
      if (numberA !== numberB) {
        return numberA < numberB ? -1 : 1;
      }

      i += runA;
      j += runB;
    } else {
      const byCharacter = lowerAscii(a[i]) - lowerAscii(b[j]);
      if (byCharacter !== 0) {
        return byCharacter < 0 ? -1 : 1;
      }

      i += 1;
      j += 1;
    }
  }

  // a name that is the start of the other comes first
  return Math.sign((a.length - i) - (b.length - j));
}

function compareGoals(a: SortableGoal, b: SortableGoal): number {
  const byStarred = Number(isGoalStarred(b)) - Number(isGoalStarred(a));
  if (byStarred !== 0) {
    return byStarred;
  }

  const byName = compareGoalNames(`${a.name ?? ''}`, `${b.name ?? ''}`);
  if (byName !== 0) {
    return byName;
  }

  return Number(a.idgoal) - Number(b.idgoal);
}

/**
 * Returns the goals in the order they are displayed: starred goals first, then by name (see
 * `compareGoalNames()`). Goals with an equal name are ordered by their ID. Matches
 * `Piwik\Plugins\Goals\GoalsSorter::sortStarredFirst()`.
 *
 * Always sort with this before listing goals: `Goals.getGoals` is not ordered for display, and the
 * goals of one site are an object indexed by goal ID, which JavaScript iterates in numeric order
 * no matter what order the server sent.
 *
 * Entries without a numeric goal ID, such as the ecommerce order and abandoned cart pseudo-goals,
 * cannot be starred and keep their position.
 */
export default function sortGoalsStarredFirst<T extends SortableGoal>(
  goals?: T[]|Record<string, T>|null,
): T[] {
  const list = Object.values(goals || {});
  const sorted = list.filter(isSortable).sort(compareGoals);

  return list.map((goal) => (isSortable(goal) ? sorted.shift()! : goal));
}
