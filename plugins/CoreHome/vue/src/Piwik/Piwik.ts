/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import PiwikUrl from '../PiwikUrl/PiwikUrl';
import Periods from '../Periods/Periods';
import { format } from '../Periods/utilities';

let originalTitle: string;

const { piwik, broadcast, piwikHelper } = window;

piwik.helper = piwikHelper;
piwik.broadcast = broadcast;

function isValidPeriod(periodStr: string, dateStr: string) {
  try {
    Periods.parse(periodStr, dateStr);
    return true;
  } catch (e) {
    return false;
  }
}

piwik.updatePeriodParamsFromUrl = function updatePeriodParamsFromUrl() {
  let date = PiwikUrl.getSearchParam('date');
  const period = PiwikUrl.getSearchParam('period');
  if (!isValidPeriod(period, date)) {
    // invalid data in URL
    return;
  }

  if (piwik.period === period && piwik.currentDateString === date) {
    // this period / date is already loaded
    return;
  }

  piwik.period = period;

  const dateRange = Periods.parse(period, date).getDateRange();
  piwik.startDateString = format(dateRange[0]);
  piwik.endDateString = format(dateRange[1]);

  piwik.updateDateInTitle(date, period);

  // do not set anything to previousN/lastN, as it's more useful to plugins
  // to have the dates than previousN/lastN.
  if (piwik.period === 'range') {
    date = `${piwik.startDateString},${piwik.endDateString}`;
  }

  piwik.currentDateString = date;
};

piwik.updateDateInTitle = function updateDateInTitle(date: string, period: string) {
  if (!$('.top_controls #periodString').length) {
    return;
  }

  // Cache server-rendered page title
  originalTitle = originalTitle || document.title;

  if (originalTitle.indexOf(piwik.siteName) === 0) {
    const dateString = ` - ${Periods.parse(period, date).getPrettyString()} `;
    document.title = `${piwik.siteName}${dateString}${originalTitle.substr(piwik.siteName.length)}`;
  }
};

piwik.hasUserCapability = function hasUserCapability(capability: string) {
  return window.angular.isArray(piwik.userCapabilities)
    && piwik.userCapabilities.indexOf(capability) !== -1;
};

const Piwik = piwik;
export default Piwik;
