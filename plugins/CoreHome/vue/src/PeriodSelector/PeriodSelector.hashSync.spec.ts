/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import { Periods, format } from '../Periods';
import { getTokenPresetIdFromPeriodAndDate } from './PresetDateRangeResolver';
import {
  applyDateValuesFromHash,
  applyUiSelectionFromHash,
  getContextKeyFromParsed,
  resetSelectedDateValues,
  resolveSyncedUiSelection,
  shouldSkipHashSync,
  updateSelectedValuesFromHash,
} from './PeriodSelectorHashSync';
import { RANGE_PERIOD, isSingleCalendarPeriod } from './PeriodSelector.types';
import PeriodSelector from './PeriodSelector.vue';

window.piwik.minDateYear = 2011;
window.piwik.minDateMonth = 11;
window.piwik.minDateDay = 15;
window.piwik.maxDateYear = 2014;
window.piwik.maxDateMonth = 3;
window.piwik.maxDateDay = 29;

function createContextKey(parsed: Record<string, unknown>): string {
  return getContextKeyFromParsed(parsed);
}

describe('PeriodSelector hash sync', () => {
  const component = PeriodSelector as unknown as {
    methods: Record<string, (...args: unknown[]) => unknown>;
  };
  const { methods } = component;
  const baseContextKey = createContextKey({
    module: 'CoreHome',
    action: 'index',
    category: 'General_Actions',
    subcategory: 'General_Pages',
    date: 'today',
    period: 'day',
  });

  it('does not skip hash sync when context changes with same period/date', () => {
    const vm: any = {
      nextHashUiSelection: null,
      lastKnownHashSelectionKey: 'day|today',
      lastKnownHashContextKey: baseContextKey,
    };

    expect(shouldSkipHashSync(
      'day|today',
      createContextKey({
        module: 'CoreHome',
        action: 'index',
        category: 'General_Visitors',
        subcategory: 'General_Overview',
        date: 'today',
        period: 'day',
      }),
      vm.nextHashUiSelection,
      vm.lastKnownHashSelectionKey,
      vm.lastKnownHashContextKey,
    )).toBe(false);
  });

  it('skips hash sync only when both selection and context keys are unchanged and no pending ui sync', () => {
    const vm: any = {
      nextHashUiSelection: null,
      lastKnownHashSelectionKey: 'day|today',
      lastKnownHashContextKey: baseContextKey,
    };

    expect(shouldSkipHashSync(
      'day|today',
      baseContextKey,
      vm.nextHashUiSelection,
      vm.lastKnownHashSelectionKey,
      vm.lastKnownHashContextKey,
    )).toBe(true);

    vm.nextHashUiSelection = { type: 'period', id: 'day' };
    expect(shouldSkipHashSync(
      'day|today',
      baseContextKey,
      vm.nextHashUiSelection,
      vm.lastKnownHashSelectionKey,
      vm.lastKnownHashContextKey,
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

    expect(shouldSkipHashSync(
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
      vm.nextHashUiSelection,
      vm.lastKnownHashSelectionKey,
      vm.lastKnownHashContextKey,
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
    expect(shouldSkipHashSync(
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
      vm.nextHashUiSelection,
      vm.lastKnownHashSelectionKey,
      vm.lastKnownHashContextKey,
    )).toBe(false);
  });

  it('changes context key when non-ignored params change', () => {
    const contextA = getContextKeyFromParsed({
      module: 'CoreHome',
      action: 'index',
      category: 'General_Actions',
      subcategory: 'General_Pages',
      idGoal: '1',
      date: 'today',
      period: 'day',
    });
    const contextB = getContextKeyFromParsed({
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
    const contextA = getContextKeyFromParsed({
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
    const contextB = getContextKeyFromParsed({
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
    const contextA = getContextKeyFromParsed({
      module: 'CoreHome',
      action: 'index',
      category: 'General_Actions',
      subcategory: 'General_Pages',
      compareSegments: ['countryCode==US'],
      date: 'today',
      period: 'day',
    });
    const contextB = getContextKeyFromParsed({
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
    const contextA = getContextKeyFromParsed({
      module: 'CoreHome',
      action: 'index',
      category: 'General_Actions',
      subcategory: 'General_Pages',
      segment: 'countryCode==US',
      date: 'today',
      period: 'day',
    });
    const contextB = getContextKeyFromParsed({
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
    const result = resolveSyncedUiSelection(
      'day|today',
      baseContextKey,
      null,
      null,
      null,
    );

    expect(result.syncedUiSelection).toBeNull();
    expect(result.lastInteractionSource).toBeNull();
    expect(result.lastKnownHashSelectionKey).toBe('day|today');
    expect(result.lastKnownHashContextKey).toBe(baseContextKey);
  });

  it('applies preset ownership from tokenized hash values in hash sync module', () => {
    const state: any = {
      periodsFiltered: ['day', 'week', 'month', 'year', 'range'],
      uiSelection: { type: 'period', id: 'day' },
      activePresetId: null,
      pendingPresetSelection: { id: 'last30days' },
    };

    applyUiSelectionFromHash(state, 'range', 'last7', null, {
      getTokenPresetIdFromPeriodAndDate: () => 'last7days',
      setUiSelection(selection: { type: string; id: string }) {
        state.uiSelection = selection;
      },
      clearPresetSelection() {
        state.activePresetId = null;
        state.pendingPresetSelection = null;
      },
    });

    expect(state.uiSelection).toEqual({ type: 'preset', id: 'last7days' });
    expect(state.activePresetId).toBe('last7days');
    expect(state.pendingPresetSelection).toBeNull();
  });

  it('resets selected date values in hash sync module', () => {
    const state: any = {
      committedAnchorDate: new Date('2026-02-18'),
      appliedRangeStartDate: '2026-02-01',
      appliedRangeEndDate: '2026-02-18',
    };

    resetSelectedDateValues(state);

    expect(state.committedAnchorDate).toBeNull();
    expect(state.appliedRangeStartDate).toBeNull();
    expect(state.appliedRangeEndDate).toBeNull();
  });

  it('applies non-range date values through hash sync module', () => {
    const state: any = {
      committedAnchorDate: null,
      appliedRangeStartDate: null,
      appliedRangeEndDate: null,
      singleCalendarPeriod: 'day',
      singleCalendarSelectedDate: null,
    };

    applyDateValuesFromHash(state, 'week', '2026-02-18', {
      rangePeriod: RANGE_PERIOD,
      parseRange: () => [new Date(), new Date()],
      parseDate: (value: string) => new Date(value),
      format,
      siteMinAllowedDate: new Date(2011, 10, 15),
      siteMaxAllowedDate: new Date(2014, 2, 29),
      isSingleCalendarPeriod,
      setRangeStartEndFromPeriod: jest.fn(),
    });

    expect(state.committedAnchorDate).toEqual(new Date('2026-02-18'));
    expect(state.singleCalendarPeriod).toBe('week');
    expect(state.singleCalendarSelectedDate).toEqual(new Date('2026-02-18'));
  });

  it('sets range validity true and clamps resolved range when hash sync hydrates a tokenized range', () => {
    const originalUrl = (MatomoUrl as any).url.value;
    const vm: any = {
      nextHashUiSelection: null,
      nextHashSelectionKey: null,
      nextHashContextKey: null,
      lastKnownHashSelectionKey: null,
      lastKnownHashContextKey: null,
      periodsFiltered: ['day', 'week', 'month', 'year', 'range'],
      uiSelection: { type: 'period', id: 'day' },
      committedPeriod: 'day',
      selectedPeriod: 'day',
      committedAnchorDate: null,
      appliedRangeStartDate: null,
      appliedRangeEndDate: null,
      pendingPresetSelection: { id: 'last30days' },
      calendarViewport: 'single',
      compareAppliedSignature: '',
      compareCurrentSignature: '{}',
      isRangeValid: null,
      getCurrentContextKey: jest.fn(() => baseContextKey),
      applyUiSelectionFromHash: methods.applyUiSelectionFromHash,
      setUiSelection: methods.setUiSelection,
      clearPresetSelection: methods.clearPresetSelection,
      resetSelectedDateValues: methods.resetSelectedDateValues,
      applyDateValuesFromHash: methods.applyDateValuesFromHash,
    };

    (MatomoUrl as any).url.value = new URL(
      'https://matomo.test/index.php?module=CoreHome&action=index&period=range&date=last7'
      + '#?period=range&date=last7&category=General_Actions&subcategory=General_Pages',
    );

    updateSelectedValuesFromHash(vm, {
      parsed: MatomoUrl.parsed.value as Record<string, unknown>,
      currentContextKey: baseContextKey,
      rangePeriod: RANGE_PERIOD,
      parsePeriod: (periodValue: string, dateValue: string) => {
        Periods.parse(periodValue, dateValue);
      },
      getTokenPresetIdFromPeriodAndDate,
      setUiSelection: (selection: { type: string; id: string }, source: null) => {
        methods.setUiSelection.call(vm, selection, source);
      },
      clearPresetSelection: () => {
        methods.clearPresetSelection.call(vm);
      },
      parseRange: (periodValue: string, dateValue: string) => {
        const parsedRangePeriod = Periods.get(periodValue).parse(dateValue);
        return parsedRangePeriod.getDateRange() as [Date, Date];
      },
      parseDate: (dateValue: string) => new Date(dateValue),
      format,
      siteMinAllowedDate: new Date(2011, 10, 15),
      siteMaxAllowedDate: new Date(2014, 2, 29),
      isSingleCalendarPeriod,
      setRangeStartEndFromPeriod: (periodValue: string, dateValue: string) => {
        vm.setRangeStartEndFromPeriod(periodValue, dateValue);
      },
    });
    const [expectedStartDate, expectedEndDate] = Periods.parse('range', 'last7').getDateRange();

    expect(vm.isRangeValid).toBe(true);
    expect(vm.appliedRangeStartDate).toBe(
      format(expectedStartDate < new Date(2011, 10, 15) ? new Date(2011, 10, 15) : expectedStartDate),
    );
    expect(vm.appliedRangeEndDate).toBe(
      format(expectedEndDate > new Date(2014, 2, 29) ? new Date(2014, 2, 29) : expectedEndDate),
    );
    expect(vm.uiSelection).toEqual({ type: 'preset', id: 'last7days' });
    expect(vm.activePresetId).toBe('last7days');
    expect(vm.committedPeriod).toBe('range');
    expect(vm.calendarViewport).toBe('range');

    (MatomoUrl as any).url.value = originalUrl;
  });

  it('re-syncs staged preset when only report context changes', () => {
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
    (wrapper.vm as any).updateSelectedValuesFromHash();

    (wrapper.vm as any).pendingPresetSelection = {
      id: 'last7days',
      period: 'range',
      date: 'last7',
      startDate: new Date('2026-02-12'),
      endDate: new Date('2026-02-18'),
    };
    (wrapper.vm as any).activePresetId = 'last7days';
    (wrapper.vm as any).uiSelection = { type: 'preset', id: 'last7days' };

    setUrl(
      'https://matomo.test/index.php?module=CoreHome&action=index&period=day&date=today'
      + '#?period=day&date=today&category=General_Visitors&subcategory=General_Overview',
    );
    (wrapper.vm as any).updateSelectedValuesFromHash();

    expect((wrapper.vm as any).pendingPresetSelection).toBeNull();
    expect((wrapper.vm as any).activePresetId).toBe('today');
    expect((wrapper.vm as any).uiSelection).toEqual({ type: 'preset', id: 'today' });
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

  it('does not reapply staged ui selection when context changes but selection key stays the same', () => {
    const result = resolveSyncedUiSelection(
      'day|today',
      createContextKey({
        module: 'CoreHome',
        action: 'index',
        category: 'General_Visitors',
        subcategory: 'General_Overview',
        period: 'day',
        date: 'today',
      }),
      { type: 'preset', id: 'last7days' },
      'day|today',
      createContextKey({
        module: 'CoreHome',
        action: 'index',
        category: 'General_Actions',
        subcategory: 'General_Pages',
        period: 'day',
        date: 'today',
      }),
    );

    expect(result.syncedUiSelection).toBeNull();
    expect(result.nextHashUiSelection).toBeNull();
    expect(result.nextHashSelectionKey).toBeNull();
    expect(result.nextHashContextKey).toBeNull();
  });
});
