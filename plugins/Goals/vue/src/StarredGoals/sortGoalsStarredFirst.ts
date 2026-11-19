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

function compareGoals(a: SortableGoal, b: SortableGoal): number {
  const byStarred = Number(isGoalStarred(b)) - Number(isGoalStarred(a));
  if (byStarred !== 0) {
    return byStarred;
  }

  const byName = `${a.name ?? ''}`.localeCompare(
    `${b.name ?? ''}`,
    undefined,
    { sensitivity: 'base', numeric: true },
  );
  if (byName !== 0) {
    return byName;
  }

  return Number(a.idgoal) - Number(b.idgoal);
}

/**
 * Returns the goals in the order they are displayed: starred goals first, then by name ignoring
 * case and comparing numbers by their value ("Goal 2" before "Goal 10"). Goals with an equal name
 * are ordered by their ID. Matches `Piwik\Plugins\Goals\GoalsSorter::sortStarredFirst()`.
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
