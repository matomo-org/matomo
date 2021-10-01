import translate from "../translate";
import Periods from "./Periods";
import { parseDate, format, todayIsInRange } from './utilities';

export default class DayPeriod {
  constructor(private dateInPeriod: Date) {}

  static parse(strDate: string): DayPeriod {
    return new DayPeriod(parseDate(strDate));
  }

  static getDisplayText() {
    return translate('Intl_PeriodDay');
  }

  getPrettyString() {
    return format(this.dateInPeriod);
  }

  getDateRange() {
    return [new Date(this.dateInPeriod.getTime()), new Date(this.dateInPeriod.getTime())];
  }

  containsToday() {
    return todayIsInRange(this.getDateRange());
  }
}

Periods.addCustomPeriod('day', DayPeriod);
