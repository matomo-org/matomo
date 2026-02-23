/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

window.piwik.minDateYear = 2011;
window.piwik.minDateMonth = 11;
window.piwik.minDateDay = 15;
window.piwik.maxDateYear = 2014;
window.piwik.maxDateMonth = 3;
window.piwik.maxDateDay = 29;

// eslint-disable-next-line @typescript-eslint/no-var-requires
const PeriodSelector = require('./PeriodSelector.vue').default;

describe('CoreHome/PeriodSelector/PeriodSelector persistent calendar behavior', () => {
  const component = PeriodSelector as unknown as {
    methods: Record<string, (...args: unknown[]) => unknown>;
    computed: Record<string, (...args: unknown[]) => unknown>;
  };
  const { methods, computed } = component;

  it('stages preset selection and switches to dual calendar for range presets', () => {
    const vm: any = {
      uiSelection: { type: 'period', id: 'day' },
      lastInteractionSource: null,
      activePresetId: null,
      pendingPresetSelection: null,
      selectedPeriod: 'day',
      calendarViewport: 'single',
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
  });

  it('keeps single calendar for non-dual presets', () => {
    const vm: any = {
      uiSelection: { type: 'period', id: 'day' },
      lastInteractionSource: null,
      activePresetId: null,
      pendingPresetSelection: null,
      selectedPeriod: 'day',
      calendarViewport: 'range',
      singleCalendarPeriod: 'day',
      singleCalendarDate: null,
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
  });

  it('applies preset only on apply click', () => {
    const vm: any = {
      uiSelection: { type: 'preset', id: 'last30days' },
      pendingPresetSelection: {
        id: 'last30days',
        period: 'range',
        date: 'last30',
      },
      periodValue: 'day',
      commitSelectionToUrl: jest.fn(),
    };

    methods.onApplyClicked.call(vm);

    expect(vm.periodValue).toBe('range');
    expect(vm.commitSelectionToUrl).toHaveBeenCalledWith('last30', 'range');
  });

  it('keeps thisWeekMonToday compatibility semantics when applying preset', () => {
    const vm: any = {
      uiSelection: { type: 'preset', id: 'thisWeekMonToday' },
      pendingPresetSelection: {
        id: 'thisWeekMonToday',
        period: 'week',
        date: 'today',
      },
      periodValue: 'day',
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

  it('marks non-range period click as pending and does not apply on apply click', () => {
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

  it('applies non-range period via calendar click', () => {
    const vm: any = {
      calendarViewport: 'single',
      uiSelection: { type: 'period', id: 'week' },
      selectedPeriod: 'week',
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

  it('updates range values only when viewport is range and owner is period', () => {
    const allowedVm: any = {
      calendarViewport: 'range',
      uiSelection: { type: 'period', id: 'range' },
      selectedPeriod: 'range',
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
      setUiSelection: jest.fn(),
      setPendingCalendarSelection: jest.fn(),
      clearPresetSelection: jest.fn(),
      commitSelectionToUrl: jest.fn(),
    };

    methods.onDatePickerSelected.call(vm, new Date('2026-02-18'));

    expect(vm.commitSelectionToUrl).not.toHaveBeenCalled();
    expect(vm.setPendingCalendarSelection).not.toHaveBeenCalled();
  });

  it('shows and hides apply button according to pending state rules', () => {
    const pendingNonRangeVm: any = {
      pendingPresetSelection: null,
      selectedPeriod: 'week',
      isCompareDirty: true,
      hasPendingNonRangePeriodChange: true,
    };
    expect(computed.shouldShowApplyButton.call(pendingNonRangeVm)).toBe(false);

    const presetVm: any = {
      pendingPresetSelection: { id: 'today' },
      selectedPeriod: 'day',
      isCompareDirty: false,
      hasPendingNonRangePeriodChange: false,
    };
    expect(computed.shouldShowApplyButton.call(presetVm)).toBe(true);

    const rangeVm: any = {
      pendingPresetSelection: null,
      selectedPeriod: 'range',
      isCompareDirty: false,
      hasPendingNonRangePeriodChange: false,
    };
    expect(computed.shouldShowApplyButton.call(rangeVm)).toBe(true);

    const compareVm: any = {
      pendingPresetSelection: null,
      selectedPeriod: 'day',
      isCompareDirty: true,
      hasPendingNonRangePeriodChange: false,
    };
    expect(computed.shouldShowApplyButton.call(compareVm)).toBe(true);
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
