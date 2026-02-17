/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

const PresetDateRanges = require('./PresetDateRanges.vue').default;

interface PresetDateRangeSelection {
  id: string;
  period: string;
  date: string;
  startDate: Date;
  endDate: Date;
}

describe('CoreHome/PeriodSelector/PresetDateRanges component', () => {
  function getSelectPayload(wrapper: ReturnType<typeof mount>): PresetDateRangeSelection {
    return wrapper.emitted('select')?.[0]?.[0] as PresetDateRangeSelection;
  }

  function mountComponent(customProps = {}) {
    return mount(PresetDateRanges, {
      props: {
        modelValue: null,
        minDate: new Date('2000-01-01'),
        maxDate: new Date('2100-12-31'),
        today: new Date('2026-02-16'),
        ...customProps,
      },
    });
  }

  it('should render all preset options', () => {
    const wrapper = mountComponent();

    expect(wrapper.findAll('input[type="radio"]').length).toBe(13);
  });

  it('should emit update:modelValue and select payload when preset is selected', async () => {
    const wrapper = mountComponent();

    await wrapper.find('#preset_date_lastMonth').trigger('change');

    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['lastMonth']);

    const selectPayload = getSelectPayload(wrapper);
    expect(selectPayload.id).toBe('lastMonth');
    expect(selectPayload.period).toBe('month');
    expect(selectPayload.date).toBe('lastmonth');
    expect(selectPayload.startDate.toISOString().substring(0, 10)).toBe('2026-01-01');
    expect(selectPayload.endDate.toISOString().substring(0, 10)).toBe('2026-01-31');
  });

  it('should emit explicit date ranges for range presets', async () => {
    const wrapper = mountComponent();

    await wrapper.find('#preset_date_last7days').trigger('change');

    const selectPayload = getSelectPayload(wrapper);
    expect(selectPayload.period).toBe('range');
    expect(selectPayload.date).toBe('2026-02-10,2026-02-16');
  });

  it('should resolve monday/sunday week behavior correctly', async () => {
    const wrapper = mountComponent({ today: new Date('2026-02-15') });

    await wrapper.find('#preset_date_lastWeekMonSun').trigger('change');

    const selectPayload = getSelectPayload(wrapper);
    expect(selectPayload.startDate.toISOString().substring(0, 10)).toBe('2026-02-02');
    expect(selectPayload.endDate.toISOString().substring(0, 10)).toBe('2026-02-08');
  });

  it('should resolve quarter boundary behavior correctly', async () => {
    const wrapper = mountComponent({ today: new Date('2026-04-01') });

    await wrapper.find('#preset_date_lastQuarter').trigger('change');

    const selectPayload = getSelectPayload(wrapper);
    expect(selectPayload.period).toBe('range');
    expect(selectPayload.date).toBe('2026-01-01,2026-03-31');
  });

  it('should clamp payload date range to min/max dates', async () => {
    const wrapper = mountComponent({
      minDate: new Date('2026-02-14'),
      maxDate: new Date('2026-02-15'),
      today: new Date('2026-02-16'),
    });

    await wrapper.find('#preset_date_last7days').trigger('change');

    const selectPayload = getSelectPayload(wrapper);
    expect(selectPayload.startDate.toISOString().substring(0, 10)).toBe('2026-02-14');
    expect(selectPayload.endDate.toISOString().substring(0, 10)).toBe('2026-02-15');
  });
});
