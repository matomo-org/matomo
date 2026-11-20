/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * How many cards a single-row section shows at the current width. One number decides both what is
 * rendered and whether "See all" is there, which a stylesheet hiding the overflow could not.
 *
 * Keep the counts in step with `.pluginGrid`'s columns; `visibleCardCount.spec.ts` pins them.
 */

/**
 * Narrowest first, first match wins: every max-width below the current width matches at once.
 *
 * Below three columns a section deliberately runs to two rows, or it would come down to a single
 * card, which is why 1280px reports four rather than two.
 */
const SINGLE_ROW_BREAKPOINTS: ReadonlyArray<{ query: string, cards: number }> = [
  { query: '(max-width: 760px)', cards: 2 },
  { query: '(max-width: 1280px)', cards: 4 },
  { query: '(max-width: 1520px)', cards: 3 },
  { query: '(max-width: 1800px)', cards: 4 },
];

/** The widest breakpoint's count, and so the most cards one row ever shows. */
export const SINGLE_ROW_MAX_CARDS = 5;

function supportsMatchMedia(): boolean {
  return typeof window !== 'undefined' && typeof window.matchMedia === 'function';
}

/**
 * How many cards a single-row grid is showing right now. Answers with the widest count where
 * `matchMedia` is unavailable, so a caller renders a full row rather than nothing.
 */
export function visibleCardCount(): number {
  if (!supportsMatchMedia()) {
    return SINGLE_ROW_MAX_CARDS;
  }

  const breakpoint = SINGLE_ROW_BREAKPOINTS.find(
    (candidate) => window.matchMedia(candidate.query).matches,
  );

  return breakpoint ? breakpoint.cards : SINGLE_ROW_MAX_CARDS;
}

/**
 * Calls back whenever the answer changes, and returns the unsubscribe. Each subscriber opens its
 * own queries: `change` fires only on a crossing, and no module state outlives its section.
 */
export function observeVisibleCardCount(onChange: (count: number) => void): () => void {
  if (!supportsMatchMedia()) {
    return () => undefined;
  }

  const notify = () => onChange(visibleCardCount());
  const queries = SINGLE_ROW_BREAKPOINTS.map((breakpoint) => {
    const query = window.matchMedia(breakpoint.query);
    query.addEventListener('change', notify);
    return query;
  });

  return () => queries.forEach((query) => query.removeEventListener('change', notify));
}
