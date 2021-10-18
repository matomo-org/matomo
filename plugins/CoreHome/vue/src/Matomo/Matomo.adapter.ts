/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import Matomo from './Matomo';
import {IAngularEvent} from 'angular';

function piwikService() {
  return Matomo;
}

angular.module('piwikApp.service').service('piwik', piwikService);

function initPiwikService(piwik, $rootScope) {
  // overwrite $rootScope so all events also go through Matomo.postEvent(...) too.
  const oldEmit = $rootScope.$emit;
  $rootScope.$emit = function emitWrapper(name: string, ...args: any[]) {
    oldEmit.call(this, name, ...args);
    Matomo.postEvent(name, ...args);
  };

  const oldOn = $rootScope.$on;
  $rootScope.$on = function onWrapper(name: string, listener: (event: IAngularEvent, ...args: any[]) => any) {
    const deregister = oldOn.call(this, name, listener);
    Matomo.on(name, listener);

    return function deregisterBoth() {
      deregister();
      Matomo.off(name, listener);
    };
  };

  $rootScope.$on('$locationChangeSuccess', piwik.updatePeriodParamsFromUrl);
}

initPiwikService.$inject = ['piwik', '$rootScope'];

angular.module('piwikApp.service').run(initPiwikService);
