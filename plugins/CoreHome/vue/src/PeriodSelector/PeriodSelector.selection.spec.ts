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
const CONTEXT_KEY_IGNORED_PARAMS = ['date', 'period', 'comparePeriods', 'comparePeriodType', 'compareDates', 'compareSegments'];

function createContextKey(parsed: Record<string, unknown>): string {
  const context: Record<string, unknown> = {};
  Object.keys(parsed)
    .filter((key) => !CONTEXT_KEY_IGNORED_PARAMS.includes(key))
    .sort()
    .forEach((key) => {
      context[key] = parsed[key];
    });
  return JSON.stringify(context);
}

describe('CoreHome/PeriodSelector/PeriodSelector persistent calendar behavior', () => {
  const component = PeriodSelector as unknown as {
    methods: Record<string, (...args: unknown[]) => unknown>;
    computed: Record<string, (...args: unknown[]) => unknown>;
  };
  const { methods, computed } = component;
  const baseContextKey = createContextKey({
    module: 'CoreHome',
    action: 'index',
    category: 'General_Actions',
    subcategory: 'General_Pages',
    date: 'today',
    period: 'day',
  });

  it('stages preset selection and switches to dual calendar for range presets', () => {
    const appliedDate = new Date('2026-02-18');
    const vm: any = {
      periodsFiltered: ['day', 'week', 'month', 'year', 'range'],
      uiSelection: { type: 'period', id: 'day' },
      lastInteractionSource: null,
      activePresetId: null,
      pendingPresetSelection: null,
      selectedPeriod: 'day',
      periodValue: 'day',
      calendarViewport: 'single',
      dateValue: appliedDate,
      startRangeDate: '2026-02-18',
      endRangeDate: '2026-02-18',
      stagedRangeStartDate: null,
      stagedRangeEndDate: null,
      isRangeValid: false,
      setUiSelection(selection: { type: string; id: string }, source: string|null) {
        this.uiSelection = selection;
        this.lastInteractionSource = source;
      },
    };

    methods.onPresetDateRangeSelected.call(vm, {
      id: 'last7days',
      period: 'range',
      date: 'last7',
      startDate: new Date('2026-02-12'),
      endDate: new Date('2026-02-18'),
    });

    expect(vm.uiSelection).toEqual({ type: 'preset', id: 'last7days' });
    expect(vm.activePresetId).toBe('last7days');
    expect(vm.pendingPresetSelection?.date).toBe('last7');
    expect(vm.calendarViewport).toBe('range');
    expect(vm.dateValue).toBe(appliedDate);
    expect(vm.startRangeDate).toBe('2026-02-18');
    expect(vm.endRangeDate).toBe('2026-02-18');
    expect(vm.stagedRangeStartDate).toBe('2026-02-12');
    expect(vm.stagedRangeEndDate).toBe('2026-02-18');
  });

  it('keeps single calendar for non-dual presets', () => {
    const vm: any = {
      periodsFiltered: ['day', 'week', 'month', 'year', 'range'],
      uiSelection: { type: 'period', id: 'day' },
      lastInteractionSource: null,
      activePresetId: null,
      pendingPresetSelection: null,
      selectedPeriod: 'day',
      calendarViewport: 'range',
      singleCalendarPeriod: 'day',
      singleCalendarDate: null,
      stagedRangeStartDate: '2026-02-10',
      stagedRangeEndDate: '2026-02-12',
      dateValue: new Date('2026-02-18'),
      startRangeDate: '2026-02-18',
      endRangeDate: '2026-02-18',
      isRangeValid: false,
      setUiSelection(selection: { type: string; id: string }, source: string|null) {
        this.uiSelection = selection;
        this.lastInteractionSource = source;
      },
    };

    methods.onPresetDateRangeSelected.call(vm, {
      id: 'yesterday',
      period: 'day',
      date: 'yesterday',
      startDate: new Date('2026-02-17'),
      endDate: new Date('2026-02-17'),
    });

    expect(vm.uiSelection).toEqual({ type: 'preset', id: 'yesterday' });
    expect(vm.calendarViewport).toBe('single');
    expect(vm.singleCalendarPeriod).toBe('day');
    expect(vm.stagedRangeStartDate).toBeNull();
    expect(vm.stagedRangeEndDate).toBeNull();
  });

  it('ignores preset selections that resolve to disallowed periods', () => {
    const vm: any = {
      periodsFiltered: ['day'],
      uiSelection: { type: 'period', id: 'day' },
      lastInteractionSource: null,
      activePresetId: null,
      pendingPresetSelection: null,
      selectedPeriod: 'day',
      calendarViewport: 'single',
      stagedRangeStartDate: null,
      stagedRangeEndDate: null,
      isRangeValid: false,
      setUiSelection: jest.fn(),
    };

    methods.onPresetDateRangeSelected.call(vm, {
      id: 'last7days',
      period: 'range',
      date: 'last7',
      startDate: new Date('2026-02-12'),
      endDate: new Date('2026-02-18'),
    });

    expect(vm.setUiSelection).not.toHaveBeenCalled();
    expect(vm.uiSelection).toEqual({ type: 'period', id: 'day' });
    expect(vm.activePresetId).toBeNull();
    expect(vm.pendingPresetSelection).toBeNull();
    expect(vm.selectedPeriod).toBe('day');
    expect(vm.calendarViewport).toBe('single');
    expect(vm.stagedRangeStartDate).toBeNull();
    expect(vm.stagedRangeEndDate).toBeNull();
    expect(vm.isRangeValid).toBe(false);
  });

  it('applies preset only on apply click', () => {
    const presetStart = new Date('2026-01-20');
    const presetEnd = new Date('2026-02-18');
    const vm: any = {
      uiSelection: { type: 'preset', id: 'last30days' },
      pendingPresetSelection: {
        id: 'last30days',
        period: 'range',
        date: 'last30',
        startDate: presetStart,
        endDate: presetEnd,
      },
      periodValue: 'day',
      dateValue: new Date('2026-02-18'),
      startRangeDate: '2026-02-18',
      endRangeDate: '2026-02-18',
      commitSelectionToUrl: jest.fn(),
    };

    methods.onApplyClicked.call(vm);

    expect(vm.periodValue).toBe('range');
    expect(vm.dateValue).toBe(presetStart);
    expect(vm.startRangeDate).toBe('2026-01-20');
    expect(vm.endRangeDate).toBe('2026-02-18');
    expect(vm.commitSelectionToUrl).toHaveBeenCalledWith('last30', 'range');
  });

  it('keeps thisWeekMonToday compatibility semantics when applying preset', () => {
    const vm: any = {
      uiSelection: { type: 'preset', id: 'thisWeekMonToday' },
      pendingPresetSelection: {
        id: 'thisWeekMonToday',
        period: 'week',
        date: 'today',
        startDate: new Date('2026-02-16'),
        endDate: new Date('2026-02-18'),
      },
      periodValue: 'day',
      dateValue: new Date('2026-02-18'),
      startRangeDate: '2026-02-18',
      endRangeDate: '2026-02-18',
      commitSelectionToUrl: jest.fn(),
    };

    methods.onApplyClicked.call(vm);

    expect(vm.periodValue).toBe('week');
    expect(vm.commitSelectionToUrl).toHaveBeenCalledWith('today', 'week');
  });

  it('applies rolling preset token even when staged range is clamped', () => {
    const vm: any = {
      uiSelection: { type: 'preset', id: 'last7days' },
      pendingPresetSelection: {
        id: 'last7days',
        period: 'range',
        date: 'last7',
        startDate: new Date('2026-02-12'),
        endDate: new Date('2026-02-18'),
      },
      startRangeDate: '2026-02-14',
      endRangeDate: '2026-02-15',
      periodValue: 'day',
      commitSelectionToUrl: jest.fn(),
    };

    methods.onApplyClicked.call(vm);

    expect(vm.periodValue).toBe('range');
    expect(vm.commitSelectionToUrl).toHaveBeenCalledWith('last7', 'range');
    expect(vm.commitSelectionToUrl).not.toHaveBeenCalledWith('2026-02-14,2026-02-15', 'range');
  });

  it('keeps currently viewing text unchanged before apply after preset click', () => {
    const appliedDate = new Date('2026-02-18');
    const vm: any = {
      periodsFiltered: ['day', 'week', 'month', 'year', 'range'],
      uiSelection: { type: 'period', id: 'day' },
      lastInteractionSource: null,
      activePresetId: null,
      pendingPresetSelection: null,
      selectedPeriod: 'day',
      periodValue: 'day',
      calendarViewport: 'single',
      dateValue: appliedDate,
      startRangeDate: '2026-02-18',
      endRangeDate: '2026-02-18',
      stagedRangeStartDate: null,
      stagedRangeEndDate: null,
      isRangeValid: false,
      setUiSelection(selection: { type: string; id: string }, source: string|null) {
        this.uiSelection = selection;
        this.lastInteractionSource = source;
      },
    };

    const before = computed.currentlyViewingText.call(vm);

    methods.onPresetDateRangeSelected.call(vm, {
      id: 'last7days',
      period: 'range',
      date: 'last7',
      startDate: new Date('2026-02-12'),
      endDate: new Date('2026-02-18'),
    });

    const after = computed.currentlyViewingText.call(vm);
    expect(before).toBe(after);
  });

  it('uses staged range preview values when range preset is selected', () => {
    const vm: any = {
      uiSelection: { type: 'preset', id: 'last7days' },
      selectedPeriod: 'range',
      stagedRangeStartDate: '2026-02-12',
      stagedRangeEndDate: '2026-02-18',
      startRangeDate: '2026-01-01',
      endRangeDate: '2026-01-31',
      isRangePresetSelection: true,
    };

    expect(computed.displayRangeStartDate.call(vm)).toBe('2026-02-12');
    expect(computed.displayRangeEndDate.call(vm)).toBe('2026-02-18');
  });

  it('marks non-range period change as pending and does not apply on apply click', () => {
    const vm: any = {
      uiSelection: { type: 'period', id: 'day' },
      lastInteractionSource: null,
      selectedPeriod: 'day',
      calendarViewport: 'single',
      singleCalendarDate: new Date('2026-02-18'),
      dateValue: new Date('2026-02-18'),
      activePresetId: 'today',
      pendingPresetSelection: { id: 'today' },
      isCompareDirty: false,
      hasPendingNonRangePeriodChange: true,
      commitSelectionToUrl: jest.fn(),
      setUiSelection(selection: { type: string; id: string }, source: string|null) {
        this.uiSelection = selection;
        this.lastInteractionSource = source;
      },
      clearPresetSelection() {
        this.activePresetId = null;
        this.pendingPresetSelection = null;
      },
    };

    methods.onPeriodOptionSelected.call(vm, { period: 'week' });

    expect(vm.selectedPeriod).toBe('week');
    expect(vm.calendarViewport).toBe('single');
    expect(vm.singleCalendarDate).toBeNull();
    expect(vm.pendingPresetSelection).toBeNull();
    expect(vm.activePresetId).toBeNull();

    methods.onApplyClicked.call(vm);

    expect(vm.commitSelectionToUrl).not.toHaveBeenCalled();
  });

  it('treats non-range pending state as period-change-only', () => {
    const samePeriodVm: any = {
      uiSelection: { type: 'period', id: 'day' },
      lastInteractionSource: 'period',
      selectedPeriod: 'day',
      periodValue: 'day',
    };
    expect(computed.hasPendingNonRangePeriodChange.call(samePeriodVm)).toBe(false);

    const changedPeriodVm: any = {
      uiSelection: { type: 'period', id: 'week' },
      lastInteractionSource: 'period',
      selectedPeriod: 'week',
      periodValue: 'day',
    };
    expect(computed.hasPendingNonRangePeriodChange.call(changedPeriodVm)).toBe(true);
  });

  it('allows compare apply path after same-period radio click', () => {
    const selectedDate = new Date('2026-02-18');
    const vm: any = {
      pendingPresetSelection: null,
      uiSelection: { type: 'period', id: 'day' },
      selectedPeriod: 'day',
      periodValue: 'day',
      dateValue: selectedDate,
      isCompareDirty: true,
      hasPendingNonRangePeriodChange: false,
      commitSelectionToUrl: jest.fn(),
    };

    methods.onApplyClicked.call(vm);

    expect(vm.commitSelectionToUrl).toHaveBeenCalledWith('2026-02-18', 'day');
  });

  it('does not skip hash sync when context changes with same period/date', () => {
    const vm: any = {
      nextHashUiSelection: null,
      lastKnownHashSelectionKey: 'day|today',
      lastKnownHashContextKey: baseContextKey,
    };

    expect(methods.shouldSkipHashSync.call(
      vm,
      'day|today',
      createContextKey({
        module: 'CoreHome',
        action: 'index',
        category: 'General_Visitors',
        subcategory: 'General_Overview',
        date: 'today',
        period: 'day',
      }),
    )).toBe(false);
  });

  it('skips hash sync only when both selection and context keys are unchanged and no pending ui sync', () => {
    const vm: any = {
      nextHashUiSelection: null,
      lastKnownHashSelectionKey: 'day|today',
      lastKnownHashContextKey: baseContextKey,
    };

    expect(methods.shouldSkipHashSync.call(
      vm,
      'day|today',
      baseContextKey,
    )).toBe(true);

    vm.nextHashUiSelection = { type: 'period', id: 'day' };
    expect(methods.shouldSkipHashSync.call(
      vm,
      'day|today',
      baseContextKey,
    )).toBe(false);
  });

  it('does not skip hash sync when idSite or segment changes with same period/date/context path', () => {
    const vm: any = {
      nextHashUiSelection: null,
      lastKnownHashSelectionKey: 'day|today',
      lastKnownHashContextKey: createContextKey({
        module: 'CoreHome',
        action: 'index',
        category: 'General_Actions',
        subcategory: 'General_Pages',
        idSite: '1',
        date: 'today',
        period: 'day',
      }),
    };

    expect(methods.shouldSkipHashSync.call(
      vm,
      'day|today',
      createContextKey({
        module: 'CoreHome',
        action: 'index',
        category: 'General_Actions',
        subcategory: 'General_Pages',
        idSite: '2',
        date: 'today',
        period: 'day',
      }),
    )).toBe(false);

    vm.lastKnownHashContextKey = createContextKey({
      module: 'CoreHome',
      action: 'index',
      category: 'General_Actions',
      subcategory: 'General_Pages',
      idSite: '1',
      segment: 'countryCode==US',
      date: 'today',
      period: 'day',
    });
    expect(methods.shouldSkipHashSync.call(
      vm,
      'day|today',
      createContextKey({
        module: 'CoreHome',
        action: 'index',
        category: 'General_Actions',
        subcategory: 'General_Pages',
        idSite: '1',
        segment: 'countryCode==NZ',
        date: 'today',
        period: 'day',
      }),
    )).toBe(false);
  });

  it('changes context key when non-ignored params change', () => {
    const contextA = methods.getContextKeyFromParsed.call({}, {
      module: 'CoreHome',
      action: 'index',
      category: 'General_Actions',
      subcategory: 'General_Pages',
      idGoal: '1',
      date: 'today',
      period: 'day',
    });
    const contextB = methods.getContextKeyFromParsed.call({}, {
      module: 'CoreHome',
      action: 'index',
      category: 'General_Actions',
      subcategory: 'General_Pages',
      idGoal: '2',
      date: 'today',
      period: 'day',
    });

    expect(contextA).not.toBe(contextB);
  });

  it('keeps context key unchanged for compare-only param changes', () => {
    const contextA = methods.getContextKeyFromParsed.call({}, {
      module: 'CoreHome',
      action: 'index',
      category: 'General_Actions',
      subcategory: 'General_Pages',
      compareSegments: ['countryCode==US'],
      comparePeriods: ['day'],
      comparePeriodType: 'previousPeriod',
      compareDates: ['2026-02-01'],
      date: 'today',
      period: 'day',
    });
    const contextB = methods.getContextKeyFromParsed.call({}, {
      module: 'CoreHome',
      action: 'index',
      category: 'General_Actions',
      subcategory: 'General_Pages',
      compareSegments: ['deviceType==desktop'],
      comparePeriods: ['range'],
      comparePeriodType: 'custom',
      compareDates: ['2026-02-01,2026-02-07'],
      date: 'today',
      period: 'day',
    });

    expect(contextA).toBe(contextB);
  });

  it('keeps context key unchanged when only compareSegments changes', () => {
    const contextA = methods.getContextKeyFromParsed.call({}, {
      module: 'CoreHome',
      action: 'index',
      category: 'General_Actions',
      subcategory: 'General_Pages',
      compareSegments: ['countryCode==US'],
      date: 'today',
      period: 'day',
    });
    const contextB = methods.getContextKeyFromParsed.call({}, {
      module: 'CoreHome',
      action: 'index',
      category: 'General_Actions',
      subcategory: 'General_Pages',
      compareSegments: ['deviceType==desktop'],
      date: 'today',
      period: 'day',
    });

    expect(contextA).toBe(contextB);
  });

  it('builds deterministic context keys regardless of object key order', () => {
    const contextA = methods.getContextKeyFromParsed.call({}, {
      module: 'CoreHome',
      action: 'index',
      category: 'General_Actions',
      subcategory: 'General_Pages',
      segment: 'countryCode==US',
      date: 'today',
      period: 'day',
    });
    const contextB = methods.getContextKeyFromParsed.call({}, {
      period: 'day',
      date: 'today',
      segment: 'countryCode==US',
      subcategory: 'General_Pages',
      category: 'General_Actions',
      action: 'index',
      module: 'CoreHome',
    });

    expect(contextA).toBe(contextB);
  });

  it('stores selection and context keys when resolving synced ui selection', () => {
    const vm: any = {
      nextHashUiSelection: null,
      nextHashSelectionKey: null,
      lastInteractionSource: 'period',
      lastKnownHashSelectionKey: null,
      lastKnownHashContextKey: null,
    };

    const synced = methods.resolveSyncedUiSelection.call(
      vm,
      'day|today',
      baseContextKey,
    );

    expect(synced).toBeNull();
    expect(vm.lastInteractionSource).toBeNull();
    expect(vm.lastKnownHashSelectionKey).toBe('day|today');
    expect(vm.lastKnownHashContextKey).toBe(baseContextKey);
  });

  it('mounted watcher re-syncs staged preset when only report context changes', async () => {
    const originalUrl = (MatomoUrl as any).url.value;
    const originalInitTopControls = window.initTopControls;
    if (!window.initTopControls) {
      window.initTopControls = jest.fn();
    }

    const setUrl = (url: string) => {
      (MatomoUrl as any).url.value = new URL(url);
    };

    setUrl(
      'https://matomo.test/index.php?module=CoreHome&action=index&period=day&date=today'
      + '#?period=day&date=today&category=General_Actions&subcategory=General_Pages',
    );

    const wrapper = mount(PeriodSelector, {
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

    await nextTick();

    (wrapper.vm as any).pendingPresetSelection = {
      id: 'last7days',
      period: 'range',
      date: 'last7',
    };
    (wrapper.vm as any).activePresetId = 'last7days';
    (wrapper.vm as any).uiSelection = { type: 'preset', id: 'last7days' };

    setUrl(
      'https://matomo.test/index.php?module=CoreHome&action=index&period=day&date=today'
      + '#?period=day&date=today&category=General_Visitors&subcategory=General_Overview',
    );
    await nextTick();

    expect((wrapper.vm as any).pendingPresetSelection).toBeNull();
    expect((wrapper.vm as any).activePresetId).toBeNull();
    expect((wrapper.vm as any).uiSelection).toEqual({ type: 'period', id: 'day' });
    expect((wrapper.vm as any).lastKnownHashContextKey).toBe(
      createContextKey({
        module: 'CoreHome',
        action: 'index',
        category: 'General_Visitors',
        subcategory: 'General_Overview',
        date: 'today',
        period: 'day',
      }),
    );

    wrapper.unmount();
    (MatomoUrl as any).url.value = originalUrl;
    window.initTopControls = originalInitTopControls;
  });

  it('applies non-range period via calendar click', () => {
    const vm: any = {
      calendarViewport: 'single',
      uiSelection: { type: 'period', id: 'week' },
      selectedPeriod: 'week',
      canInteractWithSingleCalendar: jest.fn(() => true),
      setUiSelection: jest.fn(),
      setPendingCalendarSelection: jest.fn(),
      clearPresetSelection: jest.fn(),
      commitSelectionToUrl: jest.fn(),
    };

    methods.onDatePickerSelected.call(vm, new Date('2026-02-18'));

    expect(vm.setPendingCalendarSelection).toHaveBeenCalledTimes(1);
    expect(vm.commitSelectionToUrl).toHaveBeenCalledWith(expect.any(String), 'week');
  });

  it('keeps range period pending until apply', () => {
    const vm: any = {
      uiSelection: { type: 'period', id: 'day' },
      lastInteractionSource: null,
      selectedPeriod: 'day',
      calendarViewport: 'single',
      commitSelectionToUrl: jest.fn(),
      selectedDateString: '2026-02-01,2026-02-18',
      setUiSelection(selection: { type: string; id: string }, source: string|null) {
        this.uiSelection = selection;
        this.lastInteractionSource = source;
      },
      clearPresetSelection: jest.fn(),
    };

    methods.onPeriodOptionSelected.call(vm, { period: 'range' });

    expect(vm.calendarViewport).toBe('range');
    expect(vm.commitSelectionToUrl).not.toHaveBeenCalled();

    methods.onApplyClicked.call(vm);

    expect(vm.commitSelectionToUrl).toHaveBeenCalledWith('2026-02-01,2026-02-18', 'range');
  });

  it('exits preset mode when a period option is selected and apply uses period-owned range state', () => {
    const vm: any = {
      uiSelection: { type: 'preset', id: 'last30days' },
      lastInteractionSource: 'preset',
      selectedPeriod: 'range',
      periodValue: 'day',
      calendarViewport: 'range',
      pendingPresetSelection: {
        id: 'last30days',
        period: 'range',
        date: 'last30',
      },
      stagedRangeStartDate: '2026-01-20',
      stagedRangeEndDate: '2026-02-18',
      startRangeDate: '2026-02-01',
      endRangeDate: '2026-02-18',
      selectedDateString: '2026-02-01,2026-02-18',
      commitSelectionToUrl: jest.fn(),
      setUiSelection(selection: { type: string; id: string }, source: string|null) {
        this.uiSelection = selection;
        this.lastInteractionSource = source;
      },
      clearPresetSelection() {
        this.pendingPresetSelection = null;
        this.stagedRangeStartDate = null;
        this.stagedRangeEndDate = null;
      },
    };

    methods.onPeriodOptionSelected.call(vm, { period: 'range' });

    expect(vm.uiSelection).toEqual({ type: 'period', id: 'range' });
    expect(vm.pendingPresetSelection).toBeNull();
    expect(vm.stagedRangeStartDate).toBeNull();
    expect(vm.stagedRangeEndDate).toBeNull();

    methods.onApplyClicked.call(vm);

    expect(vm.commitSelectionToUrl).toHaveBeenCalledWith('2026-02-01,2026-02-18', 'range');
    expect(vm.commitSelectionToUrl).not.toHaveBeenCalledWith('last30', 'range');
  });

  it('updates range values only when viewport is range and owner is period', () => {
    const allowedVm: any = {
      calendarViewport: 'range',
      uiSelection: { type: 'period', id: 'range' },
      selectedPeriod: 'range',
      canInteractWithRangeCalendar: jest.fn(() => true),
      isRangeValid: null,
      startRangeDate: null,
      endRangeDate: null,
      setUiSelection: jest.fn(),
    };

    methods.onRangeChange.call(allowedVm, '2026-02-01', '2026-02-18');

    expect(allowedVm.isRangeValid).toBe(true);
    expect(allowedVm.startRangeDate).toBe('2026-02-01');
    expect(allowedVm.endRangeDate).toBe('2026-02-18');

    const ignoredVm: any = {
      calendarViewport: 'range',
      uiSelection: { type: 'preset', id: 'last30days' },
      selectedPeriod: 'range',
      canInteractWithRangeCalendar: jest.fn(() => false),
      isRangeValid: false,
      startRangeDate: '2026-01-01',
      endRangeDate: '2026-01-31',
      setUiSelection: jest.fn(),
    };

    methods.onRangeChange.call(ignoredVm, '2026-02-01', '2026-02-18');

    expect(ignoredVm.isRangeValid).toBe(false);
    expect(ignoredVm.startRangeDate).toBe('2026-01-01');
    expect(ignoredVm.endRangeDate).toBe('2026-01-31');
  });

  it('keeps legacy immediate apply behavior on non-range period double click', () => {
    const vm: any = {
      periodValue: 'day',
      dateValue: new Date('2026-02-18'),
      onPeriodOptionSelected: jest.fn(),
      setPiwikPeriodAndDate: jest.fn(),
    };

    methods.onPeriodOptionDblClick.call(vm, { period: 'month' });

    expect(vm.onPeriodOptionSelected).toHaveBeenCalledWith({ period: 'month' });
    expect(vm.setPiwikPeriodAndDate).toHaveBeenCalledWith('month', vm.dateValue);
  });

  it('does not immediately apply range period double click', () => {
    const vm: any = {
      periodValue: 'day',
      dateValue: new Date('2026-02-18'),
      onPeriodOptionSelected: jest.fn(),
      setPiwikPeriodAndDate: jest.fn(),
    };

    methods.onPeriodOptionDblClick.call(vm, { period: 'range' });

    expect(vm.onPeriodOptionSelected).toHaveBeenCalledWith({ period: 'range' });
    expect(vm.setPiwikPeriodAndDate).not.toHaveBeenCalled();
  });

  it('blocks calendar commit while preset is pending', () => {
    const vm: any = {
      calendarViewport: 'single',
      uiSelection: { type: 'preset', id: 'today' },
      selectedPeriod: 'day',
      canInteractWithSingleCalendar: jest.fn(() => false),
      setUiSelection: jest.fn(),
      setPendingCalendarSelection: jest.fn(),
      clearPresetSelection: jest.fn(),
      commitSelectionToUrl: jest.fn(),
    };

    methods.onDatePickerSelected.call(vm, new Date('2026-02-18'));

    expect(vm.commitSelectionToUrl).not.toHaveBeenCalled();
    expect(vm.setPendingCalendarSelection).not.toHaveBeenCalled();
  });

  it('makes range picker readonly when a range preset owns selection', () => {
    const presetRangeVm: any = {
      uiSelection: { type: 'preset', id: 'last30days' },
      selectedPeriod: 'range',
    };
    expect(computed.isRangePresetSelection.call(presetRangeVm)).toBe(true);

    const periodRangeVm: any = {
      uiSelection: { type: 'period', id: 'range' },
      selectedPeriod: 'range',
    };
    expect(computed.isRangePresetSelection.call(periodRangeVm)).toBe(false);
  });

  it('blocks range date-cell clicks only when range preset owns selection', () => {
    const presetVm: any = {
      isRangePresetSelection: true,
    };

    const blockedEvent: any = {
      target: {
        closest: jest.fn(() => ({})),
      },
      preventDefault: jest.fn(),
      stopPropagation: jest.fn(),
    };

    methods.onRangePresetDateCellClickCapture.call(presetVm, blockedEvent);

    expect(blockedEvent.preventDefault).toHaveBeenCalledTimes(1);
    expect(blockedEvent.stopPropagation).toHaveBeenCalledTimes(1);

    const ignoredEvent: any = {
      target: {
        closest: jest.fn(() => null),
      },
      preventDefault: jest.fn(),
      stopPropagation: jest.fn(),
    };

    methods.onRangePresetDateCellClickCapture.call(presetVm, ignoredEvent);

    expect(ignoredEvent.preventDefault).not.toHaveBeenCalled();
    expect(ignoredEvent.stopPropagation).not.toHaveBeenCalled();

    const periodVm: any = {
      isRangePresetSelection: false,
    };
    methods.onRangePresetDateCellClickCapture.call(periodVm, blockedEvent);
    expect(blockedEvent.target.closest).toHaveBeenCalledTimes(1);
  });

  it('applies a clamped date when movePeriod shifts past max boundary', () => {
    const maxDate = new Date(
      window.piwik.maxDateYear,
      window.piwik.maxDateMonth - 1,
      window.piwik.maxDateDay,
    );
    const movedDate = new Date(maxDate.getTime());
    movedDate.setDate(movedDate.getDate() + 1);

    const vm: any = {
      periodValue: 'day',
      dateValue: new Date(maxDate.getTime()),
      canMovePeriod: jest.fn(() => true),
      setPiwikPeriodAndDate: jest.fn(),
    };

    methods.movePeriod.call(vm, 1);

    const appliedDate = vm.setPiwikPeriodAndDate.mock.calls[0][1] as Date;
    expect(vm.setPiwikPeriodAndDate).toHaveBeenCalledWith('day', expect.any(Date));
    expect(appliedDate.getTime()).toBe(maxDate.getTime());
    expect(appliedDate.getTime()).not.toBe(movedDate.getTime());
  });
});

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
    (wrapper.vm as any).selectedPeriod = 'day';
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
    (wrapper.vm as any).selectedPeriod = 'day';
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
    (wrapper.vm as any).selectedPeriod = 'range';
    (wrapper.vm as any).calendarViewport = 'range';
    (wrapper.vm as any).isRangeValid = false;
    (wrapper.vm as any).startRangeDate = '2026-01-01';
    (wrapper.vm as any).endRangeDate = '2026-01-31';

    wrapper.findComponent({ name: 'DateRangePicker' }).vm.$emit('range-change', {
      start: '2026-02-01',
      end: '2026-02-18',
    });
    await nextTick();

    expect((wrapper.vm as any).isRangeValid).toBe(false);
    expect((wrapper.vm as any).startRangeDate).toBe('2026-01-01');
    expect((wrapper.vm as any).endRangeDate).toBe('2026-01-31');
    wrapper.unmount();
  });

  it('allows dual-calendar interaction when period option owns selection', async () => {
    const wrapper = mountSelector();
    await nextTick();

    (wrapper.vm as any).uiSelection = { type: 'period', id: 'range' };
    (wrapper.vm as any).selectedPeriod = 'range';
    (wrapper.vm as any).calendarViewport = 'range';
    (wrapper.vm as any).isRangeValid = null;
    (wrapper.vm as any).startRangeDate = null;
    (wrapper.vm as any).endRangeDate = null;

    wrapper.findComponent({ name: 'DateRangePicker' }).vm.$emit('range-change', {
      start: '2026-02-01',
      end: '2026-02-18',
    });
    await nextTick();

    expect((wrapper.vm as any).isRangeValid).toBe(true);
    expect((wrapper.vm as any).startRangeDate).toBe('2026-02-01');
    expect((wrapper.vm as any).endRangeDate).toBe('2026-02-18');
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
});
