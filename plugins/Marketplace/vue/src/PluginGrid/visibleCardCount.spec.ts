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

interface StubbedViewport {
  /** Moves to a new width and fires `change` on every query, as a browser would. */
  resizeTo: (width: number) => void;
  removed: () => number;
}

/**
 * A matchMedia stub driven by a width rather than a fixed boolean, so the breakpoint table itself
 * is what is under test. jsdom implements no matchMedia at all.
 */
function stubViewport(width: number): StubbedViewport {
  let current = width;
  const handlers: (() => void)[] = [];
  let removed = 0;

  window.matchMedia = ((query: string) => ({
    get matches() {
      const max = /max-width:\s*(\d+)px/.exec(query);
      return max ? current <= Number(max[1]) : false;
    },
    media: query,
    addEventListener: (_event: string, handler: () => void) => handlers.push(handler),
    removeEventListener: () => { removed += 1; },
  })) as unknown as typeof window.matchMedia;

  return {
    resizeTo: (next: number) => {
      current = next;
      handlers.forEach((handler) => handler());
    },
    removed: () => removed,
  };
}

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

  it('detaches every listener once the last subscriber goes', () => {
    const viewport = stubViewport(1900);

    const unobserveA = observeVisibleCardCount(vi.fn());
    const unobserveB = observeVisibleCardCount(vi.fn());

    unobserveA();
    expect(viewport.removed()).toBe(0);

    unobserveB();
    expect(viewport.removed()).toBeGreaterThan(0);
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
