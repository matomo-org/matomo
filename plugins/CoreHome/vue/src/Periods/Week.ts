import translate from "../translate";
import Periods from "./Periods";
import { parseDate, format, todayIsInRange } from './utilities';

export default class WeekPeriod {
  constructor(private dateInPeriod: Date) {}

  static parse(strDate: string): WeekPeriod {
    return new WeekPeriod(parseDate(strDate));
  }

  static getDisplayText() {
    return translate('Intl_PeriodWeek');
  }

  getPrettyString() {
    var weekDates = this.getDateRange();
    var startWeek = format(weekDates[0]);
    var endWeek = format(weekDates[1]);

    return translate('General_DateRangeFromTo', [startWeek, endWeek]);
    return format(this.dateInPeriod);
  }

  getDateRange() {
    var daysToMonday = (this.dateInPeriod.getDay() + 6) % 7;

    var startWeek = new Date(this.dateInPeriod.getTime());
    startWeek.setDate(this.dateInPeriod.getDate() - daysToMonday);

    var endWeek = new Date(startWeek.getTime());
    endWeek.setDate(startWeek.getDate() + 6);

    return [startWeek, endWeek];
  }

  containsToday() {
    return todayIsInRange(this.getDateRange());
  }
}

Periods.addCustomPeriod('week', WeekPeriod);
