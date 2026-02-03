/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { translate } from '../translate';
import Periods from './Periods';
import { parseDate, todayIsInRange } from './utilities';

export default class QuarterPeriod {
  constructor(private dateInPeriod: Date) {}

  static parse(strDate: string): QuarterPeriod {
    return new QuarterPeriod(parseDate(strDate));
  }

  static getDisplayText(): string {
    return translate('Intl_PeriodQuarter');
  }

  getQuarterNumber(): number {
    return Math.ceil((this.dateInPeriod.getMonth() + 1) / 3);
  }

  getPrettyString(): string {
    const quarterNum = this.getQuarterNumber();
    const year = this.dateInPeriod.getFullYear();
    return `Q${quarterNum} ${year}`;
  }

  getDateRange(): Date[] {
    const quarterNum = this.getQuarterNumber();
    const year = this.dateInPeriod.getFullYear();

    // Start month: Q1=0, Q2=3, Q3=6, Q4=9 (0-indexed)
    const startMonth = (quarterNum - 1) * 3;

    const startDate = new Date(year, startMonth, 1);
    const endDate = new Date(year, startMonth + 3, 0); // Last day of end month

    return [startDate, endDate];
  }

  containsToday(): boolean {
    return todayIsInRange(this.getDateRange());
  }
}

Periods.addCustomPeriod('quarter', QuarterPeriod);
