/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// CoreHome has no jest module mapping, so virtual-mock it: Sparkline as a prop-declaring stub,
// Tooltips the directive the real MetricValue registers, and NumberFormatter to format numbers.
jest.mock('CoreHome', () => ({
  Tooltips: {},
  Sparkline: {
    name: 'Sparkline',
    props: ['params', 'seriesIndices'],
    template: '<img class="sparkline-stub" />',
  },
  NumberFormatter: {
    formatNumber: (value: number) => String(value),
  },
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const DateComparison = require('./DateComparison.vue').default;

function createWrapper(overrides = {}) {
  const sparkline = {
    url: '?module=API&action=get&columns=nb_visits&compareDates[]=2026-05-03',
    metrics: {
      'Monday, May 4, 2026': [
        {
          value: '10,558',
          description: 'Visits',
          title: 'Visits',
          evolution: {
            percent: '+0.5%', trend: 53, isLowerValueBetter: false, tooltip: 'since last period',
          },
        },
        { value: '9,527', description: 'unique visitors', title: 'Unique visitors' },
      ],
      'Sunday, May 3, 2026': [
        { value: '12,558', description: 'Visits', title: 'Visits' },
        { value: '10,527', description: 'unique visitors', title: 'Unique visitors' },
      ],
    },
    order: 1,
    title: null,
    group: '0',
    seriesIndices: [0, 1],
    graphParams: null,
    ...overrides,
  };

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return mount(DateComparison as any, { props: { sparkline } });
}

describe('CoreVisualizations/DateComparison', () => {
  it('renders the metric name as the card title', () => {
    const wrapper = createWrapper();

    expect(wrapper.find('.dateComparison__metric').text()).toBe('Visits');
  });

  it('renders one column per compared date with its date label', () => {
    const wrapper = createWrapper();

    const labels = wrapper.findAll('.dateAtom').map((node) => node.text());
    expect(labels).toEqual(['Monday, May 4, 2026', 'Sunday, May 3, 2026']);
    expect(wrapper.findAll('.dateComparison__period').length).toBe(2);
  });

  it('renders the primary and secondary value of each date column', () => {
    const wrapper = createWrapper();

    const columns = wrapper.findAll('.dateComparison__period');
    expect(columns[0].find('.metricValue__number').text()).toBe('10,558');
    expect(columns[0].find('.metricValue__secondaryValue').text()).toBe('9,527');
    expect(columns[0].find('.metricValue__secondaryLabel').text()).toBe('unique visitors');
    expect(columns[1].find('.metricValue__number').text()).toBe('12,558');
    expect(columns[1].find('.metricValue__secondaryValue').text()).toBe('10,527');
  });

  it('renders an EvolutionBadge only for the date that has evolution data', () => {
    const wrapper = createWrapper();

    const badges = wrapper.findAllComponents({ name: 'EvolutionBadge' });
    expect(badges.length).toBe(1);
    expect(badges[0].props('percent')).toBe('+0.5%');
    expect(badges[0].props('trend')).toBe(53);
    expect(badges[0].props('tooltip')).toBe('since last period');

    // ...and it belongs to the first column.
    const columns = wrapper.findAll('.dateComparison__period');
    expect(columns[0].findComponent({ name: 'EvolutionBadge' }).exists()).toBe(true);
    expect(columns[1].findComponent({ name: 'EvolutionBadge' }).exists()).toBe(false);
  });

  it('coerces a null evolution tooltip to an empty string for the badge', () => {
    const wrapper = createWrapper({
      metrics: {
        'Monday, May 4, 2026': [
          {
            value: '10,558',
            description: 'Visits',
            title: 'Visits',
            evolution: {
              percent: '-2%', trend: -10, isLowerValueBetter: false, tooltip: null,
            },
          },
        ],
      },
      seriesIndices: [0],
    });

    expect(wrapper.findComponent({ name: 'EvolutionBadge' }).props('tooltip')).toBe('');
  });

  it('formats raw numeric metric values through NumberFormatter', () => {
    const wrapper = createWrapper({
      metrics: {
        'Monday, May 4, 2026': [{ value: 10558, description: 'Visits', title: 'Visits' }],
      },
      seriesIndices: [0],
    });

    expect(wrapper.find('.metricValue__number').text()).toBe('10558');
  });

  it('passes the sparkline url and every series index to the single Sparkline', () => {
    const wrapper = createWrapper();

    const sparklines = wrapper.findAllComponents({ name: 'Sparkline' });
    expect(sparklines.length).toBe(1);
    expect(sparklines[0].props('params')).toBe(
      '?module=API&action=get&columns=nb_visits&compareDates[]=2026-05-03',
    );
    expect(sparklines[0].props('seriesIndices')).toEqual([0, 1]);
  });
});
