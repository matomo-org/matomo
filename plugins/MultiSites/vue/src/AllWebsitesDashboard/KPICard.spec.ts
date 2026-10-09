/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

vi.mock('CoreHome', () => ({
  Tooltips: {},
}));

import KPICard from './KPICard.vue';

const kpi = {
  icon: 'icon-hits',
  title: 'General_ColumnHits',
  value: '2,912,345',
  valueCompact: '2.9M',
  evolutionPeriod: 'day',
  evolutionTrend: 0,
  evolutionValue: '',
};

function tooltipText(modelValue: Record<string, unknown>): string {
  const wrapper = mount(KPICard, {
    props: { modelValue },
    global: { mocks: { translate: (key: string) => key } },
  });

  return wrapper.find('[role="tooltip"]').text();
}

describe('MultiSites/KPICard.vue', () => {
  it('shows the exact value in the tooltip', () => {
    const text = tooltipText(kpi);

    expect(text).toContain('General_ColumnHits');
    expect(text).toContain('2,912,345');
  });

  it('shows the exact value before the description when the card has one', () => {
    const text = tooltipText({ ...kpi, tooltipBody: 'MultiSites_HitsIncludingAiTooltip' });

    expect(text).toContain('2,912,345');
    expect(text.indexOf('2,912,345'))
      .toBeLessThan(text.indexOf('MultiSites_HitsIncludingAiTooltip'));
  });
});
