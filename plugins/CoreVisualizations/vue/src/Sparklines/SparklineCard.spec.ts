/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { nextTick } from 'vue';
import { mount } from '@vue/test-utils';

// The shell renders the real NoComparison body, which mounts the real MetricValue (Tooltips
// directive) and the Sparkline. CoreHome is aliased to its source by the vitest config, so mock it.
vi.mock('CoreHome', () => ({
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
}));

import SparklineCard from './SparklineCard.vue';

describe('CoreVisualizations/SparklineCard', () => {
  // The card measures its sparkline slot and holds the sparkline back until it has a size; jsdom
  // reports 0 for every element, so give the slot a width the way a real layout would.
  beforeEach(() => {
    vi.spyOn(Element.prototype, 'getBoundingClientRect')
      .mockReturnValue({ width: 420, height: 40 } as DOMRect);
  });

  afterEach(() => {
    // vitest.config.ts sets clearMocks: false, so the rect spy has to be restored explicitly.
    vi.restoreAllMocks();
  });

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

  it('renders the card frame classes and composes the primary value + sparkline', async () => {
    const wrapper = createWrapper();
    await nextTick();

    expect(wrapper.classes()).toContain('sparkline');
    expect(wrapper.classes()).toContain('sparklineCard');
    expect(wrapper.classes()).not.toContain('notLinkable');
    expect(wrapper.find('.metricValue__title').text()).toBe('Visits');
    expect(wrapper.find('.metricValue__number').text()).toBe('1,234');
    expect(wrapper.find('.sparkline-stub').exists()).toBe(true);
  });

  it('renders the shared sparkline slot, forwarding the entry url and series indices', async () => {
    // The shell owns the single sparkline for both bodies; here the comparison entry carries a
    // series index per compared date.
    const wrapper = createWrapper(comparisonSparkline);
    await nextTick();

    expect(wrapper.find('.sparklineCard__sparkline').exists()).toBe(true);
    const sparkline = wrapper.findComponent({ name: 'Sparkline' });
    expect(sparkline.props('params')).toBe(
      '?module=API&action=get&columns=nb_visits&compareDates[]=2026-05-03',
    );
    expect(sparkline.props('seriesIndices')).toEqual([0, 1]);
  });

  it('sizes the sparkline from the measured slot, the same way in both modes', async () => {
    // Both modes take the width from the slot they are given, so a comparison card's wider slot is
    // the only thing that makes its sparkline wider — no per-mode constant to keep in sync.
    const plain = createWrapper();
    const comparing = createWrapper(comparisonSparkline);
    await nextTick();

    const plainSparkline = plain.findComponent({ name: 'Sparkline' });
    expect(plainSparkline.props('width')).toBe(420);
    expect(plainSparkline.props('height')).toBe(40);

    const comparingSparkline = comparing.findComponent({ name: 'Sparkline' });
    expect(comparingSparkline.props('width')).toBe(420);
    expect(comparingSparkline.props('height')).toBe(40);
  });

  it('publishes the measured size as the custom properties the stylesheet draws the image at', async () => {
    const wrapper = createWrapper();
    await nextTick();

    const slot = wrapper.find('.sparklineCard__sparkline');
    expect(slot.attributes('style')).toContain('--sparklineCard-img-width: 420px');
    expect(slot.attributes('style')).toContain('--sparklineCard-img-height: 40px');
  });

  it('keeps the slot but holds the sparkline back while the card has no measurable width', async () => {
    // A card inside a collapsed widget or an inactive reporting tab.
    vi.spyOn(Element.prototype, 'getBoundingClientRect')
      .mockReturnValue({ width: 0, height: 0 } as DOMRect);

    const wrapper = createWrapper();
    await nextTick();

    expect(wrapper.find('.sparklineCard__sparkline').exists()).toBe(true);
    expect(wrapper.findComponent({ name: 'Sparkline' }).exists()).toBe(false);
  });

  it('renders the backend period tooltip as the sparkline slot title', () => {
    const tooltip = 'Each data point in the sparkline represents a day. '
      + 'Period: Mar 24. Period 2: Mar 17.';
    const wrapper = createWrapper({ ...comparisonSparkline, tooltip });

    expect(wrapper.find('.sparklineCard__sparkline').attributes('title')).toBe(tooltip);
  });

  it('omits the title when the backend sends an empty tooltip', () => {
    const wrapper = createWrapper({ ...baseSparkline, tooltip: '' });

    expect(wrapper.find('.sparklineCard__sparkline').attributes('title')).toBeUndefined();
  });

  it('omits the title when the entry carries no tooltip at all', () => {
    const wrapper = createWrapper();

    expect(wrapper.find('.sparklineCard__sparkline').attributes('title')).toBeUndefined();
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
