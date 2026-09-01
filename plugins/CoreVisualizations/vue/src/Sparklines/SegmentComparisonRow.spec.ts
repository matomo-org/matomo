/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { nextTick } from 'vue';
import { mount } from '@vue/test-utils';

// The row mounts the real MetricValue (Tooltips directive, NumberFormatter) and the reused
// Sparkline. CoreHome is a cross-plugin import the vitest config aliases to its source, so mock it here.
vi.mock('CoreHome', () => ({
  Tooltips: {},
  // ucfirst is mocked as an identity passthrough; its capitalization is covered by ucfirst.spec.
  ucfirst: (s?: string) => s ?? '',
  Sparkline: {
    name: 'Sparkline',
    props: ['params', 'seriesIndices', 'width', 'height'],
    // Declared like the real component, so @loading-change is treated as a listener instead of
    // ending up on the element as a stray attribute.
    emits: ['loadingChange'],
    template: '<img class="sparkline-stub" />',
  },
  NumberFormatter: {
    formatNumber: (value: number) => String(value),
  },
}));

import SegmentComparisonRow from './SegmentComparisonRow.vue';

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

// The row waits for its slot to have a size before rendering the sparkline, and jsdom reports 0
// for everything, so give the slot a width the way a real layout would.
function stubSlotWidth() {
  beforeEach(() => {
    vi.spyOn(Element.prototype, 'getBoundingClientRect')
      .mockReturnValue({ width: 420, height: 40 } as DOMRect);
  });

  afterEach(() => {
    // clearMocks is off in vitest.config.ts, so restore the spy by hand.
    vi.restoreAllMocks();
  });
}

describe('CoreVisualizations/SegmentComparisonRow', () => {
  stubSlotWidth();

  it('renders the segment name as a chip, with the full name as a hover-recovery title', () => {
    const chip = createWrapper().find('.sparklineSegmentComparisonRow__chip');

    expect(chip.text()).toBe('Eu visitors');
    expect(chip.attributes('title')).toBe('Eu visitors');
  });

  it('renders the primary and secondary metric values, formatting raw numbers', () => {
    const wrapper = createWrapper();

    expect(wrapper.find('.metricValue__number').text()).toBe('10558');
    expect(wrapper.find('.metricValue__secondaryLine').text()).toBe('9527 unique visitors');
  });

  it('omits the MetricValue title (the card shows the metric name once above the rows)', () => {
    expect(createWrapper().find('.metricValue__title').exists()).toBe(false);
  });

  it('renders its own single-series sparkline with the entry url and series index', async () => {
    // The slot is measured on mount, so the sparkline appears on the next tick.
    const wrapper = createWrapper();
    await nextTick();

    const sparkline = wrapper.findComponent({ name: 'Sparkline' });

    expect(sparkline.props('params')).toBe(
      '?module=API&action=get&columns=nb_visits&segment=continentCode==eur',
    );
    expect(sparkline.props('seriesIndices')).toEqual([1]);
    expect(sparkline.props('width')).toBe(420);
    expect(sparkline.props('height')).toBe(40);
  });

  it('renders the backend period tooltip as the sparkline slot title', () => {
    const tooltip = 'Each data point in the sparkline represents a day. '
      + 'Period: Mar 24. Period 2: Mar 17.';
    const slot = createWrapper({ segment: segment({ tooltip }) })
      .find('.sparklineSegmentComparisonRow__sparkline');

    expect(slot.attributes('title')).toBe(tooltip);
  });

  it('omits the title when the backend sends an empty tooltip', () => {
    const slot = createWrapper({ segment: segment({ tooltip: '' }) })
      .find('.sparklineSegmentComparisonRow__sparkline');

    expect(slot.attributes('title')).toBeUndefined();
  });

  it('omits the title when the entry carries no tooltip at all', () => {
    const slot = createWrapper().find('.sparklineSegmentComparisonRow__sparkline');

    expect(slot.attributes('title')).toBeUndefined();
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

// Segment + date: the same row, but the segment entry carries one value column per compared date
// (metricsOrder length > 1), a per-date evolution on the current period, and a multi-series sparkline.
function segmentDate(overrides = {}) {
  return {
    url: '?module=API&action=get&columns=nb_visits&segment=continentCode==eur&comparePeriods[]=range',
    metrics: {
      'Apr 23 - May 2, 2026': [
        {
          value: 23558,
          description: 'visits',
          title: 'Visits',
          column: '',
          group: 'Apr 23 - May 2, 2026',
          evolution: {
            percent: '+28.5%', trend: 5000, isLowerValueBetter: false, tooltip: 'more than before',
          },
        },
      ],
      'Mar 24 - Apr 2, 2026': [
        {
          value: 30119, description: 'visits', title: 'Visits', column: '', group: 'Mar 24 - Apr 2, 2026',
        },
      ],
    },
    metricsOrder: ['Apr 23 - May 2, 2026', 'Mar 24 - Apr 2, 2026'],
    order: 501,
    title: 'Eu visitors',
    group: '0',
    seriesIndices: [1, 3],
    graphParams: null,
    ...overrides,
  };
}

function createSegmentDateWrapper(props = {}) {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return mount(SegmentComparisonRow as any, { props: { segment: segmentDate(), ...props } });
}

describe('CoreVisualizations/SegmentComparisonRow segment + date', () => {
  stubSlotWidth();

  it('renders one value column per compared date, split by a separator', () => {
    const wrapper = createSegmentDateWrapper();

    expect(wrapper.findAll('.periodColumns__column')).toHaveLength(2);
    expect(wrapper.findAll('.periodColumns__separator')).toHaveLength(1);

    const numbers = wrapper.findAll('.metricValue__number').map((n) => n.text());
    expect(numbers).toEqual(['23558', '30119']);
  });

  it('labels each column with its compared date (only shown when comparing more than one date)', () => {
    const labels = createSegmentDateWrapper().findAll('.dateAtom').map((d) => d.text());

    expect(labels).toEqual(['Apr 23 - May 2, 2026', 'Mar 24 - Apr 2, 2026']);
  });

  it('shows the evolution badge only on the period that carries evolution (the current date)', () => {
    const wrapper = createSegmentDateWrapper();

    expect(wrapper.findAllComponents({ name: 'EvolutionBadge' })).toHaveLength(1);
    expect(wrapper.findComponent({ name: 'EvolutionBadge' }).props('percent')).toBe('+28.5%');
  });

  it('renders a multi-series sparkline (one series per compared date) sized from its slot', async () => {
    const wrapper = createSegmentDateWrapper();
    await nextTick();

    const sparkline = wrapper.findComponent({ name: 'Sparkline' });

    expect(sparkline.props('seriesIndices')).toEqual([1, 3]);
    // Segment + date rows sit in a wider card, so only their slot is wider.
    expect(sparkline.props('width')).toBe(420);
    expect(sparkline.props('height')).toBe(40);
    expect(sparkline.classes()).toContain('sparklineImg--fluid');
  });

  it('goes back to loading the moment a resize is observed, before the image is even requested', async () => {
    // Set when the resize is observed, not when the debounce fires: between those two the image
    // on screen is already the wrong size for its slot.
    const wrapper = createSegmentDateWrapper();
    await nextTick();
    await wrapper.findComponent({ name: 'Sparkline' }).vm.$emit('loadingChange', false);
    expect(wrapper.find('.sparklineSegmentComparisonRow__sparkline').classes())
      .not.toContain('sparklineSegmentComparisonRow__sparkline--loading');

    // The slot really has to change width: a reflow that leaves it alone must not blink the card.
    vi.spyOn(Element.prototype, 'getBoundingClientRect')
      .mockReturnValue({ width: 560, height: 40 } as DOMRect);
    const all = (window as unknown as { __resizeObservers: { trigger(e: unknown[]): void }[] })
      .__resizeObservers;
    all[all.length - 1].trigger([]);
    await nextTick();

    expect(wrapper.find('.sparklineSegmentComparisonRow__sparkline').classes())
      .toContain('sparklineSegmentComparisonRow__sparkline--loading');
  });

  it('shows the placeholder until the sparkline reports it has something to display', async () => {
    const wrapper = createSegmentDateWrapper();
    await nextTick();

    expect(wrapper.find('.sparklineSegmentComparisonRow__sparkline').classes())
      .toContain('sparklineSegmentComparisonRow__sparkline--loading');

    await wrapper.findComponent({ name: 'Sparkline' }).vm.$emit('loadingChange', false);

    expect(wrapper.find('.sparklineSegmentComparisonRow__sparkline').classes())
      .not.toContain('sparklineSegmentComparisonRow__sparkline--loading');
  });
});
