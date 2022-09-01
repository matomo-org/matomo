/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import Periods from './Periods';
import './Day';
import './Week';
import './Month';
import './Year';
import RangePeriod from './Range';
import { parseDate } from './utilities';

describe('CoreHome/Periods', () => {
  function clearDate(strDate: Date|string):Date {
    const date = new Date(strDate);
    date.setHours(0);
    date.setMinutes(0);
    date.setSeconds(0);
    date.setMilliseconds(0);

    return date;
  }

  let originalDateNow: (() => number)|null;

  beforeEach(() => {
    originalDateNow = null;
    window.piwik.timezoneOffset = 0;
  });

  afterEach(() => {
    if (originalDateNow) {
      Date.now = originalDateNow;
    }
  });

  it('should get daterange for day', () => {
    const day = '2021-03-10';

    const result = Periods.parse('day', day).getDateRange();
    const expected = [clearDate(day), clearDate(day)];

    expect(result).toEqual(expected);
  })

  it('should get daterange for week', function() {
    const day = '2021-03-10';
    const monday = '2021-03-08';
    const sunday = '2021-03-14';

    const result = Periods.parse('week', day).getDateRange();
    const expected = [clearDate(monday), clearDate(sunday)];

    expect(result).toEqual(expected);
  });

  it('should get daterange for month', function() {
    const day = '2021-03-10';
    const first = '2021-03-01';
    const last = '2021-03-31';

    const result = Periods.parse('month', day).getDateRange();
    const expected = [clearDate(first), clearDate(last)];

    expect(result).toEqual(expected);
  });

  it('should get daterange for month for date 31th', function() {
    const day = '2021-03-31';
    const first = '2021-03-01';
    const last = '2021-03-31';

    const result = Periods.parse('month', day).getDateRange();
    const expected = [clearDate(first), clearDate(last)];

    expect(result).toEqual(expected);
  });

  it('should get daterange for year', function() {
    const day = '2021-03-10';
    const first = '2021-01-01';
    const last = '2021-12-31';

    const result = Periods.parse('year', day).getDateRange();
    const expected = [clearDate(first), clearDate(last)];

    expect(result).toEqual(expected);
  });

  it('should get daterange for year for date 31th december', function() {
    const day = '2021-12-31';
    const first = '2021-01-01';
    const last = '2021-12-31';

    const result = Periods.parse('year', day).getDateRange();
    const expected = [clearDate(first), clearDate(last)];

    expect(result).toEqual(expected);
  });

  it('should get proper month rangeperiod when date is 31th march', function() {
    const day = '2021-03-31';
    const first = '2021-02-01';
    const last = '2021-03-31';

    const result = RangePeriod.getLastNRange('month', 2, day);

    expect(result.startDate).toEqual(clearDate(first));
    expect(result.endDate).toEqual(clearDate(last));
  });

  it('should parse last month properly when date is 31th march', function() {
    originalDateNow = Date.now;
    Date.now = function mockNow() {
      return clearDate('2021-03-31').getTime();
    };

    const result = parseDate('last month');

    expect(result.getMonth()).toEqual(1); // 1 is February
  });

  it('should parse last month properly', function() {
    originalDateNow = Date.now;
    Date.now = function mockNow() {
      return clearDate('2021-03-10').getTime();
    };

    const result = parseDate('last month');

    expect(result.getMonth()).toEqual(1); // 1 is February
  });

  it('should contains today for daterange if it contains', function() {
    const day = '2021-03-10';

    originalDateNow = Date.now;
    Date.now = function() {
      return clearDate(day).getTime();
    };

    const result = Periods.parse('week', day).containsToday();

    expect(result).toBe(true);
  });

  it('should not contains today for daterange if it not contains', function() {
    const today = '2021-03-10';
    const day = '2021-03-17';

    originalDateNow = Date.now;
    Date.now = function() {
      return clearDate(today).getTime();
    };

    const result = Periods.parse('week', day).containsToday();

    expect(result).toBe(false);
  });
});
