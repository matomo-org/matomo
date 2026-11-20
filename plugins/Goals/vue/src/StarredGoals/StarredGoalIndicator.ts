/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { h, render } from 'vue';
import { Matomo, StarToggle, translate } from 'CoreHome';

const STARRED_GOAL_ATTRIBUTE = 'data-starred-goal';
const INDICATOR_CLASS = 'starredGoalIndicator';

/**
 * Adds the read-only "Starred goal" star to an element that shows the name of a starred goal.
 * Does nothing if the element already has one.
 */
export function addStarredGoalIndicator(element: HTMLElement): void {
  if (element.querySelector(`.${INDICATOR_CLASS}`)) {
    return;
  }

  const indicator = document.createElement('span');
  indicator.className = INDICATOR_CLASS;

  const star = document.createElement('span');
  star.className = `${INDICATOR_CLASS}__star`;
  indicator.appendChild(star);

  render(h(StarToggle, {
    starred: true,
    readonly: true,
    small: true,
    title: translate('Goals_StarredGoal'),
  }), star);

  // in a report table the column name is in a child of the header cell
  const target = element.querySelector('.thDIV') || element.querySelector('a') || element;
  const name = target.firstChild;
  target.insertBefore(indicator, name);

  // A line may wrap right after the star. In a narrow column, such as the header of a goals table
  // where a long goal name is one word, the name would then start on the next line, apart from its
  // star. So the first word of the name is moved into the indicator, which does not wrap.
  if (name && name.nodeType === Node.TEXT_NODE) {
    const text = name as Text;
    const firstWordLength = text.data.search(/\s|$/);
    if (firstWordLength > 0) {
      text.splitText(firstWordLength);
      indicator.appendChild(text);
    }
  }
}

// The goals table and the titles on the Goals overview are rendered on the server, which marks the
// column headers and titles of starred goals with this attribute.
Matomo.on('Matomo.processDynamicHtml', ($element: JQuery) => {
  $element.find(`[${STARRED_GOAL_ATTRIBUTE}]`).each((i, e) => {
    addStarredGoalIndicator(e);
  });
});
