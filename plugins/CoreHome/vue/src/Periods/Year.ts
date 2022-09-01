/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { translate } from '../translate';
import Periods from './Periods';
import { parseDate, todayIsInRange } from './utilities';

export default class YearPeriod {
  constructor(private dateInPeriod: Date) {}

  static parse(strDate: string): YearPeriod {
    return new YearPeriod(parseDate(strDate));
  }

  static getDisplayText(): string {
    return translate('Intl_PeriodYear');
  }

  getPrettyString(): string {
    return this.dateInPeriod.getFullYear().toString();
  }

  getDateRange(): Date[] {
    const startYear = new Date(this.dateInPeriod.getTime());
    startYear.setMonth(0);
    startYear.setDate(1);

    const endYear = new Date(this.dateInPeriod.getTime());
    endYear.setMonth(12);
    endYear.setDate(0);

    return [startYear, endYear];
  }

  containsToday(): boolean {
    return todayIsInRange(this.getDateRange());
  }
}

Periods.addCustomPeriod('year', YearPeriod);
