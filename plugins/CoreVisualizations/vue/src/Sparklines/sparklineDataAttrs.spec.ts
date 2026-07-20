/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// The helper derives data-graph-params from the sparkline url via CoreHome's MatomoUrl.parse;
// CoreHome has no jest module mapping, so virtual-mock a query-string parser like the real one
// (which receives the query string without its leading '?').
jest.mock('CoreHome', () => ({
  MatomoUrl: {
    parse: (search: string) => {
      const params: Record<string, string> = {};
      new URLSearchParams(search).forEach((value, key) => {
        params[key] = value;
      });
      return params;
    },
  },
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const { sparklineGraphParamsAttr, sparklineSeriesIndicesAttr } = require('./sparklineDataAttrs');

function entry(overrides = {}) {
  return {
    url: '?module=API&action=get&columns=nb_visits',
    metrics: { '': [] },
    order: 1,
    title: null,
    group: '0',
    seriesIndices: null,
    graphParams: null,
    ...overrides,
  };
}

describe('CoreVisualizations/sparklineDataAttrs', () => {
  describe('sparklineGraphParamsAttr', () => {
    it('prefers explicit backend graphParams verbatim over the url', () => {
      expect(sparklineGraphParamsAttr(entry({ graphParams: { columns: 'nb_actions' } })))
        .toBe('{"columns":"nb_actions"}');
    });

    it('derives columns/rows/idGoal from the url when graphParams is empty', () => {
      // The reused Sparkline renders its image with `src` (no `data-src`), so the legacy click
      // handler can't read the reload columns off the img; we supply them from the url.
      expect(sparklineGraphParamsAttr(entry({
        url: '?module=API&action=get&columns=nb_visits&rows=Search&idGoal=1',
      }))).toBe('{"columns":"nb_visits","rows":"Search","idGoal":"1"}');
    });

    it('returns null when neither graphParams nor url carries reload params', () => {
      expect(sparklineGraphParamsAttr(entry({ url: '?module=API&action=get', graphParams: null })))
        .toBeNull();
    });
  });

  describe('sparklineSeriesIndicesAttr', () => {
    it('serialises populated series indices (one per series)', () => {
      expect(sparklineSeriesIndicesAttr(entry({ seriesIndices: [1] }))).toBe('[1]');
      expect(sparklineSeriesIndicesAttr(entry({ seriesIndices: [0, 2] }))).toBe('[0,2]');
    });

    it('returns null for no-comparison entries (null or empty series indices)', () => {
      expect(sparklineSeriesIndicesAttr(entry({ seriesIndices: null }))).toBeNull();
      expect(sparklineSeriesIndicesAttr(entry({ seriesIndices: [] }))).toBeNull();
    });
  });
});
