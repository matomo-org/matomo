/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// CoreHome is a package-style cross-plugin import with no jest module mapping, so it must be
// virtually mocked. Tooltips is a (no-op here) directive; NumberFormatter formats raw numeric
// values (the mock echoes value + precision so tests can assert both). ucfirst is an identity
// spy here; its casing behavior is covered by CoreHome's own ucfirst.spec.
jest.mock('CoreHome', () => ({
  Tooltips: {},
  ucfirst: jest.fn((text?: string) => text ?? ''),
  NumberFormatter: {
    formatNumber: (value: number, precision: number) => `${value}#${precision}`,
  },
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const MetricValue = require('./MetricValue.vue').default;
// eslint-disable-next-line @typescript-eslint/no-var-requires
const ucfirstMock = require('CoreHome').ucfirst as jest.Mock;

describe('CoreVisualizations/MetricValue', () => {
  const originalDocumentLanguage = document.documentElement.lang;

  beforeEach(() => {
    document.documentElement.lang = 'en';
    ucfirstMock.mockClear();
  });

  afterAll(() => {
    document.documentElement.lang = originalDocumentLanguage;
  });

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

  it('capitalizes the title using the document language', () => {
    document.documentElement.lang = 'tr';

    mount(MetricValue as any, {
      props: {
        title: 'istanbul',
        value: '190',
      },
    });

    expect(ucfirstMock).toHaveBeenCalledWith('istanbul', 'tr');
  });

  it('locale-formats a raw numeric value (with precision 2) but leaves strings verbatim', () => {
    const wrapper = mount(MetricValue as any, {
      props: {
        title: 'Visits',
        value: 10558,
        secondaryValue: 9527,
      },
    });

    expect(wrapper.find('.metricValue__number').text()).toBe('10558#2');
    expect(wrapper.find('.metricValue__secondaryLine').text()).toBe('9527#2');
  });

  it('exposes the displayed value as the number tooltip (recoverable when truncated)', () => {
    const wrapper = mount(MetricValue as any, {
      props: {
        title: 'Visits',
        value: 10558,
      },
    });

    // The formatted value is mirrored into the title attribute so a clipped number stays readable on
    // hover, matching the title element's own tooltip.
    expect(wrapper.find('.metricValue__number').attributes('title')).toBe('10558#2');
  });

  it('renders the secondary value and label as one line', () => {
    const wrapper = mount(MetricValue as any, {
      props: {
        title: 'Visits',
        value: '10,558',
        secondaryValue: '9,527',
        secondaryLabel: 'unique visitors',
      },
    });

    expect(wrapper.find('.metricValue__secondary').exists()).toBe(true);
    expect(wrapper.find('.metricValue__secondaryLine').text()).toBe('9,527 unique visitors');
  });

  it('renders the secondary value without a label when no label is given', () => {
    const wrapper = mount(MetricValue as any, {
      props: {
        title: 'Visits',
        value: '10,558',
        secondaryValue: '9,527',
      },
    });

    expect(wrapper.find('.metricValue__secondaryLine').text()).toBe('9,527');
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

  it('falls back to the full title as the tooltip and sets no documented class without documentation', () => {
    const wrapper = mount(MetricValue as any, {
      props: {
        title: 'Searches',
        value: '190',
      },
    });

    const title = wrapper.find('.metricValue__title');
    expect(title.attributes('title')).toBe('Searches');
    expect(title.classes()).not.toContain('metricValue__title--documented');
  });

  it('omits the title element when no title is given (date-comparison value column)', () => {
    const wrapper = mount(MetricValue as any, {
      props: {
        value: '10,558',
      },
    });

    expect(wrapper.find('.metricValue__title').exists()).toBe(false);
    expect(wrapper.find('.metricValue__number').text()).toBe('10,558');
  });

  it('renders the value at a leading %s placeholder in the secondary label', () => {
    const wrapper = mount(MetricValue as any, {
      props: {
        title: 'Direct Entry',
        value: '4,242',
        secondaryValue: '12%',
        secondaryLabel: '%s of visits',
      },
    });

    expect(wrapper.find('.metricValue__secondaryLine').text()).toBe('12% of visits');
  });

  it('renders the value at a mid-string %s placeholder, preserving word order', () => {
    const wrapper = mount(MetricValue as any, {
      props: {
        title: 'Plays',
        value: '1,234',
        secondaryValue: '567',
        secondaryLabel: 'by %s unique visitors',
      },
    });

    expect(wrapper.find('.metricValue__secondaryLine').text()).toBe('by 567 unique visitors');
  });

  it('renders the value at a trailing %s placeholder (non-leading locales)', () => {
    const wrapper = mount(MetricValue as any, {
      props: {
        title: 'Direct Entry',
        value: '4,242',
        secondaryValue: '12%',
        secondaryLabel: 'foo %s',
      },
    });

    expect(wrapper.find('.metricValue__secondaryLine').text()).toBe('foo 12%');
  });

  it('substitutes a value containing $ without treating it as a regex backreference', () => {
    const wrapper = mount(MetricValue as any, {
      props: {
        title: 'Revenue',
        value: '4,242',
        secondaryValue: '$12',
        secondaryLabel: '%s of total',
      },
    });

    expect(wrapper.find('.metricValue__secondaryLine').text()).toBe('$12 of total');
  });

  it('preserves locale-significant whitespace in the value and translated label', () => {
    const wrapper = mount(MetricValue as any, {
      props: {
        title: 'Visits',
        value: '10,558',
        secondaryValue: '9\u202F527',
        secondaryLabel: '%s\u00A0visiteurs uniques',
      },
    });

    expect(wrapper.find('.metricValue__secondaryLine').element.textContent)
      .toBe('9\u202F527\u00A0visiteurs uniques');
  });

  it('leaves a placeholder-free title unchanged', () => {
    const wrapper = mount(MetricValue as any, {
      props: {
        title: 'Conversions',
        value: '190',
      },
    });

    expect(wrapper.find('.metricValue__title').text()).toBe('Conversions');
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
