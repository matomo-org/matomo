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

  it('allows single-calendar interaction after a preset shortcut is selected', async () => {
    const wrapper = mountSelector();

    const commitSelectionToUrl = jest.fn();
    (wrapper.vm as any).commitSelectionToUrl = commitSelectionToUrl;
    await wrapper.setData({
      uiSelection: { type: 'period', id: 'day' },
      selectedPeriod: 'day',
      calendarViewport: 'single',
    });

    wrapper.findComponent({ name: 'PeriodDatePicker' }).vm.$emit('select', {
      date: new Date('2026-02-18'),
    });

    expect(commitSelectionToUrl).toHaveBeenCalledTimes(1);
    wrapper.unmount();
  });

  it('allows single-calendar interaction after switching to a period option', async () => {
    const wrapper = mountSelector();

    const commitSelectionToUrl = jest.fn();
    (wrapper.vm as any).commitSelectionToUrl = commitSelectionToUrl;
    await wrapper.setData({
      uiSelection: { type: 'period', id: 'day' },
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

  it('allows dual-calendar interaction after a preset shortcut is selected', async () => {
    const wrapper = mountSelector();
    await wrapper.setData({
      uiSelection: { type: 'period', id: 'range' },
      selectedPeriod: 'range',
      calendarViewport: 'range',
      isRangeValid: false,
      appliedRangeStartDate: '2026-01-01',
      appliedRangeEndDate: '2026-01-31',
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

  it('marks the corresponding period as selected when a preset resolves to that period', async () => {
    const wrapper = mountSelector();

    (wrapper.vm as any).onPresetDateRangeSelected({
      id: 'today',
      period: 'day',
      date: '2026-02-18',
      urlDate: 'today',
      selectedDate: new Date('2026-02-18'),
      startDate: new Date('2026-02-18'),
      endDate: new Date('2026-02-18'),
    });

    await wrapper.vm.$nextTick();
    expect(wrapper.findComponent({ name: 'PeriodOptions' }).props('checkedPeriodId')).toBe('day');

    (wrapper.vm as any).onPresetDateRangeSelected({
      id: 'lastWeekMonSun',
      period: 'week',
      date: '2026-02-09',
      urlDate: 'lastweek',
      selectedDate: new Date('2026-02-09'),
      startDate: new Date('2026-02-09'),
      endDate: new Date('2026-02-15'),
    });

    await wrapper.vm.$nextTick();
    expect(wrapper.findComponent({ name: 'PeriodOptions' }).props('checkedPeriodId')).toBe('week');
    wrapper.unmount();
  });

  it('keeps a preset shortcut highlighted after close/reopen without apply', async () => {
    const wrapper = mountSelector();

    (wrapper.vm as any).onPresetDateRangeSelected({
      id: 'last30days',
      period: 'range',
      date: '2026-01-20,2026-02-18',
      urlDate: 'last30',
      selectedDate: new Date('2026-02-18'),
      startDate: new Date('2026-01-20'),
      endDate: new Date('2026-02-18'),
    });

    expect((wrapper.vm as any).uiSelection).toEqual({ type: 'preset', id: 'last30days' });
    expect((wrapper.vm as any).activePresetId).toBe('last30days');

    (wrapper.vm as any).onClosed({ detail: 1 });
    (wrapper.vm as any).onExpand({ detail: 1 });

    expect((wrapper.vm as any).uiSelection).toEqual({ type: 'preset', id: 'last30days' });
    expect((wrapper.vm as any).activePresetId).toBe('last30days');
    wrapper.unmount();
  });

  it('switches the highlighted preset off when a period option is selected', async () => {
    const wrapper = mountSelector();

    (wrapper.vm as any).onPresetDateRangeSelected({
      id: 'last30days',
      period: 'range',
      date: '2026-01-20,2026-02-18',
      urlDate: 'last30',
      selectedDate: new Date('2026-02-18'),
      startDate: new Date('2026-01-20'),
      endDate: new Date('2026-02-18'),
    });

    expect((wrapper.vm as any).uiSelection).toEqual({ type: 'preset', id: 'last30days' });
    expect((wrapper.vm as any).activePresetId).toBe('last30days');

    wrapper.findComponent({ name: 'PeriodOptions' }).vm.$emit('select', { period: 'month' });

    expect((wrapper.vm as any).uiSelection).toEqual({ type: 'period', id: 'month' });
    expect((wrapper.vm as any).activePresetId).toBe('thisMonth');
    expect((wrapper.vm as any).pendingPresetSelection).toBeNull();
    wrapper.unmount();
  });

  it('closes on outside click without committing a staged range selection', async () => {
    const wrapper = mountSelector();

    const updateLocationSpy = jest.spyOn(MatomoUrl, 'updateLocation');

    (wrapper.vm as any).onPresetDateRangeSelected({
      id: 'last30days',
      period: 'range',
      date: '2026-01-20,2026-02-18',
      urlDate: 'last30',
      selectedDate: new Date('2026-02-18'),
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
    expect((wrapper.vm as any).pendingPresetSelection).toEqual(expect.objectContaining({
      id: 'last30days',
      period: 'range',
    }));
    expect((wrapper.vm as any).activePresetId).toBe('last30days');

    updateLocationSpy.mockRestore();
    wrapper.unmount();
  });
});
