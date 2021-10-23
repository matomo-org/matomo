/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IAngularEvent } from 'angular';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import Periods from '../Periods/Periods';
import { format } from '../Periods/utilities';

interface EventListener {
  (...args: any[]): any; // eslint-disable-line

  wrapper: (evt: Event) => void; // eslint-disable-line
}

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
  let date = MatomoUrl.getSearchParam('date');
  const period = MatomoUrl.getSearchParam('period');
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

piwik.on = function addMatomoEventListener(eventName: string, listener: EventListener) {
  function listenerWrapper(evt: Event) {
    listener(...(evt as CustomEvent<any[]>).detail); // eslint-disable-line
  }

  listener.wrapper = listenerWrapper;

  window.addEventListener(eventName, listener);
};

piwik.off = function removeMatomoEventListener(eventName: string, listener: EventListener) {
  window.removeEventListener(eventName, listener.wrapper);
};

piwik.postEvent = function postMatomoEvent(
  eventName: string,
  ...args: any[] // eslint-disable-line
): IAngularEvent {
  const event = new CustomEvent(eventName, { detail: args });
  window.dispatchEvent(event);

  // required until angularjs is removed
  return (piwik.helper.getAngularDependency('$rootScope') as any) // eslint-disable-line
    .$oldEmit(eventName, ...args);
};

const Matomo = piwik;
export default Matomo;
