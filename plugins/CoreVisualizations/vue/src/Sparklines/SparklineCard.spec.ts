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
  // ucfirst is mocked as an identity passthrough; its capitalization is covered by ucfirst.spec.
  ucfirst: (s?: string) => s ?? '',
  Sparkline: {
    name: 'Sparkline',
    props: ['params', 'seriesIndices', 'width', 'height'],
    template: '<img class="sparkline-stub" />',
  },
  // SparklineCard derives graph-params from the sparkline url; parse a query string like the real
  // MatomoUrl.parse (which receives the query string without its leading '?').
  MatomoUrl: {
    parse: (search: string) => {
      const params: Record<string, string> = {};
      new URLSearchParams(search).forEach((value, key) => {
        params[key] = value;
      });
      return params;
    },
  },
  // The DateComparison body (rendered for comparison entries) formats raw numeric metric values.
  NumberFormatter: {
    formatNumber: (value: number) => String(value),
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

  const comparisonSparkline = {
    url: '?module=API&action=get&columns=nb_visits&compareDates[]=2026-05-03',
    metrics: {
      'Monday, May 4, 2026': [{ value: '10,558', description: 'Visits', title: 'Visits' }],
      'Sunday, May 3, 2026': [{ value: '12,558', description: 'Visits', title: 'Visits' }],
    },
    metricsOrder: ['Monday, May 4, 2026', 'Sunday, May 3, 2026'],
    order: 1,
    title: null,
    group: '0',
    seriesIndices: [0, 1],
    graphParams: null,
  };

  it('renders the no-comparison body and forwards the sparkline to it', () => {
    const wrapper = createWrapper();

    const body = wrapper.findComponent({ name: 'NoComparison' });
    expect(body.exists()).toBe(true);
    expect(body.props('sparkline')).toEqual(baseSparkline);
    expect(wrapper.findComponent({ name: 'DateComparison' }).exists()).toBe(false);
  });

  it('renders the date-comparison body for entries carrying series indices', () => {
    const wrapper = createWrapper(comparisonSparkline);

    const body = wrapper.findComponent({ name: 'DateComparison' });
    expect(body.exists()).toBe(true);
    expect(body.props('sparkline')).toEqual(comparisonSparkline);
    expect(wrapper.findComponent({ name: 'NoComparison' }).exists()).toBe(false);
    expect(wrapper.find('.sparklineDateComparison__title').text()).toBe('Visits');
    expect(wrapper.findAll('.dateAtom').length).toBe(2);
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

  it('renders the shared sparkline slot, forwarding the entry url and series indices', () => {
    // The shell owns the single sparkline for both bodies; here the comparison entry carries a
    // series index per compared date.
    const wrapper = createWrapper(comparisonSparkline);

    expect(wrapper.find('.sparklineCard__sparkline').exists()).toBe(true);
    const sparkline = wrapper.findComponent({ name: 'Sparkline' });
    expect(sparkline.props('params')).toBe(
      '?module=API&action=get&columns=nb_visits&compareDates[]=2026-05-03',
    );
    expect(sparkline.props('seriesIndices')).toEqual([0, 1]);
  });

  it('sizes the shared sparkline per mode — wider (760) when comparing, 380 otherwise', () => {
    const plain = createWrapper();
    const plainSparkline = plain.findComponent({ name: 'Sparkline' });
    expect(plainSparkline.props('width')).toBe(380);
    expect(plainSparkline.props('height')).toBe(40);
    expect(plain.find('.sparklineCard__sparkline--wide').exists()).toBe(false);

    const comparing = createWrapper(comparisonSparkline);
    const comparingSparkline = comparing.findComponent({ name: 'Sparkline' });
    expect(comparingSparkline.props('width')).toBe(760);
    expect(comparingSparkline.props('height')).toBe(40);
    expect(comparing.find('.sparklineCard__sparkline--wide').exists()).toBe(true);
  });

  it('does not render the segment title region in no-comparison mode', () => {
    const wrapper = createWrapper();

    expect(wrapper.find('.sparklineCard__title').exists()).toBe(false);
  });

  it('renders the segment title region when sparkline.title is set', () => {
    const wrapper = createWrapper({ ...baseSparkline, title: 'Firefox' });

    expect(wrapper.find('.sparklineCard__title').text()).toBe('Firefox');
  });

  it('omits graph-params / series-indices when none is set nor derivable from the url', () => {
    const wrapper = createWrapper({ ...baseSparkline, url: '?module=API&action=get' });

    expect(wrapper.attributes('data-graph-params')).toBeUndefined();
    expect(wrapper.attributes('data-series-indices')).toBeUndefined();
  });

  it('derives graph-params columns/rows/idGoal from the url when graphParams is empty', () => {
    // The reused Sparkline renders its image with `src` (no `data-src`), so the legacy click
    // handler can't read the reload columns off the img; the card supplies them from its url.
    const wrapper = createWrapper({
      ...baseSparkline,
      url: '?module=API&action=get&columns=nb_visits&rows=Search&idGoal=1',
    });

    expect(wrapper.attributes('data-graph-params')).toBe(
      '{"columns":"nb_visits","rows":"Search","idGoal":"1"}',
    );
  });

  it('emits explicit graphParams verbatim, taking precedence over the url', () => {
    const wrapper = createWrapper({
      ...baseSparkline,
      // url carries nb_visits, but explicit graphParams wins.
      graphParams: { columns: 'nb_actions' },
      seriesIndices: [0, 1],
    });

    expect(wrapper.attributes('data-graph-params')).toBe('{"columns":"nb_actions"}');
    expect(wrapper.attributes('data-series-indices')).toBe('[0,1]');
  });

  it('adds the notLinkable class when sparklines are not linkable', () => {
    const wrapper = createWrapper(baseSparkline, false);

    expect(wrapper.classes()).toContain('notLinkable');
  });
});
