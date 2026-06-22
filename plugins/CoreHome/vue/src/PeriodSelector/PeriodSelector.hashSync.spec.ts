/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import { Periods, format } from '../Periods';
import PeriodSelector from './PeriodSelector.vue';
import {
  getContextKeyFromParsed,
  resolveSyncedUiSelection,
  shouldSkipHashSync,
} from './PeriodSelector.hashSync';

window.piwik.minDateYear = 2011;
window.piwik.minDateMonth = 11;
window.piwik.minDateDay = 15;
window.piwik.maxDateYear = 2014;
window.piwik.maxDateMonth = 3;
window.piwik.maxDateDay = 29;

function createContextKey(parsed: Record<string, unknown>): string {
  return getContextKeyFromParsed(parsed);
}

function createBaseContext(overrides: Record<string, unknown> = {}): Record<string, unknown> {
  return {
    module: 'CoreHome',
    action: 'index',
    category: 'General_Actions',
    subcategory: 'General_Pages',
    date: 'today',
    period: 'day',
    ...overrides,
  };
}

describe('PeriodSelector hash sync', () => {
  const component = PeriodSelector as unknown as {
    methods: Record<string, (...args: unknown[]) => unknown>;
  };
  const { methods } = component;
  const baseContextKey = createContextKey(createBaseContext());

  it('does not skip hash sync when context changes with same period/date', () => {
    const vm: any = {
      nextHashUiSelection: null,
      lastKnownHashSelectionKey: 'day|today',
      lastKnownHashContextKey: baseContextKey,
    };

    expect(shouldSkipHashSync(
      'day|today',
      createContextKey(createBaseContext({
        category: 'General_Visitors',
        subcategory: 'General_Overview',
      })),
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
      lastKnownHashContextKey: createContextKey(createBaseContext({ idSite: '1' })),
    };

    expect(shouldSkipHashSync(
      'day|today',
      createContextKey(createBaseContext({ idSite: '2' })),
      vm.nextHashUiSelection,
      vm.lastKnownHashSelectionKey,
      vm.lastKnownHashContextKey,
    )).toBe(false);

    vm.lastKnownHashContextKey = createContextKey(createBaseContext({
      idSite: '1',
      segment: 'countryCode==US',
    }));
    expect(shouldSkipHashSync(
      'day|today',
      createContextKey(createBaseContext({
        idSite: '1',
        segment: 'countryCode==NZ',
      })),
      vm.nextHashUiSelection,
      vm.lastKnownHashSelectionKey,
      vm.lastKnownHashContextKey,
    )).toBe(false);
  });

  it('changes context key when non-ignored params change', () => {
    const contextA = getContextKeyFromParsed(createBaseContext({ idGoal: '1' }));
    const contextB = getContextKeyFromParsed(createBaseContext({ idGoal: '2' }));

    expect(contextA).not.toBe(contextB);
  });

  it('keeps context key unchanged for compare-only param changes', () => {
    const contextA = getContextKeyFromParsed(createBaseContext({
      compareSegments: ['countryCode==US'],
      comparePeriods: ['day'],
      comparePeriodType: 'previousPeriod',
      compareDates: ['2026-02-01'],
    }));
    const contextB = getContextKeyFromParsed(createBaseContext({
      compareSegments: ['deviceType==desktop'],
      comparePeriods: ['range'],
      comparePeriodType: 'custom',
      compareDates: ['2026-02-01,2026-02-07'],
    }));

    expect(contextA).toBe(contextB);
  });

  it('keeps context key unchanged when only compareSegments changes', () => {
    const contextA = getContextKeyFromParsed(createBaseContext({ compareSegments: ['countryCode==US'] }));
    const contextB = getContextKeyFromParsed(createBaseContext({ compareSegments: ['deviceType==desktop'] }));

    expect(contextA).toBe(contextB);
  });

  it('builds deterministic context keys regardless of object key order', () => {
    const parsed = createBaseContext({ segment: 'countryCode==US', });

    const contextA = getContextKeyFromParsed(parsed);
    // Reorder the keys
    const contextB = getContextKeyFromParsed({
      period: parsed.period,
      date: parsed.date,
      segment: parsed.segment,
      subcategory: parsed.subcategory,
      category: parsed.category,
      action: parsed.action,
      module: parsed.module,
    });

    expect(contextA).toBe(contextB);
  });

  it('stores selection and context keys when resolving synced ui selection', () => {
    const result = resolveSyncedUiSelection(
      'day|today',
      baseContextKey,
      null,
      null,
    );

    expect(result.syncedUiSelection).toBeNull();
    expect(result.lastInteractionSource).toBeNull();
    expect(result.lastKnownHashSelectionKey).toBe('day|today');
    expect(result.lastKnownHashContextKey).toBe(baseContextKey);
  });

  it('sets range validity true when hash sync hydrates a valid range', () => {
    const originalUrl = (MatomoUrl as any).url.value;
    const vm: any = {
      nextHashUiSelection: null,
      nextHashSelectionKey: null,
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
      setRangeStartEndFromPeriod: methods.setRangeStartEndFromPeriod,
    };

    (MatomoUrl as any).url.value = new URL(
      'https://matomo.test/index.php?module=CoreHome&action=index&period=range&date=last7'
      + '#?period=range&date=last7&category=General_Actions&subcategory=General_Pages',
    );

    methods.updateSelectedValuesFromHash.call(vm);
    const [expectedStartDate, expectedEndDate] = Periods.parse('range', 'last7').getDateRange();

    expect(vm.isRangeValid).toBe(true);
    expect(vm.appliedRangeStartDate).toBe(format(expectedStartDate));
    expect(vm.appliedRangeEndDate).toBe(format(expectedEndDate));
    expect(vm.calendarViewport).toBe('range');
    expect(vm.uiSelection).toEqual({ type: 'preset', id: 'last7days' });
    expect(vm.pendingPresetSelection).toBeNull();

    (MatomoUrl as any).url.value = originalUrl;
  });

  it('sets single calendar viewport when hash sync hydrates a valid non-range period', () => {
    const originalUrl = (MatomoUrl as any).url.value;
    const vm: any = {
      nextHashUiSelection: null,
      nextHashSelectionKey: null,
      lastKnownHashSelectionKey: null,
      lastKnownHashContextKey: null,
      periodsFiltered: ['day', 'week', 'month', 'year', 'range'],
      uiSelection: { type: 'period', id: 'range' },
      committedPeriod: 'range',
      selectedPeriod: 'range',
      committedAnchorDate: null,
      appliedRangeStartDate: null,
      appliedRangeEndDate: null,
      pendingPresetSelection: { id: 'last30days' },
      calendarViewport: 'range',
      compareAppliedSignature: '',
      compareCurrentSignature: '{}',
      isRangeValid: null,
      getCurrentContextKey: jest.fn(() => baseContextKey),
      applyUiSelectionFromHash: methods.applyUiSelectionFromHash,
      setUiSelection: methods.setUiSelection,
      clearPresetSelection: methods.clearPresetSelection,
      resetSelectedDateValues: methods.resetSelectedDateValues,
      applyDateValuesFromHash: methods.applyDateValuesFromHash,
      setRangeStartEndFromPeriod: methods.setRangeStartEndFromPeriod,
    };

    (MatomoUrl as any).url.value = new URL(
      'https://matomo.test/index.php?module=CoreHome&action=index&period=day&date=today'
      + '#?period=day&date=today&category=General_Actions&subcategory=General_Pages',
    );

    expect(() => methods.updateSelectedValuesFromHash.call(vm)).not.toThrow();
    expect(vm.isRangeValid).toBeNull();
    expect(vm.calendarViewport).toBe('single');
    expect(vm.uiSelection).toEqual({ type: 'preset', id: 'today' });

    (MatomoUrl as any).url.value = originalUrl;
  });

  it('does not preserve rolling ownership for explicit dates that match a preset', () => {
    const vm: any = {
      periodsFiltered: ['day', 'week', 'month', 'year', 'range'],
      uiSelection: { type: 'preset', id: 'thisMonth' },
      selectedPeriod: 'month',
      committedPeriod: 'month',
      committedAnchorDate: new Date('2026-02-16'),
      appliedRangeStartDate: '2026-02-01',
      appliedRangeEndDate: '2026-02-16',
      setUiSelection(selection: { type: string; id: string }, source: string|null) {
        this.uiSelection = selection;
        this.lastInteractionSource = source;
      },
      clearPresetSelection() {
        this.pendingPresetSelection = null;
      },
    };

    methods.applyUiSelectionFromHash.call(vm, 'month', '2026-02-16', null);

    expect(vm.uiSelection).toEqual({ type: 'period', id: 'month' });
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
      date: '2026-02-12,2026-02-18',
      urlDate: 'last7',
      startDate: new Date('2026-02-12'),
      endDate: new Date('2026-02-18'),
    };
    (wrapper.vm as any).uiSelection = { type: 'period', id: 'range' };

    setUrl(
      'https://matomo.test/index.php?module=CoreHome&action=index&period=day&date=today'
      + '#?period=day&date=today&category=General_Visitors&subcategory=General_Overview',
    );
    (wrapper.vm as any).updateSelectedValuesFromHash();

    expect((wrapper.vm as any).pendingPresetSelection).toBeNull();
    expect((wrapper.vm as any).activePresetId).toBe('today');
    expect((wrapper.vm as any).uiSelection).toEqual({ type: 'preset', id: 'today' });
    expect((wrapper.vm as any).isApplyEnabled()).toBe(true);
    expect((wrapper.vm as any).lastKnownHashContextKey).toBe(
      createContextKey(createBaseContext({ category: 'General_Visitors', subcategory: 'General_Overview' })),
    );

    wrapper.unmount();
    (MatomoUrl as any).url.value = originalUrl;
    window.initTopControls = originalInitTopControls;
  });
});
