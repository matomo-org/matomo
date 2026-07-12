/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// The card mounts the real SegmentComparisonRow -> MetricValue + Sparkline chain and derives its
// data-graph-params via MatomoUrl.parse. CoreHome has no jest module mapping, so mock everything
// that chain pulls from it.
jest.mock('CoreHome', () => ({
  Tooltips: {},
  Sparkline: {
    name: 'Sparkline',
    props: ['params', 'seriesIndices', 'width', 'height'],
    template: '<img class="sparkline-stub" />',
  },
  MatomoUrl: {
    parse: (search: string) => {
      const params: Record<string, string> = {};
      new URLSearchParams(search).forEach((value, key) => {
        params[key] = value;
      });
      return params;
    },
  },
  NumberFormatter: {
    formatNumber: (value: number) => String(value),
  },
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const SegmentComparisonCard = require('./SegmentComparisonCard.vue').default;

function segment(title: string, seriesIndex: number, value: number) {
  return {
    url: '?module=API&action=get&columns=nb_visits',
    metrics: {
      'Jan 12 - 17, 2012': [
        {
          value, description: 'visits', title: 'Visits', column: 'nb_visits',
        },
        {
          value: 0, description: 'unique visitors', title: 'Unique visitors', column: 'nb_uniq_visitors',
        },
      ],
    },
    metricsOrder: ['Jan 12 - 17, 2012'],
    order: seriesIndex,
    title,
    group: '0',
    seriesIndices: [seriesIndex],
    graphParams: null,
  };
}

function createWrapper(props = {}) {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return mount(SegmentComparisonCard as any, {
    props: {
      segments: [segment('All visits', 0, 10558), segment('Eu visitors', 1, 12558)],
      ...props,
    },
  });
}

describe('CoreVisualizations/SegmentComparisonCard', () => {
  it('shows the metric name once as the card title, not repeated per row', () => {
    const wrapper = createWrapper();

    expect(wrapper.find('.sparklineSegmentComparisonCard__title').text()).toBe('Visits');
    // The rows use MetricValue with no title, so the metric name is not repeated.
    expect(wrapper.findAll('.metricValue__title').length).toBe(0);
  });

  it('is the single .sparkline click-to-evolution link, carrying the metric graph params', () => {
    const wrapper = createWrapper();

    expect(wrapper.classes()).toContain('sparkline');
    expect(wrapper.classes()).toContain('sparklineSegmentComparisonCard');
    expect(wrapper.classes()).not.toContain('notLinkable');
    // The rows are presentational, not links.
    expect(wrapper.findAll('.sparklineSegmentComparisonRow.sparkline').length).toBe(0);
    // Reload params come from the shared metric columns; series indices are the union per segment.
    expect(wrapper.attributes('data-graph-params')).toBe('{"columns":"nb_visits"}');
    expect(wrapper.attributes('data-series-indices')).toBe('[0,1]');
  });

  it('stacks one row per compared segment, each with its chip and value', () => {
    const wrapper = createWrapper();

    expect(wrapper.findAllComponents({ name: 'SegmentComparisonRow' }).length).toBe(2);
    expect(wrapper.findAll('.sparklineSegmentComparisonRow__chip').map((node) => node.text()))
      .toEqual(['All visits', 'Eu visitors']);
    expect(wrapper.findAll('.metricValue__number').map((node) => node.text()))
      .toEqual(['10558', '12558']);
  });

  it('surfaces the metric documentation as the card-title tooltip', () => {
    // Columns are populated in segment comparison (unlike date comparison), so the doc resolves.
    const wrapper = createWrapper({ allMetricsDocumentation: { nb_visits: 'The number of visits.' } });
    const title = wrapper.find('.sparklineSegmentComparisonCard__title');

    expect(title.attributes('title')).toBe('The number of visits.');
    expect(title.classes()).toContain('sparklineSegmentComparisonCard__title--documented');
  });

  it('falls back to the metric name as the title tooltip when undocumented', () => {
    const title = createWrapper().find('.sparklineSegmentComparisonCard__title');

    expect(title.attributes('title')).toBe('Visits');
    expect(title.classes()).not.toContain('sparklineSegmentComparisonCard__title--documented');
  });

  it('marks the whole card notLinkable when sparklines are not linkable', () => {
    const wrapper = createWrapper({ areSparklinesLinkable: false });

    // The gate is on the card (the single link), not on individual rows.
    expect(wrapper.classes()).toContain('notLinkable');
    expect(wrapper.findAll('.sparklineSegmentComparisonRow.notLinkable').length).toBe(0);
  });
});
