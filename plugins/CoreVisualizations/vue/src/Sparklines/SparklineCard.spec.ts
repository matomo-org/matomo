/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// The shell renders the real NoComparison body, which mounts the real MetricValue (Tooltips
// directive) and the Sparkline. CoreHome has no jest module mapping, so mock it virtually.
jest.mock('CoreHome', () => ({
  Tooltips: {},
  Sparkline: {
    name: 'Sparkline',
    props: ['params', 'seriesIndices'],
    template: '<img class="sparkline-stub" />',
  },
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const SparklineCard = require('./SparklineCard.vue').default;

describe('CoreVisualizations/SparklineCard', () => {
  const baseSparkline = {
    url: '?module=API&action=get&columns=nb_visits',
    metrics: { '': [{ value: '1,234', description: 'Visits', column: 'nb_visits' }] },
    order: 1,
    title: null,
    group: '0',
    seriesIndices: null,
    graphParams: null,
  };

  function createWrapper(
    sparkline: unknown = baseSparkline,
    areSparklinesLinkable = true,
    allMetricsDocumentation: Record<string, string> = {},
  ) {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    return mount(SparklineCard as any, {
      props: { sparkline, areSparklinesLinkable, allMetricsDocumentation },
    });
  }

  it('renders the no-comparison body and forwards the sparkline to it', () => {
    const wrapper = createWrapper();

    const body = wrapper.findComponent({ name: 'NoComparison' });
    expect(body.exists()).toBe(true);
    expect(body.props('sparkline')).toEqual(baseSparkline);
  });

  it('forwards allMetricsDocumentation to the body so the title shows the metric tooltip', () => {
    const wrapper = createWrapper(baseSparkline, true, { nb_visits: 'The number of visits.' });

    const body = wrapper.findComponent({ name: 'NoComparison' });
    expect(body.props('allMetricsDocumentation')).toEqual({ nb_visits: 'The number of visits.' });
    expect(wrapper.find('.metricValue__title').attributes('title')).toBe('The number of visits.');
  });

  it('renders the card frame classes and composes the primary value + sparkline', () => {
    const wrapper = createWrapper();

    expect(wrapper.classes()).toContain('sparkline');
    expect(wrapper.classes()).toContain('sparklineCard');
    expect(wrapper.classes()).not.toContain('notLinkable');
    expect(wrapper.find('.metricValue__title').text()).toBe('Visits');
    expect(wrapper.find('.metricValue__number').text()).toBe('1,234');
    expect(wrapper.find('.sparkline-stub').exists()).toBe(true);
  });

  it('does not render the segment title region in no-comparison mode', () => {
    const wrapper = createWrapper();

    expect(wrapper.find('.sparklineCard__title').exists()).toBe(false);
  });

  it('renders the segment title region when sparkline.title is set', () => {
    const wrapper = createWrapper({ ...baseSparkline, title: 'Firefox' });

    expect(wrapper.find('.sparklineCard__title').text()).toBe('Firefox');
  });

  it('omits empty graph-params / series-indices attributes', () => {
    const wrapper = createWrapper();

    expect(wrapper.attributes('data-graph-params')).toBeUndefined();
    expect(wrapper.attributes('data-series-indices')).toBeUndefined();
  });

  it('emits graph-params / series-indices attributes as JSON when populated', () => {
    const wrapper = createWrapper({
      ...baseSparkline,
      graphParams: { columns: 'nb_visits' },
      seriesIndices: [0, 1],
    });

    expect(wrapper.attributes('data-graph-params')).toBe('{"columns":"nb_visits"}');
    expect(wrapper.attributes('data-series-indices')).toBe('[0,1]');
  });

  it('adds the notLinkable class when sparklines are not linkable', () => {
    const wrapper = createWrapper(baseSparkline, false);

    expect(wrapper.classes()).toContain('notLinkable');
  });
});
