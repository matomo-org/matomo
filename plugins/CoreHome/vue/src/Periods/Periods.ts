/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

interface Period {
  getPrettyString(): string;
  getDateRange(): Date[];
  containsToday(): boolean;
}

interface PeriodClass {
  parse(strDate: string|Date): Period;
  getDisplayText(): string;
}

/**
 * Matomo period management service for the frontend.
 *
 * Usage:
 *
 *     var DayPeriod = matomoPeriods.get('day');
 *     var day = new DayPeriod(new Date());
 *
 * or
 *
 *     var day = matomoPeriods.parse('day', '2013-04-05');
 *
 * Adding custom periods:
 *
 * To add your own period to the frontend, create a period class for it
 * w/ the following methods:
 *
 * - **getPrettyString()**: returns a human readable display string for the period.
 * - **getDateRange()**: returns an array w/ two elements, the first being the start
 *                       Date of the period, the second being the end Date. The dates
 *                       must be Date objects, not strings, and are inclusive.
 * - **containsToday()**: returns true if the date period contains today. False if not.
 * - (_static_) **parse(strDate)**: creates a new instance of this period from the
 *                                  value of the 'date' query parameter.
 * - (_static_) **getDisplayText**: returns translated text for the period, eg, 'month',
 *                                  'week', etc.
 *
 * Then call Periods.addCustomPeriod w/ your period class:
 *
 *     Periods.addCustomPeriod('mycustomperiod', MyCustomPeriod);
 *
 * NOTE: currently only single date periods like day, week, month year can
 *       be extended. Other types of periods that require a special UI to
 *       view/edit aren't, since there is currently no way to use a
 *       custom UI for a custom period.
 */
class Periods {
  periods: {[name: string]: PeriodClass} = {};

  periodOrder: string[] = [];

  addCustomPeriod(name: string, periodClass: PeriodClass) {
    if (this.periods[name]) {
      throw new Error(`The "${name}" period already exists! It cannot be overridden.`);
    }

    this.periods[name] = periodClass;
    this.periodOrder.push(name);
  }

  getAllLabels(): string[] {
    return Array<string>().concat(this.periodOrder);
  }

  get(strPeriod: string): PeriodClass {
    const periodClass = this.periods[strPeriod];
    if (!periodClass) {
      throw new Error(`Invalid period label: ${strPeriod}`);
    }
    return periodClass;
  }

  parse(strPeriod: string, strDate: string): Period {
    return this.get(strPeriod).parse(strDate);
  }

  isRecognizedPeriod(strPeriod: string): boolean {
    return !!this.periods[strPeriod];
  }
}

export default new Periods();
