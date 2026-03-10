/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { format } from '../Periods';
import type {
  InteractionSource,
  SingleCalendarPeriod,
  UiSelection,
} from './PeriodSelector.types';
import type {
  PresetDateRangeId,
  PresetDateRangeSelection,
} from './PresetDateRangeResolver';
import {
  RANGE_PERIOD,
  isSingleCalendarPeriod,
} from './PeriodSelector.types';

export interface SelectionState {
  uiSelection: UiSelection;
  lastInteractionSource: InteractionSource;
  activePresetId: PresetDateRangeId | null;
  pendingPresetSelection: PresetDateRangeSelection | null;
  selectedPeriod: string;
  committedPeriod: string;
  committedAnchorDate: Date | null;
  calendarViewport: 'single' | 'range';
  singleCalendarPeriod: SingleCalendarPeriod;
  singleCalendarSelectedDate: Date | null;
  isRangeValid: boolean | null;
}

export interface PendingPeriodDateDeps {
  setRangeStartEndFromPeriod: (period: string, date: string) => void;
}

export function setUiSelection(
  state: SelectionState,
  selection: UiSelection,
  source: InteractionSource,
): void {
  state.uiSelection = selection;
  state.lastInteractionSource = source;
}

export function clearPresetSelection(state: SelectionState): void {
  state.activePresetId = null;
  state.pendingPresetSelection = null;
}

export function setPendingPeriodAndDate(
  state: SelectionState,
  period: string,
  date: Date,
  deps: PendingPeriodDateDeps,
): void {
  state.committedPeriod = period;
  state.selectedPeriod = period;
  state.committedAnchorDate = date;
  deps.setRangeStartEndFromPeriod(period, format(date));
  if (isSingleCalendarPeriod(period)) {
    state.singleCalendarPeriod = period;
    state.singleCalendarSelectedDate = date;
  }
}

export function onPeriodOptionSelected(
  state: SelectionState,
  periodSelection: { period: string },
): void {
  setUiSelection(state, { type: 'period', id: periodSelection.period }, 'period');
  state.selectedPeriod = periodSelection.period;
  clearPresetSelection(state);
  if (periodSelection.period === RANGE_PERIOD) {
    state.calendarViewport = 'range';
    state.isRangeValid = true;
    return;
  }

  state.calendarViewport = 'single';
  if (isSingleCalendarPeriod(periodSelection.period)) {
    state.singleCalendarPeriod = periodSelection.period;
  }
  state.singleCalendarSelectedDate = periodSelection.period === state.committedPeriod
    ? state.committedAnchorDate
    : null;
}

export function onPeriodOptionDblClick(
  state: SelectionState,
  periodSelection: { period: string },
  deps: {
    setPiwikPeriodAndDate: (period: string, date: Date) => void;
  },
): void {
  onPeriodOptionSelected(state, periodSelection);
  if (periodSelection.period === RANGE_PERIOD
    || periodSelection.period === state.committedPeriod
    || !state.committedAnchorDate
  ) {
    return;
  }

  deps.setPiwikPeriodAndDate(periodSelection.period, state.committedAnchorDate);
}

export function onPresetDateRangeSelected(
  state: SelectionState,
  periodsFiltered: string[],
  selection: PresetDateRangeSelection,
): void {
  if (!periodsFiltered.includes(selection.period)) {
    return;
  }

  setUiSelection(state, { type: 'preset', id: selection.id }, 'preset');
  state.activePresetId = selection.id;
  state.selectedPeriod = selection.period;
  state.isRangeValid = true;
  state.pendingPresetSelection = selection;
  if (selection.period === RANGE_PERIOD) {
    state.calendarViewport = 'range';
    return;
  }

  state.calendarViewport = 'single';
  state.singleCalendarSelectedDate = selection.startDate;
  if (isSingleCalendarPeriod(selection.period)) {
    state.singleCalendarPeriod = selection.period;
  }
}

export function getCurrentRollingDateParamIfOwnedByPreset(
  state: Pick<SelectionState, 'uiSelection' | 'committedPeriod'>,
  currentUrlPeriod: string,
  currentUrlDate: string,
  getTokenPresetIdFromPeriodAndDate: (period: string, date: string) => PresetDateRangeId | null,
): string | null {
  if (state.uiSelection.type !== 'preset') {
    return null;
  }

  if (currentUrlPeriod !== state.committedPeriod || !currentUrlDate) {
    return null;
  }

  const presetId = getTokenPresetIdFromPeriodAndDate(currentUrlPeriod, currentUrlDate);
  if (presetId !== state.uiSelection.id) {
    return null;
  }

  return currentUrlDate;
}
