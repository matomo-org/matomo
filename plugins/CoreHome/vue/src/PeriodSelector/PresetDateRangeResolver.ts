/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { format } from '../Periods';

export type PresetDateRangeId =
  | 'today'
  | 'yesterday'
  | 'last7days'
  | 'last30days'
  | 'last90days'
  | 'lastWeekMonSun'
  | 'lastMonth'
  | 'lastQuarter'
  | 'lastYear'
  | 'thisWeekMonToday'
  | 'thisMonth'
  | 'thisQuarter'
  | 'thisYear';

export interface PresetDateRangeOption {
  id: PresetDateRangeId;
  labelKey: string;
}

export interface PresetDateRangeSelection {
  id: PresetDateRangeId;
  period: 'day'|'week'|'month'|'year'|'range';
  date: string;
  startDate: Date;
  endDate: Date;
}

export const PRESET_DATE_RANGE_PERIODS: Record<PresetDateRangeId, PresetDateRangeSelection['period']> = {
  today: 'day',
  yesterday: 'day',
  last7days: 'range',
  last30days: 'range',
  last90days: 'range',
  lastWeekMonSun: 'week',
  lastMonth: 'month',
  lastQuarter: 'range',
  lastYear: 'year',
  thisWeekMonToday: 'week',
  thisMonth: 'month',
  thisQuarter: 'range',
  thisYear: 'year',
};

export const PRESET_DATE_RANGES: PresetDateRangeOption[] = [
  { id: 'today', labelKey: 'CoreHome_PresetDateToday' },
  { id: 'yesterday', labelKey: 'CoreHome_PresetDateYesterday' },
  { id: 'last7days', labelKey: 'CoreHome_PresetDateLast7Days' },
  { id: 'last30days', labelKey: 'CoreHome_PresetDateLast30Days' },
  { id: 'last90days', labelKey: 'CoreHome_PresetDateLast90Days' },
  { id: 'lastWeekMonSun', labelKey: 'CoreHome_PresetDateLastWeekMonSun' },
  { id: 'lastMonth', labelKey: 'CoreHome_PresetDateLastMonth' },
  { id: 'lastQuarter', labelKey: 'CoreHome_PresetDateLastQuarter' },
  { id: 'lastYear', labelKey: 'CoreHome_PresetDateLastYear' },
  { id: 'thisWeekMonToday', labelKey: 'CoreHome_PresetDateThisWeekMonToday' },
  { id: 'thisMonth', labelKey: 'CoreHome_PresetDateThisMonth' },
  { id: 'thisQuarter', labelKey: 'CoreHome_PresetDateThisQuarter' },
  { id: 'thisYear', labelKey: 'CoreHome_PresetDateThisYear' },
];

const PRESET_TOKEN_TO_ID_MAP: Record<string, PresetDateRangeId> = {
  'day|today': 'today',
  'day|yesterday': 'yesterday',
  'range|last7': 'last7days',
  'range|last30': 'last30days',
  'range|last90': 'last90days',
  'week|lastweek': 'lastWeekMonSun',
  'month|lastmonth': 'lastMonth',
  'year|lastyear': 'lastYear',
  'week|today': 'thisWeekMonToday',
  'month|today': 'thisMonth',
  'year|today': 'thisYear',
};

export function getTokenPresetIdFromPeriodAndDate(
  period: string,
  date: string,
): PresetDateRangeId|null {
  return PRESET_TOKEN_TO_ID_MAP[`${period}|${date}`] || null;
}

function cloneDate(date: Date): Date {
  return new Date(date.getTime());
}

function addDays(date: Date, days: number): Date {
  const nextDate = cloneDate(date);
  nextDate.setDate(nextDate.getDate() + days);
  return nextDate;
}

function startOfMonth(date: Date): Date {
  return new Date(date.getFullYear(), date.getMonth(), 1);
}

function endOfMonth(date: Date): Date {
  return new Date(date.getFullYear(), date.getMonth() + 1, 0);
}

function startOfWeekMonday(date: Date): Date {
  const daysToMonday = (date.getDay() + 6) % 7;
  return addDays(date, -daysToMonday);
}

function startOfQuarter(date: Date): Date {
  const month = date.getMonth();
  const quarterStartMonth = month - (month % 3);
  return new Date(date.getFullYear(), quarterStartMonth, 1);
}

function makeRangeDateParam(startDate: Date, endDate: Date): string {
  return `${format(startDate)},${format(endDate)}`;
}

export function clampDateToBounds(date: Date, minDate: Date, maxDate: Date): Date {
  if (date < minDate) {
    return new Date(minDate.getTime());
  }

  if (date > maxDate) {
    return new Date(maxDate.getTime());
  }

  return date;
}

export function resolvePresetDateRange(
  presetId: PresetDateRangeId,
  todayInput: Date,
): PresetDateRangeSelection {
  const today = cloneDate(todayInput);

  switch (presetId) {
    case 'today':
      return {
        id: presetId,
        period: 'day',
        date: 'today',
        startDate: today,
        endDate: today,
      };
    case 'yesterday': {
      const yesterday = addDays(today, -1);
      return {
        id: presetId,
        period: 'day',
        date: 'yesterday',
        startDate: yesterday,
        endDate: yesterday,
      };
    }
    case 'last7days': {
      const startDate = addDays(today, -6);
      return {
        id: presetId,
        period: 'range',
        date: 'last7',
        startDate,
        endDate: today,
      };
    }
    case 'last30days': {
      const startDate = addDays(today, -29);
      return {
        id: presetId,
        period: 'range',
        date: 'last30',
        startDate,
        endDate: today,
      };
    }
    case 'last90days': {
      const startDate = addDays(today, -89);
      return {
        id: presetId,
        period: 'range',
        date: 'last90',
        startDate,
        endDate: today,
      };
    }
    case 'lastWeekMonSun': {
      const thisWeekStart = startOfWeekMonday(today);
      const startDate = addDays(thisWeekStart, -7);
      const endDate = addDays(startDate, 6);
      return {
        id: presetId,
        period: 'week',
        date: 'lastweek',
        startDate,
        endDate,
      };
    }
    case 'lastMonth': {
      const lastMonthDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
      const startDate = startOfMonth(lastMonthDate);
      const endDate = endOfMonth(lastMonthDate);
      return {
        id: presetId,
        period: 'month',
        date: 'lastmonth',
        startDate,
        endDate,
      };
    }
    case 'lastQuarter': {
      const thisQuarterStart = startOfQuarter(today);
      const endDate = addDays(thisQuarterStart, -1);
      const startDate = startOfQuarter(endDate);
      return {
        id: presetId,
        period: 'range',
        date: makeRangeDateParam(startDate, endDate),
        startDate,
        endDate,
      };
    }
    case 'lastYear': {
      const year = today.getFullYear() - 1;
      const startDate = new Date(year, 0, 1);
      const endDate = new Date(year, 11, 31);
      return {
        id: presetId,
        period: 'year',
        date: 'lastyear',
        startDate,
        endDate,
      };
    }
    case 'thisWeekMonToday': {
      const startDate = startOfWeekMonday(today);
      // Intentionally keep compatibility with existing URL semantics:
      // period=week&date=today remains the canonical "this week (Mon - Today)" selection.
      // The selector derives the visible effective range from the current max-date bounds.
      return {
        id: presetId,
        period: 'week',
        date: 'today',
        startDate,
        endDate: today,
      };
    }
    case 'thisMonth': {
      const startDate = startOfMonth(today);
      return {
        id: presetId,
        period: 'month',
        date: 'today',
        startDate,
        endDate: today,
      };
    }
    case 'thisQuarter': {
      const startDate = startOfQuarter(today);
      return {
        id: presetId,
        period: 'range',
        date: makeRangeDateParam(startDate, today),
        startDate,
        endDate: today,
      };
    }
    case 'thisYear': {
      const startDate = new Date(today.getFullYear(), 0, 1);
      return {
        id: presetId,
        period: 'year',
        date: 'today',
        startDate,
        endDate: today,
      };
    }
    default:
      throw new Error(`Unknown preset date range: ${presetId as string}`);
  }
}
