/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { translate } from '../translate';
import Periods from './Periods';
import { parseDate, format, todayIsInRange } from './utilities';

export default class WeekPeriod {
  constructor(private dateInPeriod: Date) {}

  static parse(strDate: string): WeekPeriod {
    return new WeekPeriod(parseDate(strDate));
  }

  static getDisplayText(): string {
    return translate('Intl_PeriodWeek');
  }

  getPrettyString(): string {
    const weekDates = this.getDateRange();
    const startWeek = format(weekDates[0]);
    const endWeek = format(weekDates[1]);

    return translate('General_DateRangeFromTo', [startWeek, endWeek]);
  }

  getDateRange(): Date[] {
    const daysToMonday = (this.dateInPeriod.getDay() + 6) % 7;

    const startWeek = new Date(this.dateInPeriod.getTime());
    startWeek.setDate(this.dateInPeriod.getDate() - daysToMonday);

    const endWeek = new Date(startWeek.getTime());
    endWeek.setDate(startWeek.getDate() + 6);

    return [startWeek, endWeek];
  }

  containsToday(): boolean {
    return todayIsInRange(this.getDateRange());
  }
}

Periods.addCustomPeriod('week', WeekPeriod);
