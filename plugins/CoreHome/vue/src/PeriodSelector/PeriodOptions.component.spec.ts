/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import PeriodOptions from './PeriodOptions.vue';

jest.mock('../translate', () => ({
  translate: (key: string) => {
    const messages: Record<string, string> = {
      Intl_PeriodDay: 'day',
      Intl_PeriodWeek: 'week',
      Intl_PeriodMonth: 'month',
      Intl_PeriodYear: 'year',
      General_DateRangeInPeriodList: 'date range',
      General_Custom: 'Custom',
      General_ChoosePeriod: 'Choose period',
      General_DoubleClickToChangePeriod: 'Double click to change period',
    };

    return messages[key] || key;
  },
}));

describe('PeriodOptions', () => {
  function mountComponent(customProps = {}) {
    return mount(PeriodOptions, {
      props: {
        modelValue: 'day',
        periods: ['day', 'week', 'month', 'year', 'range'],
        checkedPeriodId: 'day',
        activeDatePeriod: 'day',
        ...customProps,
      },
    });
  }

  it('should render all provided period options', () => {
    const wrapper = mountComponent();

    expect(wrapper.findAll('.periodOptions button').length).toBe(5);
    expect(wrapper.findAll('.periodOptions button')[0].attributes('id')).toBe('period_id_range');
  });

  it('should expose group semantics for period options', () => {
    const wrapper = mountComponent();
    const optionsGroup = wrapper.find('.periodOptions');

    expect(optionsGroup.attributes('role')).toBe('group');
    expect(optionsGroup.attributes('aria-label')).toBe('Choose period');
  });

  it('should capitalize period labels and show custom date range for range', () => {
    const wrapper = mountComponent();

    const dayText = wrapper.find('#period_id_day').text().trim();
    const rangeText = wrapper.find('#period_id_range').text().trim();

    expect(dayText?.charAt(0)).toBe(dayText?.charAt(0)?.toUpperCase());
    expect(rangeText).toBe('Custom date range');
  });

  it('should emit update:modelValue and select when period is selected', async () => {
    const wrapper = mountComponent();

    await wrapper.find('#period_id_month').trigger('click');

    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['month']);
    expect(wrapper.emitted('select')?.[0]).toEqual([{ period: 'month' }]);
  });

  it('should emit dblclick payload', async () => {
    const wrapper = mountComponent();

    await wrapper.find('#period_id_week').trigger('dblclick');

    expect(wrapper.emitted('dblclick')?.[0]).toEqual([{ period: 'week' }]);
  });

  it('should render native button options', () => {
    const wrapper = mountComponent();

    const options = wrapper.findAll('.periodOptions button');
    expect(options.length).toBe(5);
    options.forEach((option) => {
      expect(option.attributes('type')).toBe('button');
    });
  });

  it('should expose selected state with aria-pressed', async () => {
    const wrapper = mountComponent({
      checkedPeriodId: 'day',
    });

    expect(wrapper.find('#period_id_day').attributes('aria-pressed')).toBe('true');
    expect(wrapper.find('#period_id_week').attributes('aria-pressed')).toBe('false');

    await wrapper.setProps({ checkedPeriodId: 'week' });

    expect(wrapper.find('#period_id_day').attributes('aria-pressed')).toBe('false');
    expect(wrapper.find('#period_id_week').attributes('aria-pressed')).toBe('true');
  });

  it('should check only when owner is active', async () => {
    const wrapper = mountComponent({
      modelValue: 'month',
      checkedPeriodId: null,
    });

    expect(wrapper.find('#period_id_month').classes()).not.toContain('selected-period-label');

    await wrapper.setProps({ checkedPeriodId: 'month' });

    expect(wrapper.find('#period_id_month').classes()).toContain('selected-period-label');
  });

  it('should set empty tooltip for active date period and non-empty for others', () => {
    const wrapper = mountComponent({
      modelValue: 'day',
      activeDatePeriod: 'day',
    });

    const dayLabel = wrapper.find('#period_id_day').element as HTMLButtonElement;
    const weekLabel = wrapper.find('#period_id_week').element as HTMLButtonElement;

    expect(dayLabel.title).toBe('');
    expect(weekLabel.title).not.toBe('');
  });
});
