import translate from "../translate";
import Periods from "./Periods";
import { parseDate, todayIsInRange } from './utilities';

export default class YearPeriod {
  constructor(private dateInPeriod: Date) {}

  static parse(strDate: string): YearPeriod {
    return new YearPeriod(parseDate(strDate));
  }

  static getDisplayText() {
    return translate('Intl_PeriodYear');
  }

  getPrettyString() {
    return this.dateInPeriod.getFullYear();
  }

  getDateRange() {
    var startYear = new Date(this.dateInPeriod.getTime());
    startYear.setMonth(0);
    startYear.setDate(1);

    var endYear = new Date(this.dateInPeriod.getTime());
    endYear.setMonth(12);
    endYear.setDate(0);

    return [startYear, endYear];
  }

  containsToday() {
    return todayIsInRange(this.getDateRange());
  }
}

Periods.addCustomPeriod('year', YearPeriod);
