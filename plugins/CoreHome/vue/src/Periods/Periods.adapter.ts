/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import Periods from './Periods';
import RangePeriod from './Range';
import { parseDate, format, todayIsInRange } from './utilities';

window.piwik.addCustomPeriod = Periods.addCustomPeriod.bind(Periods);

function piwikPeriods() {
  return {
    getAllLabels: Periods.getAllLabels.bind(Periods),
    isRecognizedPeriod: Periods.isRecognizedPeriod.bind(Periods),
    get: Periods.get.bind(Periods),
    parse: Periods.parse.bind(Periods),
    parseDate,
    format,
    RangePeriod,
    todayIsInRange,
  };
}

window.angular.module('piwikApp.service').factory('piwikPeriods', piwikPeriods);
