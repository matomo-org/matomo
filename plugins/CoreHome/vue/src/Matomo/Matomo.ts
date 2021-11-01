/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IAngularEvent } from 'angular';
import Periods from '../Periods/Periods';

interface EventListener {
  (...args: any[]): any; // eslint-disable-line

  wrapper: (evt: Event) => void; // eslint-disable-line
}

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
  ...args: any[], // eslint-disable-line
): IAngularEvent {
  piwik.postEventNoEmit(eventName, ...args);

  // required until angularjs is removed
  return (piwik.helper.getAngularDependency('$rootScope') as any) // eslint-disable-line
    .$oldEmit(eventName, ...args);
};

piwik.postEventNoEmit = function postEventNoEmit(
  eventName: string,
  ...args: any[], // eslint-disable-line
): void {
  const event = new CustomEvent(eventName, { detail: args });
  window.dispatchEvent(event);
};

const Matomo = piwik;
export default Matomo;
