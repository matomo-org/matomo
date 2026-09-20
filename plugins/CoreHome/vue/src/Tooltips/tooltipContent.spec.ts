/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import tooltipContent from './tooltipContent';

// The sanitizer itself lives in the polyfill bundle, so it is stubbed with an identity function
// here - these tests cover what the helper does around it.
const sanitize = vi.fn((value: unknown) => `${value}`);

(window as unknown as { vueSanitizeTooltip: unknown }).vueSanitizeTooltip = sanitize;

function element(title?: string): HTMLElement {
  const el = document.createElement('span');
  if (typeof title !== 'undefined') {
    el.setAttribute('title', title);
  }
  return el;
}

describe('CoreHome/tooltipContent', () => {
  beforeEach(() => {
    sanitize.mockClear();
  });

  it('sanitizes the title of the element it is called on', () => {
    expect(tooltipContent.call(element('a title'))).toEqual('a title');
    expect(sanitize).toHaveBeenCalledWith('a title');
  });

  it('hands the title over unchanged, line breaks included', () => {
    tooltipContent.call(element('first\nsecond'));
    expect(sanitize).toHaveBeenCalledWith('first\nsecond');
  });

  it('passes an empty string if there is no title', () => {
    expect(tooltipContent.call(element())).toEqual('');
    expect(sanitize).toHaveBeenCalledWith('');
  });
});
