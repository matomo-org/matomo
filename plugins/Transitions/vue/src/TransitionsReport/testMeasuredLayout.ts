/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * jsdom reports every element as 0x0, which leaves the ribbon layer nothing to lay out. Gives every
 * element the same usable rect for the duration of a spec, and returns the spy so a test that cares
 * about how often a measurement happened can count the calls.
 *
 * Scoped to the specs that need it rather than the shared test bootstrap, which every plugin's
 * specs load: a global non-zero rect would quietly change unrelated components' behaviour.
 */
export function stubElementRects(rect = { top: 0, height: 100, width: 100 }) {
  const domRect = {
    ...rect,
    left: 0,
    right: rect.width,
    bottom: rect.top + rect.height,
    x: 0,
    y: rect.top,
    toJSON: () => rect,
  } as DOMRect;

  return vi.spyOn(Element.prototype, 'getBoundingClientRect').mockReturnValue(domRect);
}
