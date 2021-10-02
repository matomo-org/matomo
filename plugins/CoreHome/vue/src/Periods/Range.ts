import translate from '../translate';
import Periods from './Periods';
import {
  parseDate,
  format,
  getToday,
  todayIsInRange,
} from './utilities';

export default class RangePeriod {
  constructor(public readonly startDate: Date, public readonly endDate: Date, public readonly childPeriodType: string) {}

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
}

Periods.addCustomPeriod('range', RangePeriod);
