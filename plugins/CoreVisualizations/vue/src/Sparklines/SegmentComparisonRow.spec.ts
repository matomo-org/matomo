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
    expect(wrapper.find('.metricValue__secondaryValue').text()).toBe('9527');
    expect(wrapper.find('.metricValue__secondaryLabel').text()).toBe('unique visitors');
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
