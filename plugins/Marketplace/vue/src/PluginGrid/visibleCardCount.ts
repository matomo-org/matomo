/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * How many cards a single-row section is showing at the current width.
 *
 * `PluginGrid.less` cuts a section's row down to one screenful with nth-child rules rather than
 * JavaScript, so a section holding more plugins than fit is not showing them - and its "See all"
 * has to be there. That decision needs the number the stylesheet arrived at, which is why the
 * counts are mirrored here.
 *
 * Keep this table and `.pluginGrid--singleRow` in step. Nothing fails loudly if they drift: a
 * "See all" just goes missing while cards are cut off, or appears on a row that is already whole.
 * `visibleCardCount.spec.ts` pins the sequence so an edit to one of them fails a test instead.
 *
 * This is a plain module, not a composable, so the MediaQueryList objects are shared by every
 * section on the page and stay out of Vue's reactive proxies - a proxied MediaQueryList throws
 * when its own methods are called on it, see the note in `CategoryTabs.vue`.
 */

/**
 * Narrowest first, first match wins: every max-width below the current width matches at once.
 *
 * Below three columns the stylesheet deliberately shows two rows, or a section would come down to
 * a single card, which is why 1280px reports four rather than two.
 */
export const SINGLE_ROW_BREAKPOINTS: ReadonlyArray<{ query: string, cards: number }> = [
  { query: '(max-width: 760px)', cards: 2 },
  { query: '(max-width: 1280px)', cards: 4 },
  { query: '(max-width: 1520px)', cards: 3 },
  { query: '(max-width: 1800px)', cards: 4 },
];

/** The widest breakpoint's count, and so the most cards one row ever shows. */
export const SINGLE_ROW_MAX_CARDS = 5;

type Listener = (count: number) => void;

const listeners = new Set<Listener>();
let queries: MediaQueryList[]|null = null;

function supportsMatchMedia(): boolean {
  return typeof window !== 'undefined' && typeof window.matchMedia === 'function';
}

/**
 * How many cards a single-row grid is showing right now.
 *
 * Answers with the widest count where `matchMedia` is unavailable - jsdom, or a render with no
 * layout - so a caller renders a full row rather than nothing.
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

function notify(): void {
  const count = visibleCardCount();
  listeners.forEach((listener) => listener(count));
}

function attach(): void {
  if (queries || !supportsMatchMedia()) {
    return;
  }

  queries = SINGLE_ROW_BREAKPOINTS.map((breakpoint) => {
    const query = window.matchMedia(breakpoint.query);
    query.addEventListener('change', notify);
    return query;
  });
}

function detach(): void {
  if (queries) {
    queries.forEach((query) => query.removeEventListener('change', notify));
  }

  queries = null;
}

/**
 * Calls back whenever the answer changes, and returns the unsubscribe.
 *
 * One `change` listener per breakpoint is shared by every subscriber, attached with the first and
 * dropped with the last. `change` fires only when a breakpoint is crossed, so this costs nothing
 * while a window is being dragged within one.
 */
export function observeVisibleCardCount(onChange: Listener): () => void {
  listeners.add(onChange);
  attach();

  return () => {
    listeners.delete(onChange);
    if (!listeners.size) {
      detach();
    }
  };
}
