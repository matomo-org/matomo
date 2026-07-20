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
  lastInteractionSource: null;
};

export function resolveSyncedUiSelection<PresetId extends string>(
  currentSelectionKey: string,
  currentContextKey: string,
  nextHashUiSelection: UiSelection<PresetId> | null,
  nextHashSelectionKey: string | null,
): HashSyncResolution<PresetId> {
  const isExpectedHashUpdate = !!nextHashUiSelection
    && nextHashSelectionKey === currentSelectionKey;

  const syncedUiSelection = isExpectedHashUpdate && nextHashUiSelection
    ? { ...nextHashUiSelection }
    : null;

  return {
    syncedUiSelection,
    lastKnownHashSelectionKey: currentSelectionKey,
    lastKnownHashContextKey: currentContextKey,
    nextHashUiSelection: null,
    nextHashSelectionKey: null,
    lastInteractionSource: null,
  };
}
