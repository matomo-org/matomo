/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import sortGoalsStarredFirst, { compareGoalNames, isGoalStarred } from './sortGoalsStarredFirst';

function names(goals: { name?: string }[]) {
  return goals.map((goal) => goal.name);
}

describe('Goals/sortGoalsStarredFirst', () => {
  it('puts starred goals first and sorts both groups by name', () => {
    const sorted = sortGoalsStarredFirst([
      { idgoal: 1, name: 'Newsletter', starred: false },
      { idgoal: 2, name: 'Download', starred: true },
      { idgoal: 3, name: 'Contact', starred: false },
      { idgoal: 4, name: 'Checkout', starred: true },
    ]);

    expect(names(sorted)).toEqual(['Checkout', 'Download', 'Contact', 'Newsletter']);
  });

  it('sorts by name only when no goal is starred', () => {
    const sorted = sortGoalsStarredFirst([
      { idgoal: 1, name: 'banana' },
      { idgoal: 2, name: 'Cherry' },
      { idgoal: 3, name: 'apple' },
    ]);

    expect(names(sorted)).toEqual(['apple', 'banana', 'Cherry']);
  });

  it('sorts by name only when all goals are starred', () => {
    const sorted = sortGoalsStarredFirst([
      { idgoal: 1, name: 'b', starred: true },
      { idgoal: 2, name: 'a', starred: true },
    ]);

    expect(names(sorted)).toEqual(['a', 'b']);
  });

  it('compares numbers in names by their value and ignores case', () => {
    const sorted = sortGoalsStarredFirst([
      { idgoal: 1, name: 'Goal 10' },
      { idgoal: 2, name: 'goal 2' },
      { idgoal: 3, name: 'Zebra' },
      { idgoal: 4, name: 'apple' },
      { idgoal: 5, name: 'Goal 1' },
    ]);

    expect(names(sorted)).toEqual(['apple', 'Goal 1', 'goal 2', 'Goal 10', 'Zebra']);
  });

  // same cases and expected order as GoalsSorterTest in PHP
  it('sorts names without digits like strcasecmp in PHP, as Matomo did before', () => {
    const sorted = sortGoalsStarredFirst([
      '{{_Vue.h.constructor`_x(4)`()}}', 'name event', '<script>_x(1)</script>', '\u00c9crire',
      'click event', '_underscore', 'Zebra', 'category event', '#hash', 'apple pay',
    ].map((name, index) => ({ idgoal: index + 1, name })));

    expect(names(sorted)).toEqual([
      '#hash', '<script>_x(1)</script>', '_underscore', 'apple pay', 'category event', 'click event',
      'name event', 'Zebra', '{{_Vue.h.constructor`_x(4)`()}}', '\u00c9crire',
    ]);
  });

  it('compares numbers by their value at any length', () => {
    const sorted = sortGoalsStarredFirst([
      'Goal 100000000000000000000', 'Goal A', 'Goal 99999999999999999999', 'Goal 007', 'Goal',
      'Goal 8', 'Goal 7b',
    ].map((name, index) => ({ idgoal: index + 1, name })));

    expect(names(sorted)).toEqual([
      'Goal', 'Goal 007', 'Goal 7b', 'Goal 8', 'Goal 99999999999999999999',
      'Goal 100000000000000000000', 'Goal A',
    ]);
  });

  it('compares characters outside the basic plane by their code point, like the PHP sorter', () => {
    // UTF-16 would put U+1F600 (a surrogate pair) before U+FF21, PHP compares UTF-8 bytes
    expect(compareGoalNames('\uff21', '\u{1f600}')).toBe(-1);
    expect(compareGoalNames('\u{1f600}', '\uff21')).toBe(1);
  });

  it('orders goals with an equal name by their ID', () => {
    const sorted = sortGoalsStarredFirst([
      { idgoal: '12', name: 'signup' },
      { idgoal: '3', name: 'Signup' },
      { idgoal: '7', name: 'SIGNUP' },
    ]);

    expect(sorted.map((goal) => goal.idgoal)).toEqual(['3', '7', '12']);
  });

  it('sorts an object indexed by goal ID, as Goals.getGoals returns for one site', () => {
    const sorted = sortGoalsStarredFirst({
      1: { idgoal: 1, name: 'Zebra', starred: false },
      2: { idgoal: 2, name: 'Apple', starred: false },
      3: { idgoal: 3, name: 'Mango', starred: true },
    });

    expect(names(sorted)).toEqual(['Mango', 'Apple', 'Zebra']);
  });

  it('keeps ecommerce pseudo-goals where they are', () => {
    const sorted = sortGoalsStarredFirst([
      { idgoal: 'ecommerceOrder', name: 'Ecommerce order' },
      { idgoal: 2, name: 'Zebra' },
      { idgoal: 'ecommerceAbandonedCart', name: 'Abandoned cart' },
      { idgoal: 1, name: 'Apple', starred: true },
      { idgoal: 3, name: 'Banana' },
    ]);

    expect(names(sorted)).toEqual(['Ecommerce order', 'Apple', 'Abandoned cart', 'Banana', 'Zebra']);
  });

  it('returns an empty list when there are no goals and does not change the input', () => {
    expect(sortGoalsStarredFirst([])).toEqual([]);
    expect(sortGoalsStarredFirst(null)).toEqual([]);
    expect(sortGoalsStarredFirst({})).toEqual([]);

    const goals = [{ idgoal: 1, name: 'b' }, { idgoal: 2, name: 'a' }];
    sortGoalsStarredFirst(goals);
    expect(names(goals)).toEqual(['b', 'a']);
  });

  it('recognises the starred values the API and other formats use', () => {
    expect(isGoalStarred({ starred: true })).toBe(true);
    expect(isGoalStarred({ starred: 1 })).toBe(true);
    expect(isGoalStarred({ starred: '1' })).toBe(true);
    expect(isGoalStarred({ starred: false })).toBe(false);
    expect(isGoalStarred({ starred: 0 })).toBe(false);
    expect(isGoalStarred({ starred: '0' })).toBe(false);
    expect(isGoalStarred({})).toBe(false);
    expect(isGoalStarred(null)).toBe(false);
  });
});
