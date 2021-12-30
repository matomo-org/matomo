/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IAngularEvent, IRootScopeService } from 'angular';
import Matomo from './Matomo';

function piwikService() {
  return Matomo;
}

window.angular.module('piwikApp.service').service('piwik', piwikService);

function initPiwikService(piwik: PiwikGlobal, $rootScope: IRootScopeService) {
  // overwrite $rootScope so all events also go through Matomo.postEvent(...) too.
  ($rootScope as any).$oldEmit = $rootScope.$emit; // eslint-disable-line
  $rootScope.$emit = function emitWrapper(name: string, ...args: any[]): IAngularEvent { // eslint-disable-line
    Matomo.postEventNoEmit(name, ...args);
    return (this as any).$oldEmit(name, ...args); // eslint-disable-line
  };

  ($rootScope as any).$oldBroadcast = $rootScope.$broadcast; // eslint-disable-line
  $rootScope.$broadcast = function broadcastWrapper(name: string, ...args: any[]): IAngularEvent { // eslint-disable-line
    Matomo.postEventNoEmit(name, ...args);
    return (this as any).$oldBroadcast(name, ...args); // eslint-disable-line
  };

  $rootScope.$on('$locationChangeSuccess', piwik.updatePeriodParamsFromUrl);
}

initPiwikService.$inject = ['piwik', '$rootScope'];

window.angular.module('piwikApp.service').run(initPiwikService);
