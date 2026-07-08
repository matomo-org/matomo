/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { flushPromises, mount } from '@vue/test-utils';

// The grid mounts the real SparklineCard -> NoComparison -> MetricValue chain, so the mock
// provides everything that chain pulls from CoreHome: the Tooltips directive (MetricValue),
// MatomoUrl (SparklineCard derives graph-params from the url) and NumberFormatter
// (NoComparison formats raw numeric metric values). CoreHome has no jest module mapping.
jest.mock('CoreHome', () => ({
  Tooltips: {},
  Sparkline: { template: '<img class="sparkline-stub" />' },
  // SparklineCard calls MatomoUrl.parse to derive data-graph-params, but no test here
  // asserts on it and the fixture urls carry no columns/rows/idGoal, so an empty object
  // is enough to satisfy the `parsed[key]` lookups without crashing.
  MatomoUrl: {
    parse: () => ({}),
  },
  NumberFormatter: {
    formatNumber: (value: number) => String(value),
  },
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const SparklinesGrid = require('./SparklinesGrid.vue').default;

describe('CoreVisualizations/SparklinesGrid', () => {
  let initializeSparklinesSpy: jest.Mock;

  beforeEach(() => {
    // sparkline.js (which defines window.initializeSparklines) is loaded on every real
    // Matomo page but not in the jest bootstrap, so stub it here.
    initializeSparklinesSpy = jest.fn();
    window.initializeSparklines = initializeSparklinesSpy;
  });

  function entry(description: string, order = 1) {
    return {
      url: '?module=API&action=get',
      metrics: { '': [{ value: '1', description }] },
      order,
      title: null,
      group: '0',
      seriesIndices: null,
      graphParams: null,
    };
  }

  function comparisonEntry(order = 1) {
    // Date-comparison entry: metrics grouped by pretty date label + a series index per date.
    return {
      url: '?module=API&action=get&compareDates[]=2026-05-03',
      metrics: {
        'Monday, May 4, 2026': [{ value: '1', description: 'Visits', title: 'Visits' }],
        'Sunday, May 3, 2026': [{ value: '2', description: 'Visits', title: 'Visits' }],
      },
      order,
      title: null,
      group: '0',
      seriesIndices: [0, 1],
      graphParams: null,
    };
  }

  function comparisonEntry3Dates(order = 1) {
    // Same as comparisonEntry() but comparing three dates, so the grid should widen the card.
    return {
      url: '?module=API&action=get&compareDates[]=2026-05-03&compareDates[]=2026-05-02',
      metrics: {
        'Monday, May 4, 2026': [{ value: '1', description: 'Visits', title: 'Visits' }],
        'Sunday, May 3, 2026': [{ value: '2', description: 'Visits', title: 'Visits' }],
        'Saturday, May 2, 2026': [{ value: '3', description: 'Visits', title: 'Visits' }],
      },
      order,
      title: null,
      group: '0',
      seriesIndices: [0, 1, 2],
      graphParams: null,
    };
  }

  function placeholder(order: number) {
    // Mirrors Config::addPlaceholder(): empty url + no metrics, used only for legacy layout.
    return {
      url: '',
      metrics: {},
      order,
      title: null,
      group: `placeholder${order}`,
      seriesIndices: null,
      graphParams: null,
    };
  }

  function createWrapper(props: Record<string, unknown> = {}) {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    return mount(SparklinesGrid as any, {
      props: {
        sparklines: { 0: [entry('Visits')], 1: [entry('Actions'), entry('Bounce rate')] },
        ...props,
      },
    });
  }

  it('flattens grouped sparklines into one card per entry', () => {
    const wrapper = createWrapper();

    expect(wrapper.findAllComponents({ name: 'SparklineCard' }).length).toBe(3);
  });

  it('drops layout placeholders so they do not render as empty cards', () => {
    // The flagship Visits Overview adds placeholders (order 10/31/50) purely for the legacy
    // 2-column layout; they have an empty url and no metrics and must not reach the grid.
    const wrapper = createWrapper({
      sparklines: {
        0: [entry('Visits', 1)],
        1: [placeholder(10)],
        2: [entry('Actions', 20)],
      },
    });

    const titles = wrapper.findAll('.metricValue__title').map((node) => node.text());
    expect(wrapper.findAllComponents({ name: 'SparklineCard' }).length).toBe(2);
    expect(titles).toEqual(['Visits', 'Actions']);
  });

  it('forwards allMetricsDocumentation to each card, surfacing it as the title tooltip', () => {
    const wrapper = createWrapper({
      sparklines: { 0: [{ ...entry('Visits'), metrics: { '': [{ value: '1', description: 'Visits', column: 'nb_visits' }] } }] },
      allMetricsDocumentation: { nb_visits: 'The number of visits.' },
    });

    const card = wrapper.findComponent({ name: 'SparklineCard' });
    expect(card.props('allMetricsDocumentation')).toEqual({ nb_visits: 'The number of visits.' });
    expect(wrapper.find('.metricValue__title').attributes('title')).toBe('The number of visits.');
  });

  it('lays cards out on a CSS grid, not the Materialize float row', () => {
    const wrapper = createWrapper();
    const grid = wrapper.find('.sparklinesGrid');

    // The `row` class must be gone: its clearfix ::after pseudo-element would otherwise become a
    // stray empty grid cell.
    expect(grid.classes()).not.toContain('row');
    // Cards are direct grid children now (no per-card col wrapper div).
    expect(wrapper.findAllComponents({ name: 'SparklineCard' }).length).toBe(3);
    expect(grid.attributes('style')).toContain('--sparklines-card-min-width: 260px');
  });

  it('orders cards by backend `order`, not by numeric group-key iteration order', () => {
    // The group keys are the metric index, and getSortedSparklines() may insert them out
    // of numeric order (e.g. a later metric with a lower `order`). Object.values() iterates
    // numeric keys ascending, so without the explicit sort the cards would render as
    // Third, First, Second here instead of in `order`.
    const wrapper = createWrapper({
      sparklines: {
        1: [entry('First', 1)],
        0: [entry('Third', 30)],
        2: [entry('Second', 20)],
      },
    });

    const titles = wrapper.findAll('.metricValue__title').map((node) => node.text());
    expect(titles).toEqual(['First', 'Second', 'Third']);
  });

  it('uses a smaller card minimum in widget mode so two still fit across', () => {
    const wrapper = createWrapper({ isWidget: true });
    const grid = wrapper.find('.sparklinesGrid');

    expect(grid.attributes('style')).toContain('--sparklines-card-min-width: 160px');
  });

  it('widens the card minimum when comparing and renders the comparison body', () => {
    const wrapper = createWrapper({ sparklines: { 0: [comparisonEntry()] }, isComparing: true });
    const grid = wrapper.find('.sparklinesGrid');

    // 2 compared dates -> 64 + 150 * 2.
    expect(grid.attributes('style')).toContain('--sparklines-card-min-width: 364px');
    expect(wrapper.findComponent({ name: 'DateComparison' }).exists()).toBe(true);
  });

  it('scales the comparison card minimum with the number of compared dates', () => {
    const wrapper = createWrapper({
      sparklines: { 0: [comparisonEntry3Dates()] },
      isComparing: true,
    });
    const grid = wrapper.find('.sparklinesGrid');

    // 3 compared dates -> 64 + 150 * 3, and one column per date renders inside the card.
    expect(grid.attributes('style')).toContain('--sparklines-card-min-width: 514px');
    expect(wrapper.findAll('.dateComparison__period').length).toBe(3);
  });

  it('re-runs the sparkline click-to-evolution wiring after mount', async () => {
    createWrapper();
    await flushPromises();

    expect(initializeSparklinesSpy).toHaveBeenCalledTimes(1);
  });
});
