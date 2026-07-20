/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// CoreHome is a package-style cross-plugin import with no jest module mapping, so it must be
// virtually mocked. Sparkline becomes a stub that declares its props (so they can be asserted),
// and Tooltips is the (no-op here) directive used by the real MetricValue this component mounts.
jest.mock('CoreHome', () => ({
  Tooltips: {},
  Sparkline: {
    name: 'Sparkline',
    props: ['params', 'seriesIndices'],
    template: '<img class="sparkline-stub" />',
  },
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const NoComparison = require('./NoComparison.vue').default;

function makeSparkline(overrides = {}) {
  return {
    url: '?module=API&action=get&columns=nb_visits',
    metrics: { '': [{ value: '10,558', description: 'Visits', column: 'nb_visits' }] },
    order: 1,
    title: null,
    group: '0',
    seriesIndices: null,
    graphParams: null,
    ...overrides,
  };
}

function createWrapper(sparkline: unknown, allMetricsDocumentation: Record<string, string> = {}) {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return mount(NoComparison as any, { props: { sparkline, allMetricsDocumentation } });
}

describe('CoreVisualizations/NoComparison', () => {
  it('passes the primary metric to MetricValue as title + value', () => {
    const wrapper = createWrapper(makeSparkline());

    const metricValue = wrapper.findComponent({ name: 'MetricValue' });
    expect(metricValue.props('title')).toBe('Visits');
    expect(metricValue.props('value')).toBe('10,558');
  });

  it('resolves the primary metric documentation by column and passes it to MetricValue', () => {
    const wrapper = createWrapper(
      makeSparkline(),
      { nb_visits: 'The number of visits.' },
    );

    expect(wrapper.findComponent({ name: 'MetricValue' }).props('documentation'))
      .toBe('The number of visits.');
  });

  it('passes undefined documentation when the metric column is unknown or empty', () => {
    const wrapper = createWrapper(
      makeSparkline({ metrics: { '': [{ value: '10,558', description: 'Visits', column: '' }] } }),
      { nb_visits: 'The number of visits.' },
    );

    expect(wrapper.findComponent({ name: 'MetricValue' }).props('documentation')).toBeUndefined();
  });

  it('passes a second metric to MetricValue as the secondary value + label', () => {
    const wrapper = createWrapper(makeSparkline({
      metrics: {
        '': [
          { value: '10,558', description: 'Visits' },
          { value: '9,527', description: 'unique' },
        ],
      },
    }));

    const metricValue = wrapper.findComponent({ name: 'MetricValue' });
    expect(metricValue.props('secondaryValue')).toBe('9,527');
    expect(metricValue.props('secondaryLabel')).toBe('unique');
    expect(wrapper.find('.metricValue__secondaryValue').text()).toBe('9,527');
  });

  it('omits the secondary line when there is only one metric', () => {
    const wrapper = createWrapper(makeSparkline());

    const metricValue = wrapper.findComponent({ name: 'MetricValue' });
    expect(metricValue.props('secondaryValue')).toBeUndefined();
    expect(wrapper.find('.metricValue__secondary').exists()).toBe(false);
  });

  it('renders an EvolutionBadge with the entry evolution props when present', () => {
    const wrapper = createWrapper(makeSparkline({
      evolution: {
        percent: '+0.5%',
        trend: 53,
        isLowerValueBetter: false,
        tooltip: 'since last period',
      },
    }));

    const badge = wrapper.findComponent({ name: 'EvolutionBadge' });
    expect(badge.exists()).toBe(true);
    expect(badge.props('percent')).toBe('+0.5%');
    expect(badge.props('trend')).toBe(53);
    expect(badge.props('isLowerValueBetter')).toBe(false);
    expect(badge.props('tooltip')).toBe('since last period');
  });

  it('renders no EvolutionBadge when the entry has no evolution', () => {
    const wrapper = createWrapper(makeSparkline());

    expect(wrapper.findComponent({ name: 'EvolutionBadge' }).exists()).toBe(false);
  });

  it('coerces a null evolution tooltip to an empty string for the badge', () => {
    const wrapper = createWrapper(makeSparkline({
      evolution: {
        percent: '-2%',
        trend: -10,
        isLowerValueBetter: false,
        tooltip: null,
      },
    }));

    expect(wrapper.findComponent({ name: 'EvolutionBadge' }).props('tooltip')).toBe('');
  });

  it('passes the sparkline url and series indices to the Sparkline', () => {
    const wrapper = createWrapper(makeSparkline({ seriesIndices: [0, 1] }));

    const sparkline = wrapper.findComponent({ name: 'Sparkline' });
    expect(sparkline.props('params')).toBe('?module=API&action=get&columns=nb_visits');
    expect(sparkline.props('seriesIndices')).toEqual([0, 1]);
  });
});
