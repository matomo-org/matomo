/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import Matomo from '../Matomo/Matomo';
import { translate } from '../translate';
import type {
  PresetDateRangeId,
  PresetDateRangeSelection,
} from './PresetDateRangeResolver';
import type { UiSelection as HashSyncUiSelection } from './PeriodSelectorHashSync';

const NBSP = Matomo.helper.htmlDecode('&nbsp;');

export const COMPARE_PERIOD_TYPES = ['custom', 'previousPeriod', 'previousYear'];

export const COMPARE_PERIOD_OPTIONS = [
  { key: 'custom', value: translate('General_Custom') },
  {
    key: 'previousPeriod',
    value: translate('General_PreviousPeriod').replace(/\s+/, NBSP),
  },
  {
    key: 'previousYear',
    value: translate('General_PreviousYear').replace(/\s+/, NBSP),
  },
];

// the date when the site was created
export function getSiteMinAllowedDate(): Date {
  return new Date(
    Matomo.minDateYear,
    Matomo.minDateMonth - 1,
    Matomo.minDateDay,
  );
}

// today/now
export function getSiteMaxAllowedDate(): Date {
  return new Date(
    Matomo.maxDateYear,
    Matomo.maxDateMonth - 1,
    Matomo.maxDateDay,
  );
}

export const RANGE_PERIOD = 'range';

export type InteractionSource = 'period' | 'preset' | 'calendar' | 'range' | null;
export type SingleCalendarPeriod = 'day' | 'week' | 'month' | 'year';
export type CalendarViewport = 'single' | 'range';
export type UiSelection = HashSyncUiSelection<PresetDateRangeId>;
export type CompareStateChangeSource = 'user' | 'sync' | 'context';

export interface CompareStateChangePayload {
  isComparing: boolean;
  comparePeriodType: string;
  compareStartDate: string;
  compareEndDate: string;
  compareCurrentSignature: string;
  isCompareRangeValid: boolean;
  source: CompareStateChangeSource;
}

export function isValidDate(candidateDate: unknown): boolean {
  if (Object.prototype.toString.call(candidateDate) !== '[object Date]') {
    return false;
  }

  return !Number.isNaN((candidateDate as Date).getTime());
}

export function isSingleCalendarPeriod(period: string): period is SingleCalendarPeriod {
  return period === 'day'
    || period === 'week'
    || period === 'month'
    || period === 'year';
}

export interface PeriodSelectorState {
  uiSelection: UiSelection;
  lastInteractionSource: InteractionSource;
  nextHashUiSelection: UiSelection|null;
  nextHashSelectionKey: string|null;
  nextHashContextKey: string|null;
  lastKnownHashSelectionKey: string|null;
  lastKnownHashContextKey: string|null;
  minAllowedDate: Date;
  maxAllowedDate: Date;
  activePresetId: PresetDateRangeId|null;
  pendingPresetSelection: PresetDateRangeSelection|null;
  committedPeriod: string;
  committedAnchorDate: Date|null;
  selectedPeriod: string;
  calendarViewport: CalendarViewport;
  singleCalendarPeriod: SingleCalendarPeriod;
  singleCalendarSelectedDate: Date|null;
  appliedRangeStartDate: string|null;
  appliedRangeEndDate: string|null;
  isRangeValid: boolean|null;
  isLoadingNewPage: boolean;
  isComparing: null|boolean;
  comparePeriodType: string;
  compareStartDate: string;
  compareEndDate: string;
  compareCurrentSignature: string;
  isCompareRangeValidValue: boolean;
  compareAppliedSignature: string;
}
