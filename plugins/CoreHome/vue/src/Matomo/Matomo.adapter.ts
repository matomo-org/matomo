/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import Matomo from './Matomo';

function piwikService() {
  return Matomo;
}

angular.module('piwikApp.service').service('piwik', piwikService);

function initPiwikService(piwik, $rootScope) {
  // overwrite $rootScope so all events also go through Matomo.postEvent(...) too.
  $rootScope.$oldEmit = $rootScope.$emit;
  $rootScope.$emit = function emitWrapper(name: string, ...args: any[]) { // eslint-disable-line
    Matomo.postEvent(name, ...args);
  };

  $rootScope.$on('$locationChangeSuccess', piwik.updatePeriodParamsFromUrl);
}

initPiwikService.$inject = ['piwik', '$rootScope'];

angular.module('piwikApp.service').run(initPiwikService);
