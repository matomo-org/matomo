/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import PeriodSelector from './PeriodSelector.vue';

window.piwik.minDateYear = 2011;
window.piwik.minDateMonth = 11;
window.piwik.minDateDay = 15;
window.piwik.maxDateYear = 2014;
window.piwik.maxDateMonth = 3;
window.piwik.maxDateDay = 29;

describe('PeriodSelector template contract', () => {
  const originalInitTopControls = window.initTopControls;
  const originalUrl = (MatomoUrl as any).url.value;

  const setUrl = (url: string) => {
    (MatomoUrl as any).url.value = new URL(url);
  };

  function mountSelector() {
    return mount(PeriodSelector, {
      shallow: true,
      props: {
        periods: ['day', 'week', 'month', 'year', 'range'],
      },
      global: {
        mocks: {
          translate: (key: string) => key,
        },
        stubs: {
          PeriodSelectorOptionsColumn: false,
          PeriodSelectorCalendarColumn: false,
        },
      },
    });
  }

  beforeEach(() => {
    if (!window.initTopControls) {
      window.initTopControls = jest.fn();
    }

    setUrl(
      'https://matomo.test/index.php?module=CoreHome&action=index&period=day&date=today'
      + '#?period=day&date=today&category=General_Actions&subcategory=General_Pages',
    );
  });

  afterEach(() => {
    (MatomoUrl as any).url.value = originalUrl;
    window.initTopControls = originalInitTopControls;
  });

  it('keeps DOM hooks used by styles and behavior', async () => {
    const wrapper = mountSelector();

    expect(wrapper.find('#periodMore').exists()).toBe(true);
    expect(wrapper.find('#periodMore').classes()).toContain('single-calendar');
    expect(wrapper.find('#otherPeriods').exists()).toBe(true);
    expect(wrapper.find('#datepicker').exists()).toBe(true);
    expect(wrapper.find('#calendarApply').exists()).toBe(true);
    expect(wrapper.find('#ajaxLoadingCalendar').exists()).toBe(false);

    await wrapper.setData({
      isLoadingNewPage: true,
      selectedPeriod: 'range',
    });

    expect(wrapper.find('#periodMore').classes()).toContain('dual-calendar');
    expect(wrapper.find('#ajaxLoadingCalendar').exists()).toBe(true);
    wrapper.unmount();
  });

  it('forwards child events to existing parent handlers/state', async () => {
    const wrapper = mountSelector();

    wrapper.findComponent({ name: 'PeriodSelectorOptionsColumn' }).vm.$emit('period-select', {
      period: 'range',
    });

    expect((wrapper.vm as any).selectedPeriod).toBe('range');
    expect((wrapper.vm as any).calendarViewport).toBe('range');

    await wrapper.setData({
      uiSelection: { type: 'period', id: 'range' },
      selectedPeriod: 'range',
      calendarViewport: 'range',
      isRangeValid: null,
      appliedRangeStartDate: null,
      appliedRangeEndDate: null,
    });

    wrapper.findComponent({ name: 'PeriodSelectorCalendarColumn' }).vm.$emit('range-change', {
      start: '2026-02-01',
      end: '2026-02-18',
    });

    expect((wrapper.vm as any).appliedRangeStartDate).toBe('2026-02-01');
    expect((wrapper.vm as any).appliedRangeEndDate).toBe('2026-02-18');

    wrapper.findComponent({ name: 'PeriodSelectorCalendarColumn' }).vm.$emit(
      'update:comparePeriodType',
      'previousYear',
    );

    expect((wrapper.vm as any).comparePeriodType).toBe('previousYear');
    wrapper.unmount();
  });
});
