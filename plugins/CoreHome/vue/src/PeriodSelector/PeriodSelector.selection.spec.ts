/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const PeriodSelector = require('./PeriodSelector.vue').default;

describe('CoreHome/PeriodSelector/PeriodSelector selection ownership', () => {
  const methods = (PeriodSelector as unknown as { methods: Record<string, Function> }).methods;

  it('should set uiSelection to preset on preset click', () => {
    const vm: any = {
      uiSelection: { type: 'period', id: 'day' },
      lastInteractionSource: null,
      activePresetId: null,
      selectedPeriod: 'day',
      periodValue: 'day',
      dateValue: null,
      startRangeDate: null,
      endRangeDate: null,
      isRangeValid: null,
      activePresetSelection: null,
      setProgrammaticRangeLock: jest.fn(),
      setProgrammaticDatePickerLock: jest.fn(),
      programmaticRangeLock: null,
      setUiSelection(selection: { type: string; id: string }, source: string|null) {
        this.uiSelection = selection;
        this.lastInteractionSource = source;
      },
    };

    methods.onPresetDateRangeSelected.call(vm, {
      id: 'lastMonth',
      period: 'month',
      date: 'lastmonth',
      startDate: new Date('2026-01-01'),
      endDate: new Date('2026-01-31'),
    });

    expect(vm.uiSelection).toEqual({ type: 'preset', id: 'lastMonth' });
    expect(vm.activePresetId).toBe('lastMonth');
    expect(vm.selectedPeriod).toBe('month');
    expect(vm.programmaticRangeLock).toBeNull();
  });

  it('should set uiSelection to period on period click', () => {
    const vm: any = {
      uiSelection: { type: 'preset', id: 'lastMonth' },
      lastInteractionSource: null,
      selectedPeriod: 'month',
      activePresetId: 'lastMonth',
      activePresetSelection: { id: 'lastMonth' },
      setUiSelection(selection: { type: string; id: string }, source: string|null) {
        this.uiSelection = selection;
        this.lastInteractionSource = source;
      },
      clearPresetSelection() {
        this.activePresetId = null;
        this.activePresetSelection = null;
      },
    };

    methods.onPeriodOptionSelected.call(vm, { period: 'year' });

    expect(vm.uiSelection).toEqual({ type: 'period', id: 'year' });
    expect(vm.selectedPeriod).toBe('year');
    expect(vm.activePresetId).toBeNull();
    expect(vm.activePresetSelection).toBeNull();
  });

  it('should apply preset magic date only when preset-owned selection is active', () => {
    const vm = {
      selectedPeriod: 'month',
      periodValue: 'month',
      dateValue: new Date('2026-01-01'),
      uiSelection: { type: 'preset', id: 'lastMonth' },
      activePresetSelection: {
        id: 'lastMonth',
        period: 'month',
        date: 'lastmonth',
        startDate: new Date('2026-01-01'),
        endDate: new Date('2026-01-31'),
      },
      commitSelectionToUrl: jest.fn(),
      setPiwikPeriodAndDate: jest.fn(),
    };

    Object.defineProperty(vm, 'selectedDateString', {
      get() {
        return '2026-01-01,2026-01-31';
      },
    });

    methods.onApplyClicked.call(vm);

    expect(vm.commitSelectionToUrl).toHaveBeenCalledWith('lastmonth', 'month');
    expect(vm.setPiwikPeriodAndDate).not.toHaveBeenCalled();
  });

  it('should ignore range change ownership updates when selected period is not range', () => {
    const vm = {
      selectedPeriod: 'day',
      uiSelection: { type: 'preset', id: 'today' },
      isRangeValid: null,
      startRangeDate: '2026-02-16',
      endRangeDate: '2026-02-16',
      activePresetId: 'today',
      activePresetSelection: { id: 'today' },
      lastInteractionSource: 'preset',
    };

    methods.onRangeChange.call(vm, '2026-02-10', '2026-02-16');

    expect(vm.uiSelection).toEqual({ type: 'preset', id: 'today' });
    expect(vm.activePresetId).toBe('today');
    expect(vm.isRangeValid).toBe(true);
  });

  it('should keep preset ownership for programmatic range sync events', () => {
    const vm = {
      selectedPeriod: 'range',
      uiSelection: { type: 'preset', id: 'last7days' },
      programmaticRangeLock: {
        targetRange: '2026-02-10,2026-02-16',
      },
      isRangeValid: null,
      startRangeDate: null,
      endRangeDate: null,
      activePresetId: 'last7days',
      activePresetSelection: { id: 'last7days' },
      lastInteractionSource: 'preset',
    };

    methods.onRangeChange.call(vm, '2026-02-10', '2026-02-16');

    expect(vm.uiSelection).toEqual({ type: 'preset', id: 'last7days' });
    expect(vm.activePresetId).toBe('last7days');
    expect(vm.programmaticRangeLock).toBeNull();
  });

  it('should process non-matching range input while lock is active', () => {
    const vm: any = {
      selectedPeriod: 'range',
      uiSelection: { type: 'preset', id: 'last7days' },
      programmaticRangeLock: {
        targetRange: '2026-02-10,2026-02-16',
      },
      isRangeValid: null,
      startRangeDate: null,
      endRangeDate: null,
      activePresetId: 'last7days',
      activePresetSelection: { id: 'last7days' },
      lastInteractionSource: 'preset',
      setUiSelection(selection: { type: string; id: string }, source: string|null) {
        this.uiSelection = selection;
        this.lastInteractionSource = source;
      },
      clearPresetSelection() {
        this.activePresetId = null;
        this.activePresetSelection = null;
      },
    };

    methods.onRangeChange.call(vm, '2026-02-09', '2026-02-16');

    expect(vm.uiSelection).toEqual({ type: 'period', id: 'range' });
    expect(vm.activePresetId).toBeNull();
    expect(vm.activePresetSelection).toBeNull();
    expect(vm.programmaticRangeLock).toBeNull();
  });

  it('should ignore matching programmatic date picker sync event', () => {
    const vm: any = {
      selectedPeriod: 'day',
      uiSelection: { type: 'preset', id: 'today' },
      programmaticDatePickerLock: {
        targetPeriod: 'day',
        targetDate: '2026-02-18',
      },
      lastInteractionSource: 'preset',
      programmaticRangeLock: null,
      commitSelectionToUrl: jest.fn(),
      setPendingCalendarSelection: jest.fn(),
      clearPresetSelection: jest.fn(),
      setUiSelection: jest.fn(),
    };

    methods.onDatePickerSelected.call(vm, new Date('2026-02-18'));

    expect(vm.programmaticDatePickerLock).toBeNull();
    expect(vm.commitSelectionToUrl).not.toHaveBeenCalled();
    expect(vm.setPendingCalendarSelection).not.toHaveBeenCalled();
  });
});
