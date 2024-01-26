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

export function getWeekNumber(date: Date): number {
  // Algorithm from https://www.w3resource.com/javascript-exercises/javascript-date-exercise-24.php
  // and updated based on http://www.java2s.com/example/nodejs/date/get-the-iso-week-date-week-number.html
  // for legibility

  // Create a copy of the date object
  const dt = new Date(date.valueOf());

  // ISO week date weeks start on Monday so correct the day number
  const dayNr = (date.getDay() + 6) % 7;

  // ISO 8601 states that week 1 is the week with the first thursday of that year.
  // Set the target date to the thursday in the target week
  dt.setDate(dt.getDate() - dayNr + 3);

  // Store the millisecond value of the target date
  const firstThursdayUTC = dt.valueOf();

  // Set the target to the first Thursday of the year
  // First set the target to january first
  dt.setMonth(0, 1);
  // Not a Thursday? Correct the date to the next Thursday
  if (dt.getDay() !== 4) {
    const daysToNextThursday = ((4 - dt.getDay()) + 7) % 7;
    dt.setMonth(0, 1 + daysToNextThursday);
  }

  // The week number is the number of weeks between the
  // first Thursday of the year and the Thursday in the target week
  return 1 + Math.ceil((firstThursdayUTC - dt.valueOf()) / (7 * 24 * 3600 * 1000 /* 1 week */));
}

// check whether two dates are in the same period, e.g. a week, a month or a year
export function datesAreInTheSamePeriod(date1: Date, date2: Date, period: string): boolean {
  const year1 = date1.getFullYear();
  const month1 = date1.getMonth();
  const day1 = date1.getDate();
  const week1 = getWeekNumber(date1);

  const year2 = date2.getFullYear();
  const month2 = date2.getMonth();
  const day2 = date2.getDate();
  const week2 = getWeekNumber(date2);

  switch (period) {
    case 'day':
      return year1 === year2 && month1 === month2 && day1 === day2;
    case 'week':
      return year1 === year2 && week1 === week2;
    case 'month':
      return year1 === year2 && month1 === month2;
    case 'year':
      return year1 === year2;
    default:
      return false;
  }
}
