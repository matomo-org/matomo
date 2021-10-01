import Periods from './Periods';
import RangePeriod from './Range';
import { parseDate, format, todayIsInRange } from './utilities';

window['piwik'].addCustomPeriod = Periods.addCustomPeriod.bind(Periods);

export default function piwikPeriods() {
  return {
    getAllLabels: Periods.getAllLabels.bind(Periods),
    isRecognizedPeriod: Periods.isRecognizedPeriod,
    get: Periods.get,
    parse: Periods.parse,
    parseDate: parseDate,
    format: format,
    RangePeriod: RangePeriod,
    todayIsInRange: todayIsInRange
  };
}

angular.module('piwikApp.service').factory('piwikPeriods', piwikPeriods);
