/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

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
