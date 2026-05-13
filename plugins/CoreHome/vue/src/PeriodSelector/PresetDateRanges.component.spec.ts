/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import { format } from '../Periods';
import PresetDateRanges from './PresetDateRanges.vue';

jest.mock('../translate', () => ({
  translate: (key: string) => {
    const messages: Record<string, string> = {
      General_DoubleClickToChangePeriod: 'Double click to change period',
    };

    return messages[key] || key;
  },
}));

interface PresetDateRangeSelection {
  id: string;
  period: string;
  date: string;
  startDate: Date;
  endDate: Date;
}

describe('PresetDateRanges', () => {
  function getSelectPayload(wrapper: ReturnType<typeof mount>): PresetDateRangeSelection {
    const events = wrapper.emitted('select') || [];
    return events[events.length - 1]?.[0] as PresetDateRangeSelection;
  }

  function mountComponent(customProps = {}) {
    return mount(PresetDateRanges, {
      props: {
        modelValue: null,
        checkedPresetId: null,
        minDate: new Date('2000-01-01'),
        maxDate: new Date('2100-12-31'),
        today: new Date('2026-02-16'),
        allowedPeriods: ['day', 'week', 'month', 'year', 'range'],
        ...customProps,
      },
    });
  }

  it('should render all preset options', () => {
    const wrapper = mountComponent();

    expect(wrapper.findAll('input[type="radio"]').length).toBe(13);
    expect(wrapper.findAll('.preset-date-range-group').length).toBe(4);
    expect(wrapper.findAll('.preset-date-range-group-separator').length).toBe(3);
  });

  it('should render only day presets when only day is allowed', () => {
    const wrapper = mountComponent({ allowedPeriods: ['day'] });

    const presetIds = wrapper.findAll('input[type="radio"]').map((input) => input.attributes('id'));
    expect(presetIds).toEqual(['preset_date_today', 'preset_date_yesterday']);
    expect(wrapper.findAll('.preset-date-range-group').length).toBe(1);
    expect(wrapper.findAll('.preset-date-range-group-separator').length).toBe(0);
  });

  it('should render only range presets when only range is allowed', () => {
    const wrapper = mountComponent({ allowedPeriods: ['range'] });

    const presetIds = wrapper.findAll('input[type="radio"]').map((input) => input.attributes('id'));
    expect(presetIds).toEqual([
      'preset_date_last7days',
      'preset_date_last30days',
      'preset_date_last90days',
      'preset_date_lastQuarter',
      'preset_date_thisQuarter',
    ]);
    expect(wrapper.findAll('.preset-date-range-group').length).toBe(3);
    expect(wrapper.findAll('.preset-date-range-group-separator').length).toBe(2);
  });

  it('should render day and range presets when only day and range are allowed', () => {
    const wrapper = mountComponent({ allowedPeriods: ['day', 'range'] });

    const presetIds = wrapper.findAll('input[type="radio"]').map((input) => input.attributes('id'));
    expect(presetIds).toEqual([
      'preset_date_today',
      'preset_date_yesterday',
      'preset_date_last7days',
      'preset_date_last30days',
      'preset_date_last90days',
      'preset_date_lastQuarter',
      'preset_date_thisQuarter',
    ]);
  });

  it('should emit update:modelValue and select payload when preset is selected', async () => {
    const wrapper = mountComponent();

    await wrapper.find('#preset_date_lastMonth').trigger('change');

    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['lastMonth']);

    const selectPayload = getSelectPayload(wrapper);
    expect(selectPayload.id).toBe('lastMonth');
    expect(selectPayload.period).toBe('month');
    expect(selectPayload.date).toBe('lastmonth');
    expect(format(selectPayload.startDate)).toBe('2026-01-01');
    expect(format(selectPayload.endDate)).toBe('2026-01-31');
  });

  it('should emit explicit date ranges for range presets', async () => {
    const wrapper = mountComponent();

    await wrapper.find('#preset_date_last7days').trigger('change');

    const selectPayload = getSelectPayload(wrapper);
    expect(selectPayload.period).toBe('range');
    expect(selectPayload.date).toBe('last7');
  });

  it('should emit dblclick payload for presets', async () => {
    const wrapper = mountComponent();

    await wrapper.find('#preset_date_last7days').trigger('dblclick');

    expect(wrapper.emitted('dblclick')?.[0]?.[0]).toMatchObject({
      id: 'last7days',
      period: 'range',
      date: 'last7',
    });
  });

  it('should resolve monday/sunday week behavior correctly', async () => {
    const wrapper = mountComponent({ today: new Date('2026-02-15') });

    await wrapper.find('#preset_date_lastWeekMonSun').trigger('change');

    const selectPayload = getSelectPayload(wrapper);
    expect(format(selectPayload.startDate)).toBe('2026-02-02');
    expect(format(selectPayload.endDate)).toBe('2026-02-08');
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
    expect(format(selectPayload.startDate)).toBe('2026-02-14');
    expect(format(selectPayload.endDate)).toBe('2026-02-15');
    expect(selectPayload.date).toBe('last7');
  });

  it('should resolve period and date values for all presets', async () => {
    const testCases = [
      { id: 'today', period: 'day', date: 'today' },
      { id: 'yesterday', period: 'day', date: 'yesterday' },
      { id: 'last7days', period: 'range', date: 'last7' },
      { id: 'last30days', period: 'range', date: 'last30' },
      { id: 'last90days', period: 'range', date: 'last90' },
      { id: 'lastWeekMonSun', period: 'week', date: 'lastweek' },
      { id: 'lastMonth', period: 'month', date: 'lastmonth' },
      { id: 'lastQuarter', period: 'range', date: '2025-10-01,2025-12-31' },
      { id: 'lastYear', period: 'year', date: 'lastyear' },
      // Intentional compatibility behavior: week + today remains canonical here.
      { id: 'thisWeekMonToday', period: 'week', date: 'today' },
      { id: 'thisMonth', period: 'month', date: 'today' },
      { id: 'thisQuarter', period: 'range', date: '2026-01-01,2026-02-16' },
      { id: 'thisYear', period: 'year', date: 'today' },
    ];

    const wrapper = mountComponent();

    for (const testCase of testCases) {
      await wrapper.find(`#preset_date_${testCase.id}`).trigger('change');
      const selectPayload = getSelectPayload(wrapper);
      expect(selectPayload.period).toBe(testCase.period);
      expect(selectPayload.date).toBe(testCase.date);
    }
  });

  it('should throw for an unknown preset id', () => {
    const wrapper = mountComponent();

    expect(() => (wrapper.vm as unknown as { handlePresetSelected: (id: string) => void })
      .handlePresetSelected('unknown')).toThrow('Unknown preset date range: unknown');
  });

  it('should check presets only when preset owner is active', async () => {
    const wrapper = mountComponent({
      modelValue: 'lastMonth',
      checkedPresetId: null,
    });

    expect((wrapper.find('#preset_date_lastMonth').element as HTMLInputElement).checked).toBe(false);

    await wrapper.setProps({ checkedPresetId: 'lastMonth' });

    expect((wrapper.find('#preset_date_lastMonth').element as HTMLInputElement).checked).toBe(true);
  });

  it('should set empty tooltip for checked preset and non-empty for others', () => {
    const wrapper = mountComponent({
      checkedPresetId: 'today',
    });

    const todayLabel = wrapper.find('#preset_date_today').element.parentElement as HTMLLabelElement;
    const yesterdayLabel = wrapper.find('#preset_date_yesterday').element.parentElement as HTMLLabelElement;

    expect(todayLabel.title).toBe('');
    expect(yesterdayLabel.title).toBe('Double click to change period');
  });
});
