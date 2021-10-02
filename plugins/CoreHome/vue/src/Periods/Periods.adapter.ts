import Periods from './Periods';
import RangePeriod from './Range';
import { parseDate, format, todayIsInRange } from './utilities';

piwik.addCustomPeriod = Periods.addCustomPeriod.bind(Periods);

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

angular.module('piwikApp.service').factory('piwikPeriods', piwikPeriods);
