/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import PeriodSelector from './PeriodSelector.vue';
import { Periods, format } from '../Periods';
import MatomoUrl from '../MatomoUrl/MatomoUrl';

window.piwik.minDateYear = 2011;
window.piwik.minDateMonth = 11;
window.piwik.minDateDay = 15;
window.piwik.maxDateYear = 2014;
window.piwik.maxDateMonth = 3;
window.piwik.maxDateDay = 29;

describe('PeriodSelector', () => {
  const component = PeriodSelector as unknown as {
    methods: Record<string, (...args: unknown[]) => unknown>;
    computed: Record<string, (...args: unknown[]) => unknown>;
  };
  const { methods, computed } = component;
  function callOnApplyClicked(vm: any) {
    vm.hasPendingPresetSelectionOwnedByUi = methods.hasPendingPresetSelectionOwnedByUi;
    vm.shouldCloseSelectorWithoutApplying = methods.shouldCloseSelectorWithoutApplying;
    vm.hasCommittedRangeBounds = methods.hasCommittedRangeBounds;
    vm.applyPendingPresetSelection = methods.applyPendingPresetSelection;
    vm.applyRangeSelection = methods.applyRangeSelection;
    vm.applyNonRangeOrCompareChanges = methods.applyNonRangeOrCompareChanges;
    methods.onApplyClicked.call(vm);
  }
  function createApplyVm(overrides: Record<string, unknown> = {}): any {
    return {
      pendingPresetSelection: null,
      uiSelection: { type: 'period', id: 'day' },
      selectedPeriod: 'day',
      committedPeriod: 'day',
      committedAnchorDate: new Date('2026-02-18'),
      appliedRangeStartDate: '2026-02-01',
      appliedRangeEndDate: '2026-02-18',
      selectedDateString: '2026-02-01,2026-02-18',
      isCompareDirty: true,
      isComparing: false,
      comparePeriodType: 'previousPeriod',
      isCompareRangeValid: jest.fn(() => true),
      hasPendingNonRangePeriodChange: false,
      getCurrentRollingDateParamIfOwnedByPreset: jest.fn(() => null),
      closePeriodSelector: jest.fn(),
      commitSelectionToUrl: jest.fn(),
      ...overrides,
    };
  }
  function expectCommitSelection(vm: any, date: string, period: string) {
    expect(vm.commitSelectionToUrl).toHaveBeenCalledWith(date, period);
  }
  function expectNoCommitSelection(vm: any) {
    expect(vm.commitSelectionToUrl).not.toHaveBeenCalled();
  }
  it('stages preset selection and switches to dual calendar for range presets', () => {
    const appliedDate = new Date('2026-02-18');
    const vm: any = {
      periodsFiltered: ['day', 'week', 'month', 'year', 'range'],
      uiSelection: { type: 'period', id: 'day' },
      lastInteractionSource: null,
      activePresetId: null,
      pendingPresetSelection: null,
      selectedPeriod: 'day',
      committedPeriod: 'day',
      calendarViewport: 'single',
      committedAnchorDate: appliedDate,
      appliedRangeStartDate: '2026-02-18',
      appliedRangeEndDate: '2026-02-18',
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
    expect(vm.committedAnchorDate).toBe(appliedDate);
    expect(vm.appliedRangeStartDate).toBe('2026-02-18');
    expect(vm.appliedRangeEndDate).toBe('2026-02-18');
    expect(vm.pendingPresetSelection?.startDate).toEqual(new Date('2026-02-12'));
    expect(vm.pendingPresetSelection?.endDate).toEqual(new Date('2026-02-18'));
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
      singleCalendarSelectedDate: null,
      committedAnchorDate: new Date('2026-02-18'),
      appliedRangeStartDate: '2026-02-18',
      appliedRangeEndDate: '2026-02-18',
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
    expect(vm.pendingPresetSelection?.id).toBe('yesterday');
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
    expect(vm.isRangeValid).toBe(false);
  });

  it('applies preset only on apply click', () => {
    const presetStart = new Date('2026-01-20');
    const presetEnd = new Date('2026-02-18');
    const vm: any = createApplyVm({
      uiSelection: { type: 'preset', id: 'last30days' },
      pendingPresetSelection: {
        id: 'last30days',
        period: 'range',
        date: 'last30',
        startDate: presetStart,
        endDate: presetEnd,
      },
      committedPeriod: 'day',
      appliedRangeStartDate: '2026-02-18',
      appliedRangeEndDate: '2026-02-18',
    });

    callOnApplyClicked(vm);

    expect(vm.committedPeriod).toBe('range');
    expect(vm.committedAnchorDate).toBe(presetStart);
    expect(vm.appliedRangeStartDate).toBe('2026-01-20');
    expect(vm.appliedRangeEndDate).toBe('2026-02-18');
    expectCommitSelection(vm, 'last30', 'range');
  });

  it('keeps thisWeekMonToday compatibility semantics when applying preset', () => {
    const vm: any = createApplyVm({
      uiSelection: { type: 'preset', id: 'thisWeekMonToday' },
      pendingPresetSelection: {
        id: 'thisWeekMonToday',
        period: 'week',
        date: 'today',
        startDate: new Date('2026-02-16'),
        endDate: new Date('2026-02-18'),
      },
      committedPeriod: 'day',
      appliedRangeStartDate: '2026-02-18',
      appliedRangeEndDate: '2026-02-18',
    });

    callOnApplyClicked(vm);

    expect(vm.committedPeriod).toBe('week');
    expectCommitSelection(vm, 'today', 'week');
  });

  it('applies rolling preset token even when staged range is clamped', () => {
    const vm: any = createApplyVm({
      uiSelection: { type: 'preset', id: 'last7days' },
      pendingPresetSelection: {
        id: 'last7days',
        period: 'range',
        date: 'last7',
        startDate: new Date('2026-02-12'),
        endDate: new Date('2026-02-18'),
      },
      appliedRangeStartDate: '2026-02-14',
      appliedRangeEndDate: '2026-02-15',
      committedPeriod: 'day',
    });

    callOnApplyClicked(vm);

    expect(vm.committedPeriod).toBe('range');
    expectCommitSelection(vm, 'last7', 'range');
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
      committedPeriod: 'day',
      calendarViewport: 'single',
      committedAnchorDate: appliedDate,
      appliedRangeStartDate: '2026-02-18',
      appliedRangeEndDate: '2026-02-18',
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
      pendingPresetSelection: {
        id: 'last7days',
        period: 'range',
        date: 'last7',
        startDate: new Date('2026-02-12'),
        endDate: new Date('2026-02-18'),
      },
      appliedRangeStartDate: '2026-01-01',
      appliedRangeEndDate: '2026-01-31',
      isRangePresetSelection: true,
    };

    expect(computed.displayRangeStartDate.call(vm)).toBe('2026-02-12');
    expect(computed.displayRangeEndDate.call(vm)).toBe('2026-02-18');
  });

  it('marks non-range period change as pending and does not apply on apply click', () => {
    const vm: any = createApplyVm({
      uiSelection: { type: 'period', id: 'day' },
      lastInteractionSource: null,
      selectedPeriod: 'day',
      calendarViewport: 'single',
      singleCalendarSelectedDate: new Date('2026-02-18'),
      activePresetId: 'today',
      pendingPresetSelection: { id: 'today' },
      isCompareDirty: false,
      hasPendingNonRangePeriodChange: true,
      setUiSelection(selection: { type: string; id: string }, source: string|null) {
        this.uiSelection = selection;
        this.lastInteractionSource = source;
      },
      clearPresetSelection() {
        this.activePresetId = null;
        this.pendingPresetSelection = null;
      },
    });

    methods.onPeriodOptionSelected.call(vm, { period: 'week' });

    expect(vm.selectedPeriod).toBe('week');
    expect(vm.calendarViewport).toBe('single');
    expect(vm.singleCalendarSelectedDate).toBeNull();
    expect(vm.pendingPresetSelection).toBeNull();
    expect(vm.activePresetId).toBeNull();

    callOnApplyClicked(vm);

    expectNoCommitSelection(vm);
  });

  it('restores applied date when returning to applied period without calendar click', () => {
    const appliedDate = new Date('2026-03-03');
    const vm: any = {
      uiSelection: { type: 'period', id: 'day' },
      lastInteractionSource: null,
      selectedPeriod: 'day',
      committedPeriod: 'day',
      committedAnchorDate: appliedDate,
      calendarViewport: 'single',
      singleCalendarPeriod: 'day',
      singleCalendarSelectedDate: appliedDate,
      clearPresetSelection: jest.fn(),
      setUiSelection(selection: { type: string; id: string }, source: string|null) {
        this.uiSelection = selection;
        this.lastInteractionSource = source;
      },
    };

    methods.onPeriodOptionSelected.call(vm, { period: 'week' });
    expect(vm.singleCalendarSelectedDate).toBeNull();

    methods.onPeriodOptionSelected.call(vm, { period: 'day' });
    expect(vm.singleCalendarSelectedDate).toBe(appliedDate);
  });

  it('treats non-range pending state as period-change-only', () => {
    const samePeriodVm: any = {
      uiSelection: { type: 'period', id: 'day' },
      lastInteractionSource: 'period',
      selectedPeriod: 'day',
      committedPeriod: 'day',
    };
    expect(computed.hasPendingNonRangePeriodChange.call(samePeriodVm)).toBe(false);

    const changedPeriodVm: any = {
      uiSelection: { type: 'period', id: 'week' },
      lastInteractionSource: 'period',
      selectedPeriod: 'week',
      committedPeriod: 'day',
    };
    expect(computed.hasPendingNonRangePeriodChange.call(changedPeriodVm)).toBe(true);
  });

  it('rehydrates preset ownership from tokenized hash values', () => {
    const vm: any = {
      periodsFiltered: ['day', 'week', 'month', 'year', 'range'],
      uiSelection: { type: 'period', id: 'day' },
      activePresetId: null,
      pendingPresetSelection: { id: 'last30days' },
      setUiSelection: jest.fn(),
      clearPresetSelection: jest.fn(),
    };

    methods.applyUiSelectionFromHash.call(vm, 'range', 'last7', null);

    expect(vm.uiSelection).toEqual({ type: 'preset', id: 'last7days' });
    expect(vm.activePresetId).toBe('last7days');
    expect(vm.pendingPresetSelection).toBeNull();
    expect(vm.setUiSelection).not.toHaveBeenCalled();
    expect(vm.clearPresetSelection).not.toHaveBeenCalled();
  });

  it('does not infer quarter presets from explicit range values in hash sync', () => {
    const vm: any = {
      periodsFiltered: ['day', 'week', 'month', 'year', 'range'],
      uiSelection: { type: 'preset', id: 'last7days' },
      activePresetId: 'last7days',
      setUiSelection(selection: { type: string; id: string }, source: string|null) {
        this.uiSelection = selection;
        this.lastInteractionSource = source;
      },
      clearPresetSelection() {
        this.activePresetId = null;
        this.pendingPresetSelection = null;
      },
    };

    methods.applyUiSelectionFromHash.call(vm, 'range', '2026-01-01,2026-03-31', null);

    expect(vm.uiSelection).toEqual({ type: 'period', id: 'range' });
    expect(vm.activePresetId).toBeNull();
  });

  it('keeps rolling range token on compare-only apply when preset owns selection', () => {
    const vm: any = createApplyVm({
      selectedPeriod: 'range',
      committedPeriod: 'range',
      appliedRangeStartDate: '2026-02-01',
      appliedRangeEndDate: '2026-02-18',
      getCurrentRollingDateParamIfOwnedByPreset: jest.fn(() => 'last7'),
    });

    callOnApplyClicked(vm);

    expectCommitSelection(vm, 'last7', 'range');
  });

  it('commits explicit range date on compare-only apply when range is not preset-owned', () => {
    const vm: any = createApplyVm({
      selectedPeriod: 'range',
      committedPeriod: 'range',
      appliedRangeStartDate: '2026-02-01',
      appliedRangeEndDate: '2026-02-18',
      getCurrentRollingDateParamIfOwnedByPreset: jest.fn(() => null),
    });

    callOnApplyClicked(vm);

    expectCommitSelection(vm, '2026-02-01,2026-02-18', 'range');
  });

  it('keeps rolling non-range token on compare-only apply when preset owns selection', () => {
    const vm: any = createApplyVm({
      selectedPeriod: 'week',
      committedPeriod: 'week',
      committedAnchorDate: new Date('2026-02-18'),
      getCurrentRollingDateParamIfOwnedByPreset: jest.fn(() => 'today'),
    });

    callOnApplyClicked(vm);

    expectCommitSelection(vm, 'today', 'week');
  });

  it('commits explicit non-range date on compare-only apply when selection is already committed', () => {
    const vm: any = createApplyVm({
      selectedPeriod: 'week',
      committedPeriod: 'week',
      committedAnchorDate: new Date('2026-02-18'),
      getCurrentRollingDateParamIfOwnedByPreset: jest.fn(() => null),
    });

    callOnApplyClicked(vm);

    expectCommitSelection(vm, '2026-02-18', 'week');
  });

  it('closes selector for non-range preset no-op apply when compare is unchanged', () => {
    const vm: any = createApplyVm({
      uiSelection: { type: 'preset', id: 'today' },
      selectedPeriod: 'day',
      committedPeriod: 'day',
      isComparing: false,
      comparePeriodType: 'previousPeriod',
      isCompareRangeValid: jest.fn(() => true),
      isCompareDirty: false,
    });

    callOnApplyClicked(vm);

    expect(vm.closePeriodSelector).toHaveBeenCalledTimes(1);
    expectNoCommitSelection(vm);
  });

  it('disables apply for pending non-range period option selection', () => {
    const vm: any = {
      uiSelection: { type: 'period', id: 'week' },
      hasPendingNonRangePeriodChange: true,
      selectedPeriod: 'week',
      pendingPresetSelection: null,
      isRangeValid: true,
      isComparing: false,
      comparePeriodType: 'previousPeriod',
      isCompareRangeValid: jest.fn(() => true),
    };

    expect(methods.isApplyEnabled.call(vm)).toBe(false);
  });

  it('enables apply for a valid period-owned range selection', () => {
    const vm: any = {
      uiSelection: { type: 'period', id: 'range' },
      selectedPeriod: 'range',
      hasPendingNonRangePeriodChange: false,
      pendingPresetSelection: null,
      isRangeValid: true,
      isComparing: false,
      comparePeriodType: 'previousPeriod',
      isCompareRangeValid: jest.fn(() => true),
    };

    expect(methods.isApplyEnabled.call(vm)).toBe(true);
  });

  it('disables apply when opening with non-range period option selected and no compare changes', () => {
    const vm: any = {
      uiSelection: { type: 'period', id: 'day' },
      selectedPeriod: 'day',
      isCompareDirty: false,
      hasPendingNonRangePeriodChange: false,
      pendingPresetSelection: null,
      isRangeValid: true,
      isComparing: false,
      comparePeriodType: 'previousPeriod',
      isCompareRangeValid: jest.fn(() => true),
    };

    expect(methods.isApplyEnabled.call(vm)).toBe(false);
  });

  it('intentional: keeps Apply disabled for period-owned non-range selection even when compare is dirty', () => {
    const vm: any = {
      uiSelection: { type: 'period', id: 'day' },
      selectedPeriod: 'day',
      isCompareDirty: true,
      hasPendingNonRangePeriodChange: false,
      pendingPresetSelection: null,
      isRangeValid: true,
      isComparing: true,
      comparePeriodType: 'custom',
      isCompareRangeValid: jest.fn(() => true),
    };

    expect(methods.isApplyEnabled.call(vm)).toBe(false);
  });

  it('intentional: compare edits in period-owned non-range mode require calendar click to commit', () => {
    const originalInitTopControls = window.initTopControls;
    window.initTopControls = jest.fn();
    const updateLocationSpy = jest.spyOn(MatomoUrl, 'updateLocation');
    const selectedDate = new Date('2026-02-19');
    const vm: any = {
      calendarViewport: 'single',
      uiSelection: { type: 'period', id: 'day' },
      selectedPeriod: 'day',
      committedPeriod: 'day',
      committedAnchorDate: new Date('2026-02-18'),
      appliedRangeStartDate: '2026-02-18',
      appliedRangeEndDate: '2026-02-18',
      singleCalendarPeriod: 'day',
      singleCalendarSelectedDate: new Date('2026-02-18'),
      selectedComparisonParams: {
        comparePeriods: ['day'],
        comparePeriodType: 'previousPeriod',
        compareDates: ['2026-02-18'],
      },
      compareCurrentSignature: '{"isComparing":true,"comparePeriodType":"previousPeriod"}',
      compareAppliedSignature: '',
      nextHashUiSelection: null,
      nextHashSelectionKey: null,
      isLoadingNewPage: false,
      canInteractWithSingleCalendar: jest.fn(() => true),
      clearPresetSelection: jest.fn(),
      closePeriodSelector: jest.fn(),
      setUiSelection(selection: { type: string; id: string }, source: string|null) {
        this.uiSelection = selection;
        this.lastInteractionSource = source;
      },
      setRangeStartEndFromPeriod: methods.setRangeStartEndFromPeriod,
      setPendingPeriodAndDate: methods.setPendingPeriodAndDate,
      propagateNewUrlParams: methods.propagateNewUrlParams,
      commitSelectionToUrl: methods.commitSelectionToUrl,
    };

    try {
      methods.onDatePickerSelected.call(vm, selectedDate);

      expect(updateLocationSpy).toHaveBeenCalledWith(expect.objectContaining({
        date: '2026-02-19',
        period: 'day',
        comparePeriods: ['day'],
        comparePeriodType: 'previousPeriod',
        compareDates: ['2026-02-18'],
      }));
    } finally {
      updateLocationSpy.mockRestore();
      window.initTopControls = originalInitTopControls;
    }
  });

  it('enables apply immediately when date range period option is selected', () => {
    const vm: any = {
      uiSelection: { type: 'period', id: 'day' },
      lastInteractionSource: null,
      selectedPeriod: 'day',
      calendarViewport: 'single',
      isRangeValid: null,
      isCompareDirty: false,
      hasPendingNonRangePeriodChange: false,
      pendingPresetSelection: null,
      isComparing: false,
      comparePeriodType: 'previousPeriod',
      setUiSelection(selection: { type: string; id: string }, source: string|null) {
        this.uiSelection = selection;
        this.lastInteractionSource = source;
      },
      clearPresetSelection: jest.fn(),
      isCompareRangeValid: jest.fn(() => true),
    };

    methods.onPeriodOptionSelected.call(vm, { period: 'range' });

    expect(vm.isRangeValid).toBe(true);
    expect(methods.isApplyEnabled.call(vm)).toBe(true);
  });

  it('applies non-range period via calendar click', () => {
    const vm: any = {
      calendarViewport: 'single',
      uiSelection: { type: 'period', id: 'week' },
      selectedPeriod: 'week',
      canInteractWithSingleCalendar: jest.fn(() => true),
      setUiSelection: jest.fn(),
      setPendingPeriodAndDate: jest.fn(),
      clearPresetSelection: jest.fn(),
      commitSelectionToUrl: jest.fn(),
    };

    methods.onDatePickerSelected.call(vm, new Date('2026-02-18'));

    expect(vm.setPendingPeriodAndDate).toHaveBeenCalledTimes(1);
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
      getCurrentRollingDateParamIfOwnedByPreset: jest.fn(() => null),
      setUiSelection(selection: { type: string; id: string }, source: string|null) {
        this.uiSelection = selection;
        this.lastInteractionSource = source;
      },
      clearPresetSelection: jest.fn(),
    };

    methods.onPeriodOptionSelected.call(vm, { period: 'range' });

    expect(vm.calendarViewport).toBe('range');
    expect(vm.commitSelectionToUrl).not.toHaveBeenCalled();

    callOnApplyClicked(vm);

    expect(vm.commitSelectionToUrl).toHaveBeenCalledWith('2026-02-01,2026-02-18', 'range');
  });

  it('exits preset mode when a period option is selected and apply uses period-owned range state', () => {
    const vm: any = {
      uiSelection: { type: 'preset', id: 'last30days' },
      lastInteractionSource: 'preset',
      selectedPeriod: 'range',
      committedPeriod: 'day',
      calendarViewport: 'range',
      pendingPresetSelection: {
        id: 'last30days',
        period: 'range',
        date: 'last30',
        startDate: new Date('2026-01-20'),
        endDate: new Date('2026-02-18'),
      },
      appliedRangeStartDate: '2026-02-01',
      appliedRangeEndDate: '2026-02-18',
      selectedDateString: '2026-02-01,2026-02-18',
      getCurrentRollingDateParamIfOwnedByPreset: jest.fn(() => null),
      commitSelectionToUrl: jest.fn(),
      setUiSelection(selection: { type: string; id: string }, source: string|null) {
        this.uiSelection = selection;
        this.lastInteractionSource = source;
      },
      clearPresetSelection() {
        this.pendingPresetSelection = null;
      },
    };

    methods.onPeriodOptionSelected.call(vm, { period: 'range' });

    expect(vm.uiSelection).toEqual({ type: 'period', id: 'range' });
    expect(vm.pendingPresetSelection).toBeNull();

    callOnApplyClicked(vm);

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
      appliedRangeStartDate: null,
      appliedRangeEndDate: null,
      setUiSelection: jest.fn(),
    };

    methods.onRangeChange.call(allowedVm, '2026-02-01', '2026-02-18');

    expect(allowedVm.isRangeValid).toBe(true);
    expect(allowedVm.appliedRangeStartDate).toBe('2026-02-01');
    expect(allowedVm.appliedRangeEndDate).toBe('2026-02-18');

    const ignoredVm: any = {
      calendarViewport: 'range',
      uiSelection: { type: 'preset', id: 'last30days' },
      selectedPeriod: 'range',
      canInteractWithRangeCalendar: jest.fn(() => false),
      isRangeValid: false,
      appliedRangeStartDate: '2026-01-01',
      appliedRangeEndDate: '2026-01-31',
      setUiSelection: jest.fn(),
    };

    methods.onRangeChange.call(ignoredVm, '2026-02-01', '2026-02-18');

    expect(ignoredVm.isRangeValid).toBe(false);
    expect(ignoredVm.appliedRangeStartDate).toBe('2026-01-01');
    expect(ignoredVm.appliedRangeEndDate).toBe('2026-01-31');
  });

  it('applies preset immediately on double click', () => {
    const selection = {
      id: 'last30days',
      period: 'range',
      date: 'last30',
      startDate: new Date('2026-01-20'),
      endDate: new Date('2026-02-18'),
    };
    const vm: any = {
      onPresetDateRangeSelected: jest.fn(),
      onApplyClicked: jest.fn(),
      hasInvalidCustomComparison: jest.fn(() => false),
    };

    methods.onPresetDateRangeDblClick.call(vm, selection);

    expect(vm.onPresetDateRangeSelected).toHaveBeenCalledWith(selection);
    expect(vm.onApplyClicked).toHaveBeenCalled();
  });

  it('does not immediately apply preset double click when custom comparison range is invalid', () => {
    const selection = {
      id: 'last30days',
      period: 'range',
      date: 'last30',
      startDate: new Date('2026-01-20'),
      endDate: new Date('2026-02-18'),
    };
    const vm: any = {
      onPresetDateRangeSelected: jest.fn(),
      onApplyClicked: jest.fn(),
      hasInvalidCustomComparison: jest.fn(() => true),
      showInvalidComparisonMessage: jest.fn(),
    };

    methods.onPresetDateRangeDblClick.call(vm, selection);

    expect(vm.onPresetDateRangeSelected).toHaveBeenCalledWith(selection);
    expect(vm.onApplyClicked).not.toHaveBeenCalled();
    expect(vm.showInvalidComparisonMessage).toHaveBeenCalled();
  });

  it('keeps legacy immediate apply behavior on non-range period double click', () => {
    const vm: any = {
      committedPeriod: 'day',
      committedAnchorDate: new Date('2026-02-18'),
      onPeriodOptionSelected: jest.fn(),
      setPiwikPeriodAndDate: jest.fn(),
      hasInvalidCustomComparison: jest.fn(() => false),
    };

    methods.onPeriodOptionDblClick.call(vm, { period: 'month' });

    expect(vm.onPeriodOptionSelected).toHaveBeenCalledWith({ period: 'month' });
    expect(vm.setPiwikPeriodAndDate).toHaveBeenCalledWith('month', vm.committedAnchorDate);
  });

  it('does not immediately apply range period double click', () => {
    const vm: any = {
      committedPeriod: 'day',
      committedAnchorDate: new Date('2026-02-18'),
      onPeriodOptionSelected: jest.fn(),
      setPiwikPeriodAndDate: jest.fn(),
      hasInvalidCustomComparison: jest.fn(() => false),
    };

    methods.onPeriodOptionDblClick.call(vm, { period: 'range' });

    expect(vm.onPeriodOptionSelected).toHaveBeenCalledWith({ period: 'range' });
    expect(vm.setPiwikPeriodAndDate).not.toHaveBeenCalled();
  });

  it('does not immediately apply non-range period double click when custom comparison range is invalid', () => {
    const vm: any = {
      committedPeriod: 'day',
      committedAnchorDate: new Date('2026-02-18'),
      onPeriodOptionSelected: jest.fn(),
      setPiwikPeriodAndDate: jest.fn(),
      hasInvalidCustomComparison: jest.fn(() => true),
      showInvalidComparisonMessage: jest.fn(),
    };

    methods.onPeriodOptionDblClick.call(vm, { period: 'month' });

    expect(vm.onPeriodOptionSelected).toHaveBeenCalledWith({ period: 'month' });
    expect(vm.setPiwikPeriodAndDate).not.toHaveBeenCalled();
    expect(vm.showInvalidComparisonMessage).toHaveBeenCalled();
  });

  it('shows invalid comparison message only after an invalid apply interaction', () => {
    const vm: any = {
      shouldShowInvalidComparisonMessage: false,
      hasInvalidCustomComparison: jest.fn(() => true),
      showInvalidComparisonMessage: methods.showInvalidComparisonMessage,
    };

    expect(methods.shouldDisplayInvalidComparisonMessage.call(vm)).toBe(false);

    methods.onDisabledApplyInteraction.call(vm);

    expect(vm.shouldShowInvalidComparisonMessage).toBe(true);
    expect(methods.shouldDisplayInvalidComparisonMessage.call(vm)).toBe(true);
  });

  it('clears invalid comparison message when compare input changes', () => {
    const vm: any = {
      shouldShowInvalidComparisonMessage: true,
      isComparing: true,
      comparePeriodType: 'custom',
      compareStartDate: '',
      compareEndDate: '2026-02-18',
      dismissInvalidComparisonMessage: methods.dismissInvalidComparisonMessage,
    };

    methods.onCompareStartDateUpdated.call(vm, '2026-02-01');

    expect(vm.compareStartDate).toBe('2026-02-01');
    expect(vm.shouldShowInvalidComparisonMessage).toBe(false);
  });

  it('blocks calendar commit while preset is pending', () => {
    const vm: any = {
      calendarViewport: 'single',
      uiSelection: { type: 'preset', id: 'today' },
      selectedPeriod: 'day',
      canInteractWithSingleCalendar: jest.fn(() => false),
      setUiSelection: jest.fn(),
      setPendingPeriodAndDate: jest.fn(),
      clearPresetSelection: jest.fn(),
      commitSelectionToUrl: jest.fn(),
    };

    methods.onDatePickerSelected.call(vm, new Date('2026-02-18'));

    expect(vm.commitSelectionToUrl).not.toHaveBeenCalled();
    expect(vm.setPendingPeriodAndDate).not.toHaveBeenCalled();
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
      committedPeriod: 'day',
      committedAnchorDate: new Date(maxDate.getTime()),
      minAllowedDate: new Date(
        window.piwik.minDateYear,
        window.piwik.minDateMonth - 1,
        window.piwik.minDateDay,
      ),
      maxAllowedDate: maxDate,
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
