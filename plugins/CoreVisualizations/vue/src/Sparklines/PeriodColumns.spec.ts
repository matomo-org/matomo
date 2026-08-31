/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// PeriodColumns mounts the real MetricValue (Tooltips directive) + DateAtom + EvolutionBadge chain.
// CoreHome has no jest module mapping, so virtual-mock what that chain imports from it.
jest.mock('CoreHome', () => ({
  Tooltips: {},
  // ucfirst is mocked as an identity passthrough; its capitalization is covered by ucfirst.spec.
  ucfirst: (s?: string) => s ?? '',
  NumberFormatter: {
    formatNumber: (value: number) => String(value),
  },
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const PeriodColumns = require('./PeriodColumns.vue').default;

// A SparklineEntry as it reaches PeriodColumns: metric groups keyed by period label, ordered by
// metricsOrder. PeriodColumns derives its columns (primary + optional secondary) from this.
function multiPeriodEntry() {
  return {
    metricsOrder: ['Apr 23 - May 2, 2026', 'Mar 24 - Apr 2, 2026'],
    metrics: {
      'Apr 23 - May 2, 2026': [
        {
          value: '23,558',
          evolution: {
            percent: '+28.5%', trend: 5000, isLowerValueBetter: false, tooltip: 'more than before',
          },
        },
        { value: '9,527', description: 'unique visitors' },
      ],
      'Mar 24 - Apr 2, 2026': [
        { value: '30,119' },
      ],
    },
  };
}

function createWrapper(entry: Record<string, unknown> = multiPeriodEntry()) {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return mount(PeriodColumns as any, { props: { entry } });
}

describe('CoreVisualizations/PeriodColumns', () => {
  it('renders one column per period, in order, split by a separator between columns', () => {
    const wrapper = createWrapper();

    expect(wrapper.findAll('.periodColumns__column')).toHaveLength(2);
    expect(wrapper.findAll('.periodColumns__separator')).toHaveLength(1);

    const values = wrapper.findAll('.metricValue__number').map((n) => n.text());
    expect(values).toEqual(['23,558', '30,119']);
  });

  it('labels each column with its period when comparing more than one period', () => {
    const labels = createWrapper().findAll('.dateAtom').map((d) => d.text());

    expect(labels).toEqual(['Apr 23 - May 2, 2026', 'Mar 24 - Apr 2, 2026']);
  });

  it('renders the primary and secondary value of a column', () => {
    const first = createWrapper().findAll('.periodColumns__column')[0];

    expect(first.find('.metricValue__number').text()).toBe('23,558');
    expect(first.find('.metricValue__secondaryLine').text()).toBe('9,527 unique visitors');
  });

  it('renders an EvolutionBadge only for the period that carries evolution', () => {
    const wrapper = createWrapper();
    const badges = wrapper.findAllComponents({ name: 'EvolutionBadge' });

    expect(badges).toHaveLength(1);
    expect(badges[0].props('percent')).toBe('+28.5%');

    const columns = wrapper.findAll('.periodColumns__column');
    expect(columns[0].findComponent({ name: 'EvolutionBadge' }).exists()).toBe(true);
    expect(columns[1].findComponent({ name: 'EvolutionBadge' }).exists()).toBe(false);
  });

  it('renders a single column with no date label and no separator (one period)', () => {
    const wrapper = createWrapper({
      metricsOrder: ['Jan 12 - 17, 2012'],
      metrics: { 'Jan 12 - 17, 2012': [{ value: '10,558' }] },
    });

    expect(wrapper.findAll('.periodColumns__column')).toHaveLength(1);
    expect(wrapper.findAll('.periodColumns__separator')).toHaveLength(0);
    expect(wrapper.findAll('.dateAtom')).toHaveLength(0);
    expect(wrapper.find('.metricValue__number').text()).toBe('10,558');
  });
});
