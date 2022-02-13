/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export function format(date: Date): string {
  return $.datepicker.formatDate('yy-mm-dd', date);
}

export function getToday(): Date {
  const date = new Date(Date.now());

  // undo browser timezone
  date.setTime(date.getTime() + date.getTimezoneOffset() * 60 * 1000);

  // apply Matomo site timezone (if it exists)
  date.setHours(date.getHours() + ((window.piwik.timezoneOffset || 0) / 3600));

  // get rid of hours/minutes/seconds/etc.
  date.setHours(0);
  date.setMinutes(0);
  date.setSeconds(0);
  date.setMilliseconds(0);
  return date;
}

export function parseDate(date: string|Date): Date {
  if (date instanceof Date) {
    return date;
  }

  const strDate = decodeURIComponent(date).trim();
  if (strDate === '') {
    throw new Error('Invalid date, empty string.');
  }

  if (strDate === 'today'
    || strDate === 'now'
  ) {
    return getToday();
  }

  if (strDate === 'yesterday'
    // note: ignoring the 'same time' part since the frontend doesn't care about the time
    || strDate === 'yesterdaySameTime'
  ) {
    const yesterday = getToday();
    yesterday.setDate(yesterday.getDate() - 1);
    return yesterday;
  }

  if (strDate.match(/last[ -]?week/i)) {
    const lastWeek = getToday();
    lastWeek.setDate(lastWeek.getDate() - 7);
    return lastWeek;
  }

  if (strDate.match(/last[ -]?month/i)) {
    const lastMonth = getToday();
    lastMonth.setDate(1);
    lastMonth.setMonth(lastMonth.getMonth() - 1);
    return lastMonth;
  }

  if (strDate.match(/last[ -]?year/i)) {
    const lastYear = getToday();
    lastYear.setFullYear(lastYear.getFullYear() - 1);
    return lastYear;
  }

  return $.datepicker.parseDate('yy-mm-dd', strDate);
}

export function todayIsInRange(dateRange: Date[]): boolean {
  if (dateRange.length !== 2) {
    return false;
  }

  if (getToday() >= dateRange[0] && getToday() <= dateRange[1]) {
    return true;
  }

  return false;
}
