/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { flushPromises, mount } from '@vue/test-utils';

// The grid mounts the real SparklineCard -> NoComparison -> MetricValue chain (and, in segment mode,
// SegmentComparisonCard -> SegmentComparisonRow -> MetricValue + Sparkline), so the mock supplies
// what those chains pull from CoreHome (no jest module mapping): the Tooltips directive, MatomoUrl
// (data-graph-params) and NumberFormatter (raw numeric values).
jest.mock('CoreHome', () => ({
  Tooltips: {},
  Sparkline: { template: '<img class="sparkline-stub" />' },
  // The cards call MatomoUrl.parse for data-graph-params, but no test asserts on it and the fixture
  // urls carry no columns/rows/idGoal, so an empty object satisfies the `parsed[key]` lookups.
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
      metricsOrder: ['Monday, May 4, 2026', 'Sunday, May 3, 2026'],
      order,
      title: null,
      group: '0',
      seriesIndices: [0, 1],
      graphParams: null,
    };
  }

  function segmentEntry(title: string, seriesIndex: number, order: number, value = '1') {
    // Segment-comparison entry: metrics under a single period label + one series index per segment.
    return {
      url: '?module=API&action=get&columns=nb_visits',
      metrics: {
        'Jan 12 - 17, 2012': [
          {
            value, description: 'visits', title: 'Visits', column: 'nb_visits',
          },
          { value: '0', description: 'unique visitors', title: 'Unique visitors' },
        ],
      },
      metricsOrder: ['Jan 12 - 17, 2012'],
      order,
      title,
      group: '0',
      seriesIndices: [seriesIndex],
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

  it('uses the wider comparison columns (s12 m12 l6 xl6) in date comparison', () => {
    const wrapper = createWrapper({ sparklines: { 0: [comparisonEntry()] }, comparisonMode: 'date' });
    const col = wrapper.find('.row.sparklinesGrid > div');

    expect(col.classes()).toEqual(expect.arrayContaining(['col', 's12', 'm12', 'l6', 'xl6']));
    // ...and the comparison body is what renders inside.
    expect(wrapper.findComponent({ name: 'DateComparison' }).exists()).toBe(true);
  });

  it('collapses date-comparison cards to a single column in widget mode', () => {
    const wrapper = createWrapper({
      sparklines: { 0: [comparisonEntry()] },
      comparisonMode: 'date',
      isWidget: true,
    });
    const col = wrapper.find('.row.sparklinesGrid > div');

    expect(col.classes()).toContain('s12');
    expect(col.classes()).not.toContain('xl6');
  });

  it('renders one SegmentComparisonCard per metric group in segment mode', () => {
    const wrapper = createWrapper({
      comparisonMode: 'segment',
      sparklines: {
        0: [segmentEntry('All visits', 0, 0), segmentEntry('Eu visitors', 1, 1)],
        1: [segmentEntry('All visits', 0, 100), segmentEntry('Eu visitors', 1, 101)],
      },
    });

    const cards = wrapper.findAllComponents({ name: 'SegmentComparisonCard' });
    expect(cards.length).toBe(2);
    expect(wrapper.findAllComponents({ name: 'SparklineCard' }).length).toBe(0);
    // Each card stacks one row per compared segment.
    expect(cards[0].findAllComponents({ name: 'SegmentComparisonRow' }).length).toBe(2);
  });

  it('shows the segment chips and values per stacked segment in segment mode', () => {
    const wrapper = createWrapper({
      comparisonMode: 'segment',
      sparklines: {
        0: [segmentEntry('All visits', 0, 0, '10'), segmentEntry('Eu visitors', 1, 1, '20')],
      },
    });

    expect(wrapper.findAll('.sparklineSegmentComparisonRow__chip').map((n) => n.text())).toEqual(['All visits', 'Eu visitors']);
    expect(wrapper.findAll('.metricValue__number').map((n) => n.text())).toEqual(['10', '20']);
  });

  it('uses the standard (non-wide) grid columns for segment cards', () => {
    const wrapper = createWrapper({
      comparisonMode: 'segment',
      sparklines: { 0: [segmentEntry('All visits', 0, 0)] },
    });
    const col = wrapper.find('.row.sparklinesGrid > div');

    expect(col.classes()).toEqual(expect.arrayContaining(['col', 's6', 'm6', 'l4', 'xl3']));
  });

  it('orders segment cards by each metric group\'s lowest entry order', () => {
    const wrapper = createWrapper({
      comparisonMode: 'segment',
      sparklines: {
        0: [segmentEntry('All', 0, 300, '3')],
        1: [segmentEntry('All', 0, 100, '1')],
        2: [segmentEntry('All', 0, 200, '2')],
      },
    });

    // One card (one row) per group; sorting by the group's lowest order yields values 1, 2, 3.
    expect(wrapper.findAll('.metricValue__number').map((n) => n.text())).toEqual(['1', '2', '3']);
  });

  it('renders no EvolutionBadge in segment mode', () => {
    const wrapper = createWrapper({
      comparisonMode: 'segment',
      sparklines: { 0: [segmentEntry('All visits', 0, 0), segmentEntry('Eu visitors', 1, 1)] },
    });

    expect(wrapper.findAllComponents({ name: 'EvolutionBadge' }).length).toBe(0);
  });

  it('re-runs the sparkline click-to-evolution wiring after mount', async () => {
    createWrapper();
    await flushPromises();

    expect(initializeSparklinesSpy).toHaveBeenCalledTimes(1);
  });
});
