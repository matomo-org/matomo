/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import ComparisonsStore from '../Comparisons/Comparisons.store.instance';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import PeriodSelectorCompareControls from './PeriodSelectorCompareControls.vue';

describe('PeriodSelectorCompareControls', () => {
  const originalUrl = (MatomoUrl as any).url.value;
  let getPeriodComparisonsSpy: jest.SpyInstance;
  let isComparingPeriodsSpy: jest.SpyInstance;

  function setUrl(url: string) {
    (MatomoUrl as any).url.value = new URL(url);
  }

  function mountComponent(customProps = {}) {
    return mount(PeriodSelectorCompareControls, {
      props: {
        isComparisonEnabled: true,
        selectedPeriod: 'day',
        committedAnchorDate: new Date('2026-02-18'),
        appliedRangeStartDate: '2026-02-01',
        appliedRangeEndDate: '2026-02-07',
        comparePeriodDropdownOptions: [
          { key: 'custom', value: 'Custom' },
          { key: 'previousPeriod', value: 'Previous period' },
          { key: 'previousYear', value: 'Previous year' },
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

  beforeEach(() => {
    setUrl('https://matomo.test/index.php?period=day&date=2026-02-18');
    getPeriodComparisonsSpy = jest.spyOn(ComparisonsStore, 'getPeriodComparisons').mockReturnValue([]);
    isComparingPeriodsSpy = jest.spyOn(ComparisonsStore, 'isComparingPeriods').mockReturnValue(false);
  });

  afterEach(() => {
    getPeriodComparisonsSpy.mockRestore();
    isComparingPeriodsSpy.mockRestore();
    (MatomoUrl as any).url.value = originalUrl;
  });

  it('emits compare-state-change on mount after hydration', () => {
    const wrapper = mountComponent();

    expect(wrapper.emitted('compare-state-change')?.[0]).toEqual([{
      isComparing: false,
      comparePeriodType: 'previousPeriod',
      compareStartDate: '',
      compareEndDate: '',
      compareCurrentSignature: '{"isComparing":false,"comparePeriodType":"previousPeriod","compareStartDate":"","compareEndDate":""}',
      isCompareRangeValid: false,
      source: 'sync',
    }]);
    expect((wrapper.vm as any).selectedComparisonParams).toEqual({});
  });

  it('emits compare-state-change when compare checkbox changes', async () => {
    const wrapper = mountComponent();

    await wrapper.find('#comparePeriodTo').setValue(true);

    expect(wrapper.emitted('compare-state-change')?.at(-1)?.[0]).toEqual(expect.objectContaining({
      isComparing: true,
      comparePeriodType: 'previousPeriod',
      source: 'user',
    }));
    expect((wrapper.vm as any).selectedComparisonParams).toEqual({
      comparePeriods: ['day'],
      comparePeriodType: 'previousPeriod',
      compareDates: ['2026-02-17'],
    });
  });

  it('emits derived compare params for previous year on period change', async () => {
    const wrapper = mountComponent();

    await wrapper.find('#comparePeriodTo').setValue(true);
    await wrapper.find('.field-stub[data-name="comparePeriodToDropdown"]').setValue('previousYear');

    expect(wrapper.emitted('compare-state-change')?.at(-1)?.[0]).toEqual(expect.objectContaining({
      comparePeriodType: 'previousYear',
      source: 'user',
    }));
    expect((wrapper.vm as any).selectedComparisonParams).toEqual({
      comparePeriods: ['day'],
      comparePeriodType: 'previousYear',
      compareDates: ['2025-02-18'],
    });
  });

  it('emits custom compare payload and validity updates', async () => {
    const wrapper = mountComponent();

    await wrapper.find('#comparePeriodTo').setValue(true);
    await wrapper.find('.field-stub[data-name="comparePeriodToDropdown"]').setValue('custom');

    const startField = wrapper.find('.field-stub[data-name="comparePeriodStartDate"]');
    const endField = wrapper.find('.field-stub[data-name="comparePeriodEndDate"]');

    await startField.setValue('2026-02-02');
    await endField.setValue('2026-02-08');

    expect(wrapper.emitted('compare-state-change')?.at(-1)?.[0]).toEqual(expect.objectContaining({
      comparePeriodType: 'custom',
      compareStartDate: '2026-02-02',
      compareEndDate: '2026-02-08',
      isCompareRangeValid: true,
      compareCurrentSignature: '{"isComparing":true,"comparePeriodType":"custom","compareStartDate":"2026-02-02","compareEndDate":"2026-02-08"}',
      source: 'user',
    }));
    expect((wrapper.vm as any).selectedComparisonParams).toEqual({
      comparePeriods: ['range'],
      comparePeriodType: 'custom',
      compareDates: ['2026-02-02,2026-02-08'],
    });
  });

  it('keeps compare controls hidden and disabled according to state', () => {
    const wrapper = mountComponent();

    const compareDropdown = wrapper.find('.field-stub[data-name="comparePeriodToDropdown"]');

    expect(compareDropdown.attributes('disabled')).toBeDefined();
    expect(wrapper.find('#comparePeriodStartDate').exists()).toBe(false);
    expect(wrapper.find('#comparePeriodEndDate').exists()).toBe(false);
  });

  it('hydrates custom compare state from store and parsed hash', async () => {
    isComparingPeriodsSpy.mockReturnValue(true);
    getPeriodComparisonsSpy.mockReturnValue([
      { params: { period: 'day', date: '2026-02-18' } },
      { params: { period: 'range', date: '2026-02-01,2026-02-07' } },
    ]);
    setUrl('https://matomo.test/index.php?period=day&date=2026-02-18&comparePeriodType=custom');

    const wrapper = mountComponent();
    await wrapper.vm.$nextTick();

    expect(wrapper.find('#comparePeriodStartDate').exists()).toBe(true);
    expect((wrapper.find('.field-stub[data-name="comparePeriodStartDate"]').element as HTMLInputElement).value).toBe('2026-02-01');
    expect(wrapper.emitted('compare-state-change')?.[0]?.[0]).toEqual(expect.objectContaining({
      isComparing: true,
      comparePeriodType: 'custom',
      compareStartDate: '2026-02-01',
      compareEndDate: '2026-02-07',
      source: 'sync',
    }));
  });

  it('derives previous period for range selections', async () => {
    const wrapper = mountComponent({
      selectedPeriod: 'range',
      committedAnchorDate: new Date('2026-02-01'),
      appliedRangeStartDate: '2026-02-10',
      appliedRangeEndDate: '2026-02-12',
    });

    await wrapper.find('#comparePeriodTo').setValue(true);

    expect(wrapper.emitted('compare-state-change')?.at(-1)?.[0]).toEqual(expect.objectContaining({
    }));
    expect((wrapper.vm as any).selectedComparisonParams).toEqual({
      comparePeriods: ['range'],
      comparePeriodType: 'previousPeriod',
      compareDates: ['2026-02-07,2026-02-09'],
    });
  });

  it('derives previous year for range selections', async () => {
    const wrapper = mountComponent({
      selectedPeriod: 'range',
      committedAnchorDate: new Date('2026-02-01'),
      appliedRangeStartDate: '2026-02-10',
      appliedRangeEndDate: '2026-02-12',
    });

    await wrapper.find('#comparePeriodTo').setValue(true);
    await wrapper.find('.field-stub[data-name="comparePeriodToDropdown"]').setValue('previousYear');

    expect(wrapper.emitted('compare-state-change')?.at(-1)?.[0]).toEqual(expect.objectContaining({
    }));
    expect((wrapper.vm as any).selectedComparisonParams).toEqual({
      comparePeriods: ['range'],
      comparePeriodType: 'previousYear',
      compareDates: ['2025-02-10,2025-02-12'],
    });
  });

  it('marks invalid custom compare dates as invalid', async () => {
    const wrapper = mountComponent();

    await wrapper.find('#comparePeriodTo').setValue(true);
    await wrapper.find('.field-stub[data-name="comparePeriodToDropdown"]').setValue('custom');
    await wrapper.find('.field-stub[data-name="comparePeriodStartDate"]').setValue('nope');
    await wrapper.find('.field-stub[data-name="comparePeriodEndDate"]').setValue('still-nope');

    expect(wrapper.emitted('compare-state-change')?.at(-1)?.[0]).toEqual(expect.objectContaining({
      isCompareRangeValid: false,
      source: 'user',
    }));
  });
});
