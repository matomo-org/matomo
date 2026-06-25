/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// CoreHome is a package-style cross-plugin import with no jest module mapping,
// so it must be virtually mocked. Tooltips is used only as a (no-op here) directive.
jest.mock('CoreHome', () => ({
  Tooltips: {},
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const MetricValue = require('./MetricValue.vue').default;

describe('CoreVisualizations/MetricValue', () => {
  it('renders the title and the pre-formatted value', () => {
    const wrapper = mount(MetricValue as any, {
      props: {
        title: 'Searches',
        value: '190',
      },
    });

    expect(wrapper.find('.metricValue__title').text()).toBe('Searches');
    expect(wrapper.find('.metricValue__number').text()).toBe('190');
  });

  it('renders the secondary value and label as separate elements', () => {
    const wrapper = mount(MetricValue as any, {
      props: {
        title: 'Visits',
        value: '10,558',
        secondaryValue: '9,527',
        secondaryLabel: 'unique visitors',
      },
    });

    expect(wrapper.find('.metricValue__secondary').exists()).toBe(true);
    expect(wrapper.find('.metricValue__secondaryValue').text()).toBe('9,527');
    expect(wrapper.find('.metricValue__secondaryLabel').text()).toBe('unique visitors');
  });

  it('renders the secondary value without a label when no label is given', () => {
    const wrapper = mount(MetricValue as any, {
      props: {
        title: 'Visits',
        value: '10,558',
        secondaryValue: '9,527',
      },
    });

    expect(wrapper.find('.metricValue__secondaryValue').text()).toBe('9,527');
    expect(wrapper.find('.metricValue__secondaryLabel').exists()).toBe(false);
  });

  it('omits the secondary line entirely when no secondary value is provided', () => {
    const wrapper = mount(MetricValue as any, {
      props: {
        title: 'Average visit duration',
        value: '4min 22s',
      },
    });

    expect(wrapper.find('.metricValue__secondary').exists()).toBe(false);
  });

  it('exposes documentation as the title tooltip and flags the title as documented', () => {
    const wrapper = mount(MetricValue as any, {
      props: {
        title: 'Searches',
        value: '190',
        documentation: 'The number of searches.',
      },
    });

    const title = wrapper.find('.metricValue__title');
    expect(title.attributes('title')).toBe('The number of searches.');
    expect(title.classes()).toContain('metricValue__title--documented');
  });

  it('sets no tooltip and no documented class when documentation is absent', () => {
    const wrapper = mount(MetricValue as any, {
      props: {
        title: 'Searches',
        value: '190',
      },
    });

    const title = wrapper.find('.metricValue__title');
    expect(title.attributes('title')).toBeUndefined();
    expect(title.classes()).not.toContain('metricValue__title--documented');
  });

  it('renders content passed to the evolution slot next to the value', () => {
    const wrapper = mount(MetricValue as any, {
      props: {
        title: 'Searches',
        value: '190',
      },
      slots: {
        evolution: '<span class="fake-badge">-4%</span>',
      },
    });

    const primary = wrapper.find('.metricValue__primary');
    expect(primary.find('.fake-badge').exists()).toBe(true);
    expect(primary.find('.fake-badge').text()).toBe('-4%');
  });
});
