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

  it('uses the responsive grid columns (s6 m6 l4 xl3) on reporting pages', () => {
    const wrapper = createWrapper();
    const col = wrapper.find('.row.sparklinesGrid > div');

    expect(col.classes()).toEqual(expect.arrayContaining(['col', 's6', 'm6', 'l4', 'xl3']));
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

  it('collapses to two columns in widget mode', () => {
    const wrapper = createWrapper({ isWidget: true });
    const col = wrapper.find('.row.sparklinesGrid > div');

    expect(col.classes()).toContain('s6');
    expect(col.classes()).not.toContain('xl3');
  });

  it('re-runs the sparkline click-to-evolution wiring after mount', async () => {
    createWrapper();
    await flushPromises();

    expect(initializeSparklinesSpy).toHaveBeenCalledTimes(1);
  });
});
