/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import { Periods, format } from '../Periods';
import {
  getContextKeyFromParsed,
  resolveSyncedUiSelection,
  shouldSkipHashSync,
} from './PeriodSelectorHashSync';

window.piwik.minDateYear = 2011;
window.piwik.minDateMonth = 11;
window.piwik.minDateDay = 15;
window.piwik.maxDateYear = 2014;
window.piwik.maxDateMonth = 3;
window.piwik.maxDateDay = 29;

// eslint-disable-next-line @typescript-eslint/no-var-requires
const PeriodSelector = require('./PeriodSelector.vue').default;

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
});
