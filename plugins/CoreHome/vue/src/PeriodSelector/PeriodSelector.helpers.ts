/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  Periods,
  Range,
  format,
  parseDate,
} from '../Periods';
import { RANGE_PERIOD } from './PeriodSelector.types';

export interface CompareParamsState {
  isComparing: boolean | null;
  comparePeriodType: string;
  compareStartDate: string;
  compareEndDate: string;
  selectedPeriod: string;
  committedAnchorDate: Date | null;
  appliedRangeStartDate: string | null;
  appliedRangeEndDate: string | null;
}

export function isKeyboardExpandEvent(event: MouseEvent | KeyboardEvent): boolean {
  return event.detail === 0;
}

export function stripCompareDateParams(
  baseUrlParams: Record<string, unknown>,
): Record<string, unknown> {
  const paramsWithoutCompare = { ...baseUrlParams };
  // Intentionally keep compareSegments. Only date-period compare params are reset here.
  delete paramsWithoutCompare.comparePeriods;
  delete paramsWithoutCompare.comparePeriodType;
  delete paramsWithoutCompare.compareDates;
  return paramsWithoutCompare;
}

export function shiftDateByPeriod(sourceDate: Date, period: string, direction: number): Date {
  const shiftedDate = new Date(sourceDate.getTime());

  switch (period) {
    case 'day':
      shiftedDate.setDate(shiftedDate.getDate() + direction);
      break;
    case 'week':
      shiftedDate.setDate(shiftedDate.getDate() + direction * 7);
      break;
    case 'month':
      shiftedDate.setMonth(shiftedDate.getMonth() + direction);
      break;
    case 'year':
      shiftedDate.setFullYear(shiftedDate.getFullYear() + direction);
      break;
    default:
      break;
  }

  return shiftedDate;
}

export function clampDateToBounds(date: Date, minDate: Date, maxDate: Date): Date {
  const clampedDate = new Date(date.getTime());

  if (clampedDate < minDate) {
    clampedDate.setTime(minDate.getTime());
  }

  if (clampedDate > maxDate) {
    clampedDate.setTime(maxDate.getTime());
  }

  return clampedDate;
}

export function getSelectedComparisonParamsForState(
  state: CompareParamsState,
): Record<string, unknown> {
  if (!state.isComparing) {
    return {};
  }

  if (state.comparePeriodType === 'custom') {
    return {
      comparePeriods: [RANGE_PERIOD],
      comparePeriodType: 'custom',
      compareDates: [`${state.compareStartDate},${state.compareEndDate}`],
    };
  }

  if (state.comparePeriodType === 'previousPeriod') {
    if (state.selectedPeriod === RANGE_PERIOD) {
      const currentStartRange = parseDate(state.appliedRangeStartDate!);
      const currentEndRange = parseDate(state.appliedRangeEndDate!);
      const previousPeriodEndDate = Range.getLastNRange('day', 2, currentStartRange).startDate;
      const selectedRangeLengthInDays = Math.floor(
        (currentEndRange.valueOf() - currentStartRange.valueOf()) / 86400000,
      );
      const previousRange = Range.getLastNRange(
        'day',
        1 + selectedRangeLengthInDays,
        previousPeriodEndDate,
      );
      const previousRangeDateValue = `${format(previousRange.startDate)},${format(previousRange.endDate)}`;

      return {
        comparePeriods: [RANGE_PERIOD],
        comparePeriodType: 'previousPeriod',
        compareDates: [previousRangeDateValue],
      };
    }

    const previousPeriodStartDate = Range.getLastNRange(
      state.selectedPeriod,
      2,
      state.committedAnchorDate!,
    ).startDate;

    return {
      comparePeriods: [state.selectedPeriod],
      comparePeriodType: 'previousPeriod',
      compareDates: [format(previousPeriodStartDate)],
    };
  }

  if (state.comparePeriodType === 'previousYear') {
    const selectedDateValue = state.selectedPeriod === RANGE_PERIOD
      ? `${state.appliedRangeStartDate},${state.appliedRangeEndDate}`
      : format(state.committedAnchorDate!);

    const previousYearComparisonDateRange = Periods.parse(
      state.selectedPeriod,
      selectedDateValue,
    ).getDateRange();
    previousYearComparisonDateRange[0].setFullYear(
      previousYearComparisonDateRange[0].getFullYear() - 1,
    );
    previousYearComparisonDateRange[1].setFullYear(
      previousYearComparisonDateRange[1].getFullYear() - 1,
    );

    if (state.selectedPeriod === RANGE_PERIOD) {
      return {
        comparePeriods: [RANGE_PERIOD],
        comparePeriodType: 'previousYear',
        compareDates: [
          `${format(previousYearComparisonDateRange[0])},${format(previousYearComparisonDateRange[1])}`,
        ],
      };
    }

    return {
      comparePeriods: [state.selectedPeriod],
      comparePeriodType: 'previousYear',
      compareDates: [format(previousYearComparisonDateRange[0])],
    };
  }

  return {};
}
