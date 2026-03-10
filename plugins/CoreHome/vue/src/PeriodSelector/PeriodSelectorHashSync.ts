/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export const CONTEXT_KEY_IGNORED_PARAMS = [
  'date',
  'period',
  'comparePeriods',
  'comparePeriodType',
  'compareDates',
  'compareSegments',
];

export type UiSelection<PresetId extends string = string> =
  { type: 'period'; id: string } | { type: 'preset'; id: PresetId };

export function getSelectionKey(period: string, date: string): string {
  return `${period}|${date}`;
}

export function getContextKeyFromParsed(parsed: Record<string, unknown>): string {
  const normalizedContext: Record<string, unknown> = {};
  Object.keys(parsed)
    .filter((key) => !CONTEXT_KEY_IGNORED_PARAMS.includes(key))
    .sort()
    .forEach((key) => {
      normalizedContext[key] = parsed[key];
    });
  return JSON.stringify(normalizedContext);
}

export function shouldSkipHashSync(
  currentSelectionKey: string,
  currentContextKey: string,
  nextHashUiSelection: UiSelection<string>|null,
  lastKnownHashSelectionKey: string|null,
  lastKnownHashContextKey: string|null,
): boolean {
  return !nextHashUiSelection
    && currentSelectionKey === lastKnownHashSelectionKey
    && currentContextKey === lastKnownHashContextKey;
}

export type HashSyncResolution<PresetId extends string> = {
  syncedUiSelection: UiSelection<PresetId> | null;
  lastKnownHashSelectionKey: string;
  lastKnownHashContextKey: string;
  nextHashUiSelection: null;
  nextHashSelectionKey: null;
  nextHashContextKey: null;
  lastInteractionSource: null;
};

export function resolveSyncedUiSelection<PresetId extends string>(
  currentSelectionKey: string,
  currentContextKey: string,
  nextHashUiSelection: UiSelection<PresetId> | null,
  nextHashSelectionKey: string | null,
  nextHashContextKey: string | null,
): HashSyncResolution<PresetId> {
  const isExpectedHashUpdate = !!nextHashUiSelection
    && nextHashSelectionKey === currentSelectionKey
    && nextHashContextKey === currentContextKey;

  const syncedUiSelection = isExpectedHashUpdate && nextHashUiSelection
    ? { ...nextHashUiSelection }
    : null;

  return {
    syncedUiSelection,
    lastKnownHashSelectionKey: currentSelectionKey,
    lastKnownHashContextKey: currentContextKey,
    nextHashUiSelection: null,
    nextHashSelectionKey: null,
    nextHashContextKey: null,
    lastInteractionSource: null,
  };
}

export interface HashSyncState<PresetId extends string = string> {
  nextHashUiSelection: UiSelection<PresetId> | null;
  nextHashSelectionKey: string | null;
  nextHashContextKey: string | null;
  lastKnownHashSelectionKey: string | null;
  lastKnownHashContextKey: string | null;
  lastInteractionSource: 'period' | 'preset' | 'calendar' | 'range' | null;
  periodsFiltered: string[];
  uiSelection: UiSelection<PresetId>;
  activePresetId: PresetId | null;
  pendingPresetSelection: { id: PresetId } | null;
  committedPeriod: string;
  selectedPeriod: string;
  committedAnchorDate: Date | null;
  appliedRangeStartDate: string | null;
  appliedRangeEndDate: string | null;
  singleCalendarPeriod: string;
  singleCalendarSelectedDate: Date | null;
  isRangeValid: boolean | null;
  calendarViewport: 'single' | 'range';
  compareAppliedSignature: string;
  compareCurrentSignature: string;
}

export function applyUiSelectionFromHash<PresetId extends string>(
  state: HashSyncState<PresetId>,
  selectedPeriod: string,
  selectedDate: string,
  syncedUiSelection: UiSelection<PresetId> | null,
  deps: {
    getTokenPresetIdFromPeriodAndDate: (period: string, date: string) => PresetId | null;
    setUiSelection: (selection: UiSelection<PresetId>, source: null) => void;
    clearPresetSelection: () => void;
  },
): void {
  if (syncedUiSelection) {
    state.uiSelection = syncedUiSelection;
    state.activePresetId = syncedUiSelection.type === 'preset'
      ? syncedUiSelection.id
      : null;
    return;
  }

  const presetId = deps.getTokenPresetIdFromPeriodAndDate(selectedPeriod, selectedDate);
  if (presetId && state.periodsFiltered.includes(selectedPeriod)) {
    state.uiSelection = { type: 'preset', id: presetId };
    state.activePresetId = presetId;
    state.pendingPresetSelection = null;
    return;
  }

  deps.setUiSelection({ type: 'period', id: selectedPeriod }, null);
  deps.clearPresetSelection();
}

export function resetSelectedDateValues<PresetId extends string>(
  state: Pick<HashSyncState<PresetId>, 'committedAnchorDate' | 'appliedRangeStartDate' | 'appliedRangeEndDate'>,
): void {
  state.committedAnchorDate = null;
  state.appliedRangeStartDate = null;
  state.appliedRangeEndDate = null;
}

export function applyDateValuesFromHash<PresetId extends string>(
  state: Pick<
    HashSyncState<PresetId>,
    'committedAnchorDate'
    | 'appliedRangeStartDate'
    | 'appliedRangeEndDate'
    | 'singleCalendarPeriod'
    | 'singleCalendarSelectedDate'
  >,
  period: string,
  date: string,
  deps: {
    rangePeriod: string;
    parseRange: (periodValue: string, dateValue: string) => [Date, Date];
    parseDate: (dateValue: string) => Date;
    format: (dateValue: Date) => string;
    siteMinAllowedDate: Date;
    siteMaxAllowedDate: Date;
    isSingleCalendarPeriod: (periodValue: string) => boolean;
    setRangeStartEndFromPeriod: (periodValue: string, dateValue: string) => void;
  },
): void {
  if (period === deps.rangePeriod) {
    const [startDate, endDate] = deps.parseRange(period, date);
    state.committedAnchorDate = startDate;
    state.appliedRangeStartDate = deps.format(
      startDate < deps.siteMinAllowedDate ? deps.siteMinAllowedDate : startDate,
    );
    state.appliedRangeEndDate = deps.format(
      endDate > deps.siteMaxAllowedDate ? deps.siteMaxAllowedDate : endDate,
    );
    return;
  }

  state.committedAnchorDate = deps.parseDate(date);
  deps.setRangeStartEndFromPeriod(period, date);
  if (deps.isSingleCalendarPeriod(period)) {
    state.singleCalendarPeriod = period;
  }
  state.singleCalendarSelectedDate = state.committedAnchorDate;
}

export function updateSelectedValuesFromHash<PresetId extends string>(
  state: HashSyncState<PresetId>,
  deps: {
    parsed: Record<string, unknown>;
    currentContextKey: string;
    rangePeriod: string;
    parsePeriod: (period: string, dateValue: string) => void;
    getTokenPresetIdFromPeriodAndDate: (period: string, date: string) => PresetId | null;
    setUiSelection: (selection: UiSelection<PresetId>, source: null) => void;
    clearPresetSelection: () => void;
    parseRange: (period: string, dateValue: string) => [Date, Date];
    parseDate: (dateValue: string) => Date;
    format: (dateValue: Date) => string;
    siteMinAllowedDate: Date;
    siteMaxAllowedDate: Date;
    isSingleCalendarPeriod: (periodValue: string) => boolean;
    setRangeStartEndFromPeriod: (periodValue: string, dateValue: string) => void;
  },
): void {
  const currentDate = (deps.parsed.date as string) || '';
  const currentPeriod = (deps.parsed.period as string) || '';
  const currentSelectionKey = getSelectionKey(currentPeriod, currentDate);

  if (shouldSkipHashSync(
    currentSelectionKey,
    deps.currentContextKey,
    state.nextHashUiSelection,
    state.lastKnownHashSelectionKey,
    state.lastKnownHashContextKey,
  )) {
    return;
  }

  const hashSyncResolution = resolveSyncedUiSelection<PresetId>(
    currentSelectionKey,
    deps.currentContextKey,
    state.nextHashUiSelection,
    state.nextHashSelectionKey,
    state.nextHashContextKey,
  );
  state.nextHashUiSelection = hashSyncResolution.nextHashUiSelection;
  state.nextHashSelectionKey = hashSyncResolution.nextHashSelectionKey;
  state.nextHashContextKey = hashSyncResolution.nextHashContextKey;
  state.lastInteractionSource = hashSyncResolution.lastInteractionSource;
  state.lastKnownHashSelectionKey = hashSyncResolution.lastKnownHashSelectionKey;
  state.lastKnownHashContextKey = hashSyncResolution.lastKnownHashContextKey;

  applyUiSelectionFromHash(
    state,
    currentPeriod,
    currentDate,
    hashSyncResolution.syncedUiSelection,
    {
      getTokenPresetIdFromPeriodAndDate: deps.getTokenPresetIdFromPeriodAndDate,
      setUiSelection: deps.setUiSelection,
      clearPresetSelection: deps.clearPresetSelection,
    },
  );
  state.committedPeriod = currentPeriod;
  state.selectedPeriod = currentPeriod;
  resetSelectedDateValues(state);

  try {
    deps.parsePeriod(currentPeriod, currentDate);
  } catch (e) {
    state.isRangeValid = currentPeriod === deps.rangePeriod ? false : null;
    return;
  }

  applyDateValuesFromHash(state, currentPeriod, currentDate, {
    rangePeriod: deps.rangePeriod,
    parseRange: deps.parseRange,
    parseDate: deps.parseDate,
    format: deps.format,
    siteMinAllowedDate: deps.siteMinAllowedDate,
    siteMaxAllowedDate: deps.siteMaxAllowedDate,
    isSingleCalendarPeriod: deps.isSingleCalendarPeriod,
    setRangeStartEndFromPeriod: deps.setRangeStartEndFromPeriod,
  });
  state.isRangeValid = currentPeriod === deps.rangePeriod ? true : null;
  state.pendingPresetSelection = null;
  state.calendarViewport = currentPeriod === deps.rangePeriod ? 'range' : 'single';
  state.compareAppliedSignature = state.compareCurrentSignature;
}
