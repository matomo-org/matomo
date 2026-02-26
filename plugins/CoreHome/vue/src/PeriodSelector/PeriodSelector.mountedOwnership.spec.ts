/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import MatomoUrl from '../MatomoUrl/MatomoUrl';

window.piwik.minDateYear = 2011;
window.piwik.minDateMonth = 11;
window.piwik.minDateDay = 15;
window.piwik.maxDateYear = 2014;
window.piwik.maxDateMonth = 3;
window.piwik.maxDateDay = 29;

// eslint-disable-next-line @typescript-eslint/no-var-requires
const PeriodSelector = require('./PeriodSelector.vue').default;

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
    await nextTick();

    const commitSelectionToUrl = jest.fn();
    (wrapper.vm as any).commitSelectionToUrl = commitSelectionToUrl;
    (wrapper.vm as any).uiSelection = { type: 'preset', id: 'today' };
    (wrapper.vm as any).uiSelectedPeriod = 'day';
    (wrapper.vm as any).calendarViewport = 'single';

    wrapper.findComponent({ name: 'PeriodDatePicker' }).vm.$emit('select', {
      date: new Date('2026-02-18'),
    });
    await nextTick();

    expect(commitSelectionToUrl).not.toHaveBeenCalled();
    wrapper.unmount();
  });

  it('allows single-calendar interaction after switching ownership to period option', async () => {
    const wrapper = mountSelector();
    await nextTick();

    const commitSelectionToUrl = jest.fn();
    (wrapper.vm as any).commitSelectionToUrl = commitSelectionToUrl;
    (wrapper.vm as any).uiSelection = { type: 'preset', id: 'today' };
    (wrapper.vm as any).uiSelectedPeriod = 'day';
    (wrapper.vm as any).calendarViewport = 'single';

    wrapper.findComponent({ name: 'PeriodOptions' }).vm.$emit('select', { period: 'day' });
    await nextTick();
    wrapper.findComponent({ name: 'PeriodDatePicker' }).vm.$emit('select', {
      date: new Date('2026-02-18'),
    });
    await nextTick();

    expect(commitSelectionToUrl).toHaveBeenCalledTimes(1);
    wrapper.unmount();
  });

  it('blocks dual-calendar interaction while preset owns selection', async () => {
    const wrapper = mountSelector();
    await nextTick();

    (wrapper.vm as any).uiSelection = { type: 'preset', id: 'last30days' };
    (wrapper.vm as any).uiSelectedPeriod = 'range';
    (wrapper.vm as any).calendarViewport = 'range';
    (wrapper.vm as any).isRangeValid = false;
    (wrapper.vm as any).appliedRangeStartDate = '2026-01-01';
    (wrapper.vm as any).appliedRangeEndDate = '2026-01-31';

    wrapper.findComponent({ name: 'DateRangePicker' }).vm.$emit('range-change', {
      start: '2026-02-01',
      end: '2026-02-18',
    });
    await nextTick();

    expect((wrapper.vm as any).isRangeValid).toBe(false);
    expect((wrapper.vm as any).appliedRangeStartDate).toBe('2026-01-01');
    expect((wrapper.vm as any).appliedRangeEndDate).toBe('2026-01-31');
    wrapper.unmount();
  });

  it('allows dual-calendar interaction when period option owns selection', async () => {
    const wrapper = mountSelector();
    await nextTick();

    (wrapper.vm as any).uiSelection = { type: 'period', id: 'range' };
    (wrapper.vm as any).uiSelectedPeriod = 'range';
    (wrapper.vm as any).calendarViewport = 'range';
    (wrapper.vm as any).isRangeValid = null;
    (wrapper.vm as any).appliedRangeStartDate = null;
    (wrapper.vm as any).appliedRangeEndDate = null;

    wrapper.findComponent({ name: 'DateRangePicker' }).vm.$emit('range-change', {
      start: '2026-02-01',
      end: '2026-02-18',
    });
    await nextTick();

    expect((wrapper.vm as any).isRangeValid).toBe(true);
    expect((wrapper.vm as any).appliedRangeStartDate).toBe('2026-02-01');
    expect((wrapper.vm as any).appliedRangeEndDate).toBe('2026-02-18');
    wrapper.unmount();
  });

  it('keeps preset ownership after close/reopen without apply', async () => {
    const wrapper = mountSelector();
    await nextTick();

    (wrapper.vm as any).onPresetDateRangeSelected({
      id: 'today',
      period: 'day',
      date: 'today',
      startDate: new Date('2026-02-18'),
      endDate: new Date('2026-02-18'),
    });
    await nextTick();

    expect((wrapper.vm as any).uiSelection).toEqual({ type: 'preset', id: 'today' });
    expect((wrapper.vm as any).activePresetId).toBe('today');

    (wrapper.vm as any).onClosed({ detail: 1 });
    await nextTick();
    (wrapper.vm as any).onExpand({ detail: 1 });
    await nextTick();

    expect((wrapper.vm as any).uiSelection).toEqual({ type: 'preset', id: 'today' });
    expect((wrapper.vm as any).activePresetId).toBe('today');
    wrapper.unmount();
  });

  it('switches checked ownership from preset to period when a period option is selected', async () => {
    const wrapper = mountSelector();
    await nextTick();

    (wrapper.vm as any).onPresetDateRangeSelected({
      id: 'last30days',
      period: 'range',
      date: 'last30',
      startDate: new Date('2026-01-20'),
      endDate: new Date('2026-02-18'),
    });
    await nextTick();

    expect((wrapper.vm as any).uiSelection).toEqual({ type: 'preset', id: 'last30days' });
    expect((wrapper.vm as any).activePresetId).toBe('last30days');

    wrapper.findComponent({ name: 'PeriodOptions' }).vm.$emit('select', { period: 'month' });
    await nextTick();

    expect((wrapper.vm as any).uiSelection).toEqual({ type: 'period', id: 'month' });
    expect((wrapper.vm as any).activePresetId).toBeNull();
    expect((wrapper.vm as any).pendingPresetSelection).toBeNull();
    wrapper.unmount();
  });

  it('closes on outside click without applying pending preset selection', async () => {
    const wrapper = mountSelector();
    await nextTick();

    const updateLocationSpy = jest.spyOn(MatomoUrl, 'updateLocation');

    (wrapper.vm as any).onPresetDateRangeSelected({
      id: 'last30days',
      period: 'range',
      date: 'last30',
      startDate: new Date('2026-01-20'),
      endDate: new Date('2026-02-18'),
    });
    await nextTick();

    const root = wrapper.find('.periodSelector').element as HTMLElement;
    root.classList.add('expanded');

    document.body.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
    document.body.dispatchEvent(new MouseEvent('mouseup', { bubbles: true }));
    await nextTick();

    expect(root.classList.contains('expanded')).toBe(false);
    expect(updateLocationSpy).not.toHaveBeenCalled();
    expect((wrapper.vm as any).uiSelection).toEqual({ type: 'preset', id: 'last30days' });
    expect((wrapper.vm as any).pendingPresetSelection).toBeTruthy();

    updateLocationSpy.mockRestore();
    wrapper.unmount();
  });
});
