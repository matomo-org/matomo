/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// The row mounts the real MetricValue (Tooltips directive, NumberFormatter) and the reused
// Sparkline. CoreHome has no jest module mapping, so mock it virtually.
jest.mock('CoreHome', () => ({
  Tooltips: {},
  // ucfirst is mocked as an identity passthrough; its capitalization is covered by ucfirst.spec.
  ucfirst: (s?: string) => s ?? '',
  Sparkline: {
    name: 'Sparkline',
    props: ['params', 'seriesIndices', 'width', 'height'],
    template: '<img class="sparkline-stub" />',
  },
  NumberFormatter: {
    formatNumber: (value: number) => String(value),
  },
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const SegmentComparisonRow = require('./SegmentComparisonRow.vue').default;

function segment(overrides = {}) {
  return {
    url: '?module=API&action=get&columns=nb_visits&segment=continentCode==eur',
    metrics: {
      'Jan 12 - 17, 2012': [
        {
          value: 10558, description: 'visits', title: 'Visits', column: 'nb_visits',
        },
        {
          value: 9527, description: 'unique visitors', title: 'Unique visitors', column: 'nb_uniq_visitors',
        },
      ],
    },
    metricsOrder: ['Jan 12 - 17, 2012'],
    order: 501,
    title: 'Eu visitors',
    group: '0',
    seriesIndices: [1],
    graphParams: null,
    ...overrides,
  };
}

function createWrapper(props = {}) {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return mount(SegmentComparisonRow as any, { props: { segment: segment(), ...props } });
}

describe('CoreVisualizations/SegmentComparisonRow', () => {
  it('renders the segment name as a chip, with the full name as a hover-recovery title', () => {
    const chip = createWrapper().find('.sparklineSegmentComparisonRow__chip');

    expect(chip.text()).toBe('Eu visitors');
    expect(chip.attributes('title')).toBe('Eu visitors');
  });

  it('renders the primary and secondary metric values, formatting raw numbers', () => {
    const wrapper = createWrapper();

    expect(wrapper.find('.metricValue__number').text()).toBe('10558');
    expect(wrapper.find('.metricValue__secondaryLine').text()).toBe('9527 unique visitors');
  });

  it('omits the MetricValue title (the card shows the metric name once above the rows)', () => {
    expect(createWrapper().find('.metricValue__title').exists()).toBe(false);
  });

  it('renders its own single-series sparkline with the entry url and series index', () => {
    const sparkline = createWrapper().findComponent({ name: 'Sparkline' });

    expect(sparkline.props('params')).toBe(
      '?module=API&action=get&columns=nb_visits&segment=continentCode==eur',
    );
    expect(sparkline.props('seriesIndices')).toEqual([1]);
    expect(sparkline.props('width')).toBe(380);
    expect(sparkline.props('height')).toBe(40);
  });

  it('is a plain presentational block, not itself a .sparkline link (the card is the link)', () => {
    const wrapper = createWrapper();

    expect(wrapper.classes()).toContain('sparklineSegmentComparisonRow');
    expect(wrapper.classes()).not.toContain('sparkline');
    expect(wrapper.attributes('data-series-indices')).toBeUndefined();
    expect(wrapper.attributes('data-graph-params')).toBeUndefined();
  });

  it('renders no EvolutionBadge (segment comparison carries no evolution)', () => {
    expect(createWrapper().findComponent({ name: 'EvolutionBadge' }).exists()).toBe(false);
  });
});

// Segment + date: the same row, but the segment entry carries one value column per compared date
// (metricsOrder length > 1), a per-date evolution on the current period, and a multi-series sparkline.
function segmentDate(overrides = {}) {
  return {
    url: '?module=API&action=get&columns=nb_visits&segment=continentCode==eur&comparePeriods[]=range',
    metrics: {
      'Apr 23 - May 2, 2026': [
        {
          value: 23558,
          description: 'visits',
          title: 'Visits',
          column: '',
          group: 'Apr 23 - May 2, 2026',
          evolution: {
            percent: '+28.5%', trend: 5000, isLowerValueBetter: false, tooltip: 'more than before',
          },
        },
      ],
      'Mar 24 - Apr 2, 2026': [
        {
          value: 30119, description: 'visits', title: 'Visits', column: '', group: 'Mar 24 - Apr 2, 2026',
        },
      ],
    },
    metricsOrder: ['Apr 23 - May 2, 2026', 'Mar 24 - Apr 2, 2026'],
    order: 501,
    title: 'Eu visitors',
    group: '0',
    seriesIndices: [1, 3],
    graphParams: null,
    ...overrides,
  };
}

function createSegmentDateWrapper(props = {}) {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return mount(SegmentComparisonRow as any, { props: { segment: segmentDate(), ...props } });
}

describe('CoreVisualizations/SegmentComparisonRow segment + date', () => {
  it('renders one value column per compared date, split by a separator', () => {
    const wrapper = createSegmentDateWrapper();

    expect(wrapper.findAll('.periodColumns__column')).toHaveLength(2);
    expect(wrapper.findAll('.periodColumns__separator')).toHaveLength(1);

    const numbers = wrapper.findAll('.metricValue__number').map((n) => n.text());
    expect(numbers).toEqual(['23558', '30119']);
  });

  it('labels each column with its compared date (only shown when comparing more than one date)', () => {
    const labels = createSegmentDateWrapper().findAll('.dateAtom').map((d) => d.text());

    expect(labels).toEqual(['Apr 23 - May 2, 2026', 'Mar 24 - Apr 2, 2026']);
  });

  it('shows the evolution badge only on the period that carries evolution (the current date)', () => {
    const wrapper = createSegmentDateWrapper();

    expect(wrapper.findAllComponents({ name: 'EvolutionBadge' })).toHaveLength(1);
    expect(wrapper.findComponent({ name: 'EvolutionBadge' }).props('percent')).toBe('+28.5%');
  });

  it('renders a wider, multi-series sparkline (one series per compared date)', () => {
    const wrapper = createSegmentDateWrapper();
    const sparkline = wrapper.findComponent({ name: 'Sparkline' });

    expect(sparkline.props('seriesIndices')).toEqual([1, 3]);
    expect(sparkline.props('width')).toBe(760);
    expect(wrapper.find('.sparklineSegmentComparisonRow__sparkline--wide').exists()).toBe(true);
  });
});
