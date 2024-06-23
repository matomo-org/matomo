/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import Periods from '../Periods/Periods';

let originalTitle: string;

const { piwik, broadcast, piwikHelper } = window;

piwik.helper = piwikHelper;
piwik.broadcast = broadcast;

piwik.updateDateInTitle = function updateDateInTitle(date: string, period: string) {
  if (!$('.top_controls #periodString').length) {
    return;
  }

  // Cache server-rendered page title
  originalTitle = originalTitle || document.title;

  if (originalTitle.indexOf(piwik.siteName) === 0) {
    const dateString = ` - ${Periods.parse(period, date).getPrettyString()} `;
    document.title = `${piwik.siteName}${dateString}${originalTitle.slice(piwik.siteName.length)}`;
  }
};

piwik.hasUserCapability = function hasUserCapability(capability: string) {
  return Array.isArray(piwik.userCapabilities)
    && piwik.userCapabilities.indexOf(capability) !== -1;
};

piwik.on = function addMatomoEventListener(eventName: string, listener: WrappedEventListener) {
  function listenerWrapper(evt: Event) {
    listener(...(evt as CustomEvent<any[]>).detail); // eslint-disable-line
  }

  listener.wrapper = listenerWrapper;

  window.addEventListener(eventName, listenerWrapper);
};

piwik.off = function removeMatomoEventListener(eventName: string, listener: WrappedEventListener) {
  if (listener.wrapper) {
    window.removeEventListener(eventName, listener.wrapper);
  }
};

piwik.postEvent = function postMatomoEvent(
  eventName: string,
  ...args: any[] // eslint-disable-line
): void {
  const event = new CustomEvent(eventName, { detail: args });
  window.dispatchEvent(event);
};

const Matomo = piwik;
export default Matomo;
