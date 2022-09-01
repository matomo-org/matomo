/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { translate } from '../translate';
import Periods from './Periods';
import { parseDate, todayIsInRange } from './utilities';

export default class MonthPeriod {
  constructor(private dateInPeriod: Date) {}

  static parse(strDate: string): MonthPeriod {
    return new MonthPeriod(parseDate(strDate));
  }

  static getDisplayText(): string {
    return translate('Intl_PeriodMonth');
  }

  getPrettyString(): string {
    const month = translate(`Intl_Month_Long_StandAlone_${this.dateInPeriod.getMonth() + 1}`);
    return `${month} ${this.dateInPeriod.getFullYear()}`;
  }

  getDateRange(): Date[] {
    const startMonth = new Date(this.dateInPeriod.getTime());
    startMonth.setDate(1);

    const endMonth = new Date(this.dateInPeriod.getTime());
    endMonth.setDate(1);
    endMonth.setMonth(endMonth.getMonth() + 1);
    endMonth.setDate(0);

    return [startMonth, endMonth];
  }

  containsToday(): boolean {
    return todayIsInRange(this.getDateRange());
  }
}

Periods.addCustomPeriod('month', MonthPeriod);
