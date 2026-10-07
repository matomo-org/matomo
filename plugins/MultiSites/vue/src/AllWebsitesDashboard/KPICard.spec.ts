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

function tooltipContent(modelValue: Record<string, unknown>): string {
  const wrapper = mount(KPICard, {
    props: { modelValue },
    global: { mocks: { translate: (key: string) => key } },
  });

  return (wrapper.vm as unknown as { tooltipContent: () => string }).tooltipContent();
}

describe('MultiSites/KPICard.vue', () => {
  it('shows the exact value in the tooltip', () => {
    const content = tooltipContent(kpi);

    expect(content).toContain('General_ColumnHits');
    expect(content).toContain('2,912,345');
  });

  it('shows the exact value along with the description when the card has one', () => {
    const content = tooltipContent({ ...kpi, tooltipBody: 'MultiSites_TotalHitsIncludingAiTooltip' });

    expect(content).toContain('2,912,345');
    expect(content).toContain('MultiSites_TotalHitsIncludingAiTooltip');
  });
});
