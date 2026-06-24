/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

jest.mock('CoreHome', () => ({
  Sparkline: { template: '<img class="sparkline-stub" />' },
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

  function createWrapper(sparkline: unknown = baseSparkline, areSparklinesLinkable = true) {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    return mount(SparklineCard as any, {
      props: { sparkline, areSparklinesLinkable },
    });
  }

  it('renders the primary metric description as title and the raw value + sparkline', () => {
    const wrapper = createWrapper();

    expect(wrapper.find('.sparkline-title').text()).toBe('Visits');
    expect(wrapper.find('.sparkline__value').text()).toBe('1,234');
    expect(wrapper.find('.sparkline-stub').exists()).toBe(true);
    expect(wrapper.classes()).toContain('sparkline');
    expect(wrapper.classes()).not.toContain('notLinkable');
  });

  it('omits empty graph-params / series-indices attributes', () => {
    const wrapper = createWrapper();

    expect(wrapper.attributes('data-graph-params')).toBeUndefined();
    expect(wrapper.attributes('data-series-indices')).toBeUndefined();
  });

  it('adds the notLinkable class when sparklines are not linkable', () => {
    const wrapper = createWrapper(baseSparkline, false);

    expect(wrapper.classes()).toContain('notLinkable');
  });

});
