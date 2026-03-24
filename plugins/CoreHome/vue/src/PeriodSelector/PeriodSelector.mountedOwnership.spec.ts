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

describe('CoreHome/PeriodSelector/PeriodSelector mounted ownership interactions', () => {
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

  it('blocks single-calendar interaction while preset owns selection', async () => {
    const wrapper = mountSelector();

    const commitSelectionToUrl = jest.fn();
    (wrapper.vm as any).commitSelectionToUrl = commitSelectionToUrl;
    await wrapper.setData({
      uiSelection: { type: 'preset', id: 'today' },
      selectedPeriod: 'day',
      calendarViewport: 'single',
    });

    expect(wrapper.find('.period-date').classes()).toContain('calendar-disabled');
    expect(wrapper.findComponent({ name: 'PeriodDatePicker' }).props('disabled')).toBe(true);

    wrapper.findComponent({ name: 'PeriodDatePicker' }).vm.$emit('select', {
      date: new Date('2026-02-18'),
    });

    expect(commitSelectionToUrl).not.toHaveBeenCalled();
    wrapper.unmount();
  });

  it('allows single-calendar interaction after switching ownership to period option', async () => {
    const wrapper = mountSelector();

    const commitSelectionToUrl = jest.fn();
    (wrapper.vm as any).commitSelectionToUrl = commitSelectionToUrl;
    await wrapper.setData({
      uiSelection: { type: 'preset', id: 'today' },
      selectedPeriod: 'day',
      calendarViewport: 'single',
    });

    wrapper.findComponent({ name: 'PeriodOptions' }).vm.$emit('select', { period: 'day' });
    wrapper.findComponent({ name: 'PeriodDatePicker' }).vm.$emit('select', {
      date: new Date('2026-02-18'),
    });

    expect(commitSelectionToUrl).toHaveBeenCalledTimes(1);
    wrapper.unmount();
  });

  it('blocks dual-calendar interaction while preset owns selection', async () => {
    const wrapper = mountSelector();
    await wrapper.setData({
      uiSelection: { type: 'preset', id: 'last30days' },
      selectedPeriod: 'range',
      calendarViewport: 'range',
      isRangeValid: false,
      appliedRangeStartDate: '2026-01-01',
      appliedRangeEndDate: '2026-01-31',
    });

    expect(wrapper.findComponent({ name: 'DateRangePicker' }).props('disabled')).toBe(true);

    wrapper.findComponent({ name: 'DateRangePicker' }).vm.$emit('range-change', {
      start: '2026-02-01',
      end: '2026-02-18',
    });

    expect((wrapper.vm as any).isRangeValid).toBe(false);
    expect((wrapper.vm as any).appliedRangeStartDate).toBe('2026-01-01');
    expect((wrapper.vm as any).appliedRangeEndDate).toBe('2026-01-31');
    wrapper.unmount();
  });

  it('allows dual-calendar interaction when period option owns selection', async () => {
    const wrapper = mountSelector();
    await wrapper.setData({
      uiSelection: { type: 'period', id: 'range' },
      selectedPeriod: 'range',
      calendarViewport: 'range',
      isRangeValid: null,
      appliedRangeStartDate: null,
      appliedRangeEndDate: null,
    });

    wrapper.findComponent({ name: 'DateRangePicker' }).vm.$emit('range-change', {
      start: '2026-02-01',
      end: '2026-02-18',
    });

    expect((wrapper.vm as any).isRangeValid).toBe(true);
    expect((wrapper.vm as any).appliedRangeStartDate).toBe('2026-02-01');
    expect((wrapper.vm as any).appliedRangeEndDate).toBe('2026-02-18');
    wrapper.unmount();
  });

  it('keeps preset ownership after close/reopen without apply', async () => {
    const wrapper = mountSelector();

    (wrapper.vm as any).onPresetDateRangeSelected({
      id: 'today',
      period: 'day',
      date: 'today',
      startDate: new Date('2026-02-18'),
      endDate: new Date('2026-02-18'),
    });

    expect((wrapper.vm as any).uiSelection).toEqual({ type: 'preset', id: 'today' });
    expect((wrapper.vm as any).activePresetId).toBe('today');

    (wrapper.vm as any).onClosed({ detail: 1 });
    (wrapper.vm as any).onExpand({ detail: 1 });

    expect((wrapper.vm as any).uiSelection).toEqual({ type: 'preset', id: 'today' });
    expect((wrapper.vm as any).activePresetId).toBe('today');
    wrapper.unmount();
  });

  it('switches checked ownership from preset to period when a period option is selected', async () => {
    const wrapper = mountSelector();

    (wrapper.vm as any).onPresetDateRangeSelected({
      id: 'last30days',
      period: 'range',
      date: 'last30',
      startDate: new Date('2026-01-20'),
      endDate: new Date('2026-02-18'),
    });

    expect((wrapper.vm as any).uiSelection).toEqual({ type: 'preset', id: 'last30days' });
    expect((wrapper.vm as any).activePresetId).toBe('last30days');

    wrapper.findComponent({ name: 'PeriodOptions' }).vm.$emit('select', { period: 'month' });

    expect((wrapper.vm as any).uiSelection).toEqual({ type: 'period', id: 'month' });
    expect((wrapper.vm as any).activePresetId).toBeNull();
    expect((wrapper.vm as any).pendingPresetSelection).toBeNull();
    wrapper.unmount();
  });

  it('closes on outside click without applying pending preset selection', async () => {
    const wrapper = mountSelector();

    const updateLocationSpy = jest.spyOn(MatomoUrl, 'updateLocation');

    (wrapper.vm as any).onPresetDateRangeSelected({
      id: 'last30days',
      period: 'range',
      date: 'last30',
      startDate: new Date('2026-01-20'),
      endDate: new Date('2026-02-18'),
    });

    const root = wrapper.find('.periodSelector').element as HTMLElement;
    root.classList.add('expanded');

    document.body.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
    document.body.dispatchEvent(new MouseEvent('mouseup', { bubbles: true }));

    expect(root.classList.contains('expanded')).toBe(false);
    expect(updateLocationSpy).not.toHaveBeenCalled();
    expect((wrapper.vm as any).uiSelection).toEqual({ type: 'preset', id: 'last30days' });
    expect((wrapper.vm as any).pendingPresetSelection).toBeTruthy();

    updateLocationSpy.mockRestore();
    wrapper.unmount();
  });
});
