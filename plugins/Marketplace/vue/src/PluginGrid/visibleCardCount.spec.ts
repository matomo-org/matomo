/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  observeVisibleCardCount,
  SINGLE_ROW_MAX_CARDS,
  visibleCardCount,
} from './visibleCardCount';
import { stubViewport } from '../testMarketplaceFixtures';

describe('visibleCardCount', () => {
  const originalMatchMedia = window.matchMedia;

  afterEach(() => {
    window.matchMedia = originalMatchMedia;
  });

  it.each([
    [1900, 5],
    [1801, 5],
    [1800, 4],
    [1521, 4],
    [1520, 3],
    [1281, 3],
    [1280, 4],
    [761, 4],
    [760, 2],
    [400, 2],
  ])('shows %i px worth of cards as %i', (width, expected) => {
    stubViewport(width);
    expect(visibleCardCount()).toBe(expected);
  });

  it('answers with a full row where matchMedia is unavailable, rather than throwing', () => {
    delete (window as unknown as { matchMedia?: unknown }).matchMedia;
    expect(visibleCardCount()).toBe(SINGLE_ROW_MAX_CARDS);
  });

  it('calls back when a breakpoint is crossed', () => {
    const viewport = stubViewport(1900);
    const onChange = vi.fn();

    observeVisibleCardCount(onChange);
    viewport.resizeTo(760);

    expect(onChange).toHaveBeenCalledWith(2);
  });

  it('detaches its own listeners, and only its own, when it unsubscribes', () => {
    const viewport = stubViewport(1900);

    const unobserveA = observeVisibleCardCount(vi.fn());
    observeVisibleCardCount(vi.fn());

    unobserveA();

    // one per breakpoint, and the second subscriber's are still attached
    expect(viewport.removed()).toBe(4);
  });

  it('stops calling back after unsubscribing', () => {
    const viewport = stubViewport(1900);
    const onChange = vi.fn();

    observeVisibleCardCount(onChange)();
    viewport.resizeTo(760);

    expect(onChange).not.toHaveBeenCalled();
  });

  it('subscribes without throwing where matchMedia is unavailable', () => {
    delete (window as unknown as { matchMedia?: unknown }).matchMedia;
    expect(() => observeVisibleCardCount(vi.fn())()).not.toThrow();
  });
});
