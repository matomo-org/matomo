/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  Periods,
  datesAreInTheSamePeriod,
  format,
  getToday,
  parseDate,
} from '../Periods';

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
  urlDate: string;
  selectedDate: Date;
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

const PRESET_ID_TO_URL_DATE_MAP: Partial<Record<PresetDateRangeId, string>> = {
  today: 'today',
  yesterday: 'yesterday',
  last7days: 'last7',
  last30days: 'last30',
  last90days: 'last90',
  lastWeekMonSun: 'lastweek',
  lastMonth: 'lastmonth',
  lastYear: 'lastyear',
  thisWeekMonToday: 'today',
  thisMonth: 'today',
  thisYear: 'today',
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

  const withUrlDate = (
    selection: Omit<PresetDateRangeSelection, 'urlDate'>,
  ): PresetDateRangeSelection => ({
    ...selection,
    urlDate: PRESET_ID_TO_URL_DATE_MAP[presetId] || selection.date,
  });

  switch (presetId) {
    case 'today':
      return withUrlDate({
        id: presetId,
        period: 'day',
        date: format(today),
        selectedDate: today,
        startDate: today,
        endDate: today,
      });
    case 'yesterday': {
      const yesterday = addDays(today, -1);
      return withUrlDate({
        id: presetId,
        period: 'day',
        date: format(yesterday),
        selectedDate: yesterday,
        startDate: yesterday,
        endDate: yesterday,
      });
    }
    case 'last7days': {
      const startDate = addDays(today, -6);
      return withUrlDate({
        id: presetId,
        period: 'range',
        date: makeRangeDateParam(startDate, today),
        selectedDate: today,
        startDate,
        endDate: today,
      });
    }
    case 'last30days': {
      const startDate = addDays(today, -29);
      return withUrlDate({
        id: presetId,
        period: 'range',
        date: makeRangeDateParam(startDate, today),
        selectedDate: today,
        startDate,
        endDate: today,
      });
    }
    case 'last90days': {
      const startDate = addDays(today, -89);
      return withUrlDate({
        id: presetId,
        period: 'range',
        date: makeRangeDateParam(startDate, today),
        selectedDate: today,
        startDate,
        endDate: today,
      });
    }
    case 'lastWeekMonSun': {
      const thisWeekStart = startOfWeekMonday(today);
      const startDate = addDays(thisWeekStart, -7);
      const endDate = addDays(startDate, 6);
      return withUrlDate({
        id: presetId,
        period: 'week',
        date: format(startDate),
        selectedDate: startDate,
        startDate,
        endDate,
      });
    }
    case 'lastMonth': {
      const lastMonthDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
      const startDate = startOfMonth(lastMonthDate);
      const endDate = endOfMonth(lastMonthDate);
      return withUrlDate({
        id: presetId,
        period: 'month',
        date: format(startDate),
        selectedDate: startDate,
        startDate,
        endDate,
      });
    }
    case 'lastQuarter': {
      const thisQuarterStart = startOfQuarter(today);
      const endDate = addDays(thisQuarterStart, -1);
      const startDate = startOfQuarter(endDate);
      return withUrlDate({
        id: presetId,
        period: 'range',
        date: makeRangeDateParam(startDate, endDate),
        selectedDate: endDate,
        startDate,
        endDate,
      });
    }
    case 'lastYear': {
      const year = today.getFullYear() - 1;
      const startDate = new Date(year, 0, 1);
      const endDate = new Date(year, 11, 31);
      return withUrlDate({
        id: presetId,
        period: 'year',
        date: format(startDate),
        selectedDate: startDate,
        startDate,
        endDate,
      });
    }
    case 'thisWeekMonToday': {
      const startDate = startOfWeekMonday(today);
      return withUrlDate({
        id: presetId,
        period: 'week',
        date: format(today),
        selectedDate: today,
        startDate,
        endDate: today,
      });
    }
    case 'thisMonth': {
      const startDate = startOfMonth(today);
      return withUrlDate({
        id: presetId,
        period: 'month',
        date: format(today),
        selectedDate: today,
        startDate,
        endDate: today,
      });
    }
    case 'thisQuarter': {
      const startDate = startOfQuarter(today);
      return withUrlDate({
        id: presetId,
        period: 'range',
        date: makeRangeDateParam(startDate, today),
        selectedDate: today,
        startDate,
        endDate: today,
      });
    }
    case 'thisYear': {
      const startDate = new Date(today.getFullYear(), 0, 1);
      return withUrlDate({
        id: presetId,
        period: 'year',
        date: format(today),
        selectedDate: today,
        startDate,
        endDate: today,
      });
    }
    default:
      throw new Error(`Unknown preset date range: ${presetId as string}`);
  }
}

export function getPresetIdFromPeriodAndDate(
  period: string,
  date: string,
  todayInput: Date = getToday(),
): PresetDateRangeId|null {
  try {
    let selectedDate: Date|null = null;
    let selectedDateRange: Date[]|null = null;
    const matchingPreset = PRESET_DATE_RANGES.find((preset) => {
      const resolvedPreset = resolvePresetDateRange(preset.id, todayInput);
      if (resolvedPreset.period !== period) {
        return false;
      }

      if (resolvedPreset.date === date) {
        return true;
      }

      if (period !== 'range') {
        selectedDate = selectedDate || parseDate(date);
        return datesAreInTheSamePeriod(selectedDate, resolvedPreset.selectedDate, period);
      }

      selectedDateRange = selectedDateRange || Periods.parse(period, date).getDateRange();
      const presetDateRange = [resolvedPreset.startDate, resolvedPreset.endDate];

      return selectedDateRange[0].getTime() === presetDateRange[0].getTime()
        && selectedDateRange[1].getTime() === presetDateRange[1].getTime();
    });

    return matchingPreset?.id || getTokenPresetIdFromPeriodAndDate(period, date);
  } catch {
    return getTokenPresetIdFromPeriodAndDate(period, date);
  }
}
