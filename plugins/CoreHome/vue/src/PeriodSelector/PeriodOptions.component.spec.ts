/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

const PeriodOptions = require('./PeriodOptions.vue').default;

describe('CoreHome/PeriodSelector/PeriodOptions component', () => {
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

    expect(wrapper.findAll('input[type="radio"]').length).toBe(5);
  });

  it('should emit update:modelValue and select when period is selected', async () => {
    const wrapper = mountComponent();

    await wrapper.find('#period_id_month').trigger('change');

    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['month']);
    expect(wrapper.emitted('select')?.[0]).toEqual([{ period: 'month' }]);
  });

  it('should emit dblclick payload', async () => {
    const wrapper = mountComponent();

    await wrapper.find('#period_id_week').trigger('dblclick');

    expect(wrapper.emitted('dblclick')?.[0]).toEqual([{ period: 'week' }]);
  });

  it('should check only when owner is active', async () => {
    const wrapper = mountComponent({
      modelValue: 'month',
      checkedPeriodId: null,
    });

    expect((wrapper.find('#period_id_month').element as HTMLInputElement).checked).toBe(false);

    await wrapper.setProps({ checkedPeriodId: 'month' });

    expect((wrapper.find('#period_id_month').element as HTMLInputElement).checked).toBe(true);
  });

  it('should set empty tooltip for active date period and non-empty for others', () => {
    const wrapper = mountComponent({
      modelValue: 'day',
      activeDatePeriod: 'day',
    });

    const dayLabel = wrapper.find('#period_id_day').element.closest('label') as HTMLLabelElement;
    const weekLabel = wrapper.find('#period_id_week').element.closest('label') as HTMLLabelElement;

    expect(dayLabel.title).toBe('');
    expect(weekLabel.title).not.toBe('');
  });
});
