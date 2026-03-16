/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import PeriodSelectorCompareControls from './PeriodSelectorCompareControls.vue';

describe('PeriodSelectorCompareControls', () => {
  function mountComponent(customProps = {}) {
    return mount(PeriodSelectorCompareControls, {
      props: {
        isComparisonEnabled: true,
        isComparing: false,
        comparePeriodType: 'previousPeriod',
        compareStartDate: '',
        compareEndDate: '',
        comparePeriodDropdownOptions: [
          { key: 'custom', value: 'Custom' },
          { key: 'previousPeriod', value: 'Previous period' },
        ],
        ...customProps,
      },
      global: {
        stubs: {
          Field: {
            props: ['modelValue', 'name', 'uicontrol', 'disabled', 'style'],
            template: `
              <input
                class="field-stub"
                :data-name="name"
                :value="modelValue"
                :disabled="disabled"
                @input="$emit('update:modelValue', $event.target.value)"
              />
            `,
          },
        },
      },
    });
  }

  it('emits update:isComparing when compare checkbox changes', async () => {
    const wrapper = mountComponent({ isComparing: false });

    await wrapper.find('#comparePeriodTo').setValue(true);

    expect(wrapper.emitted('update:isComparing')?.[0]).toEqual([true]);
  });

  it('emits compare period type update from dropdown field', async () => {
    const wrapper = mountComponent({ isComparing: true });
    const compareTypeField = wrapper.find('.field-stub[data-name="comparePeriodToDropdown"]');

    await compareTypeField.setValue('custom');

    expect(wrapper.emitted('update:comparePeriodType')?.[0]).toEqual(['custom']);
  });

  it('emits compare range updates for custom compare mode', async () => {
    const wrapper = mountComponent({
      isComparing: true,
      comparePeriodType: 'custom',
      compareStartDate: '2026-02-01',
      compareEndDate: '2026-02-07',
    });

    const startField = wrapper.find('.field-stub[data-name="comparePeriodStartDate"]');
    const endField = wrapper.find('.field-stub[data-name="comparePeriodEndDate"]');

    await startField.setValue('2026-02-02');
    await endField.setValue('2026-02-08');

    expect(wrapper.emitted('update:compareStartDate')?.[0]).toEqual(['2026-02-02']);
    expect(wrapper.emitted('update:compareEndDate')?.[0]).toEqual(['2026-02-08']);
  });

  it('keeps compare controls hidden/disabled according to state', () => {
    const wrapper = mountComponent({
      isComparing: false,
      comparePeriodType: 'custom',
    });

    const compareDropdown = wrapper.find('.field-stub[data-name="comparePeriodToDropdown"]');

    expect(compareDropdown.attributes('disabled')).toBeDefined();
    expect(wrapper.find('#comparePeriodStartDate').exists()).toBe(false);
    expect(wrapper.find('#comparePeriodEndDate').exists()).toBe(false);
  });
});
