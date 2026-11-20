/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { defineComponent, h } from 'vue';

vi.mock('CoreHome', () => ({
  Matomo: { on: vi.fn() },
  translate: (key: string) => key,
  StarToggle: defineComponent({
    props: ['starred', 'readonly', 'small', 'title'],
    render() {
      return h('span', { class: 'starToggle', title: this.title });
    },
  }),
}));

// eslint-disable-next-line import/first
import { addStarredGoalIndicator } from './StarredGoalIndicator';

function headerCell(name: string): HTMLElement {
  const th = document.createElement('th');
  th.setAttribute('data-starred-goal', '2');
  th.innerHTML = '<div class="thDIV"></div>';
  th.querySelector('.thDIV')!.textContent = name;
  return th;
}

describe('Goals/StarredGoalIndicator', () => {
  it('puts the star before the name and keeps it on one line with the first word', () => {
    const th = headerCell('{{_Vue.h.constructor`_x(0)`()}} conversions');

    addStarredGoalIndicator(th);

    const div = th.querySelector('.thDIV')!;
    const indicator = div.firstElementChild as HTMLElement;
    expect(indicator.className).toBe('starredGoalIndicator');
    expect(indicator.querySelector('.starredGoalIndicator__star .starToggle')!.getAttribute('title'))
      .toBe('Goals_StarredGoal');
    expect(indicator.textContent).toBe('{{_Vue.h.constructor`_x(0)`()}}');
    // the rest of the name follows as text, so it can still wrap
    expect(indicator.nextSibling!.nodeType).toBe(Node.TEXT_NODE);
    expect(indicator.nextSibling!.textContent).toBe(' conversions');
    expect(div.textContent).toBe('{{_Vue.h.constructor`_x(0)`()}} conversions');
  });

  it('keeps a name of one word completely next to the star', () => {
    const th = headerCell('Newsletter');

    addStarredGoalIndicator(th);

    const indicator = th.querySelector('.starredGoalIndicator')!;
    expect(indicator.textContent).toBe('Newsletter');
    expect(th.querySelector('.thDIV')!.textContent).toBe('Newsletter');
  });

  it('only adds the star when the name does not start with text', () => {
    const title = document.createElement('h2');
    title.innerHTML = '<a><strong>Goal</strong> Newsletter</a>';

    addStarredGoalIndicator(title);

    const link = title.querySelector('a')!;
    expect(link.firstElementChild!.className).toBe('starredGoalIndicator');
    expect(link.querySelector('.starredGoalIndicator')!.textContent).toBe('');
    expect(link.textContent).toBe('Goal Newsletter');
  });

  it('adds the star only once', () => {
    const th = headerCell('Newsletter conversions');

    addStarredGoalIndicator(th);
    addStarredGoalIndicator(th);

    expect(th.querySelectorAll('.starredGoalIndicator')).toHaveLength(1);
    expect(th.querySelectorAll('.starToggle')).toHaveLength(1);
    expect(th.textContent).toBe('Newsletter conversions');
  });
});
