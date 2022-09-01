/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { translate } from '../translate';
import Periods from './Periods';
import {
  parseDate,
  format,
  getToday,
  todayIsInRange,
} from './utilities';

export default class RangePeriod {
  constructor(
    public readonly startDate: Date,
    public readonly endDate: Date,
    public readonly childPeriodType: string,
  ) {}

  /**
   * Returns a range representing the last N childPeriodType periods, including the current one.
   */
  static getLastNRange(
    childPeriodType: string,
    strAmount: string|number,
    strEndDate?: Date|string,
  ): RangePeriod {
    const nAmount = Math.max(parseInt(strAmount.toString(), 10) - 1, 0);
    if (Number.isNaN(nAmount)) {
      throw new Error('Invalid range strAmount');
    }

    let endDate = strEndDate ? parseDate(strEndDate) : getToday();

    let startDate = new Date(endDate.getTime());
    if (childPeriodType === 'day') {
      startDate.setDate(startDate.getDate() - nAmount);
    } else if (childPeriodType === 'week') {
      startDate.setDate(startDate.getDate() - (nAmount * 7));
    } else if (childPeriodType === 'month') {
      startDate.setDate(1);
      startDate.setMonth(startDate.getMonth() - nAmount);
    } else if (childPeriodType === 'year') {
      startDate.setFullYear(startDate.getFullYear() - nAmount);
    } else {
      throw new Error(`Unknown period type '${childPeriodType}'.`);
    }

    if (childPeriodType !== 'day') {
      const startPeriod = Periods.periods[childPeriodType].parse(startDate);
      const endPeriod = Periods.periods[childPeriodType].parse(endDate);

      [startDate] = startPeriod.getDateRange();
      [, endDate] = endPeriod.getDateRange();
    }

    const firstWebsiteDate = new Date(1991, 7, 6);
    if (startDate.getTime() - firstWebsiteDate.getTime() < 0) {
      switch (childPeriodType) {
        case 'year':
          startDate = new Date(1992, 0, 1);
          break;
        case 'month':
          startDate = new Date(1991, 8, 1);
          break;
        case 'week':
          startDate = new Date(1991, 8, 12);
          break;
        case 'day':
        default:
          startDate = firstWebsiteDate;
          break;
      }
    }

    return new RangePeriod(startDate, endDate, childPeriodType);
  }

  /**
   * Returns a range representing a specific child date range counted back from the end date
   *
   * @param childPeriodType Type of the period, eg. day, week, year
   * @param rangeEndDate
   * @param countBack Return only the child date range for this specific period number
   * @returns {RangePeriod}
   */
  static getLastNRangeChild(
    childPeriodType: string,
    rangeEndDate: Date|string,
    countBack: number,
  ): RangePeriod {
    const ed = rangeEndDate ? parseDate(rangeEndDate) : getToday();
    let startDate = new Date(ed.getTime());
    let endDate = new Date(ed.getTime());

    if (childPeriodType === 'day') {
      startDate.setDate(startDate.getDate() - countBack);
      endDate.setDate(endDate.getDate() - countBack);
    } else if (childPeriodType === 'week') {
      startDate.setDate(startDate.getDate() - (countBack * 7));
      endDate.setDate(endDate.getDate() - (countBack * 7));
    } else if (childPeriodType === 'month') {
      startDate.setDate(1);
      startDate.setMonth(startDate.getMonth() - countBack);
      endDate.setDate(1);
      endDate.setMonth(endDate.getMonth() - countBack);
    } else if (childPeriodType === 'year') {
      startDate.setFullYear(startDate.getFullYear() - countBack);
      endDate.setFullYear(endDate.getFullYear() - countBack);
    } else {
      throw new Error(`Unknown period type '${childPeriodType}'.`);
    }

    if (childPeriodType !== 'day') {
      const startPeriod = Periods.periods[childPeriodType].parse(startDate);
      const endPeriod = Periods.periods[childPeriodType].parse(endDate);

      [startDate] = startPeriod.getDateRange();
      [, endDate] = endPeriod.getDateRange();
    }

    const firstWebsiteDate = new Date(1991, 7, 6);
    if (startDate.getTime() - firstWebsiteDate.getTime() < 0) {
      switch (childPeriodType) {
        case 'year':
          startDate = new Date(1992, 0, 1);
          break;
        case 'month':
          startDate = new Date(1991, 8, 1);
          break;
        case 'week':
          startDate = new Date(1991, 8, 12);
          break;
        case 'day':
        default:
          startDate = firstWebsiteDate;
          break;
      }
    }

    return new RangePeriod(startDate, endDate, childPeriodType);
  }

  static parse(strDate: string, childPeriodType = 'day'): RangePeriod {
    if (/^previous/.test(strDate)) {
      const endDate = RangePeriod.getLastNRange(childPeriodType, '2').startDate;
      return RangePeriod.getLastNRange(childPeriodType, strDate.substring(8), endDate);
    }

    if (/^last/.test(strDate)) {
      return RangePeriod.getLastNRange(childPeriodType, strDate.substring(4));
    }

    const parts = decodeURIComponent(strDate).split(',');
    return new RangePeriod(parseDate(parts[0]), parseDate(parts[1]), childPeriodType);
  }

  static getDisplayText(): string {
    return translate('General_DateRangeInPeriodList');
  }

  getPrettyString(): string {
    const start = format(this.startDate);
    const end = format(this.endDate);
    return translate('General_DateRangeFromTo', [start, end]);
  }

  getDateRange(): Date[] {
    return [this.startDate, this.endDate];
  }

  containsToday(): boolean {
    return todayIsInRange(this.getDateRange());
  }

  getDayCount(): number {
    return (Math.ceil((this.endDate.getTime() - this.startDate.getTime()) / (1000 * 3600 * 24))
            + 1);
  }
}

Periods.addCustomPeriod('range', RangePeriod);
