/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// CoreHome has no jest module mapping, so virtual-mock it: Tooltips the directive the real
// MetricValue registers, and NumberFormatter to format numbers. The sparkline itself is rendered
// by the shell, not this body. ucfirst is an identity spy here; its casing behavior is
// covered by ucfirst.spec.
jest.mock('CoreHome', () => ({
  Tooltips: {},
  ucfirst: jest.fn((text?: string) => text ?? ''),
  NumberFormatter: {
    formatNumber: (value: number) => String(value),
  },
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const DateComparison = require('./DateComparison.vue').default;
// eslint-disable-next-line @typescript-eslint/no-var-requires
const ucfirstMock = require('CoreHome').ucfirst as jest.Mock;

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
    metricsOrder: ['Monday, May 4, 2026', 'Sunday, May 3, 2026'],
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
  const originalDocumentLanguage = document.documentElement.lang;

  beforeEach(() => {
    document.documentElement.lang = 'en';
    ucfirstMock.mockClear();
  });

  afterAll(() => {
    document.documentElement.lang = originalDocumentLanguage;
  });

  it('renders the metric name as the card title', () => {
    const wrapper = createWrapper();

    expect(wrapper.find('.sparklineDateComparison__title').text()).toBe('Visits');
  });

  it('capitalizes the metric title using the document language', () => {
    document.documentElement.lang = 'tr';

    createWrapper({
      metrics: {
        'Monday, May 4, 2026': [{ value: '10,558', description: 'istanbul', title: 'istanbul' }],
      },
      metricsOrder: ['Monday, May 4, 2026'],
      seriesIndices: [0],
    });

    expect(ucfirstMock).toHaveBeenCalledWith('istanbul', 'tr');
  });

  it('exposes the full title as a title attribute so a clipped metric name stays recoverable', () => {
    // The title is clamped to one line in DateComparison.less; the title attribute is the hover
    // fallback, matching MetricValue on the no-comparison card.
    const wrapper = createWrapper();

    expect(wrapper.find('.sparklineDateComparison__title').attributes('title')).toBe('Visits');
  });

  it('reads the card title from the first column in metricsOrder, not object-key order', () => {
    // Bare-integer labels: JS reorders Object.keys to ['2025','2026'], so Object.values()[0] would
    // read the 2025 column; metricsOrder pins the backend's first column (2026). (Real cards share
    // one title across columns; distinct titles here only to prove which column is read.)
    const wrapper = createWrapper({
      metrics: {
        2026: [{ value: '10,558', description: 'Visits', title: 'Visits 2026' }],
        2025: [{ value: '9,000', description: 'Visits', title: 'Visits 2025' }],
      },
      metricsOrder: ['2026', '2025'],
      seriesIndices: [0, 1],
    });

    expect(wrapper.find('.sparklineDateComparison__title').text()).toBe('Visits 2026');
  });

  it('renders one column per compared date with its date label', () => {
    const wrapper = createWrapper();

    const labels = wrapper.findAll('.dateAtom').map((node) => node.text());
    expect(labels).toEqual(['Monday, May 4, 2026', 'Sunday, May 3, 2026']);
    expect(wrapper.findAll('.periodColumns__column').length).toBe(2);
  });

  it('renders the primary and secondary value of each date column', () => {
    const wrapper = createWrapper();

    const columns = wrapper.findAll('.periodColumns__column');
    expect(columns[0].find('.metricValue__number').text()).toBe('10,558');
    expect(columns[0].find('.metricValue__secondaryLine').text()).toBe('9,527 unique visitors');
    expect(columns[1].find('.metricValue__number').text()).toBe('12,558');
    expect(columns[1].find('.metricValue__secondaryLine').text()).toBe('10,527 unique visitors');
  });

  it('renders an EvolutionBadge only for the date that has evolution data', () => {
    const wrapper = createWrapper();

    const badges = wrapper.findAllComponents({ name: 'EvolutionBadge' });
    expect(badges.length).toBe(1);
    expect(badges[0].props('percent')).toBe('+0.5%');
    expect(badges[0].props('trend')).toBe(53);
    expect(badges[0].props('tooltip')).toBe('since last period');

    // ...and it belongs to the first column.
    const columns = wrapper.findAll('.periodColumns__column');
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
      metricsOrder: ['Monday, May 4, 2026'],
      seriesIndices: [0],
    });

    expect(wrapper.findComponent({ name: 'EvolutionBadge' }).props('tooltip')).toBe('');
  });

  it('formats raw numeric metric values through NumberFormatter', () => {
    const wrapper = createWrapper({
      metrics: {
        'Monday, May 4, 2026': [{ value: 10558, description: 'Visits', title: 'Visits' }],
      },
      metricsOrder: ['Monday, May 4, 2026'],
      seriesIndices: [0],
    });

    expect(wrapper.find('.metricValue__number').text()).toBe('10558');
  });

  it('orders columns by metricsOrder, not object-key order, for bare-integer year labels', () => {
    // Bare year labels: JS re-sorts integer-like keys, so Object.keys(metrics) yields
    // ['2025','2026'] and swaps the columns; metricsOrder pins the backend order.
    const wrapper = createWrapper({
      metrics: {
        2026: [{ value: '10,558', description: 'Visits', title: 'Visits' }],
        2025: [{ value: '9,000', description: 'Visits', title: 'Visits' }],
      },
      metricsOrder: ['2026', '2025'],
      seriesIndices: [0, 1],
    });

    const labels = wrapper.findAll('.dateAtom').map((node) => node.text());
    expect(labels).toEqual(['2026', '2025']);

    const columns = wrapper.findAll('.periodColumns__column');
    expect(columns[0].find('.metricValue__number').text()).toBe('10,558');
    expect(columns[1].find('.metricValue__number').text()).toBe('9,000');
  });
});
