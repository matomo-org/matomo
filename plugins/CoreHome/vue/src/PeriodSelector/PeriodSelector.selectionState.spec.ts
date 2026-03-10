/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  clearPresetSelection,
  getCurrentRollingDateParamIfOwnedByPreset,
  onPeriodOptionSelected,
  onPresetDateRangeSelected,
} from './PeriodSelector.selectionState';

describe('PeriodSelector.selectionState', () => {
  it('clears preset selection ownership', () => {
    const state: any = {
      activePresetId: 'today',
      pendingPresetSelection: { id: 'today' },
    };

    clearPresetSelection(state);

    expect(state.activePresetId).toBeNull();
    expect(state.pendingPresetSelection).toBeNull();
  });

  it('switches to range viewport when range period is selected', () => {
    const state: any = {
      uiSelection: { type: 'period', id: 'day' },
      lastInteractionSource: null,
      activePresetId: 'today',
      pendingPresetSelection: { id: 'today' },
      selectedPeriod: 'day',
      committedPeriod: 'day',
      committedAnchorDate: new Date('2026-02-18'),
      calendarViewport: 'single',
      singleCalendarPeriod: 'day',
      singleCalendarSelectedDate: new Date('2026-02-18'),
      isRangeValid: null,
    };

    onPeriodOptionSelected(state, { period: 'range' });

    expect(state.uiSelection).toEqual({ type: 'period', id: 'range' });
    expect(state.calendarViewport).toBe('range');
    expect(state.activePresetId).toBeNull();
    expect(state.pendingPresetSelection).toBeNull();
    expect(state.isRangeValid).toBe(true);
  });

  it('stages preset ownership for allowed preset selections', () => {
    const state: any = {
      uiSelection: { type: 'period', id: 'day' },
      lastInteractionSource: null,
      activePresetId: null,
      pendingPresetSelection: null,
      selectedPeriod: 'day',
      committedPeriod: 'day',
      committedAnchorDate: new Date('2026-02-18'),
      calendarViewport: 'single',
      singleCalendarPeriod: 'day',
      singleCalendarSelectedDate: null,
      isRangeValid: false,
    };

    onPresetDateRangeSelected(state, ['day', 'week', 'month', 'year', 'range'], {
      id: 'last7days',
      period: 'range',
      date: 'last7',
      startDate: new Date('2026-02-12'),
      endDate: new Date('2026-02-18'),
    });

    expect(state.uiSelection).toEqual({ type: 'preset', id: 'last7days' });
    expect(state.activePresetId).toBe('last7days');
    expect(state.pendingPresetSelection?.date).toBe('last7');
    expect(state.calendarViewport).toBe('range');
  });

  it('returns rolling date only when preset ownership still matches url token', () => {
    const rollingDate = getCurrentRollingDateParamIfOwnedByPreset(
      {
        uiSelection: { type: 'preset', id: 'today' },
        committedPeriod: 'day',
      },
      'day',
      'today',
      () => 'today',
    );

    expect(rollingDate).toBe('today');
  });
});
