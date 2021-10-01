import translate from "../translate";
import Periods from "./Periods";
import { parseDate, todayIsInRange } from './utilities';

export default class MonthPeriod {
  constructor(private dateInPeriod: Date) {}

  static parse(strDate: string): MonthPeriod {
    return new MonthPeriod(parseDate(strDate));
  }

  static getDisplayText() {
    return translate('Intl_PeriodMonth');
  }

  getPrettyString() {
    return translate('Intl_Month_Long_StandAlone_' + (this.dateInPeriod.getMonth() + 1)) + ' ' +
      this.dateInPeriod.getFullYear();
  }

  getDateRange() {
    const startMonth = new Date(this.dateInPeriod.getTime());
    startMonth.setDate(1);

    const endMonth = new Date(this.dateInPeriod.getTime());
    endMonth.setDate(1);
    endMonth.setMonth(endMonth.getMonth() + 1);
    endMonth.setDate(0);

    return [startMonth, endMonth];
  }

  containsToday() {
    return todayIsInRange(this.getDateRange());
  }
}

Periods.addCustomPeriod('month', MonthPeriod);
